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
            <h2 class="mb-4 text-lg font-black text-slate-900">AI Insights</h2>
            <div class="space-y-3">
                <template x-for="insight in data.ai_insights" :key="insight.id">
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900" x-text="insight.title"></p>
                                <p class="mt-1 text-xs text-slate-500" x-text="insight.message"></p>
                            </div>
                            <span class="rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em]"
                                :class="insight.severity === 'critical' ? 'bg-red-100 text-red-700' : (insight.severity === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')"
                                x-text="insight.severity">
                            </span>
                        </div>
                        <p class="mt-2 text-[11px] uppercase tracking-[0.2em] text-slate-400" x-text="insight.observed_at"></p>
                    </div>
                </template>
                <p class="text-sm text-slate-500" x-show="data.ai_insights.length === 0">No insights generated yet.</p>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="mb-4 text-lg font-black text-slate-900">Smart Alerts</h2>
            <div class="space-y-3">
                <template x-for="alert in data.alerts" :key="alert.id">
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900" x-text="alert.title"></p>
                                <p class="mt-1 text-xs text-slate-500" x-text="alert.message"></p>
                            </div>
                            <span class="rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em]"
                                :class="alert.severity === 'critical' ? 'bg-red-100 text-red-700' : (alert.severity === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600')"
                                x-text="alert.severity">
                            </span>
                        </div>
                        <p class="mt-2 text-[11px] uppercase tracking-[0.2em] text-slate-400" x-text="alert.created_at"></p>
                    </div>
                </template>
                <p class="text-sm text-slate-500" x-show="data.alerts.length === 0">All systems normal.</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900">Revenue Forecast</h2>
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400" x-text="data.forecasts.revenue?.generated_at ?? ''"></span>
            </div>
            <canvas id="adminForecastChart" height="120"></canvas>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900">User Growth Forecast</h2>
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400" x-text="data.forecasts.user_growth?.generated_at ?? ''"></span>
            </div>
            <canvas id="adminUserForecastChart" height="120"></canvas>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="mb-4 text-lg font-black text-slate-900">Inventory Intelligence</h2>
            <div class="space-y-3">
                <template x-for="flag in data.inventory_flags" :key="flag.id">
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900" x-text="flag.product ?? 'Unknown product'"></p>
                                <p class="mt-1 text-xs uppercase tracking-[0.16em] text-slate-500" x-text="flag.vendor ?? 'Unknown vendor'"></p>
                            </div>
                            <span class="rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em]"
                                :class="flag.type === 'dead_stock' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-700'"
                                x-text="flag.type.replace('_', ' ')">
                            </span>
                        </div>
                        <p class="mt-2 text-[11px] uppercase tracking-[0.2em] text-slate-400" x-text="flag.detected_at"></p>
                    </div>
                </template>
                <p class="text-sm text-slate-500" x-show="data.inventory_flags.length === 0">Inventory health looks stable.</p>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="mb-4 text-lg font-black text-slate-900">Admin Audit Trail</h2>
            <div class="space-y-3">
                <template x-for="log in data.audit_logs" :key="log.id">
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400" x-text="log.action"></p>
                        <p class="mt-2 text-sm font-semibold text-slate-900" x-text="log.user ?? 'System'"></p>
                        <p class="mt-1 text-xs text-slate-500" x-text="`${log.method} ${log.path}`"></p>
                        <p class="mt-2 text-[11px] uppercase tracking-[0.2em] text-slate-400" x-text="log.created_at"></p>
                    </div>
                </template>
                <p class="text-sm text-slate-500" x-show="data.audit_logs.length === 0">No audit activity logged yet.</p>
            </div>
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
        forecastChart: null,
        userForecastChart: null,
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
            const revenueForecast = this.data.forecasts.revenue ?? { history: [], forecast: [] };
            const userForecast = this.data.forecasts.user_growth ?? { history: [], forecast: [] };
            const forecastLabels = [...revenueForecast.history.map(i => i.label), ...revenueForecast.forecast.map(i => i.label)];
            const forecastValues = [...revenueForecast.history.map(i => i.value), ...revenueForecast.forecast.map(i => i.value)];
            const userForecastLabels = [...userForecast.history.map(i => i.label), ...userForecast.forecast.map(i => i.label)];
            const userForecastValues = [...userForecast.history.map(i => i.value), ...userForecast.forecast.map(i => i.value)];

            if (this.revenueChart) this.revenueChart.destroy();
            if (this.categoryChart) this.categoryChart.destroy();
            if (this.forecastChart) this.forecastChart.destroy();
            if (this.userForecastChart) this.userForecastChart.destroy();

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

            this.forecastChart = new Chart(document.getElementById('adminForecastChart'), {
                type: 'line',
                data: {
                    labels: forecastLabels,
                    datasets: [
                        {
                            data: forecastValues,
                            borderColor: '#0f172a',
                            backgroundColor: 'rgba(15,23,42,0.1)',
                            fill: true,
                            tension: 0.35,
                        }
                    ],
                },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });

            this.userForecastChart = new Chart(document.getElementById('adminUserForecastChart'), {
                type: 'line',
                data: {
                    labels: userForecastLabels,
                    datasets: [
                        {
                            data: userForecastValues,
                            borderColor: '#6fbc5f',
                            backgroundColor: 'rgba(111,188,95,0.15)',
                            fill: true,
                            tension: 0.35,
                        }
                    ],
                },
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
