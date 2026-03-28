@extends('layouts.app', ['title' => 'Admin Users | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Administration</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">User Management</h1>
        </div>
        <a href="{{ route('admin.panel') }}" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Back to Dashboard</a>
    </div>

    <form method="GET" action="{{ route('admin.users') }}" class="mt-6 grid gap-3 rounded-3xl bg-white p-4 shadow-lg shadow-black/5 sm:grid-cols-2 lg:grid-cols-5">
        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search name, email, phone" class="rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-palm/60">
        <select name="status" class="rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-palm/60">
            <option value="">All statuses</option>
            @foreach(['active','inactive','suspended'] as $status)
                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <select name="role" class="rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-palm/60">
            <option value="">All roles</option>
            @foreach(['farmer','vendor','agronomist','admin','super_admin'] as $role)
                <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ str($role)->replace('_', ' ')->title() }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Apply Filters</button>
        <a href="{{ route('admin.users') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700">Reset</a>
    </form>

    <div class="mt-6 overflow-hidden rounded-3xl bg-white shadow-lg shadow-black/5">
        <table class="min-w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-xs uppercase tracking-[0.15em] text-slate-500">
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">Roles</th>
                    <th class="px-4 py-3">Joined</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500">ID #{{ $user->id }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            <p>{{ $user->email ?? 'No email' }}</p>
                            <p>{{ $user->phone ?? 'No phone' }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->roles->pluck('slug')->join(', ') ?: 'No role' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->created_at?->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($user->status === 'suspended' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                <form action="{{ route('admin.users.status', $user) }}" method="POST" class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="rounded-xl border-slate-200 text-xs">
                                        @foreach(['active','inactive','suspended'] as $status)
                                            <option value="{{ $status }}" @selected($user->status === $status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Update</button>
                                </form>
                                @if((int) auth()->id() !== (int) $user->id)
                                    <form action="{{ route('admin.users.impersonate', $user) }}" method="POST">
                                        @csrf
                                        <button class="rounded-xl border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">Impersonate</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">No users match the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
</section>
@endsection
