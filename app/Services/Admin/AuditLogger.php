<?php

namespace App\Services\Admin;

use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class AuditLogger
{
    public function log(Request $request, string $action): void
    {
        if (! Schema::hasTable('admin_audit_logs')) {
            return;
        }

        $payload = $this->sanitize($request->all());

        AdminAuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'method' => strtoupper($request->method()),
            'path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $payload,
        ]);
    }

    private function sanitize(array $payload): array
    {
        $redacted = Arr::except($payload, [
            'password',
            'password_confirmation',
            '_token',
            'token',
        ]);

        return $redacted;
    }
}
