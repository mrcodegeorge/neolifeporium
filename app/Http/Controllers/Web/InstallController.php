<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Install\InstallService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallController extends Controller
{
    public function __construct(private readonly InstallService $installService) {}

    public function welcome(): View
    {
        return view('install.welcome');
    }

    public function requirements(): View
    {
        $checks = $this->installService->requirements();
        $allPassed = collect($checks)->every(fn (array $check) => $check['ok']);

        return view('install.requirements', [
            'checks' => $checks,
            'allPassed' => $allPassed,
        ]);
    }

    public function database(): View
    {
        return view('install.database', [
            'defaults' => [
                'host' => session('install.db.host', env('DB_HOST', '127.0.0.1')),
                'port' => session('install.db.port', env('DB_PORT', '3306')),
                'database' => session('install.db.database', env('DB_DATABASE', 'neolifeporium')),
                'username' => session('install.db.username', env('DB_USERNAME', 'root')),
                'password' => session('install.db.password', env('DB_PASSWORD', '')),
            ],
        ]);
    }

    public function saveDatabase(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer'],
            'database' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string'],
        ]);

        $result = $this->installService->testMysqlConnection($payload);
        if (! $result['ok']) {
            return back()->withInput()->withErrors(['database' => $result['message']]);
        }

        $this->installService->writeEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $payload['host'],
            'DB_PORT' => $payload['port'],
            'DB_DATABASE' => $payload['database'],
            'DB_USERNAME' => $payload['username'],
            'DB_PASSWORD' => $payload['password'] ?? '',
        ]);

        session([
            'install.db' => $payload,
            'install.database_configured' => true,
        ]);

        return redirect()->route('install.migrate')->with('status', 'Database settings saved and verified.');
    }

    public function migrate(): View
    {
        abort_unless(session('install.database_configured', false), 403, 'Configure database first.');

        return view('install.migrate');
    }

    public function runMigrate(Request $request): RedirectResponse
    {
        abort_unless(session('install.database_configured', false), 403, 'Configure database first.');

        $payload = $request->validate([
            'run_seeders' => ['nullable', 'boolean'],
        ]);

        $result = $this->installService->runMigrations((bool) ($payload['run_seeders'] ?? false));
        if (! $result['ok']) {
            return back()->withErrors(['migration' => $result['message']]);
        }

        session(['install.migrated' => true]);

        return redirect()->route('install.admin')->with('status', 'Database migration completed.');
    }

    public function admin(): View
    {
        abort_unless(session('install.migrated', false), 403, 'Run migrations first.');

        return view('install.admin');
    }

    public function saveAdmin(Request $request): RedirectResponse
    {
        abort_unless(session('install.migrated', false), 403, 'Run migrations first.');

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $result = $this->installService->createSuperAdmin($payload);
        if (! $result['ok']) {
            return back()->withInput()->withErrors(['admin' => $result['message']]);
        }

        session(['install.admin_created' => true]);

        return redirect()->route('install.config')->with('status', 'Admin account created.');
    }

    public function config(): View
    {
        abort_unless(session('install.admin_created', false), 403, 'Create admin account first.');

        return view('install.config', [
            'defaults' => [
                'site_name' => env('APP_NAME', 'Neolifeporium'),
                'app_url' => env('APP_URL', 'http://localhost'),
                'currency' => env('APP_CURRENCY', 'GHS'),
                'timezone' => env('APP_TIMEZONE', 'Africa/Accra'),
                'paystack_public' => env('PAYSTACK_PUBLIC_KEY', ''),
                'paystack_secret' => env('PAYSTACK_SECRET_KEY', ''),
                'momo_base_url' => env('MTN_MOMO_BASE_URL', ''),
                'momo_key' => env('MTN_MOMO_API_KEY', ''),
            ],
        ]);
    }

    public function saveConfig(Request $request): RedirectResponse
    {
        abort_unless(session('install.admin_created', false), 403, 'Create admin account first.');

        $payload = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'app_url' => ['required', 'url'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:64'],
            'paystack_public' => ['nullable', 'string'],
            'paystack_secret' => ['nullable', 'string'],
            'momo_base_url' => ['nullable', 'string'],
            'momo_key' => ['nullable', 'string'],
        ]);

        $this->installService->writeEnv([
            'APP_NAME' => $payload['site_name'],
            'APP_URL' => $payload['app_url'],
            'APP_TIMEZONE' => $payload['timezone'],
            'APP_CURRENCY' => $payload['currency'],
            'PAYSTACK_PUBLIC_KEY' => $payload['paystack_public'] ?? '',
            'PAYSTACK_SECRET_KEY' => $payload['paystack_secret'] ?? '',
            'MTN_MOMO_BASE_URL' => $payload['momo_base_url'] ?? '',
            'MTN_MOMO_API_KEY' => $payload['momo_key'] ?? '',
        ]);

        session(['install.configured' => true]);

        return redirect()->route('install.finish')->with('status', 'System settings saved.');
    }

    public function finish(): View
    {
        abort_unless(session('install.configured', false), 403, 'System configuration is required first.');

        return view('install.finish');
    }

    public function runFinish(): RedirectResponse
    {
        abort_unless(session('install.configured', false), 403, 'System configuration is required first.');

        $result = $this->installService->finalize();
        if (! $result['ok']) {
            return back()->withErrors(['finalize' => $result['message']]);
        }

        session()->forget('install');

        return redirect()->route('install.success');
    }

    public function success(): View
    {
        abort_unless($this->installService->isInstalled(), 403, 'Installation has not been finalized.');

        return view('install.success');
    }
}
