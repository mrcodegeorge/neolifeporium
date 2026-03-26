@extends('layouts.app', ['title' => 'Admin Command Center | Neolifeporium'])

@section('content')
@php
    $maxDailyRevenue = max(1, collect($trends['daily_revenue'])->max('total_amount') ?? 1);
@endphp

<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8" x-data="{ tab: 'operations' }">
    <div class="rounded-[2rem] bg-[linear-gradient(140deg,#101d14_0%,#18291b_48%,#213524_100%)] p-6 text-white shadow-2xl shadow-black/20 sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-grain">Admin Command Center</p>
                <h1 class="mt-3 text-3xl font-black uppercase leading-tight sm:text-4xl">Neolifeporium Operations Dashboard</h1>
                <p class="mt-3 max-w-2xl text-sm text-white/75 sm:text-base">Monitor platform health, moderate marketplace activity, and act on risk queues from one control surface.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.settings') }}" class="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-white hover:bg-white/20">Settings</a>
                <a href="{{ route('admin.users') }}" class="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-white hover:bg-white/20">Users</a>
                <a href="{{ route('admin.vendors') }}" class="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-white hover:bg-white/20">Vendors</a>
                <a href="{{ route('admin.experts') }}" class="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-white hover:bg-white/20">Experts</a>
                <a href="{{ route('admin.products') }}" class="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-white hover:bg-white/20">Products</a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.panel') }}" class="mt-6 grid gap-3 rounded-2xl border border-white/15 bg-white/5 p-4 md:grid-cols-[1fr_1fr_auto_auto]">
            <input type="date" name="from" value="{{ $trends['from'] }}" class="rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white outline-none">
            <input type="date" name="to" value="{{ $trends['to'] }}" class="rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white outline-none">
            <button type="submit" class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-900">Apply Range</button>
            <a href="{{ route('admin.export.orders', ['from' => $trends['from'], 'to' => $trends['to']]) }}" class="rounded-xl border border-white/25 bg-white/10 px-4 py-2 text-center text-sm font-semibold text-white">
                Export Orders CSV
            </a>
        </form>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-white/60">Total Users</p>
                <p class="mt-2 text-3xl font-black">{{ number_format($overview['total_users']) }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-white/60">Active Products</p>
                <p class="mt-2 text-3xl font-black">{{ number_format($overview['active_products']) }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-white/60">GMV</p>
                <p class="mt-2 text-3xl font-black">GHS {{ number_format($overview['platform_gmv'], 2) }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-white/60">Commission</p>
                <p class="mt-2 text-3xl font-black">GHS {{ number_format($overview['commission_total'], 2) }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-amber-200/30 bg-amber-200/10 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-amber-100/80">Vendors Pending</p>
                <p class="mt-2 text-2xl font-black">{{ number_format($overview['vendors_pending_approval']) }}</p>
            </div>
            <div class="rounded-2xl border border-red-200/30 bg-red-200/10 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-red-100/80">Low Stock Alerts</p>
                <p class="mt-2 text-2xl font-black">{{ number_format($overview['low_stock_products']) }}</p>
            </div>
            <div class="rounded-2xl border border-sky-200/30 bg-sky-200/10 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-sky-100/80">Orders (Range)</p>
                <p class="mt-2 text-2xl font-black">{{ number_format($trends['orders_current']) }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] {{ $trends['orders_delta_percent'] >= 0 ? 'text-emerald-200' : 'text-red-200' }}">
                    {{ $trends['orders_delta_percent'] >= 0 ? '+' : '' }}{{ $trends['orders_delta_percent'] }}% vs prior period
                </p>
            </div>
            <div class="rounded-2xl border border-emerald-200/30 bg-emerald-200/10 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-emerald-100/80">Revenue (Range)</p>
                <p class="mt-2 text-2xl font-black">GHS {{ number_format($trends['revenue_current'], 2) }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] {{ $trends['revenue_delta_percent'] >= 0 ? 'text-emerald-200' : 'text-red-200' }}">
                    {{ $trends['revenue_delta_percent'] >= 0 ? '+' : '' }}{{ $trends['revenue_delta_percent'] }}% vs prior period
                </p>
            </div>
        </div>
    </div>

    <div class="mt-8 flex flex-wrap gap-2">
        <button type="button" @click="tab = 'operations'" :class="tab === 'operations' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600'" class="rounded-full px-4 py-2 text-sm font-semibold">Operations</button>
        <button type="button" @click="tab = 'finance'" :class="tab === 'finance' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600'" class="rounded-full px-4 py-2 text-sm font-semibold">Finance</button>
        <button type="button" @click="tab = 'activity'" :class="tab === 'activity' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600'" class="rounded-full px-4 py-2 text-sm font-semibold">Activity</button>
    </div>

    <div class="mt-6 space-y-6" x-show="tab === 'operations'" x-cloak>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($breakdowns['orders'] as $status => $count)
                <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ str($status)->replace('_', ' ') }}</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($count) }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-black text-slate-900">Pending Vendor Approvals</h2>
                    <a href="{{ route('admin.vendors') }}" class="text-xs font-semibold uppercase tracking-[0.2em] text-palm">Open Queue</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($queues['pending_vendors'] as $vendor)
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <p class="text-sm font-bold text-slate-900">{{ $vendor->business_name }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.16em] text-slate-500">{{ $vendor->user?->name }} | {{ $vendor->region ?? 'Unspecified region' }}</p>
                            <form method="POST" action="{{ route('admin.vendors.status', $vendor) }}" class="mt-3">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="verification_status" value="approved">
                                <button class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Approve Vendor</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No pending approvals right now.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-black text-slate-900">Low Stock Alerts</h2>
                    <a href="{{ route('admin.products') }}" class="text-xs font-semibold uppercase tracking-[0.2em] text-palm">Moderate</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($queues['low_stock_products'] as $product)
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <p class="text-sm font-bold text-slate-900">{{ $product->name }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.16em] text-slate-500">{{ $product->vendor?->vendorProfile?->business_name ?? $product->vendor?->name }}</p>
                            <p class="mt-2 text-xs font-semibold uppercase tracking-[0.16em] text-red-600">Inventory: {{ $product->inventory }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No low-stock products in alert state.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 space-y-6" x-show="tab === 'finance'" x-cloak>
        <div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <h2 class="text-xl font-black text-slate-900">Revenue Pulse ({{ $trends['from'] }} to {{ $trends['to'] }})</h2>
                <div class="mt-5 grid grid-cols-7 gap-3">
                    @foreach($trends['daily_revenue'] as $day)
                        <div class="flex flex-col items-center gap-2">
                            <div class="flex h-32 w-full items-end rounded-xl bg-slate-100 p-1">
                                <div class="w-full rounded-lg bg-[linear-gradient(180deg,#6fbc5f_0%,#365e32_100%)]" style="height: {{ max(8, round(($day['total_amount'] / $maxDailyRevenue) * 100)) }}%;"></div>
                            </div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">{{ $day['day'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <h2 class="text-xl font-black text-slate-900">Payment Providers</h2>
                <div class="mt-4 space-y-3">
                    @forelse($breakdowns['payments_by_provider'] as $provider)
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <p class="text-sm font-bold text-slate-900">{{ strtoupper($provider->provider) }}</p>
                            <p class="mt-2 text-xs uppercase tracking-[0.16em] text-slate-500">Transactions: {{ number_format($provider->transactions) }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.16em] text-slate-500">Amount: GHS {{ number_format((float) $provider->total_amount, 2) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No payment records available.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="text-xl font-black text-slate-900">Recent Payments</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase tracking-[0.16em] text-slate-500">
                            <th class="py-3 pr-4">Reference</th>
                            <th class="py-3 pr-4">User</th>
                            <th class="py-3 pr-4">Provider</th>
                            <th class="py-3 pr-4">Amount</th>
                            <th class="py-3 pr-4">Status</th>
                            <th class="py-3 pr-4">Date</th>
                            <th class="py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($queues['recent_payments'] as $payment)
                            <tr class="border-b border-slate-50">
                                <td class="py-3 pr-4 font-semibold text-slate-900">{{ $payment->provider_reference }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $payment->user?->name ?? 'System' }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ strtoupper($payment->provider) }}</td>
                                <td class="py-3 pr-4 font-semibold text-slate-900">GHS {{ number_format($payment->amount, 2) }}</td>
                                <td class="py-3 pr-4">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $payment->status === 'success' ? 'bg-emerald-100 text-emerald-700' : ($payment->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-slate-500">{{ $payment->created_at?->format('M d, H:i') }}</td>
                                <td class="py-3 text-right">
                                    @if($payment->status !== 'success')
                                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Mark Verified</button>
                                        </form>
                                    @else
                                        <span class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-600">Verified</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td class="py-4 text-slate-500" colspan="7">No payment activity yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 space-y-6" x-show="tab === 'activity'" x-cloak>
        <div class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <h2 class="text-xl font-black text-slate-900">Recent Orders</h2>
                <div class="mt-4 space-y-3">
                    @forelse($queues['recent_orders'] as $order)
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-bold text-slate-900">{{ $order->order_number }}</p>
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">{{ ucfirst($order->status) }}</span>
                            </div>
                            <p class="mt-2 text-xs uppercase tracking-[0.16em] text-slate-500">{{ $order->farmer?->name }} -> {{ $order->vendor?->vendorProfile?->business_name ?? $order->vendor?->name }}</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">GHS {{ number_format($order->total_amount, 2) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No recent orders yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <h2 class="text-xl font-black text-slate-900">Recent Signups</h2>
                <div class="mt-4 space-y-3">
                    @forelse($queues['recent_signups'] as $user)
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <p class="text-sm font-bold text-slate-900">{{ $user->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $user->email ?? $user->phone ?? 'No contact' }}</p>
                            <p class="mt-2 text-xs uppercase tracking-[0.16em] text-slate-500">{{ $user->roles->pluck('slug')->implode(', ') ?: 'No role assigned' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No recent signups.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <h2 class="text-xl font-black text-slate-900">Upcoming Advisory Bookings</h2>
                <div class="mt-4 space-y-3">
                    @forelse($queues['upcoming_bookings'] as $booking)
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <p class="text-sm font-bold text-slate-900">{{ $booking->topic }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.16em] text-slate-500">{{ $booking->farmer?->name }} with {{ $booking->agronomist?->name }}</p>
                            <p class="mt-2 text-xs font-semibold text-slate-700">{{ $booking->scheduled_for?->format('M d, Y H:i') }}</p>
                            <form method="POST" action="{{ route('admin.bookings.status', $booking) }}" class="mt-3 flex gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                    @foreach(['pending','confirmed','completed','cancelled'] as $status)
                                        <option value="{{ $status }}" @selected($booking->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <button class="rounded-lg bg-slate-900 px-3 py-1 text-xs font-semibold text-white">Update</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No upcoming sessions.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <h2 class="text-xl font-black text-slate-900">Top Products by Units Sold</h2>
                <div class="mt-4 space-y-3">
                    @forelse($queues['top_products'] as $product)
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <p class="text-sm font-bold text-slate-900">{{ $product->name }}</p>
                            <p class="mt-2 text-xs uppercase tracking-[0.16em] text-slate-500">Units: {{ number_format($product->units_sold) }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.16em] text-slate-500">Revenue: GHS {{ number_format((float) $product->revenue, 2) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No product sales data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
