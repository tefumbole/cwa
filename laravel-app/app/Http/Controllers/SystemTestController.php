<?php

namespace App\Http\Controllers;

use App\Services\BeyondWasenderService;
use App\Services\MobileMoneyHolderService;
use App\Support\CountryDialCodes;
use App\Support\SiteContent;
use App\Support\SystemTestAccount;
use App\Support\SystemTestDraft;
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
    public function show(Request $request)
    {
        $pages = $this->pages();
        $phone = session('system_test_phone');
        $draft = $phone ? SystemTestDraft::find($phone) : null;
        if ($phone && ! $draft) {
            session()->forget('system_test_phone');
            $phone = null;
        }

        if ($request->query('step') === 'name' && session('system_test_lookup_phone')) {
            return view('system_test.form', $this->viewData($pages, null, [
                'mode' => 'name',
                'lookupPhone' => session('system_test_lookup_phone'),
                'suggestedName' => (string) session('system_test_lookup_name', ''),
                'nameSource' => (string) session('system_test_lookup_source', ''),
                'progress' => $this->progress($pages, ['checks' => []]),
            ]));
        }

        $verifyPhone = session('system_test_pending_phone');
        if ($request->query('verify') === '1' && $verifyPhone && SystemTestDraft::find($verifyPhone)) {
            return view('system_test.form', $this->viewData($pages, null, [
                'mode' => 'verify',
                'pendingPhone' => $verifyPhone,
                'progress' => $this->progress($pages, ['checks' => []]),
            ]));
        }

        if (! $draft) {
            return view('system_test.form', $this->viewData($pages, null, [
                'mode' => 'gate',
                'progress' => $this->progress($pages, ['checks' => []]),
            ]));
        }

        if ($request->query('review') === '1') {
            $missing = $this->missing($pages, $draft);
            if ($missing) {
                return redirect()->route('system-test.show', ['page' => $this->firstIncompletePage($pages, $draft)])->withErrors([
                    'checks' => 'Answer every question before the review. '.count($missing).' still need an answer. Not tested counts as an answer.',
                ]);
            }

            return view('system_test.form', $this->viewData($pages, $draft, [
                'mode' => 'review',
                'progress' => $this->progress($pages, $draft),
                'reviewRows' => $this->reviewRows($pages, $draft),
            ]));
        }

        $pageCount = count($pages);
        $page = (int) $request->query('page', $draft['page'] ?? 1);
        $page = max(1, min($pageCount, $page));
        $progress = $this->progress($pages, $draft);

        return view('system_test.form', $this->viewData($pages, $draft, [
            'mode' => 'test',
            'page' => $page,
            'pageCount' => $pageCount,
            'current' => $pages[$page - 1],
            'progress' => $progress,
            'numberStart' => $progress['offsets'][$page - 1],
        ]));
    }

    public function csrf()
    {
        return response()->json(['token' => csrf_token()]);
    }

    public function lookup(Request $request)
    {
        if ($this->honeypot($request)) {
            return redirect()->route('system-test.show');
        }

        $code = (string) $request->input('country_code');
        if (! isset(CountryDialCodes::all()[$code])) {
            return redirect()->route('system-test.show')->withErrors([
                'country_code' => 'Choose a country.',
            ]);
        }
        $local = trim((string) $request->input('phone_local'));
        if ($local === '') {
            return redirect()->route('system-test.show')->withInput()->withErrors([
                'phone_local' => 'Enter the phone number.',
            ]);
        }

        try {
            $phone = WhatsAppPhone::forWasender(CountryDialCodes::combine($code, $local));
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('system-test.show')->withInput()->withErrors([
                'phone_local' => 'Enter a WhatsApp number without the country code, for example 675321739.',
            ]);
        }

        $digits = preg_replace('/\D/', '', $code);
        $name = '';
        $source = '';
        if ($request->input('intent') !== 'retrieve') {
            if ($digits === '237') {
                $hit = app(MobileMoneyHolderService::class)->lookup(ltrim($phone, '+'));
                $name = trim((string) ($hit['name'] ?? ''));
                $source = $name !== '' ? 'campay' : '';
            } else {
                $name = trim((string) app(BeyondWasenderService::class)->getContactName($phone));
                $source = $name !== '' ? 'whatsapp' : '';
            }
        }

        $recovered = [
            'checks' => $request->input('checks', []),
            'notes' => $request->input('notes', []),
            'summary' => $request->input('summary'),
            'name' => trim((string) $request->input('tester_name')),
        ];
        session([
            'system_test_lookup_phone' => $phone,
            'system_test_lookup_name' => $name,
            'system_test_lookup_source' => $source,
            'system_test_recovered' => $recovered,
        ]);

        if ($request->input('intent') === 'retrieve') {
            return $this->retrieve($phone, $recovered, $name);
        }

        return redirect()->route('system-test.show', ['step' => 'name']);
    }

    protected function retrieve($phone, array $recovered, $lookedUpName)
    {
        $draft = SystemTestDraft::find($phone);
        $name = trim((string) ($draft['tester_name'] ?? ''));
        if ($name === '') {
            $name = trim((string) ($recovered['name'] ?? ''));
        }
        if ($name === '') {
            $name = trim((string) $lookedUpName);
        }
        if (! $draft) {
            $draft = SystemTestDraft::blank($name !== '' ? $name : 'Tester', $phone);
        }
        $draft = $this->fillBlanks($draft, new Request($recovered));
        if ($name !== '' && $name !== 'Tester') {
            $draft['tester_name'] = $name;
        }

        if ($this->answeredCount($draft) === 0) {
            return redirect()->route('system-test.show')->withInput()->withErrors([
                'phone_local' => 'No saved answers were found for this number. Open this page in the same browser where the answers were entered, then press Retrieve saved answers.',
            ]);
        }

        if (trim((string) $draft['tester_name']) === '' || $draft['tester_name'] === 'Tester') {
            SystemTestDraft::put($phone, $draft);
            session([
                'system_test_lookup_phone' => $phone,
                'system_test_lookup_name' => '',
                'system_test_lookup_source' => '',
            ]);

            return redirect()->route('system-test.show', ['step' => 'name']);
        }

        return $this->sendCode($phone, $draft);
    }

    public function confirmName(Request $request)
    {
        if ($this->honeypot($request)) {
            return redirect()->route('system-test.show');
        }

        $phone = session('system_test_lookup_phone');
        if (! $phone) {
            return redirect()->route('system-test.show')->withErrors([
                'phone_local' => 'Enter your phone number again.',
            ]);
        }
        $name = trim((string) $request->input('tester_name'));
        if ($name === '' || mb_strlen($name) > 120) {
            return redirect()->route('system-test.show', ['step' => 'name'])->withInput()->withErrors([
                'tester_name' => 'Enter the name to use for this test.',
            ]);
        }

        $draft = SystemTestDraft::find($phone) ?: SystemTestDraft::blank($name, $phone);
        $recovered = session('system_test_recovered');
        if (is_array($recovered)) {
            $draft = $this->fillBlanks($draft, new Request($recovered));
        }
        $draft['tester_name'] = $name;
        if ($this->answeredCount($draft) > 0) {
            $draft['page'] = $this->firstIncompletePage($this->pages(), $draft);
        }

        return $this->sendCode($phone, $draft, 'system-test.show', ['step' => 'name'], 'tester_name');
    }

    public function start(Request $request)
    {
        if ($this->honeypot($request)) {
            return redirect()->route('system-test.show');
        }

        $phone = $this->phoneOrRedirect($request->input('tester_phone'), 'system-test.show');
        if ($phone instanceof \Illuminate\Http\RedirectResponse) {
            return $phone;
        }
        $name = trim((string) $request->input('tester_name'));
        if ($name === '' || mb_strlen($name) > 120) {
            return redirect()->route('system-test.show')->withInput()->withErrors([
                'tester_name' => 'Enter your name.',
            ]);
        }
        if (SystemTestDraft::find($phone)) {
            return redirect()->route('system-test.show')->withInput()->withErrors([
                'tester_phone' => 'A saved test already exists for this number. Confirm it below to continue, or confirm it and start again.',
            ]);
        }

        $draft = $this->mergeAnywhere(SystemTestDraft::blank($name, $phone), $request);
        $draft['page'] = $this->firstIncompletePage($this->pages(), $draft);
        SystemTestDraft::put($phone, $draft);
        session(['system_test_phone' => $phone]);

        return redirect()->route('system-test.show', ['page' => $draft['page']]);
    }

    public function resume(Request $request)
    {
        if ($this->honeypot($request)) {
            return redirect()->route('system-test.show');
        }

        $phone = $this->phoneOrRedirect($request->input('tester_phone'), 'system-test.show');
        if ($phone instanceof \Illuminate\Http\RedirectResponse) {
            return $phone;
        }
        $draft = SystemTestDraft::find($phone);
        if (! $draft) {
            return redirect()->route('system-test.show')->withInput()->withErrors([
                'tester_phone' => 'No saved test was found for this number.',
            ]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $draft['otp_hash'] = password_hash($code, PASSWORD_DEFAULT);
        $draft['otp_expires'] = time() + 600;
        $draft['otp_attempts'] = 0;
        SystemTestDraft::put($phone, $draft);
        session(['system_test_pending_phone' => $phone]);

        $sent = app(BeyondWasenderService::class)->sendText(
            $phone,
            WhatsAppMessage::otpMessage($code, 'system test', 10)
        );
        if (empty($sent['success'])) {
            return redirect()->route('system-test.show')->withErrors([
                'tester_phone' => 'The code could not be sent on WhatsApp. Check the number and try again.',
            ]);
        }

        return redirect()->route('system-test.show', ['verify' => 1]);
    }

    public function verify(Request $request)
    {
        $phone = session('system_test_pending_phone');
        $draft = $phone ? SystemTestDraft::find($phone) : null;
        if (! $draft) {
            return redirect()->route('system-test.show')->withErrors([
                'tester_phone' => 'Ask for a new code to open a saved test.',
            ]);
        }

        $code = preg_replace('/\D+/', '', (string) $request->input('code'));
        if (! $draft['otp_hash'] || (int) $draft['otp_expires'] < time()) {
            return redirect()->route('system-test.show', ['verify' => 1])->withErrors([
                'code' => 'That code has expired. Ask for a new one.',
            ]);
        }
        if ((int) $draft['otp_attempts'] >= 5) {
            return redirect()->route('system-test.show', ['verify' => 1])->withErrors([
                'code' => 'Too many tries. Ask for a new code.',
            ]);
        }
        if (! password_verify($code, $draft['otp_hash'])) {
            $draft['otp_attempts'] = (int) $draft['otp_attempts'] + 1;
            SystemTestDraft::put($phone, $draft);

            return redirect()->route('system-test.show', ['verify' => 1])->withErrors([
                'code' => 'That code does not match. Check the WhatsApp message and try again.',
            ]);
        }

        $draft['otp_hash'] = null;
        $draft['otp_expires'] = null;
        $draft['otp_attempts'] = 0;
        if ($request->input('action') === 'replace') {
            $draft = SystemTestDraft::blank($draft['tester_name'], $phone);
        }
        SystemTestDraft::put($phone, $draft);
        session()->forget(['system_test_pending_phone', 'system_test_lookup_phone', 'system_test_lookup_name', 'system_test_lookup_source', 'system_test_recovered']);
        session(['system_test_phone' => $phone]);

        $login = null;
        try {
            $login = SystemTestAccount::ensure($draft['tester_name'], $phone);
        } catch (\Throwable $e) {
            \Log::error('[system-test] tester account failed: '.$e->getMessage());
        }

        $openReview = $this->missing($this->pages(), $draft) === [];
        $redirect = redirect()->route('system-test.show', $openReview ? ['review' => 1] : ['page' => $draft['page'] ?? 1]);
        if ($login && ! empty($login['password'])) {
            $sent = app(BeyondWasenderService::class)->sendText(
                $phone,
                $this->loginMessage($draft['tester_name'], $login['username'], $login['password'])
            );
            $redirect->with('test_login', [
                'username' => $login['username'],
                'password' => $login['password'],
                'sent' => ! empty($sent['success']),
            ]);
        }

        return $redirect;
    }

    public function save(Request $request)
    {
        if ($this->honeypot($request)) {
            return redirect()->route('system-test.show');
        }

        $phone = session('system_test_phone');
        $draft = $phone ? SystemTestDraft::find($phone) : null;
        if (! $draft) {
            return redirect()->route('system-test.show')->withErrors([
                'tester_phone' => 'Open your saved test again by confirming your WhatsApp number.',
            ]);
        }

        $pages = $this->pages();
        $pageCount = count($pages);
        $page = max(1, min($pageCount, (int) $request->input('page', 1)));
        $draft = $this->mergeAnywhere($this->mergePage($draft, $pages[$page - 1], $request), $request);
        $draft['page'] = $page;
        $action = (string) $request->input('action', 'later');
        if ($request->filled('goto')) {
            $action = 'goto';
        }

        if ($action === 'review' || $action === 'submit' || $action === 'confirm') {
            $missing = $this->missing($pages, $draft);
            if ($missing) {
                SystemTestDraft::put($phone, $draft);
                $first = $this->firstIncompletePage($pages, $draft);

                return redirect()->route('system-test.show', ['page' => $first])->withErrors([
                    'checks' => 'Answer every question before the review. '.count($missing).' still need an answer. Not tested counts as an answer.',
                ]);
            }
            SystemTestDraft::put($phone, $draft);
            if ($action === 'confirm') {
                $report = $this->reportFromDraft($draft);
                SystemTestDraft::forget($phone);
                session()->forget('system_test_phone');

                return $this->publish($report);
            }

            return redirect()->route('system-test.show', ['review' => 1]);
        }

        if ($action === 'next') {
            $open = $this->missingOnPage($pages[$page - 1], $draft);
            if ($open) {
                SystemTestDraft::put($phone, $draft);

                return redirect()->route('system-test.show', ['page' => $page])->withErrors([
                    'checks' => 'Answer every question on this page before the next one. '.count($open).' still need an answer. You can choose Not tested, or save and continue later.',
                ]);
            }
            $draft['page'] = min($pageCount, $page + 1);
            SystemTestDraft::put($phone, $draft);

            return redirect()->route('system-test.show', ['page' => $draft['page']])->with('test_saved', true);
        }

        if ($action === 'prev') {
            $draft['page'] = max(1, $page - 1);
        } elseif ($action === 'goto') {
            $target = max(1, min($pageCount, (int) $request->input('goto')));
            if ($target > $page && $this->missingOnPage($pages[$page - 1], $draft)) {
                SystemTestDraft::put($phone, $draft);

                return redirect()->route('system-test.show', ['page' => $page])->withErrors([
                    'checks' => 'Answer every question on this page before moving ahead. You can choose Not tested, or save and continue later.',
                ]);
            }
            $draft['page'] = $target;
        }

        SystemTestDraft::put($phone, $draft);

        return redirect()->route('system-test.show', ['page' => $draft['page']])->with('test_saved', true);
    }

    public function store(Request $request)
    {
        if (trim((string) $request->input('company_website')) !== '') {
            return redirect()->route('system-test.show');
        }

        $validator = validator($request->all(), [
            'tester_name' => 'required|string|max:120',
            'tester_phone' => 'required|string|max:30',
            'summary' => 'nullable|string|max:5000',
        ]);
        if ($validator->fails()) {
            return redirect()->route('system-test.show')->withInput()->withErrors($validator);
        }

        try {
            $testerPhone = WhatsAppPhone::forWasender($request->input('tester_phone'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('system-test.show')->withInput()->withErrors([
                'tester_phone' => 'Enter a WhatsApp number, for example 675321739 or +237675321739.',
            ]);
        }

        $draft = [
            'tester_name' => trim($request->input('tester_name')),
            'tester_phone' => $testerPhone,
            'summary' => trim((string) $request->input('summary')),
            'checks' => (array) $request->input('checks', []),
            'notes' => (array) $request->input('notes', []),
        ];
        $missing = $this->missing($this->pages(), $draft);
        if ($missing) {
            return redirect()->route('system-test.show')->withInput()->withErrors([
                'checks' => 'Answer every question before sending. '.count($missing).' still need an answer. Not tested counts as an answer.',
            ]);
        }

        return $this->publish($this->reportFromDraft($draft));
    }

    protected function publish(array $report)
    {
        $dir = storage_path('app/system-tests');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        file_put_contents($dir.'/'.$report['id'].'.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $whatsapp = app(BeyondWasenderService::class);
        $testerPhone = $report['tester_phone'];
        $testerSent = $this->sendMessages($whatsapp, $testerPhone, $this->testerResultMessages($report));
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
            $this->sendMessages($whatsapp, $adminPhone, $this->adminFailureMessages($report));
            $this->sendMessages($whatsapp, $adminPhone, $this->adminFullResultMessages($report));
        }

        $recipients = $this->reportRecipients();
        $mailed = false;
        if ($recipients) {
            try {
                Mail::send('mail.system_test_report', ['report' => $report], function ($message) use ($recipients, $report) {
                    $message->to($recipients)->subject($report['counts']['fails'].' do not work — '.$report['tester_name']);
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
            'hideSiteNav' => true,
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

    protected function sendMessages($whatsapp, $phone, array $messages)
    {
        $ok = false;
        foreach ($messages as $message) {
            $sent = $whatsapp->sendText($phone, $message);
            if (! empty($sent['success'])) {
                $ok = true;
            }
        }

        return $ok;
    }

    protected function testerResultMessages(array $report)
    {
        $fails = $this->rowsByResult($report, 'fails');
        $intro = WhatsAppMessage::greeting($report['tester_name']);
        $intro .= "Your CWACAM system test has been received. The administrator also received this result and a list of what does not work.\n";
        $intro .= WhatsAppMessage::bullet('Works', (string) $report['counts']['works']);
        $intro .= WhatsAppMessage::bullet('Does not work', (string) $report['counts']['fails']);
        $intro .= WhatsAppMessage::bullet('Not tested', (string) $report['counts']['skipped']);
        $lines = $this->resultLines($fails);
        if ($report['summary'] !== '') {
            $lines[] = '*Note:* '.mb_substr($report['summary'], 0, 500);
        }

        return $this->packMessages('✅', 'System test result', $intro, $lines);
    }

    protected function adminFailureMessages(array $report)
    {
        $fails = $this->rowsByResult($report, 'fails');
        $intro = WhatsAppMessage::greeting('Administrator');
        $intro .= '*'.$report['tester_name'].'* ('.$report['tester_phone'].") submitted a system test.\n";
        if (! $fails) {
            $intro .= "Nothing was marked as not working.\n";
        } else {
            $intro .= 'Focus on these *'.count($fails)."* items that do not work:\n";
        }

        return $this->packMessages('⚠️', 'What does not work', $intro, $this->resultLines($fails));
    }

    protected function adminFullResultMessages(array $report)
    {
        $intro = WhatsAppMessage::greeting('Administrator');
        $intro .= 'Full result from *'.$report['tester_name'].'* ('.$report['tester_phone'].").\n";
        $intro .= WhatsAppMessage::bullet('Works', (string) $report['counts']['works']);
        $intro .= WhatsAppMessage::bullet('Does not work', (string) $report['counts']['fails']);
        $intro .= WhatsAppMessage::bullet('Not tested', (string) $report['counts']['skipped']);
        $intro .= "\nThe items that do not work are in the previous message.\n";
        $lines = [];
        $works = $this->rowsByResult($report, 'works');
        $skipped = $this->rowsByResult($report, 'skipped');
        if ($works) {
            $lines[] = '*Works*';
            $lines = array_merge($lines, $this->resultLines($works));
        }
        if ($skipped) {
            $lines[] = '*Not tested*';
            $lines = array_merge($lines, $this->resultLines($skipped));
        }
        if ($report['summary'] !== '') {
            $lines[] = '*Note:* '.mb_substr($report['summary'], 0, 500);
        }
        $lines[] = WhatsAppMessage::actionLink('Open the full result', route('system-test.detail', ['id' => $report['id']]));

        return $this->packMessages('📋', 'Tester result', $intro, $lines);
    }

    protected function rowsByResult(array $report, $result)
    {
        return array_values(array_filter($report['rows'], function ($row) use ($result) {
            return $row['result'] === $result;
        }));
    }

    protected function resultLines(array $rows)
    {
        $lines = [];
        foreach ($rows as $row) {
            $line = '• *'.$row['section'].':* '.$row['text'];
            if ($row['note'] !== '') {
                $line .= "\n  _".$row['note'].'_';
            }
            $lines[] = $line;
        }

        return $lines;
    }

    protected function packMessages($emoji, $title, $intro, array $lines)
    {
        $footer = WhatsAppMessage::footer();
        $bodies = [];
        $current = rtrim($intro);
        foreach ($lines as $line) {
            $candidate = $current."\n".$line;
            if (mb_strlen($candidate) > 2800 && $current !== rtrim($intro)) {
                $bodies[] = $current;
                $current = $line;
            } else {
                $current = $candidate;
            }
        }
        $bodies[] = $current;
        $total = count($bodies);
        $messages = [];
        foreach ($bodies as $index => $body) {
            $heading = $title.($total > 1 ? ' ('.($index + 1).'/'.$total.')' : '');
            $messages[] = WhatsAppMessage::statusBlock($emoji, $heading).$body.$footer;
        }

        return $messages;
    }

    protected function sendCode($phone, array $draft, $errorRoute = 'system-test.show', array $errorParams = [], $errorKey = 'phone_local')
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $draft['otp_hash'] = password_hash($code, PASSWORD_DEFAULT);
        $draft['otp_expires'] = time() + 600;
        $draft['otp_attempts'] = 0;
        SystemTestDraft::put($phone, $draft);
        session(['system_test_pending_phone' => $phone]);

        $sent = app(BeyondWasenderService::class)->sendText(
            $phone,
            WhatsAppMessage::otpMessage($code, 'system test', 10)
        );
        if (empty($sent['success'])) {
            return redirect()->route($errorRoute, $errorParams)->withErrors([
                $errorKey => 'The code could not be sent on WhatsApp. Check the number and try again.',
            ]);
        }

        return redirect()->route('system-test.show', ['verify' => 1]);
    }

    protected function fillBlanks(array $draft, Request $request)
    {
        $known = array_keys(SystemTestGuide::checkMap());
        $checks = (array) ($draft['checks'] ?? []);
        $notes = (array) ($draft['notes'] ?? []);
        $posted = (array) $request->input('checks', []);
        $postedNotes = (array) $request->input('notes', []);
        foreach ($known as $id) {
            $current = isset($checks[$id]) ? (string) $checks[$id] : '';
            if (! in_array($current, ['works', 'fails', 'skipped'], true)
                && isset($posted[$id])
                && in_array($posted[$id], ['works', 'fails', 'skipped'], true)) {
                $checks[$id] = $posted[$id];
            }
            if (trim((string) ($notes[$id] ?? '')) === '' && trim((string) ($postedNotes[$id] ?? '')) !== '') {
                $notes[$id] = trim(mb_substr((string) $postedNotes[$id], 0, 500));
            }
        }
        $draft['checks'] = $checks;
        $draft['notes'] = $notes;
        if (trim((string) ($draft['summary'] ?? '')) === '') {
            $summary = trim((string) $request->input('summary'));
            if ($summary !== '') {
                $draft['summary'] = mb_substr($summary, 0, 5000);
            }
        }

        return $draft;
    }

    protected function answeredCount(array $draft)
    {
        $count = 0;
        foreach ((array) ($draft['checks'] ?? []) as $value) {
            if (in_array($value, ['works', 'fails', 'skipped'], true)) {
                $count++;
            }
        }

        return $count;
    }

    protected function reviewRows(array $pages, array $draft)
    {
        $checks = (array) ($draft['checks'] ?? []);
        $notes = (array) ($draft['notes'] ?? []);
        $rows = [];
        $number = 0;
        $labels = [
            'works' => 'Works',
            'fails' => 'Does not work',
            'skipped' => 'Not tested',
        ];
        foreach ($pages as $index => $page) {
            foreach ($page['checks'] as $check) {
                $number++;
                $result = isset($checks[$check['id']]) ? (string) $checks[$check['id']] : '';
                $rows[] = [
                    'number' => $number,
                    'page' => $index + 1,
                    'section' => $page['title'],
                    'text' => $check['text'],
                    'result' => $result,
                    'label' => isset($labels[$result]) ? $labels[$result] : 'No answer',
                    'note' => trim((string) ($notes[$check['id']] ?? '')),
                ];
            }
        }

        return $rows;
    }

    protected function loginMessage($name, $username, $password)
    {
        $msg = WhatsAppMessage::statusBlock('🔐', 'Test login');
        $msg .= WhatsAppMessage::greeting($name);
        $msg .= "Your CWACAM test account is ready. Use it to sign in while you test the website.\n";
        $msg .= WhatsAppMessage::bullet('Username', $username);
        $msg .= WhatsAppMessage::bullet('Password', $password);
        $msg .= WhatsAppMessage::actionLink('Login', url('/login'));
        $msg .= "\nSettings is not included on this account.\n";
        $msg .= WhatsAppMessage::footer();

        return $msg;
    }

    protected function pages()
    {
        $pages = [];
        foreach (SystemTestGuide::sections() as $section) {
            $chunks = array_chunk($section['checks'], 8);
            $count = count($chunks);
            foreach ($chunks as $index => $checks) {
                $title = $section['title'];
                if ($count > 1) {
                    $title .= ' ('.($index + 1).' of '.$count.')';
                }
                $pages[] = [
                    'id' => $section['id'].'-'.$index,
                    'title' => $title,
                    'intro' => $section['intro'],
                    'checks' => $checks,
                ];
            }
        }

        return $pages;
    }

    protected function viewData(array $pages, $draft, array $extra)
    {
        $data = [
            'pages' => $pages,
            'draft' => $draft,
            'hideSiteNav' => true,
            'mode' => 'gate',
            'page' => 1,
            'pageCount' => count($pages),
            'current' => null,
            'progress' => ['answered' => 0, 'total' => 0, 'pages' => [], 'offsets' => []],
            'numberStart' => 0,
            'pendingPhone' => '',
            'lookupPhone' => '',
            'suggestedName' => '',
            'nameSource' => '',
            'reviewRows' => [],
        ];

        return array_merge($data, $extra);
    }

    protected function progress(array $pages, array $draft)
    {
        $answered = 0;
        $total = 0;
        $offsets = [];
        $pageStats = [];
        foreach ($pages as $page) {
            $offsets[] = $total;
            $pageAnswered = count($page['checks']) - count($this->missingOnPage($page, $draft));
            $answered += $pageAnswered;
            $total += count($page['checks']);
            $pageStats[] = [
                'title' => $page['title'],
                'answered' => $pageAnswered,
                'total' => count($page['checks']),
            ];
        }

        return [
            'answered' => $answered,
            'total' => $total,
            'pages' => $pageStats,
            'offsets' => $offsets,
        ];
    }

    protected function mergeAnywhere(array $draft, Request $request)
    {
        $known = array_keys(SystemTestGuide::checkMap());
        $checks = (array) ($draft['checks'] ?? []);
        $notes = (array) ($draft['notes'] ?? []);
        $posted = (array) $request->input('checks', []);
        $postedNotes = (array) $request->input('notes', []);
        foreach ($known as $id) {
            if (isset($posted[$id]) && in_array($posted[$id], ['works', 'fails', 'skipped'], true)) {
                $checks[$id] = $posted[$id];
            }
            if (array_key_exists($id, $postedNotes) && trim((string) $postedNotes[$id]) !== '') {
                $notes[$id] = trim(mb_substr((string) $postedNotes[$id], 0, 500));
            }
        }
        $draft['checks'] = $checks;
        $draft['notes'] = $notes;
        $summary = trim((string) $request->input('summary'));
        if ($summary !== '') {
            $draft['summary'] = mb_substr($summary, 0, 5000);
        }

        return $draft;
    }

    protected function mergePage(array $draft, array $page, Request $request)
    {
        $checks = (array) ($draft['checks'] ?? []);
        $notes = (array) ($draft['notes'] ?? []);
        $posted = (array) $request->input('checks', []);
        $postedNotes = (array) $request->input('notes', []);
        foreach ($page['checks'] as $check) {
            $id = $check['id'];
            if (isset($posted[$id]) && in_array($posted[$id], ['works', 'fails', 'skipped'], true)) {
                $checks[$id] = $posted[$id];
            }
            if (array_key_exists($id, $postedNotes)) {
                $notes[$id] = trim(mb_substr((string) $postedNotes[$id], 0, 500));
            }
        }
        $draft['checks'] = $checks;
        $draft['notes'] = $notes;
        if ($request->exists('summary')) {
            $draft['summary'] = trim(mb_substr((string) $request->input('summary'), 0, 5000));
        }

        return $draft;
    }

    protected function missing(array $pages, array $draft)
    {
        $missing = [];
        foreach ($pages as $page) {
            foreach ($this->missingOnPage($page, $draft) as $id) {
                $missing[] = $id;
            }
        }

        return $missing;
    }

    protected function missingOnPage(array $page, array $draft)
    {
        $checks = (array) ($draft['checks'] ?? []);
        $missing = [];
        foreach ($page['checks'] as $check) {
            $value = isset($checks[$check['id']]) ? (string) $checks[$check['id']] : '';
            if (! in_array($value, ['works', 'fails', 'skipped'], true)) {
                $missing[] = $check['id'];
            }
        }

        return $missing;
    }

    protected function firstIncompletePage(array $pages, array $draft)
    {
        foreach ($pages as $index => $page) {
            if ($this->missingOnPage($page, $draft)) {
                return $index + 1;
            }
        }

        return count($pages);
    }

    protected function reportFromDraft(array $draft)
    {
        $map = SystemTestGuide::checkMap();
        $checks = (array) ($draft['checks'] ?? []);
        $notes = (array) ($draft['notes'] ?? []);
        $rows = [];
        $counts = ['works' => 0, 'fails' => 0, 'skipped' => 0];
        foreach ($map as $id => $check) {
            $result = isset($checks[$id]) ? (string) $checks[$id] : '';
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

        return [
            'id' => date('Ymd-His').'-'.Str::lower(Str::random(6)),
            'tester_name' => trim((string) $draft['tester_name']),
            'tester_phone' => $draft['tester_phone'],
            'tester_email' => '',
            'summary' => trim((string) ($draft['summary'] ?? '')),
            'counts' => $counts,
            'total' => count($rows),
            'rows' => $rows,
            'created_at' => date('Y-m-d H:i'),
        ];
    }

    protected function honeypot(Request $request)
    {
        return trim((string) $request->input('company_website')) !== '';
    }

    protected function phoneOrRedirect($raw, $route)
    {
        try {
            return WhatsAppPhone::forWasender($raw);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route($route)->withInput()->withErrors([
                'tester_phone' => 'Enter a WhatsApp number, for example 675321739 or +237675321739.',
            ]);
        }
    }
}
