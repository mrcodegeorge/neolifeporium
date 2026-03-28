@extends('layouts.dashboard', [
    'title' => 'Farmer Dashboard | Neolifeporium',
    'dashboardTitle' => 'Farmer Intelligence Hub',
    'dashboardSubtitle' => 'Decisions powered by data and advisory support',
    'sidebarLinks' => [
        ['label' => 'Overview', 'href' => route('dashboard')],
        ['label' => 'Marketplace', 'href' => route('marketplace.index')],
        ['label' => 'Advisory', 'href' => route('advisory.index')],
        ['label' => 'Knowledge Hub', 'href' => route('knowledge.index')],
    ],
])

@section('dashboard_content')
<div class="space-y-6" x-data="farmerDashboard(@js($dashboard), @js(route('dashboard.data')))">
    <div class="rounded-3xl bg-white p-5 shadow-lg shadow-black/5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Decision Workspace</p>
                <p class="text-sm text-slate-600">Tune your intelligence window for planning and buying.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <input type="date" x-model="range.from" class="rounded-lg border border-slate-200 px-2 py-1.5 text-xs">
                <input type="date" x-model="range.to" class="rounded-lg border border-slate-200 px-2 py-1.5 text-xs">
                <button @click="refreshWithRange" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">Apply</button>
            </div>
        </div>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <template x-for="(insight, idx) in insights" :key="`farmer-insight-${idx}`">
                <div class="rounded-2xl border p-4" :class="insight.severity === 'high' ? 'border-red-200 bg-red-50' : (insight.severity === 'positive' ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50')">
                    <p class="text-sm font-bold text-slate-900" x-text="insight.title"></p>
                    <p class="mt-1 text-xs text-slate-700" x-text="insight.message"></p>
                </div>
            </template>
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Orders</p><p class="mt-2 text-2xl font-black" x-text="data.kpis.orders_count"></p></div>
        <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">In Progress</p><p class="mt-2 text-2xl font-black" x-text="data.kpis.orders_in_progress"></p></div>
        <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Wishlist</p><p class="mt-2 text-2xl font-black" x-text="data.kpis.wishlist_items"></p></div>
        <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Advisory Sessions</p><p class="mt-2 text-2xl font-black" x-text="data.kpis.bookings_count"></p></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-black">Order Activity</h2>
                <a href="{{ route('dashboard.export.orders') }}" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">Export Orders</a>
            </div>
            <canvas id="farmerOrdersChart" height="120"></canvas>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="mb-4 text-lg font-black">Weather Alerts</h2>
            <div class="space-y-3">
                <template x-for="item in data.tables.weather_updates" :key="item.date + item.summary">
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <p class="text-sm font-bold" x-text="item.summary"></p>
                        <p class="mt-1 text-xs text-slate-500" x-text="`${item.date} | Rain probability ${item.rainfall_probability}%`"></p>
                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em]" :class="item.alert_level === 'high' ? 'text-red-600' : 'text-slate-500'" x-text="item.alert_level"></p>
                    </div>
                </template>
                <p class="text-sm text-slate-500" x-show="!data.tables.weather_updates.length">No weather insight available yet.</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h3 class="mb-4 text-lg font-black">Recommended Products</h3>
            <div class="grid gap-3 sm:grid-cols-2">
                <template x-for="item in data.tables.recommended_products" :key="item.id">
                    <a :href="`/marketplace/${item.slug}`" class="rounded-2xl border border-slate-100 p-4 hover:bg-slate-50">
                        <p class="text-sm font-bold text-slate-900" x-text="item.name"></p>
                        <p class="mt-1 text-xs text-slate-500" x-text="item.crop_type || 'General'"></p>
                        <p class="mt-2 text-sm font-semibold text-palm" x-text="currency(item.price)"></p>
                    </a>
                </template>
            </div>
            <p class="text-sm text-slate-500" x-show="!data.tables.recommended_products.length">No recommendations yet.</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h3 class="mb-4 text-lg font-black">Advisory Bookings</h3>
            <div class="space-y-3">
                <template x-for="booking in data.tables.bookings" :key="booking.topic + booking.scheduled_for">
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <p class="text-sm font-bold" x-text="booking.topic"></p>
                        <p class="mt-1 text-xs text-slate-500" x-text="booking.agronomist"></p>
                        <p class="mt-1 text-xs text-slate-500" x-text="booking.scheduled_for"></p>
                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-600" x-text="booking.status"></p>
                    </div>
                </template>
                <p class="text-sm text-slate-500" x-show="!data.tables.bookings.length">No bookings yet.</p>
            </div>
        </div>
    </div>

    <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
        <h3 class="mb-4 text-lg font-black">Knowledge Feed</h3>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <template x-for="article in data.tables.knowledge_feed" :key="article.slug">
                <a :href="`/knowledge-hub/${article.slug}`" class="rounded-2xl border border-slate-100 p-4 hover:bg-slate-50">
                    <p class="text-sm font-bold text-slate-900" x-text="article.title"></p>
                    <p class="mt-2 text-xs text-slate-500" x-text="article.excerpt || 'Open article for full guidance.'"></p>
                </a>
            </template>
        </div>
    </div>

    <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
        <h3 class="mb-4 text-lg font-black">Suggested Experts</h3>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <template x-for="expert in data.tables.suggested_experts" :key="expert.id">
                <a :href="`/advisory/experts/${expert.id}`" class="rounded-2xl border border-slate-100 p-4 hover:bg-slate-50">
                    <p class="text-sm font-bold text-slate-900" x-text="expert.name"></p>
                    <p class="mt-1 text-xs text-slate-500" x-text="expert.specialty || 'General agronomy'"></p>
                    <p class="mt-2 text-sm font-semibold text-palm" x-text="currency(expert.rate) + '/hr'"></p>
                </a>
            </template>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function farmerDashboard(initial, endpoint) {
    return {
        data: initial,
        chart: null,
        range: { from: initial.range.from, to: initial.range.to },
        insights: [],
        init() {
            this.syncInsights();
            this.renderChart();
            setInterval(() => this.refresh(), 60000);
        },
        currency(v){ return `GHS ${Number(v).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`; },
        syncInsights() {
            const insights = [];
            const inProgress = Number(this.data.kpis.orders_in_progress ?? 0);
            const weather = this.data.tables.weather_updates ?? [];
            const highAlerts = weather.filter((item) => item.alert_level === 'high').length;
            const recommendations = this.data.tables.recommended_products?.length ?? 0;

            if (highAlerts > 0) {
                insights.push({
                    severity: 'high',
                    title: 'Weather risk detected',
                    message: `${highAlerts} high-alert forecast(s). Review weather-safe inputs before checkout.`,
                });
            }
            if (inProgress > 0) {
                insights.push({
                    severity: 'medium',
                    title: 'Orders still in motion',
                    message: `${inProgress} order(s) are in progress. Track fulfillment before planting windows close.`,
                });
            }
            if (recommendations > 0) {
                insights.push({
                    severity: 'positive',
                    title: 'Actionable recommendations ready',
                    message: `${recommendations} product recommendation(s) matched to your farm profile.`,
                });
            }
            if (!insights.length) {
                insights.push({
                    severity: 'positive',
                    title: 'Dashboard is stable',
                    message: 'No urgent flags right now. Continue with this week’s plan.',
                });
            }

            this.insights = insights;
        },
        renderChart() {
            const labels = this.data.charts.orders_timeline.map(i => i.label);
            const values = this.data.charts.orders_timeline.map(i => i.value);
            if (this.chart) this.chart.destroy();
            this.chart = new Chart(document.getElementById('farmerOrdersChart'), {
                type: 'line',
                data: { labels, datasets: [{ data: values, borderColor:'#365e32', backgroundColor:'rgba(54,94,50,.15)', fill:true, tension:0.3 }]},
                options: { plugins: { legend: { display:false }}}
            });
        },
        async refresh() {
            const res = await fetch(`${endpoint}?from=${encodeURIComponent(this.range.from)}&to=${encodeURIComponent(this.range.to)}`);
            if (!res.ok) return;
            const payload = await res.json();
            this.data = payload.data;
            this.syncInsights();
            this.renderChart();
        },
        async refreshWithRange() { await this.refresh(); }
    }
}
</script>
@endsection
