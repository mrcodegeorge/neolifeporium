@extends('install.layout', ['title' => 'Database Setup | Neolifeporium', 'heading' => 'Database Configuration', 'progress' => 35])

@section('content')
<form action="{{ route('install.database.save') }}" method="POST" class="space-y-4">
    @csrf
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold">DB Host</label>
            <input name="host" value="{{ old('host', $defaults['host']) }}" required class="mt-1 w-full rounded-xl border-slate-200">
        </div>
        <div>
            <label class="text-sm font-semibold">DB Port</label>
            <input name="port" value="{{ old('port', $defaults['port']) }}" required class="mt-1 w-full rounded-xl border-slate-200">
        </div>
        <div>
            <label class="text-sm font-semibold">DB Name</label>
            <input name="database" value="{{ old('database', $defaults['database']) }}" required class="mt-1 w-full rounded-xl border-slate-200">
        </div>
        <div>
            <label class="text-sm font-semibold">DB User</label>
            <input name="username" value="{{ old('username', $defaults['username']) }}" required class="mt-1 w-full rounded-xl border-slate-200">
        </div>
    </div>
    <div>
        <label class="text-sm font-semibold">DB Password</label>
        <input type="password" name="password" value="{{ old('password', $defaults['password']) }}" class="mt-1 w-full rounded-xl border-slate-200">
    </div>
    <button class="rounded-full bg-emerald-700 px-6 py-3 text-sm font-semibold text-white">Test and Save Database</button>
</form>
@endsection
