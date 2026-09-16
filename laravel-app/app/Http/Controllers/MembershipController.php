<?php

namespace App\Http\Controllers;

use App\CwaMember;
use App\Services\CampayService;
use App\Services\MembershipPortraitService;
use App\Services\MobileMoneyHolderService;
use App\Support\CameroonMomoNetwork;
use App\Support\MembershipWhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MembershipController extends Controller
{
    public function statutes()
    {
        return view('beyond.membership.chapter', $this->chapterData('statutes'));
    }

    public function agreeStatutes(Request $request)
    {
        $request->session()->put('membership_statutes_accepted', true);

        return redirect()->route('beyond.membership.bylaws');
    }

    public function bylaws(Request $request)
    {
        if (! $request->session()->get('membership_statutes_accepted')) {
            return redirect()->route('beyond.membership')
                ->with('warning', __('cwa.membership.must_agree'));
        }

        return view('beyond.membership.chapter', $this->chapterData('bylaws'));
    }

    public function agreeBylaws(Request $request)
    {
        if (! $request->session()->get('membership_statutes_accepted')) {
            return redirect()->route('beyond.membership')
                ->with('warning', __('cwa.membership.must_agree'));
        }
        $request->session()->put('membership_bylaws_accepted', true);

        return redirect()->route('beyond.membership.register');
    }

    public function register(Request $request)
    {
        if (! $request->session()->get('membership_bylaws_accepted')) {
            return redirect()->route('beyond.membership')
                ->with('warning', __('cwa.membership.must_agree'));
        }

        return view('beyond.membership.register', [
            'ageRanges' => trans('cwa.join.ages'),
        ]);
    }

    public function holder(Request $request)
    {
        $campay = app(CampayService::class);
        $phone = $campay->normalizePhone($request->input('phone'));
        $local = CameroonMomoNetwork::localDigits($phone);
        if (strlen($local) < 9) {
            return response()->json(['ok' => false, 'name' => null, 'operator' => null]);
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
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->session()->get('membership_bylaws_accepted')) {
            return redirect()->route('beyond.membership')
                ->with('warning', __('cwa.membership.must_agree'));
        }

        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'name' => 'required|string|max:160',
            'diocese' => 'required|string|max:120',
            'parish' => 'required|string|max:120',
            'email' => 'nullable|email|max:120',
            'age_range' => 'nullable|string|max:40',
            'selfie' => 'required|image|max:8192',
            'id_front' => 'nullable|image|max:8192',
            'id_back' => 'nullable|image|max:8192',
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
        $phone = $campay->normalizePhone($data['phone']);
        if (strlen(CameroonMomoNetwork::localDigits($phone)) < 9) {
            return back()->withInput()->withErrors(['phone' => __('cwa.donate.invalid_phone')]);
        }

        $dup = CwaMember::where('phone', $phone)
            ->whereIn('status', [CwaMember::STATUS_AWAITING, CwaMember::STATUS_APPROVED])
            ->first();
        if ($dup) {
            return back()->withInput()->withErrors(['phone' => __('cwa.membership.duplicate')]);
        }

        $selfiePath = $this->storeUpload($request->file('selfie'), 'selfie');
        $portraitPath = null;
        if ($selfiePath) {
            $portraitRel = 'uploads/membership/portrait_'.Str::random(10).'.jpg';
            $ok = app(MembershipPortraitService::class)->compose(
                public_path($selfiePath),
                public_path($portraitRel)
            );
            $portraitPath = $ok ? $portraitRel : $selfiePath;
        }

        $idFront = $request->file('id_front') ? $this->storeUpload($request->file('id_front'), 'id_front') : null;
        $idBack = $request->file('id_back') ? $this->storeUpload($request->file('id_back'), 'id_back') : null;
        $signaturePath = $this->storeSignature($data['signature']);

        $member = CwaMember::create([
            'name' => $data['name'],
            'phone' => $phone,
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
                ->sendText($phone, MembershipWhatsApp::requestReceived($member->name));
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

    protected function chapterData($kind)
    {
        $statutes = trans('cwa_statutes');
        if (! is_array($statutes)) {
            $statutes = [];
        }
        $isBylaws = $kind === 'bylaws';
        $raw = $isBylaws ? ($statutes['bylaws'] ?? []) : ($statutes['statutes'] ?? []);
        $articles = [];
        foreach (array_values($raw) as $i => $article) {
            $articles[] = [
                'n' => (string) ($i + 1),
                'roman' => $article['n'] ?? '',
                'title' => $article['title'] ?? '',
                'heading' => \App\Support\CwaStatutesFormatter::heading(
                    $isBylaws ? 'bylaws' : 'statutes',
                    $article['n'] ?? '',
                    $article['title'] ?? ''
                ),
                'icon' => \App\Support\CwaStatutesFormatter::iconFor($isBylaws ? 'bylaws' : 'statutes', $i),
                'body_html' => \App\Support\CwaStatutesFormatter::bodyHtml($article['body'] ?? ''),
            ];
        }

        $termsHeading = $isBylaws
            ? (($statutes['bylaws_kicker'] ?? '').' — '.($statutes['bylaws_title'] ?? ''))
            : ($statutes['preamble_title'] ?? '');

        return [
            'kind' => $kind,
            'chapterNum' => $isBylaws ? 2 : 1,
            'kicker' => $isBylaws ? ($statutes['bylaws_kicker'] ?? '') : ($statutes['statutes_kicker'] ?? ''),
            'title' => $isBylaws ? ($statutes['bylaws_title'] ?? '') : ($statutes['statutes_title'] ?? ''),
            'preamble' => $isBylaws ? '' : ($statutes['preamble'] ?? ''),
            'preambleTitle' => $isBylaws ? trim($termsHeading) : ($statutes['preamble_title'] ?? ''),
            'articles' => $articles,
            'agreeRoute' => $isBylaws ? 'beyond.membership.agree_bylaws' : 'beyond.membership.agree_statutes',
            'acceptPrompt' => $isBylaws ? __('cwa.membership.accept_bylaws') : __('cwa.membership.accept_statutes'),
            'metaKey' => $isBylaws ? 'cwa.membership.bylaws_meta' : 'cwa.membership.statutes_meta',
            'pdfUrl' => asset('branding/cwa-statutes-'.(app()->getLocale() === 'fr' ? 'fr' : 'en').'.pdf'),
        ];
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
