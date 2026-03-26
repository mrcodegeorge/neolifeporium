<?php

namespace App\Http\Controllers\Web;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use App\Services\Auth\RoleOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly RoleOnboardingService $roleOnboarding
    ) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $remember = (bool) $request->boolean('remember');

        $attempted = Auth::attempt(['email' => $credentials['login'], 'password' => $credentials['password']], $remember);

        if (! $attempted) {
            $attempted = Auth::attempt(['phone' => $credentials['login'], 'password' => $credentials['password']], $remember);
        }

        if (! $attempted) {
            return back()
                ->withErrors(['login' => 'Invalid credentials provided.'])
                ->onlyInput('login');
        }

        $request->session()->regenerate();
        $request->user()?->forceFill(['last_login_at' => now()])->save();

        $user = $request->user()->load('roles', 'vendorProfile', 'agronomistProfile');
        $activeRole = session('active_role');

        return redirect()->to(
            $this->roleOnboarding->resolveDashboardRedirect($user, is_string($activeRole) ? $activeRole : null)
        );
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload['roles'] = $payload['roles'] ?? [$payload['role'] ?? RoleType::Farmer->value];

        if ($request->hasFile('vendor_document')) {
            $payload['vendor_document_path'] = $this->roleOnboarding->storeVerificationDocument(
                $request->file('vendor_document'),
                'vendor-'.$payload['name']
            );
        }

        if ($request->hasFile('certification_document')) {
            $payload['certification_document_path'] = $this->roleOnboarding->storeVerificationDocument(
                $request->file('certification_document'),
                'expert-'.$payload['name']
            );
        }

        $user = $this->authService->register($payload);

        Auth::login($user);
        $request->session()->regenerate();

        $user->load('roles', 'vendorProfile', 'agronomistProfile');

        return redirect()->to($this->roleOnboarding->resolveDashboardRedirect($user))
            ->with('status', 'Account created. Complete onboarding while approvals are reviewed.');
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'You have been logged out.');
    }
}
