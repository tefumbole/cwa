<?php

namespace App\Services;

use App\CwaMember;
use App\Customer;
use App\CustomerGroup;
use App\Http\Controllers\LetterController;
use App\Letter;
use App\LetterCategory;
use App\LetterTemplate;
use App\Support\LetterReference;
use App\Support\LetterSignature;
use App\Support\MembershipWhatsApp;
use Illuminate\Support\Facades\Auth;

class MembershipAdmissionService
{
    const TEMPLATE_NAME = 'Letter of Admission';

    public function createAdmissionLetter(CwaMember $member)
    {
        if ($member->letter_id) {
            return Letter::find($member->letter_id);
        }

        $actor = Auth::user();
        $year = (int) date('Y');
        $customer = $this->ensureCustomer($member);
        $template = $this->ensureTemplate();

        $portraitHtml = '';
        if ($member->portrait_path) {
            $abs = public_path($member->portrait_path);
            $src = is_file($abs) ? str_replace('\\', '/', $abs) : url('/'.ltrim($member->portrait_path, '/'));
            $portraitHtml = '<p style="text-align:center;margin:18px 0;"><img src="'.e($src).'" alt="" width="160" height="160" style="border-radius:50%;"></p>';
        }

        $body = $template && $template->body
            ? $template->body
            : $this->defaultBody();

        $body = str_replace(
            ['[year]', '[diocese]', '[parish]', '[portrait]'],
            [(string) $year, e($member->diocese), e($member->parish), $portraitHtml],
            $body
        );

        $data = [
            'category_id' => $template ? $template->category_id : null,
            'template_id' => $template ? $template->id : null,
            'reference' => LetterReference::next('membership'),
            'name' => self::TEMPLATE_NAME,
            'to' => (string) $customer->id,
            'cc' => null,
            'header' => $template ? $template->header : 'Catholic Women\'s Association (CWA) Cameroon',
            'subject' => $template && $template->subject ? $template->subject : 'Letter of Admission — [name]',
            'body' => $body,
            'footer' => $template && $template->footer ? $template->footer : 'To serve and not to be served (Mt 20:28)',
            'is_active' => 1,
            'created_by' => $actor ? $actor->id : 1,
            'people_type' => 'customer',
        ];

        $this->skipToSigner($data, $actor);

        $letter = Letter::create($data);

        $member->letter_id = $letter->id;
        $member->customer_id = $customer->id;
        $member->admitted_year = $year;
        $member->save();

        try {
            (new LetterController())->sendMsgToConcernPerson($letter);
        } catch (\Throwable $e) {
            \Log::warning('Membership admission letter notify failed: '.$e->getMessage());
        }

        return $letter;
    }

    public function onLetterDelivered(Letter $letter, $pdfUrl = null)
    {
        $member = CwaMember::where('letter_id', $letter->id)->first();
        if (! $member) {
            return;
        }

        $year = $member->admitted_year ?: (int) date('Y');
        $member->status = CwaMember::STATUS_APPROVED;
        $member->admitted_at = now();
        $member->admitted_year = $year;
        $member->save();
    }

    public function captionForLetter(Letter $letter)
    {
        $member = CwaMember::where('letter_id', $letter->id)->first();
        if (! $member) {
            return null;
        }
        $year = $member->admitted_year ?: (int) date('Y');
        $scan = url('/letters/scan/'.$letter->id);

        return MembershipWhatsApp::letterSigned($member->name, $year, $scan);
    }

    public function isAdmissionLetter(Letter $letter)
    {
        if (CwaMember::where('letter_id', $letter->id)->exists()) {
            return true;
        }
        $hay = strtolower(trim((string) ($letter->name ?? '').' '.(string) ($letter->subject ?? '')));

        return strpos($hay, 'letter of admission') !== false;
    }

    protected function ensureCustomer(CwaMember $member)
    {
        if ($member->customer_id) {
            $existing = Customer::find($member->customer_id);
            if ($existing) {
                return $existing;
            }
        }

        $phone = $member->phone;
        $customer = Customer::where('phone_number', $phone)->first();
        if ($customer) {
            $customer->name = $member->name;
            if ($member->email) {
                $customer->email = $member->email;
            }
            $customer->address = trim($member->diocese.' / '.$member->parish);
            $customer->save();

            return $customer;
        }

        $group = CustomerGroup::first();

        return Customer::create([
            'customer_group_id' => $group ? $group->id : 1,
            'name' => $member->name,
            'email' => $member->email,
            'phone_number' => $phone,
            'address' => trim($member->diocese.' / '.$member->parish) ?: 'N/A',
            'city' => 'Cameroon',
            'is_active' => true,
        ]);
    }

    protected function ensureTemplate()
    {
        $template = LetterTemplate::where('name', self::TEMPLATE_NAME)->where('is_active', true)->first();
        if ($template) {
            return $template;
        }

        $category = LetterCategory::where('name', 'Membership')->first();
        if (! $category) {
            $category = LetterCategory::create(['name' => 'Membership', 'is_active' => 1]);
        }

        return LetterTemplate::create([
            'category_id' => $category->id,
            'name' => self::TEMPLATE_NAME,
            'header' => 'Catholic Women\'s Association (CWA) Cameroon',
            'subject' => 'Letter of Admission — [name]',
            'body' => $this->defaultBody(),
            'footer' => 'To serve and not to be served (Mt 20:28)',
            'is_active' => 1,
            'created_by' => Auth::id() ?: 1,
        ]);
    }

    protected function defaultBody()
    {
        return '<p>Dear [name],</p>'
            .'<p>The National Executive of the Catholic Women\'s Association (CWA) Cameroon is pleased to admit you as a member. Your <strong>year of joining</strong> is <strong>[year]</strong>.</p>'
            .'<p>Diocese: [diocese]<br>Parish: [parish]</p>'
            .'[portrait]'
            .'<p>We welcome you into this apostolate of faith, service and sisterhood. May Our Lady of the Immaculate Conception accompany you.</p>'
            .'<p><em>To serve and not to be served</em> (Mt 20:28)</p>';
    }

    protected function skipToSigner(array &$data, $actor)
    {
        $actorId = $actor ? $actor->id : ($data['created_by'] ?? 1);
        $data['is_rejected'] = 0;
        $data['reject_by'] = null;
        $data['is_edit'] = 1;
        $data['edit_by'] = $actorId;
        $data['edit_signed_at'] = now();
        $data['edit_signature'] = null;
        $data['is_approve'] = 1;
        $data['approved_by'] = $actorId;
        $data['approve_signed_at'] = now();
        $data['approve_signature'] = null;
        $data['is_sign'] = 0;
        $data['signed_by'] = null;
        $data['sign_signature'] = null;
        $data['sign_signed_at'] = null;
        $data['is_sent'] = 0;
        $data['sent_by'] = null;
        if ($actor && ! empty($actor->stemp)) {
            $data['edit_signature'] = LetterSignature::storeFromAccountFile($actor->stemp, 'edit');
        }
        if ($actor && ! empty($actor->approve)) {
            $data['approve_signature'] = LetterSignature::storeFromAccountFile($actor->approve, 'approve');
        }
    }
}
