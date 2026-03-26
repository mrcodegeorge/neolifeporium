<?php

namespace App\Http\Middleware;

use App\Services\Install\InstallService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    public function __construct(private readonly InstallService $installService) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        if ($request->is('install*') && $this->installService->canAccessInstaller()) {
            return $next($request);
        }

        if ($this->installService->isInstalled()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Application is not installed. Complete setup at /install.',
            ], 503);
        }

        return redirect()->route('install.welcome');
    }
}
