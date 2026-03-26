@extends('layouts.dashboard', [
    'title' => 'Admin Dashboard | Neolifeporium',
    'dashboardTitle' => 'Admin Control Center',
    'dashboardSubtitle' => 'System-wide operations and analytics',
    'sidebarLinks' => [
        ['label' => 'Overview', 'href' => route('admin.panel')],
        ['label' => 'Settings', 'href' => route('admin.settings')],
        ['label' => 'Users', 'href' => route('admin.users')],
        ['label' => 'Vendors', 'href' => route('admin.vendors')],
        ['label' => 'Products', 'href' => route('admin.products')],
    ],
])

@section('dashboard_content')
<div class="space-y-6" x-data="adminDashboard(@js($dashboard), @js(route('admin.data')))">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <template x-for="card in cards" :key="card.key">
            <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500" x-text="card.label"></p>
                <p class="mt-2 text-2xl font-black text-slate-900" x-text="card.value"></p>
                <p class="mt-1 text-xs font-semibold" :class="card.delta >= 0 ? 'text-emerald-600' : 'text-red-600'" x-show="card.delta !== null" x-text="`${card.delta >= 0 ? '+' : ''}${card.delta}%`"></p>
            </div>
        </template>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900">Revenue Over Time</h2>
                <div class="flex items-center gap-2">
                    <input type="date" x-model="range.from" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                    <input type="date" x-model="range.to" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                    <button @click="refreshWithRange" class="rounded-lg bg-slate-900 px-3 py-1 text-xs font-semibold text-white">Apply</button>
                </div>
            </div>
            <canvas id="adminRevenueChart" height="120"></canvas>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="mb-4 text-lg font-black text-slate-900">Orders by Category</h2>
            <canvas id="adminCategoryChart" height="120"></canvas>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-black text-slate-900">Recent Orders</h3>
                <a :href="`{{ route('admin.export.orders') }}?from=${range.from}&to=${range.to}`" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">Export CSV</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead><tr class="border-b border-slate-100 text-xs uppercase tracking-[0.14em] text-slate-500"><th class="py-2 pr-3">Order</th><th class="py-2 pr-3">Vendor</th><th class="py-2 pr-3">Status</th><th class="py-2">Total</th></tr></thead>
                    <tbody>
                        <template x-for="order in data.tables.recent_orders" :key="order.id">
                            <tr class="border-b border-slate-50">
                                <td class="py-2 pr-3 font-semibold" x-text="order.order_number"></td>
                                <td class="py-2 pr-3" x-text="order.vendor"></td>
                                <td class="py-2 pr-3" x-text="order.status"></td>
                                <td class="py-2" x-text="currency(order.total)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h3 class="mb-4 text-lg font-black text-slate-900">Quick Action Modules</h3>
            <div class="grid gap-3 sm:grid-cols-2">
                <a href="{{ route('admin.users') }}" class="rounded-xl border border-slate-200 p-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">User Management</a>
                <a href="{{ route('admin.vendors') }}" class="rounded-xl border border-slate-200 p-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Vendor Approvals</a>
                <a href="{{ route('admin.products') }}" class="rounded-xl border border-slate-200 p-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Product Moderation</a>
                <a href="{{ route('admin.settings') }}" class="rounded-xl border border-slate-200 p-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Platform Settings</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function adminDashboard(initial, endpoint) {
    return {
        data: initial,
        range: { from: initial.range.from, to: initial.range.to },
        cards: [],
        revenueChart: null,
        categoryChart: null,
        init() {
            this.syncCards();
            this.renderCharts();
            setInterval(() => this.refresh(), 60000);
        },
        currency(v) { return `GHS ${Number(v).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`; },
        syncCards() {
            this.cards = [
                { key: 'users', label: 'Total Users', value: this.data.kpis.total_users, delta: null },
                { key: 'vendors', label: 'Total Vendors', value: this.data.kpis.total_vendors, delta: null },
                { key: 'orders', label: 'Total Orders', value: this.data.kpis.total_orders, delta: this.data.kpis.orders_delta },
                { key: 'revenue', label: 'Revenue', value: this.currency(this.data.kpis.revenue_current), delta: this.data.kpis.revenue_delta },
                { key: 'farmers', label: 'Active Farmers', value: this.data.kpis.active_farmers, delta: null },
            ];
        },
        renderCharts() {
            const revenueLabels = this.data.charts.revenue_over_time.map(i => i.label);
            const revenueValues = this.data.charts.revenue_over_time.map(i => i.value);
            const categoryLabels = this.data.charts.orders_by_category.map(i => i.label);
            const categoryValues = this.data.charts.orders_by_category.map(i => i.value);

            if (this.revenueChart) this.revenueChart.destroy();
            if (this.categoryChart) this.categoryChart.destroy();

            this.revenueChart = new Chart(document.getElementById('adminRevenueChart'), {
                type: 'line',
                data: { labels: revenueLabels, datasets: [{ data: revenueValues, borderColor: '#365e32', backgroundColor: 'rgba(54,94,50,0.15)', fill: true, tension: 0.3 }] },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });

            this.categoryChart = new Chart(document.getElementById('adminCategoryChart'), {
                type: 'bar',
                data: { labels: categoryLabels, datasets: [{ data: categoryValues, backgroundColor: '#6fbc5f' }] },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });
        },
        async refresh() {
            const res = await fetch(`${endpoint}?from=${this.range.from}&to=${this.range.to}`);
            if (!res.ok) return;
            const payload = await res.json();
            this.data = payload.data;
            this.syncCards();
            this.renderCharts();
        },
        async refreshWithRange() { await this.refresh(); }
    }
}
</script>
@endsection
