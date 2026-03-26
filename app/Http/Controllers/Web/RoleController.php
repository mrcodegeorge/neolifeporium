<?php

namespace App\Http\Controllers\Web;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Services\Auth\RoleOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(private readonly RoleOnboardingService $roleOnboarding) {}

    public function choose(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load('roles', 'vendorProfile', 'agronomistProfile');

        if ($user->roles->count() <= 1) {
            return redirect()->to($this->roleOnboarding->resolveDashboardRedirect($user));
        }

        return view('auth.choose-role', [
            'roles' => $user->roles->pluck('slug')->values(),
            'activeRole' => session('active_role'),
        ]);
    }

    public function switch(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'role' => ['required', 'string', 'in:farmer,vendor,agronomist,admin,super_admin'],
        ]);

        abort_unless(
            $request->user()->roles()->where('slug', $payload['role'])->exists(),
            403,
            'You do not have access to this role.'
        );

        $request->session()->put('active_role', $payload['role']);

        return redirect()->to($this->roleOnboarding->mapRoleToDashboardRoute($payload['role']));
    }

    public function onboarding(Request $request): View
    {
        $user = $request->user()->load('roles', 'vendorProfile', 'agronomistProfile', 'farmerProfile');

        return view('auth.role-onboarding', [
            'roleStatuses' => $this->roleOnboarding->roleStatuses($user),
            'user' => $user,
        ]);
    }

    public function applyVendor(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'max:255'],
            'product_category' => ['required', 'string', 'max:255'],
            'vendor_description' => ['nullable', 'string', 'max:1000'],
            'vendor_region' => ['nullable', 'string', 'max:255'],
            'vendor_district' => ['nullable', 'string', 'max:255'],
            'vendor_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('vendor_document')) {
            $payload['vendor_document_path'] = $this->roleOnboarding->storeVerificationDocument(
                $request->file('vendor_document'),
                'vendor-'.$request->user()->id
            );
        }

        $this->roleOnboarding->applyForRole($request->user(), RoleType::Vendor->value, $payload);

        return back()->with('status', 'Vendor application submitted for admin review.');
    }

    public function applyExpert(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'specialty' => ['required', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'expert_bio' => ['nullable', 'string', 'max:2000'],
            'regions_served_text' => ['nullable', 'string', 'max:500'],
            'certification_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if (! empty($payload['regions_served_text'])) {
            $payload['regions_served'] = collect(explode(',', $payload['regions_served_text']))
                ->map(fn (string $region): string => trim($region))
                ->filter()
                ->values()
                ->all();
        }

        if ($request->hasFile('certification_document')) {
            $payload['certification_document_path'] = $this->roleOnboarding->storeVerificationDocument(
                $request->file('certification_document'),
                'expert-'.$request->user()->id
            );
        }

        $this->roleOnboarding->applyForRole($request->user(), RoleType::Agronomist->value, $payload);

        return back()->with('status', 'Expert application submitted for admin review.');
    }

    public function redirectDashboard(Request $request): RedirectResponse
    {
        $user = $request->user()->load('roles', 'vendorProfile', 'agronomistProfile');
        $activeRole = session('active_role');

        return redirect()->to(
            $this->roleOnboarding->resolveDashboardRedirect($user, is_string($activeRole) ? $activeRole : null)
        );
    }
}
