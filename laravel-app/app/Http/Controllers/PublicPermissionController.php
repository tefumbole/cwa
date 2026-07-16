<?php

namespace App\Http\Controllers;

use App\BeyondUser;
use App\Services\BeyondWasenderService;
use App\StaffPermission;
use App\Support\WhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PublicPermissionController extends Controller
{
    protected $staffRoles = [
        'admin', 'super_admin', 'director', 'manager', 'staff', 'employee', 'teacher',
        'administrator', 'hr', 'accountant', 'technician', 'engineer',
    ];

    public function index()
    {
        $user = Auth::guard('beyond')->user();
        $isMember = $user && in_array(strtolower((string) $user->role), $this->staffRoles, true);

        return view('beyond.permissions.index', [
            'user' => $user,
            'isMember' => $isMember,
            'otpOk' => (bool) session('beyond_otp_verified'),
        ]);
    }

    public function store(Request $request, BeyondWasenderService $whatsapp)
    {
        $user = Auth::guard('beyond')->user();
        if (! $user || ! session('beyond_otp_verified')) {
            return redirect('/login')->with('warning', 'Please log in as a company member to apply for permission.');
        }
        if (! in_array(strtolower((string) $user->role), $this->staffRoles, true)) {
            return back()->withErrors(['form' => 'Only company members (staff) can apply for permissions.']);
        }

        $data = $request->validate([
            'company_role' => 'required|string|max:150',
            'from_at' => 'required|date',
            'to_at' => 'required|date|after:from_at',
            'reason' => 'required|string|max:3000',
            'phone' => 'nullable|string|max:50',
        ]);

        do {
            $ref = 'PERM-'.random_int(100000, 999999);
        } while (StaffPermission::where('reference_number', $ref)->exists());

        $permission = StaffPermission::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => $data['phone'] ?: $user->phone,
            'company_role' => $data['company_role'],
            'from_at' => $data['from_at'],
            'to_at' => $data['to_at'],
            'reason' => $data['reason'],
            'status' => StaffPermission::STATUS_PENDING,
            'reference_number' => $ref,
        ]);

        $phone = $permission->phone;
        if ($phone) {
            try {
                $msg = WhatsAppMessage::statusBlock('🗓️', 'Permission Request')
                    .WhatsAppMessage::greeting($permission->full_name)
                    ."Your permission request has been submitted and is awaiting approval.\n\n"
                    .WhatsAppMessage::bullet('Reference', $permission->reference_number)
                    .WhatsAppMessage::bullet('Role', $permission->company_role)
                    .WhatsAppMessage::bullet('From', $permission->from_at->format('Y-m-d H:i'))
                    .WhatsAppMessage::bullet('To', $permission->to_at->format('Y-m-d H:i'))
                    .WhatsAppMessage::footer();
                $whatsapp->sendText($phone, $msg);
            } catch (\Throwable $e) {
                Log::warning('Permission request WhatsApp failed: '.$e->getMessage());
            }
        }

        return redirect()->route('beyond.permissions.confirmation', $permission->reference_number);
    }

    public function confirmation($reference)
    {
        $permission = StaffPermission::where('reference_number', $reference)->first();

        return view('beyond.permissions.confirmation', compact('permission', 'reference'));
    }
}
