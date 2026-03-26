<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use App\Services\Auth\RoleOnboardingService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly RoleOnboardingService $roleOnboarding
    ) {}

    public function register(RegisterRequest $request)
    {
        $payload = $request->validated();
        $payload['roles'] = $payload['roles'] ?? [$payload['role'] ?? 'farmer'];

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

        return ApiResponse::success($user, 'Registration completed.', 201);
    }

    public function login(LoginRequest $request)
    {
        return ApiResponse::success($this->authService->login($request->validated()), 'Login successful.');
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logged out successfully.');
    }
}
