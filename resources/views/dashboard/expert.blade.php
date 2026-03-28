@extends('layouts.dashboard', [
    'title' => 'Expert Dashboard | Neolifeporium',
    'dashboardTitle' => 'Expert Service Platform',
    'dashboardSubtitle' => 'Advisory operations, sessions, and earnings',
    'sidebarLinks' => [
        ['label' => 'Overview', 'href' => route('expert.panel')],
        ['label' => 'Advisory Marketplace', 'href' => route('advisory.index')],
        ['label' => 'Knowledge Hub', 'href' => route('knowledge.index')],
    ],
])

@section('dashboard_content')
<div class="space-y-6" x-data="expertDashboard(@js($dashboard), @js(route('expert.data')))">
    <div class="rounded-3xl bg-white p-5 shadow-lg shadow-black/5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Service Command Layer</p>
                <p class="text-sm text-slate-600">Track workload quality and revenue momentum in your chosen period.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <input type="date" x-model="range.from" class="rounded-lg border border-slate-200 px-2 py-1.5 text-xs">
                <input type="date" x-model="range.to" class="rounded-lg border border-slate-200 px-2 py-1.5 text-xs">
                <button @click="refreshWithRange" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">Apply</button>
            </div>
        </div>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <template x-for="(insight, idx) in insights" :key="`expert-insight-${idx}`">
                <div class="rounded-2xl border p-4" :class="insight.severity === 'high' ? 'border-red-200 bg-red-50' : (insight.severity === 'positive' ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50')">
                    <p class="text-sm font-bold text-slate-900" x-text="insight.title"></p>
                    <p class="mt-1 text-xs text-slate-700" x-text="insight.message"></p>
                </div>
            </template>
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Total Sessions</p><p class="mt-2 text-2xl font-black" x-text="data.kpis.total_sessions"></p></div>
        <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Earnings</p><p class="mt-2 text-2xl font-black" x-text="currency(data.kpis.earnings)"></p></div>
        <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Service Score</p><p class="mt-2 text-2xl font-black" x-text="data.kpis.service_score"></p></div>
        <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5">
            <p class="text-xs uppercase tracking-[0.14em] text-slate-500">Availability</p>
            <button @click="toggleAvailability" class="mt-2 rounded-lg px-3 py-2 text-sm font-semibold" :class="data.kpis.availability ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'" x-text="data.kpis.availability ? 'Available' : 'Offline'"></button>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="mb-4 text-lg font-black">Session Status Mix</h2>
            <canvas id="expertStatusChart" height="120"></canvas>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="mb-4 text-lg font-black">Earnings Trend</h2>
            <canvas id="expertEarningsChart" height="120"></canvas>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h3 class="mb-4 text-lg font-black">Upcoming Bookings</h3>
            <div class="space-y-3">
                <template x-for="booking in data.tables.upcoming_bookings" :key="booking.id">
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <p class="text-sm font-bold" x-text="booking.topic"></p>
                        <p class="mt-1 text-xs text-slate-500" x-text="booking.farmer"></p>
                        <p class="mt-1 text-xs text-slate-500" x-text="booking.scheduled_for"></p>
                        <div class="mt-3 flex items-center gap-2">
                            <select class="rounded-lg border border-slate-200 px-2 py-1 text-xs" @change="updateBooking(booking.id, $event.target.value)">
                                <template x-for="status in ['pending','confirmed','completed','cancelled']" :key="status">
                                    <option :value="status" :selected="booking.status === status" x-text="status"></option>
                                </template>
                            </select>
                            <span class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500" x-text="booking.status"></span>
                        </div>
                    </div>
                </template>
                <p class="text-sm text-slate-500" x-show="!data.tables.upcoming_bookings.length">No upcoming sessions.</p>
            </div>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h3 class="mb-4 text-lg font-black">Session History</h3>
            <div class="space-y-3">
                <template x-for="item in data.tables.session_history" :key="item.topic + item.status">
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <p class="text-sm font-bold" x-text="item.topic"></p>
                        <p class="mt-1 text-xs text-slate-500" x-text="item.farmer"></p>
                        <p class="mt-2 text-sm font-semibold text-slate-900" x-text="currency(item.amount)"></p>
                        <p class="mt-1 text-xs uppercase tracking-[0.14em] text-slate-600" x-text="item.status"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function expertDashboard(initial, endpoint) {
    return {
        data: initial,
        statusChart: null,
        earningsChart: null,
        range: { from: initial.range.from, to: initial.range.to },
        insights: [],
        init() {
            this.syncInsights();
            this.renderCharts();
            setInterval(() => this.refresh(), 60000);
        },
        currency(v){ return `GHS ${Number(v).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`; },
        syncInsights() {
            const insights = [];
            const sessions = Number(this.data.kpis.total_sessions ?? 0);
            const score = Number(this.data.kpis.service_score ?? 0);
            const availability = Boolean(this.data.kpis.availability);

            if (!availability) {
                insights.push({
                    severity: 'medium',
                    title: 'You are currently offline',
                    message: 'Go available to capture new advisory bookings.',
                });
            }
            if (sessions >= 10) {
                insights.push({
                    severity: 'positive',
                    title: 'Strong session volume',
                    message: `${sessions} sessions in selected period. Keep response times fast.`,
                });
            }
            if (score > 0 && score < 3.5) {
                insights.push({
                    severity: 'high',
                    title: 'Service quality needs attention',
                    message: `Service score is ${score}. Review session outcomes and follow-up notes.`,
                });
            }
            if (!insights.length) {
                insights.push({
                    severity: 'positive',
                    title: 'Advisory pipeline stable',
                    message: 'No urgent service alerts right now.',
                });
            }
            this.insights = insights;
        },
        renderCharts() {
            const statusLabels = this.data.charts.sessions_by_status.map(i => i.label);
            const statusValues = this.data.charts.sessions_by_status.map(i => i.value);
            const earningsLabels = this.data.charts.earnings_trend.map(i => i.label);
            const earningsValues = this.data.charts.earnings_trend.map(i => i.value);
            if (this.statusChart) this.statusChart.destroy();
            if (this.earningsChart) this.earningsChart.destroy();
            this.statusChart = new Chart(document.getElementById('expertStatusChart'), {
                type: 'pie',
                data: { labels: statusLabels, datasets: [{ data: statusValues, backgroundColor:['#f59e0b','#0ea5e9','#10b981','#ef4444'] }]},
                options: { plugins: { legend: { position:'bottom' } } }
            });
            this.earningsChart = new Chart(document.getElementById('expertEarningsChart'), {
                type: 'line',
                data: { labels: earningsLabels, datasets: [{ data: earningsValues, borderColor:'#365e32', backgroundColor:'rgba(54,94,50,.15)', fill:true, tension:0.3 }]},
                options: { plugins: { legend: { display:false } } }
            });
        },
        async refresh() {
            const res = await fetch(`${endpoint}?from=${encodeURIComponent(this.range.from)}&to=${encodeURIComponent(this.range.to)}`);
            if (!res.ok) return;
            const payload = await res.json();
            this.data = payload.data;
            this.syncInsights();
            this.renderCharts();
        },
        async refreshWithRange() { await this.refresh(); },
        async updateBooking(id, status) {
            await fetch(`/expert-panel/bookings/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status }),
            });
            this.refresh();
        },
        async toggleAvailability() {
            await fetch(`/expert-panel/availability`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
            });
            this.refresh();
        }
    }
}
</script>
@endsection
