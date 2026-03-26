@extends('install.layout', ['title' => 'Finalize Installation | Neolifeporium', 'heading' => 'Finalize Installation', 'progress' => 95])

@section('content')
<form action="{{ route('install.finish.run') }}" method="POST" class="space-y-4">
    @csrf
    <p class="text-slate-600">Finalization will generate the application key, cache configuration/routes/views, and create the installation lock file.</p>
    <button class="rounded-full bg-emerald-700 px-6 py-3 text-sm font-semibold text-white">Finalize and Lock Installer</button>
</form>
@endsection
