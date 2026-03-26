@extends('install.layout', ['title' => 'Create Admin | Neolifeporium', 'heading' => 'Admin Account Creation', 'progress' => 65])

@section('content')
<form action="{{ route('install.admin.save') }}" method="POST" class="space-y-4">
    @csrf
    <div>
        <label class="text-sm font-semibold">Full Name</label>
        <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-xl border-slate-200">
    </div>
    <div>
        <label class="text-sm font-semibold">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-xl border-slate-200">
    </div>
    <div>
        <label class="text-sm font-semibold">Phone (optional)</label>
        <input name="phone" value="{{ old('phone') }}" class="mt-1 w-full rounded-xl border-slate-200">
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-semibold">Password</label>
            <input type="password" name="password" required class="mt-1 w-full rounded-xl border-slate-200">
        </div>
        <div>
            <label class="text-sm font-semibold">Confirm Password</label>
            <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-xl border-slate-200">
        </div>
    </div>
    <button class="rounded-full bg-emerald-700 px-6 py-3 text-sm font-semibold text-white">Create Admin</button>
</form>
@endsection
