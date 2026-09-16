<?php

namespace App\Http\Controllers;

use App\CwaMember;
use App\Services\MembershipAdmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Spatie\Permission\Models\Role;

class AdminMembershipController extends Controller
{
    protected $all_permission = [];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $role = Role::find(Auth::user()->role_id);
                if ($role) {
                    foreach (Role::findByName($role->name)->permissions as $permission) {
                        $this->all_permission[] = $permission->name;
                    }
                }
            }
            View::share('all_permission', $this->all_permission);

            return $next($request);
        });
    }

    protected function authorizeMembership()
    {
        if (in_array('membership_module', $this->all_permission, true)
            || in_array('membership.view', $this->all_permission, true)
            || in_array('membership.manage', $this->all_permission, true)) {
            return;
        }
        abort(403, 'You are not allowed to access Membership.');
    }

    public function awaiting(Request $request)
    {
        return $this->index($request, CwaMember::STATUS_AWAITING, 'membership.awaiting');
    }

    public function members(Request $request)
    {
        return $this->index($request, CwaMember::STATUS_APPROVED, 'membership.members');
    }

    public function rejected(Request $request)
    {
        return $this->index($request, CwaMember::STATUS_REJECTED, 'membership.rejected');
    }

    protected function index(Request $request, $status, $tab)
    {
        $this->authorizeMembership();
        $q = trim((string) $request->get('q'));
        $query = CwaMember::query()->where('status', $status)->orderByDesc('id');
        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('diocese', 'like', $like)
                    ->orWhere('parish', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }
        $items = $query->paginate(30)->appends($request->query());

        return view('membership.index', [
            'items' => $items,
            'q' => $q,
            'status' => $status,
            'membershipTab' => $tab,
            'counts' => $this->counts(),
        ]);
    }

    public function show($id)
    {
        $this->authorizeMembership();
        $member = CwaMember::findOrFail($id);

        return view('membership.show', [
            'member' => $member,
            'membershipTab' => $this->tabFor($member->status),
            'counts' => $this->counts(),
        ]);
    }

    public function approve(Request $request, $id)
    {
        $this->authorizeMembership();
        $member = CwaMember::findOrFail($id);
        if ($member->status === CwaMember::STATUS_REJECTED) {
            return back()->with('not_permitted', 'This membership was rejected.');
        }

        try {
            $letter = app(MembershipAdmissionService::class)->createAdmissionLetter($member);
        } catch (\Throwable $e) {
            \Log::error('Membership approval letter failed: '.$e->getMessage());

            return back()->with('not_permitted', 'Could not create the Letter of Admission. Please try again.');
        }
        $member->reviewed_at = now();
        $member->reviewed_by = Auth::id();
        $member->save();

        $msg = 'Letter of Admission created and sent to Signer (Awaiting Signature).';
        if ($letter) {
            $msg .= ' Open Letters → Awaiting Signature.';
        }

        return redirect()->route('membership.show', $member->id)->with('message', $msg);
    }

    public function reject(Request $request, $id)
    {
        $this->authorizeMembership();
        $member = CwaMember::findOrFail($id);
        $reason = trim((string) $request->input('rejection_reason'));
        $member->status = CwaMember::STATUS_REJECTED;
        $member->rejection_reason = $reason !== '' ? $reason : null;
        $member->reviewed_at = now();
        $member->reviewed_by = Auth::id();
        $member->save();

        return redirect()->route('membership.rejected')->with('message', 'Membership rejected.');
    }

    protected function counts()
    {
        return [
            'membership.awaiting' => CwaMember::where('status', CwaMember::STATUS_AWAITING)->count(),
            'membership.members' => CwaMember::where('status', CwaMember::STATUS_APPROVED)->count(),
            'membership.rejected' => CwaMember::where('status', CwaMember::STATUS_REJECTED)->count(),
        ];
    }

    protected function tabFor($status)
    {
        if ($status === CwaMember::STATUS_APPROVED) {
            return 'membership.members';
        }
        if ($status === CwaMember::STATUS_REJECTED) {
            return 'membership.rejected';
        }

        return 'membership.awaiting';
    }
}
