@extends('install.layout', ['title' => 'Installation Complete | Neolifeporium', 'heading' => 'Installation Complete', 'progress' => 100])

@section('content')
<div class="space-y-6">
    <p class="text-slate-700">Neolifeporium is now fully installed and configured.</p>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('login') }}" class="rounded-full bg-emerald-700 px-6 py-3 text-sm font-semibold text-white">Login</a>
        <a href="{{ route('admin.panel') }}" class="rounded-full border border-slate-200 px-6 py-3 text-sm font-semibold">Admin Dashboard</a>
    </div>
</div>
@endsection
