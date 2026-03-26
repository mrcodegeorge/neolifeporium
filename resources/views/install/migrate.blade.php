@extends('install.layout', ['title' => 'Migrate Database | Neolifeporium', 'heading' => 'Database Migration', 'progress' => 50])

@section('content')
<form action="{{ route('install.migrate.run') }}" method="POST" class="space-y-4">
    @csrf
    <p class="text-slate-600">Run Laravel migrations to create all required tables. You can also run seeders for demo data.</p>
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="run_seeders" value="1">
        <span class="text-sm">Run seeders after migration</span>
    </label>
    <div>
        <button class="rounded-full bg-emerald-700 px-6 py-3 text-sm font-semibold text-white">Run Migration</button>
    </div>
</form>
@endsection
