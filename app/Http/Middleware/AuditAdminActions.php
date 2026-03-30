<?php

namespace App\Http\Middleware;

use App\Services\Admin\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditAdminActions
{
    public function __construct(private readonly AuditLogger $logger) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldLog($request)) {
            return $response;
        }

        $this->logger->log($request, $this->resolveAction($request));

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        if (! $request->route()) {
            return false;
        }

        $methods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        if (! in_array(strtoupper($request->method()), $methods, true)) {
            return false;
        }

        return str_starts_with((string) $request->path(), 'admin-panel');
    }

    private function resolveAction(Request $request): string
    {
        $routeName = $request->route()?->getName();
        if ($routeName) {
            return $routeName;
        }

        return strtoupper($request->method()).' '.$request->path();
    }
}
