@extends('install.layout', ['title' => 'System Config | Neolifeporium', 'heading' => 'System Configuration', 'progress' => 80])

@section('content')
<form action="{{ route('install.config.save') }}" method="POST" class="space-y-4">
    @csrf
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold">Site Name</label>
            <input name="site_name" value="{{ old('site_name', $defaults['site_name']) }}" required class="mt-1 w-full rounded-xl border-slate-200">
        </div>
        <div>
            <label class="text-sm font-semibold">App URL</label>
            <input name="app_url" value="{{ old('app_url', $defaults['app_url']) }}" required class="mt-1 w-full rounded-xl border-slate-200">
        </div>
        <div>
            <label class="text-sm font-semibold">Currency</label>
            <input name="currency" value="{{ old('currency', $defaults['currency']) }}" required class="mt-1 w-full rounded-xl border-slate-200">
        </div>
        <div>
            <label class="text-sm font-semibold">Timezone</label>
            <input name="timezone" value="{{ old('timezone', $defaults['timezone']) }}" required class="mt-1 w-full rounded-xl border-slate-200">
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold">Paystack Public Key</label>
            <input name="paystack_public" value="{{ old('paystack_public', $defaults['paystack_public']) }}" class="mt-1 w-full rounded-xl border-slate-200">
        </div>
        <div>
            <label class="text-sm font-semibold">Paystack Secret Key</label>
            <input name="paystack_secret" value="{{ old('paystack_secret', $defaults['paystack_secret']) }}" class="mt-1 w-full rounded-xl border-slate-200">
        </div>
        <div>
            <label class="text-sm font-semibold">MTN MoMo Base URL</label>
            <input name="momo_base_url" value="{{ old('momo_base_url', $defaults['momo_base_url']) }}" class="mt-1 w-full rounded-xl border-slate-200">
        </div>
        <div>
            <label class="text-sm font-semibold">MTN MoMo API Key</label>
            <input name="momo_key" value="{{ old('momo_key', $defaults['momo_key']) }}" class="mt-1 w-full rounded-xl border-slate-200">
        </div>
    </div>

    <button class="rounded-full bg-emerald-700 px-6 py-3 text-sm font-semibold text-white">Save Configuration</button>
</form>
@endsection
