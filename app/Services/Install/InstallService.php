<?php

namespace App\Services\Install;

use App\Enums\RoleType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use PDO;
use Throwable;

class InstallService
{
    public function isRecoveryMode(): bool
    {
        return filter_var(env('INSTALLER_RECOVERY_MODE', false), FILTER_VALIDATE_BOOL);
    }

    public function lockPath(): string
    {
        return storage_path('app/installed.lock');
    }

    public function isInstalled(): bool
    {
        return file_exists($this->lockPath());
    }

    public function canAccessInstaller(): bool
    {
        return ! $this->isInstalled() || $this->isRecoveryMode();
    }

    public function createLock(): void
    {
        if (! is_dir(dirname($this->lockPath()))) {
            mkdir(dirname($this->lockPath()), 0755, true);
        }

        file_put_contents($this->lockPath(), now()->toDateTimeString());
    }

    public function requirements(): array
    {
        $mysqlCheck = $this->testMysqlConnection([
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', ''),
            'username' => env('DB_USERNAME', ''),
            'password' => env('DB_PASSWORD', ''),
        ]);

        $checks = [
            'php' => [
                'label' => 'PHP >= 8.2',
                'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'details' => PHP_VERSION,
            ],
            'mysql' => [
                'label' => 'MySQL connection',
                'ok' => $mysqlCheck['ok'],
                'details' => $mysqlCheck['ok'] ? 'Connected' : $mysqlCheck['message'],
            ],
            'pdo' => [
                'label' => 'PDO extension',
                'ok' => extension_loaded('pdo'),
                'details' => extension_loaded('pdo') ? 'Loaded' : 'Missing',
            ],
            'curl' => [
                'label' => 'cURL extension',
                'ok' => extension_loaded('curl'),
                'details' => extension_loaded('curl') ? 'Loaded' : 'Missing',
            ],
            'mbstring' => [
                'label' => 'Mbstring extension',
                'ok' => extension_loaded('mbstring'),
                'details' => extension_loaded('mbstring') ? 'Loaded' : 'Missing',
            ],
            'openssl' => [
                'label' => 'OpenSSL extension',
                'ok' => extension_loaded('openssl'),
                'details' => extension_loaded('openssl') ? 'Loaded' : 'Missing',
            ],
            'json' => [
                'label' => 'JSON extension',
                'ok' => extension_loaded('json'),
                'details' => extension_loaded('json') ? 'Loaded' : 'Missing',
            ],
        ];

        return $checks;
    }

    public function testMysqlConnection(array $config): array
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'],
                $config['database']
            );
            new PDO($dsn, $config['username'], $config['password']);

            return ['ok' => true, 'message' => 'Database connection successful.'];
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => $exception->getMessage()];
        }
    }

    public function writeEnv(array $pairs): void
    {
        $envPath = base_path('.env');
        $env = file_exists($envPath) ? file_get_contents($envPath) : '';

        foreach ($pairs as $key => $value) {
            $value = (string) $value;
            $escaped = str_contains($value, ' ') ? "\"{$value}\"" : $value;
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $env) === 1) {
                $env = preg_replace($pattern, "{$key}={$escaped}", $env);
            } else {
                $env .= PHP_EOL."{$key}={$escaped}";
            }
        }

        file_put_contents($envPath, trim($env).PHP_EOL);
    }

    public function runMigrations(bool $seed = false): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            if ($seed) {
                Artisan::call('db:seed', ['--force' => true]);
                $output .= PHP_EOL.Artisan::output();
            }

            return ['ok' => true, 'message' => trim($output)];
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => $exception->getMessage()];
        }
    }

    public function createSuperAdmin(array $payload): array
    {
        try {
            $user = User::query()->updateOrCreate(
                ['email' => $payload['email']],
                [
                    'name' => $payload['name'],
                    'phone' => $payload['phone'] ?? null,
                    'password' => Hash::make($payload['password']),
                    'status' => 'active',
                    'preferred_channel' => 'email',
                ]
            );

            $role = Role::query()->firstOrCreate(
                ['slug' => RoleType::SuperAdmin->value],
                ['name' => 'Super Admin']
            );

            if (! $user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }

            return ['ok' => true, 'message' => 'Admin account created.'];
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => $exception->getMessage()];
        }
    }

    public function finalize(): array
    {
        try {
            if (empty(env('APP_KEY'))) {
                Artisan::call('key:generate', ['--force' => true]);
            }

            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            $this->createLock();

            return ['ok' => true, 'message' => 'Installation finalized successfully.'];
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => $exception->getMessage()];
        }
    }
}
