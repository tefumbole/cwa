<?php

namespace App\Http\Controllers;

use App\Services\BeyondWasenderService;
use App\Support\SiteContent;
use App\Support\SystemTestGuide;
use App\Support\WhatsAppMessage;
use App\Support\WhatsAppPhone;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SystemTestController extends Controller
{
    public function show()
    {
        return view('system_test.form', [
            'sections' => SystemTestGuide::sections(),
        ]);
    }

    public function store(Request $request)
    {
        if (trim((string) $request->input('company_website')) !== '') {
            return redirect()->route('system-test.show');
        }

        $request->validate([
            'tester_name' => 'required|string|max:120',
            'tester_phone' => 'required|string|max:30',
            'summary' => 'nullable|string|max:5000',
        ]);

        try {
            $testerPhone = WhatsAppPhone::forWasender($request->input('tester_phone'));
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors([
                'tester_phone' => 'Enter a WhatsApp number, for example 675321739 or +237675321739.',
            ]);
        }

        $map = SystemTestGuide::checkMap();
        $posted = (array) $request->input('checks', []);
        $notes = (array) $request->input('notes', []);
        $rows = [];
        $counts = ['works' => 0, 'fails' => 0, 'skipped' => 0];

        foreach ($map as $id => $check) {
            $result = isset($posted[$id]) ? (string) $posted[$id] : 'skipped';
            if (! in_array($result, ['works', 'fails', 'skipped'], true)) {
                $result = 'skipped';
            }
            $counts[$result]++;
            $rows[] = [
                'id' => $id,
                'section' => $check['section'],
                'text' => $check['text'],
                'result' => $result,
                'note' => trim(mb_substr((string) ($notes[$id] ?? ''), 0, 500)),
            ];
        }

        $id = date('Ymd-His').'-'.Str::lower(Str::random(6));
        $report = [
            'id' => $id,
            'tester_name' => trim($request->input('tester_name')),
            'tester_phone' => $testerPhone,
            'tester_email' => '',
            'summary' => trim((string) $request->input('summary')),
            'counts' => $counts,
            'total' => count($rows),
            'rows' => $rows,
            'created_at' => date('Y-m-d H:i'),
        ];

        $dir = storage_path('app/system-tests');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        file_put_contents($dir.'/'.$id.'.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $whatsapp = app(BeyondWasenderService::class);
        $testerSent = $whatsapp->sendText($testerPhone, $this->whatsappSummary($report, false));
        $adminPhones = [];
        foreach ($this->adminPhones() as $adminPhone) {
            try {
                if (WhatsAppPhone::normalize($adminPhone) === WhatsAppPhone::normalize($testerPhone)) {
                    continue;
                }
            } catch (\InvalidArgumentException $e) {
                continue;
            }
            $adminPhones[] = $adminPhone;
            $whatsapp->sendText($adminPhone, $this->whatsappSummary($report, true));
        }

        $recipients = $this->reportRecipients();
        $mailed = false;
        if ($recipients) {
            try {
                Mail::send('mail.system_test_report', ['report' => $report], function ($message) use ($recipients, $report) {
                    $message->to($recipients)->subject('CWACAM system test '.$report['id'].' — '.$report['counts']['fails'].' not working');
                });
                $mailed = true;
            } catch (\Throwable $e) {
                \Log::error('[system-test] email failed: '.$e->getMessage());
            }
        }

        return view('system_test.thanks', [
            'report' => $report,
            'recipients' => $recipients,
            'mailed' => $mailed,
            'testerSent' => ! empty($testerSent['success']),
            'adminPhones' => $adminPhones,
        ]);
    }

    public function index()
    {
        $reports = [];
        $dir = storage_path('app/system-tests');
        if (is_dir($dir)) {
            foreach (glob($dir.'/*.json') ?: [] as $file) {
                $data = json_decode((string) file_get_contents($file), true);
                if (is_array($data) && ! empty($data['id'])) {
                    $reports[] = $data;
                }
            }
        }
        usort($reports, function ($a, $b) {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        return view('system_test.index', ['reports' => $reports]);
    }

    public function detail($id)
    {
        $report = $this->findReport($id);
        if (! $report) {
            abort(404);
        }

        return view('system_test.detail', ['report' => $report]);
    }

    protected function findReport($id)
    {
        if (! preg_match('/^[A-Za-z0-9\-]+$/', (string) $id)) {
            return null;
        }
        $path = storage_path('app/system-tests/'.$id.'.json');
        if (! is_file($path)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : null;
    }

    protected function reportRecipients()
    {
        $emails = [];
        $contact = trim((string) SiteContent::text('contact.email', 'info@cwacam.org'));
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            $emails[] = strtolower($contact);
        }

        try {
            $roleIds = DB::table('roles')->whereIn('name', ['Admin', 'Owner'])->pluck('id');
            if ($roleIds->count()) {
                $more = User::query()->whereIn('role_id', $roleIds)->where('is_active', 1)->pluck('email');
                foreach ($more as $email) {
                    $email = strtolower(trim((string) $email));
                    if (filter_var($email, FILTER_VALIDATE_EMAIL) && ! in_array($email, $emails, true)) {
                        $emails[] = $email;
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('[system-test] admin lookup failed: '.$e->getMessage());
        }

        return array_slice($emails, 0, 5);
    }

    protected function adminPhones()
    {
        $phones = [];
        try {
            $roleIds = DB::table('roles')->whereIn('name', ['Admin', 'Owner'])->pluck('id');
            if ($roleIds->count()) {
                $users = User::query()->whereIn('role_id', $roleIds)->where('is_active', 1)->get(['phone', 'additional_phone']);
                foreach ($users as $user) {
                    $phone = trim((string) ($user->phone ?: $user->additional_phone));
                    if ($phone !== '' && ! in_array($phone, $phones, true)) {
                        $phones[] = $phone;
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('[system-test] admin phone lookup failed: '.$e->getMessage());
        }

        return array_slice($phones, 0, 3);
    }

    protected function whatsappSummary(array $report, $forAdmin)
    {
        $msg = WhatsAppMessage::statusBlock($forAdmin ? '📋' : '✅', $forAdmin ? 'System test copy' : 'System test result');
        $msg .= WhatsAppMessage::greeting($forAdmin ? 'Administrator' : $report['tester_name']);
        if ($forAdmin) {
            $msg .= '*'.$report['tester_name'].'* sent a system test from '.$report['tester_phone'].".\n";
        } else {
            $msg .= "Your CWACAM system test has been received. A copy was also sent to the administrator.\n";
        }
        $msg .= WhatsAppMessage::bullet('Works', (string) $report['counts']['works']);
        $msg .= WhatsAppMessage::bullet('Does not work', (string) $report['counts']['fails']);
        $msg .= WhatsAppMessage::bullet('Not tested', (string) $report['counts']['skipped']);
        $fails = array_values(array_filter($report['rows'], function ($row) {
            return $row['result'] === 'fails';
        }));
        if ($fails) {
            $msg .= "\n*Does not work:*\n";
            foreach (array_slice($fails, 0, 8) as $row) {
                $line = $row['section'].' — '.$row['text'];
                if ($row['note'] !== '') {
                    $line .= ' ('.$row['note'].')';
                }
                $msg .= '• '.mb_substr($line, 0, 180)."\n";
            }
            if (count($fails) > 8) {
                $msg .= '• '.(count($fails) - 8)." more items are in the full report.\n";
            }
        }
        if ($report['summary'] !== '') {
            $msg .= "\n*Note:* ".mb_substr($report['summary'], 0, 400)."\n";
        }
        $msg .= WhatsAppMessage::footer();

        return $msg;
    }
}
