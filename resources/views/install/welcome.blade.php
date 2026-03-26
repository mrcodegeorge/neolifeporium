@extends('install.layout', ['title' => 'Install | Neolifeporium', 'heading' => 'Welcome', 'progress' => 10])

@section('content')
<div class="space-y-6">
    <p class="text-slate-600">Neolifeporium is an agritech marketplace and intelligence platform for farmers, vendors, and experts across Ghana and Africa.</p>
    <p class="text-slate-600">This guided installer will configure your environment, database, administrator account, and final system settings.</p>
    <a href="{{ route('install.requirements') }}" class="inline-flex rounded-full bg-emerald-700 px-6 py-3 text-sm font-semibold text-white">Start Installation</a>
</div>
@endsection
