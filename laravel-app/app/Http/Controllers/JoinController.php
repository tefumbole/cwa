<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JoinController extends Controller
{
    public function show()
    {
        return view('beyond.join', [
            'ageRanges' => trans('cwa.join.ages'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'diocese' => 'required|string|max:120',
            'parish' => 'required|string|max:120',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:120',
            'age_range' => 'nullable|string|max:40',
            'message' => 'nullable|string|max:2000',
        ], [], [
            'name' => __('cwa.join.name'),
            'diocese' => __('cwa.join.diocese'),
            'parish' => __('cwa.join.parish'),
            'phone' => __('cwa.join.phone'),
            'email' => __('cwa.join.email'),
        ]);

        $lines = [
            __('cwa.join.wa_title'),
            '',
            __('cwa.join.name') . ': ' . $data['name'],
            __('cwa.join.diocese') . ': ' . $data['diocese'],
            __('cwa.join.parish') . ': ' . $data['parish'],
            __('cwa.join.phone') . ': ' . $data['phone'],
            __('cwa.join.email') . ': ' . ($data['email'] ?: '—'),
            __('cwa.join.age') . ': ' . ($data['age_range'] ?: '—'),
        ];
        if (! empty($data['message'])) {
            $lines[] = '';
            $lines[] = __('cwa.join.message') . ':';
            $lines[] = $data['message'];
        }
        $body = implode("\n", $lines);

        $waPhone = preg_replace('/\D+/', '', \App\Support\SiteContent::text('contact.phone', ''));
        $email = \App\Support\SiteContent::text('contact.email', 'info@cwacam.org');

        if ($waPhone) {
            return redirect()->away('https://wa.me/' . $waPhone . '?text=' . rawurlencode($body));
        }

        $mailto = 'mailto:' . $email
            . '?subject=' . rawurlencode(__('cwa.join.wa_subject', ['name' => $data['name']]))
            . '&body=' . rawurlencode($body);

        return redirect()->away($mailto);
    }
}
