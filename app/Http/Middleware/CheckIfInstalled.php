<?php

namespace App\Http\Middleware;

use App\Services\Install\InstallService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfInstalled
{
    public function __construct(private readonly InstallService $installService) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->installService->canAccessInstaller()) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
