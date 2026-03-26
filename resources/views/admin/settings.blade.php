@extends('layouts.app', ['title' => 'Admin Settings | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8" x-data="{ tab: 'platform' }">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Administration</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Settings and Staff Management</h1>
        </div>
        <a href="{{ route('admin.panel') }}" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Back to Dashboard</a>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        <button type="button" @click="tab = 'platform'" :class="tab === 'platform' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600'" class="rounded-full px-4 py-2 text-sm font-semibold">Platform</button>
        <button type="button" @click="tab = 'staff'" :class="tab === 'staff' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600'" class="rounded-full px-4 py-2 text-sm font-semibold">Staff</button>
        <button type="button" @click="tab = 'roles'" :class="tab === 'roles' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600'" class="rounded-full px-4 py-2 text-sm font-semibold">Roles</button>
    </div>

    <div class="mt-6" x-show="tab === 'platform'" x-cloak>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            @csrf
            @method('PATCH')
            <h2 class="text-xl font-black text-slate-900">Platform Configuration</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Site Name</label>
                    <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? config('app.name')) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">App URL</label>
                    <input type="url" name="app_url" value="{{ old('app_url', $settings['app_url'] ?? config('app.url')) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Default Currency</label>
                    <input type="text" name="default_currency" value="{{ old('default_currency', $settings['default_currency'] ?? 'GHS') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Default Timezone</label>
                    <input type="text" name="default_timezone" value="{{ old('default_timezone', $settings['default_timezone'] ?? 'Africa/Accra') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Maintenance Message</label>
                    <textarea name="maintenance_mode_message" rows="2" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">{{ old('maintenance_mode_message', $settings['maintenance_mode_message'] ?? '') }}</textarea>
                </div>
            </div>

            <h3 class="mt-8 text-lg font-black text-slate-900">Payment Gateway Settings</h3>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Paystack Public Key</label>
                    <input type="text" name="paystack_public_key" value="{{ old('paystack_public_key', $settings['paystack_public_key'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Paystack Secret Key</label>
                    <input type="text" name="paystack_secret_key" value="{{ old('paystack_secret_key', $settings['paystack_secret_key'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">MoMo API Key</label>
                    <input type="text" name="momo_api_key" value="{{ old('momo_api_key', $settings['momo_api_key'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">MoMo API Secret</label>
                    <input type="text" name="momo_api_secret" value="{{ old('momo_api_secret', $settings['momo_api_secret'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">
                </div>
            </div>

            <h3 class="mt-8 text-lg font-black text-slate-900">Notification Settings</h3>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-slate-700">SMS Provider</label>
                    <input type="text" name="sms_provider" value="{{ old('sms_provider', $settings['sms_provider'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">SMS API Key</label>
                    <input type="text" name="sms_api_key" value="{{ old('sms_api_key', $settings['sms_api_key'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">
                </div>
            </div>

            <button type="submit" class="mt-8 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Save Platform Settings</button>
        </form>
    </div>

    <div class="mt-6 space-y-6" x-show="tab === 'staff'" x-cloak>
        <form method="POST" action="{{ route('admin.staff.store') }}" class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            @csrf
            <h2 class="text-xl font-black text-slate-900">Create Staff Account</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Full name" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Phone (optional)" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">
                <select name="status" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">
                    @foreach(['active','inactive','suspended'] as $status)
                        <option value="{{ $status }}" @selected(old('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <input type="password" name="password" placeholder="Password" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">
                <input type="password" name="password_confirmation" placeholder="Confirm password" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Staff Role</label>
                    <select name="role" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        <option value="agronomist" @selected(old('role') === 'agronomist')>Agronomist</option>
                        @if($canAssignSuperAdmin)
                            <option value="super_admin" @selected(old('role') === 'super_admin')>Super Admin</option>
                        @endif
                    </select>
                </div>
            </div>
            <button type="submit" class="mt-6 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Create Staff User</button>
        </form>

        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h3 class="text-lg font-black text-slate-900">Current Staff Members</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase tracking-[0.14em] text-slate-500">
                            <th class="py-3 pr-4">Name</th>
                            <th class="py-3 pr-4">Email</th>
                            <th class="py-3 pr-4">Roles</th>
                            <th class="py-3 pr-4">Status</th>
                            <th class="py-3">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffMembers as $staff)
                            <tr class="border-b border-slate-50">
                                <td class="py-3 pr-4 font-semibold text-slate-900">{{ $staff->name }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $staff->email ?? '-' }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $staff->roles->pluck('slug')->implode(', ') }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ ucfirst($staff->status) }}</td>
                                <td class="py-3 text-slate-500">{{ $staff->created_at?->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-slate-500">No staff members found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $staffMembers->links() }}</div>
        </div>
    </div>

    <div class="mt-6" x-show="tab === 'roles'" x-cloak>
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="text-xl font-black text-slate-900">Assign Roles to Registered Users</h2>
            <p class="mt-2 text-sm text-slate-600">Use this to promote users to vendor, admin, agronomist, or assign multiple roles.</p>
            <div class="mt-5 space-y-4">
                @foreach($registeredUsers as $user)
                    <form method="POST" action="{{ route('admin.users.roles', $user) }}" class="rounded-2xl border border-slate-100 p-4">
                        @csrf
                        @method('PATCH')
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email ?? $user->phone ?? 'No contact' }}</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.14em] text-slate-500">Current: {{ $user->roles->pluck('slug')->implode(', ') ?: 'No role' }}</p>
                            </div>
                            <button class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Update Roles</button>
                        </div>
                        <div class="mt-4 grid gap-2 sm:grid-cols-3 lg:grid-cols-5">
                            @foreach($roles as $role)
                                @continue($role->slug === 'super_admin' && ! $canAssignSuperAdmin)
                                <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700">
                                    <input type="checkbox" name="roles[]" value="{{ $role->slug }}" class="rounded border-slate-300 text-palm focus:ring-palm/30" @checked($user->roles->contains('slug', $role->slug))>
                                    {{ $role->name }}
                                </label>
                            @endforeach
                        </div>
                    </form>
                @endforeach
            </div>
            <div class="mt-4">{{ $registeredUsers->links() }}</div>
        </div>
    </div>
</section>
@endsection
