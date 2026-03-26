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
        init() {
            this.renderChart();
            setInterval(() => this.refresh(), 60000);
        },
        currency(v){ return `GHS ${Number(v).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`; },
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
            const res = await fetch(endpoint);
            if (!res.ok) return;
            const payload = await res.json();
            this.data = payload.data;
            this.renderChart();
        }
    }
}
</script>
@endsection
