<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Install Neolifeporium' }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-white p-6 shadow-xl shadow-black/5 sm:p-8">
            <div class="mb-8">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-700">Neolifeporium Setup Wizard</p>
                <h1 class="mt-2 text-2xl font-black sm:text-3xl">{{ $heading ?? 'Install Neolifeporium' }}</h1>
            </div>

            @if (filter_var(env('INSTALLER_RECOVERY_MODE', false), FILTER_VALIDATE_BOOL))
                <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Recovery mode is enabled. Installer access is temporarily reopened while <code>installed.lock</code> exists.
                </div>
            @endif

            <div class="mb-8 overflow-hidden rounded-full bg-slate-100">
                <div class="h-2 bg-emerald-600 transition-all" style="width: {{ $progress ?? 10 }}%"></div>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>
