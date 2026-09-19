<?php

namespace App\Http\Controllers;

use App\CwaMember;
use App\Services\ApplicationService;
use App\Services\CameroonIdOcrService;
use App\Services\CampayService;
use App\Services\MembershipHandoffService;
use App\Services\MembershipPortraitService;
use App\Services\MobileMoneyHolderService;
use App\Support\CameroonMomoNetwork;
use App\Support\MembershipWhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MembershipController extends Controller
{
    public function index(Request $request)
    {
        return view('beyond.membership.index', [
            'readArticles' => (bool) $request->session()->get('membership_read_articles'),
            'readBylaws' => (bool) $request->session()->get('membership_read_bylaws'),
        ]);
    }

    public function agreeStatutes()
    {
        return redirect()->route('beyond.membership.register');
    }

    public function articles(Request $request)
    {
        $request->session()->put('membership_read_articles', true);

        return view('beyond.membership.read', $this->documentView('statutes'));
    }

    public function bylaws(Request $request)
    {
        $request->session()->put('membership_read_bylaws', true);

        return view('beyond.membership.read', $this->documentView('bylaws'));
    }

    public function agreeBylaws()
    {
        return redirect()->route('beyond.membership.register');
    }

    public function register()
    {
        $countryCodes = app(ApplicationService::class)->countryCodes();
        if (isset($countryCodes['+237'])) {
            $countryCodes = ['+237' => $countryCodes['+237']] + $countryCodes;
        }

        return view('beyond.membership.register', [
            'ageRanges' => trans('cwa.join.ages'),
            'countryCodes' => $countryCodes,
        ]);
    }

    public function createHandoff(Request $request)
    {
        $purpose = $request->input('purpose') === 'sign' ? 'sign' : 'scan';
        $token = app(MembershipHandoffService::class)->create($purpose, [
            'id_type' => $request->input('id_type', 'national_id'),
        ]);
        $url = url('/membership/continue/'.$token);
        $png = base64_decode(\DNS2D::getBarcodePNG($url, 'QRCODE'));

        return response()->json([
            'ok' => true,
            'token' => $token,
            'url' => $url,
            'qr' => $png ? 'data:image/png;base64,'.base64_encode($png) : null,
        ]);
    }

    public function continueHandoff($token)
    {
        $data = app(MembershipHandoffService::class)->get($token);
        if (! $data) {
            abort(404);
        }

        return view('beyond.membership.handoff', [
            'token' => $token,
            'purpose' => $data['purpose'] ?? 'scan',
            'idType' => $data['id_type'] ?? 'national_id',
        ]);
    }

    public function handoffStatus($token)
    {
        $data = app(MembershipHandoffService::class)->get($token);
        if (! $data) {
            return response()->json(['ok' => false], 404);
        }

        return response()->json([
            'ok' => true,
            'done' => ! empty($data['done']),
            'ocr' => $data['ocr'] ?? null,
            'path' => $data['path'] ?? null,
            'signature' => $data['signature'] ?? null,
        ]);
    }

    public function handoffFile(Request $request, $token)
    {
        $svc = app(MembershipHandoffService::class);
        if (! $svc->get($token)) {
            return response()->json(['ok' => false], 404);
        }
        $request->validate(['document' => 'required|image|max:8192']);
        $path = $this->storeUpload($request->file('document'), 'id');
        $abs = $path ? public_path($path) : '';
        $idType = $request->input('id_type', 'national_id');
        $ocr = ['name' => '', 'issue_date' => '', 'issue_place' => ''];
        if ($abs && is_file($abs)) {
            $ocr = app(CameroonIdOcrService::class)->extract($abs, $idType);
        }
        $svc->merge($token, [
            'path' => $path,
            'ocr' => $ocr,
            'done' => true,
        ]);

        return response()->json(['ok' => true, 'ocr' => $ocr, 'path' => $path]);
    }

    public function handoffSign(Request $request, $token)
    {
        $svc = app(MembershipHandoffService::class);
        if (! $svc->get($token)) {
            return response()->json(['ok' => false], 404);
        }
        $sig = $request->input('signature');
        if (! is_string($sig) || strpos($sig, 'data:image') !== 0) {
            return response()->json(['ok' => false, 'error' => 'signature'], 422);
        }
        $svc->merge($token, ['signature' => $sig, 'done' => true]);

        return response()->json(['ok' => true]);
    }

    public function ocrDocument(Request $request)
    {
        $request->validate(['document' => 'required|image|max:8192']);
        $path = $this->storeUpload($request->file('document'), 'id');
        $abs = $path ? public_path($path) : '';
        $ocr = ['name' => '', 'issue_date' => '', 'issue_place' => ''];
        if ($abs && is_file($abs)) {
            $ocr = app(CameroonIdOcrService::class)->extract($abs, $request->input('id_type', 'national_id'));
        }

        return response()->json(['ok' => true, 'ocr' => $ocr, 'path' => $path]);
    }

    public function holder(Request $request)
    {
        $campay = app(CampayService::class);
        $cc = preg_replace('/\D/', '', (string) $request->input('country_code', '237'));
        $raw = $request->input('phone');
        $phone = $campay->normalizePhone($cc === '237' ? $raw : ($cc.$raw));
        $local = CameroonMomoNetwork::localDigits($phone);
        if ($cc !== '237' || strlen($local) < 9) {
            return response()->json(['ok' => true, 'name' => null, 'operator' => null, 'cameroon' => false]);
        }

        $operator = CameroonMomoNetwork::detect($phone);
        $name = null;
        try {
            $hit = app(MobileMoneyHolderService::class)->lookup($phone);
            if (! empty($hit['name'])) {
                $name = $hit['name'];
            }
            if (! empty($hit['operator'])) {
                $operator = $hit['operator'];
            }
        } catch (\Throwable $e) {
        }

        return response()->json([
            'ok' => true,
            'name' => $name,
            'operator' => $operator,
            'operator_label' => CameroonMomoNetwork::label($operator),
            'cameroon' => true,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'country_code' => 'nullable|string|max:8',
            'phone' => 'required|string|max:20',
            'whatsapp_phone' => 'nullable|string|max:20',
            'whatsapp_country' => 'nullable|string|max:8',
            'name' => 'required|string|max:160',
            'diocese' => 'required|string|max:120',
            'parish' => 'required|string|max:120',
            'email' => 'nullable|email|max:120',
            'age_range' => 'nullable|string|max:40',
            'selfie' => 'required_without:selfie_stored|nullable|image|max:8192',
            'selfie_stored' => 'nullable|string|max:180',
            'id_front' => 'nullable|image|max:8192',
            'id_stored_path' => 'nullable|string|max:180',
            'id_type' => 'nullable|string|in:national_id,passport',
            'id_issue_date' => 'nullable|string|max:40',
            'id_issue_place' => 'nullable|string|max:120',
            'signature' => 'required|string',
        ], [
            'selfie.required' => __('cwa.membership.selfie'),
            'signature.required' => __('cwa.membership.sign_required'),
        ], [
            'phone' => __('cwa.membership.phone'),
            'name' => __('cwa.membership.name'),
            'diocese' => __('cwa.join.diocese'),
            'parish' => __('cwa.join.parish'),
            'email' => __('cwa.join.email'),
        ]);

        $campay = app(CampayService::class);
        $cc = preg_replace('/\D/', '', (string) ($data['country_code'] ?? '237')) ?: '237';
        $phone = $campay->normalizePhone($cc === '237' ? $data['phone'] : ($cc.$data['phone']));
        if ($cc === '237' && strlen(CameroonMomoNetwork::localDigits($phone)) < 9) {
            return back()->withInput()->withErrors(['phone' => __('cwa.donate.invalid_phone')]);
        }
        if ($cc !== '237' && strlen(preg_replace('/\D/', '', $phone)) < 8) {
            return back()->withInput()->withErrors(['phone' => __('cwa.donate.invalid_phone')]);
        }

        $waCc = preg_replace('/\D/', '', (string) ($data['whatsapp_country'] ?? $cc)) ?: $cc;
        $waRaw = trim((string) ($data['whatsapp_phone'] ?? ''));
        $whatsapp = $waRaw !== ''
            ? $campay->normalizePhone($waCc === '237' ? $waRaw : ($waCc.$waRaw))
            : $phone;

        $dup = CwaMember::where('phone', $phone)
            ->whereIn('status', [CwaMember::STATUS_AWAITING, CwaMember::STATUS_APPROVED])
            ->first();
        if ($dup) {
            return back()->withInput()->withErrors(['phone' => __('cwa.membership.duplicate')]);
        }

        $selfiePath = $request->file('selfie')
            ? $this->storeUpload($request->file('selfie'), 'selfie')
            : $this->safeStoredPath($request->input('selfie_stored'));
        $portraitPath = null;
        if ($selfiePath) {
            $portraitRel = 'uploads/membership/portrait_'.Str::random(10).'.jpg';
            $ok = app(MembershipPortraitService::class)->compose(
                public_path($selfiePath),
                public_path($portraitRel)
            );
            $portraitPath = $ok ? $portraitRel : $selfiePath;
        }

        $idFront = $request->file('id_front')
            ? $this->storeUpload($request->file('id_front'), 'id_front')
            : $this->safeStoredPath($request->input('id_stored_path'));
        $idBack = null;
        $signaturePath = $this->storeSignature($data['signature']);

        $member = CwaMember::create([
            'name' => $data['name'],
            'phone' => $phone,
            'country_code' => '+'.$cc,
            'whatsapp_phone' => $whatsapp,
            'id_type' => $data['id_type'] ?? null,
            'id_issue_date' => $data['id_issue_date'] ?? null,
            'id_issue_place' => $data['id_issue_place'] ?? null,
            'diocese' => $data['diocese'],
            'parish' => $data['parish'],
            'email' => $data['email'] ?? null,
            'age_range' => $data['age_range'] ?? null,
            'selfie_path' => $selfiePath,
            'portrait_path' => $portraitPath,
            'id_front_path' => $idFront,
            'id_back_path' => $idBack,
            'signature_path' => $signaturePath,
            'bylaws_agreed_at' => now(),
            'status' => CwaMember::STATUS_AWAITING,
        ]);

        try {
            app(\App\Services\BeyondWasenderService::class)
                ->sendText($whatsapp ?: $phone, MembershipWhatsApp::requestReceived($member->name));
        } catch (\Throwable $e) {
            \Log::warning('Membership WhatsApp received failed: '.$e->getMessage());
        }

        $request->session()->forget(['membership_statutes_accepted', 'membership_bylaws_accepted']);
        $request->session()->put('membership_thanks_name', $member->name);

        return redirect()->route('beyond.membership.thanks');
    }

    public function thanks(Request $request)
    {
        $name = $request->session()->get('membership_thanks_name', '');

        return view('beyond.membership.thanks', ['name' => $name]);
    }

    protected function documentView($kind)
    {
        $isBylaws = $kind === 'bylaws';

        return [
            'title' => $isBylaws ? __('cwa.membership.bylaws_title') : __('cwa.membership.articles_title'),
            'meta' => $isBylaws ? __('cwa.membership.bylaws_meta') : __('cwa.membership.articles_meta'),
            'hint' => $isBylaws ? __('cwa.membership.bylaws_hint') : __('cwa.membership.articles_hint'),
            'icon' => $isBylaws ? 'scroll-text' : 'file-text',
            'items' => $this->documentItems($kind),
        ];
    }

    protected function documentItems($kind)
    {
        $doc = trans('cwa_statutes');
        if (! is_array($doc)) {
            $doc = [];
        }

        $items = [];
        if ($kind === 'statutes') {
            $preambleHtml = $doc['preamble'] ?? '';
            if ($preambleHtml !== '' && strpos($preambleHtml, '<p>') === false) {
                $preambleHtml = \App\Support\CwaStatutesFormatter::bodyHtml($preambleHtml);
            }
            $items[] = [
                'badge' => 'P',
                'heading' => $doc['preamble_title'] ?? __('cwa.membership.read_preamble'),
                'icon' => 'book-open',
                'body_html' => $preambleHtml,
            ];
        }

        $raw = $kind === 'bylaws' ? ($doc['bylaws'] ?? []) : ($doc['statutes'] ?? []);
        $type = $kind === 'bylaws' ? 'bylaws' : 'statutes';
        foreach (array_values($raw) as $i => $article) {
            $items[] = [
                'badge' => (string) ($i + 1),
                'heading' => \App\Support\CwaStatutesFormatter::heading(
                    $type,
                    $article['n'] ?? '',
                    $article['title'] ?? ''
                ),
                'icon' => \App\Support\CwaStatutesFormatter::iconFor($type, $i),
                'body_html' => \App\Support\CwaStatutesFormatter::bodyHtml($article['body'] ?? ''),
            ];
        }

        return $items;
    }

    protected function safeStoredPath($rel)
    {
        $rel = str_replace('\\', '/', (string) $rel);
        if (! preg_match('#^uploads/membership/[A-Za-z0-9._-]+$#', $rel)) {
            return null;
        }

        return is_file(public_path($rel)) ? $rel : null;
    }

    protected function storeUpload($file, $prefix)
    {
        if (! $file) {
            return null;
        }
        $dir = public_path('uploads/membership');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $ext = 'jpg';
        }
        $name = $prefix.'_'.date('YmdHis').'_'.Str::random(8).'.'.$ext;
        $file->move($dir, $name);

        return 'uploads/membership/'.$name;
    }

    protected function storeSignature($dataUrl)
    {
        if (! is_string($dataUrl) || strpos($dataUrl, 'data:image') !== 0) {
            return null;
        }
        $dir = public_path('uploads/membership');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $raw = preg_replace('#^data:image/\w+;base64,#', '', $dataUrl);
        $bin = base64_decode($raw);
        if ($bin === false) {
            return null;
        }
        $name = 'signature_'.date('YmdHis').'_'.Str::random(8).'.png';
        file_put_contents($dir.'/'.$name, $bin);

        return 'uploads/membership/'.$name;
    }
}
