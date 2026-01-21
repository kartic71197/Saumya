<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Services\Audit\ImpersonateAuditService;

class ImpersonationController extends Controller
{
    protected ImpersonateAuditService $auditService;

    public function __construct(ImpersonateAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    // Start impersonation
    public function start(Request $request, $adminId)
    {
        $superadmin = Auth::user();
        \Log::info('Superadmin ' . $superadmin->id . ' attempting to impersonate admin ' . $adminId);

        if ($superadmin->role_id != 1) {
            abort(403, 'Unauthorized');
        }

        if ($superadmin->id == $adminId) {
            return back()->with('error', 'Cannot impersonate yourself.');
        }

        $admin = User::findOrFail($adminId);

        if ($admin->role_id == 1) {
            return back()->with('error', 'Cannot impersonate another superadmin.');
        }

        // ✅ Regenerate session before impersonation
        Session::regenerate();

        // Store impersonation data in session
        session([
            'impersonator_id' => $superadmin->id,
            'impersonation_started_at' => now()->toDateTimeString(),
        ]);

        // Log impersonation start in audits
        $this->auditService->impersonateStart($superadmin->id, $admin->id, $admin->organization_id);

        // Log in as admin
        Auth::login($admin, remember: false);

        \Log::info("Impersonation started", [
            'impersonator_id' => session('impersonator_id'),
            'impersonated_id' => $admin->id,
            'session_id' => session()->getId(),
        ]);

        return redirect()->route('dashboard')->with('impersonating', true);
    }

    // Stop impersonation and return to superadmin
    public function stop(Request $request)
    {
        if (!session()->has('impersonator_id')) {
            return back()->with('error', 'Not impersonating anyone.');
        }

        $superadminId = session('impersonator_id');
        $superadmin = User::findOrFail($superadminId);
        $impersonatedUser = Auth::user();

        // Log out current admin
        Auth::logout();

        // Regenerate session
        Session::regenerate();

        // Log in as superadmin
        Auth::login($superadmin, remember: false);

        // Clear impersonation session data
        Session::forget(['impersonator_id', 'impersonation_started_at']);

        // Log impersonation end in audits
        $this->auditService->impersonateEnd($superadmin->id, $impersonatedUser->id, $impersonatedUser->organization_id);

        \Log::info("Impersonation ended", [
            'impersonator_id' => $superadmin->id,
            'stopped_at' => now()->toDateTimeString(),
            'ip' => $request->ip(),
        ]);

        // Redirect to superadmin dashboard
        return redirect()->route('admin.dashboard');
    }
}
