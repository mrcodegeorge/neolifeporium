@extends('layouts.dashboard', [
    'title' => 'Vendor Dashboard | Neolifeporium',
    'dashboardTitle' => 'Vendor Business Hub',
    'dashboardSubtitle' => 'Sales, fulfillment, and customer performance',
    'sidebarLinks' => [
        ['label' => 'Overview', 'href' => route('vendor.panel')],
        ['label' => 'Products', 'href' => route('vendor.products.index')],
        ['label' => 'Create Product', 'href' => route('vendor.products.create')],
    ],
])

@section('dashboard_content')
<div class="space-y-6" x-data="vendorDashboard(@js($dashboard), @js(route('vendor.data')))">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Total Sales</p><p class="mt-2 text-2xl font-black text-slate-900" x-text="currency(data.kpis.total_sales)"></p></div>
        <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Orders Count</p><p class="mt-2 text-2xl font-black text-slate-900" x-text="data.kpis.orders_count"></p></div>
        <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Revenue</p><p class="mt-2 text-2xl font-black text-slate-900" x-text="currency(data.kpis.revenue)"></p></div>
        <div class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Conversion Rate</p><p class="mt-2 text-2xl font-black text-slate-900" x-text="`${data.kpis.conversion_rate}%`"></p></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-black">Sales Trend</h2>
                <a href="{{ route('vendor.export.orders') }}" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">Export Orders</a>
            </div>
            <canvas id="vendorSalesTrend" height="120"></canvas>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="mb-4 text-lg font-black">Top Products</h2>
            <canvas id="vendorTopProducts" height="120"></canvas>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h3 class="mb-4 text-lg font-black">Incoming Orders</h3>
            <div class="space-y-3">
                <template x-for="order in data.tables.incoming_orders" :key="order.id">
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-bold" x-text="order.order_number"></p>
                            <select class="rounded-lg border border-slate-200 px-2 py-1 text-xs" @change="updateOrder(order.id, $event.target.value)">
                                <template x-for="status in ['pending','paid','processing','shipped','delivered','completed']" :key="status">
                                    <option :value="status" :selected="order.status === status" x-text="status"></option>
                                </template>
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-slate-500" x-text="order.farmer"></p>
                        <p class="mt-2 text-sm font-semibold text-slate-900" x-text="currency(order.total)"></p>
                    </div>
                </template>
                <p class="text-sm text-slate-500" x-show="!data.tables.incoming_orders.length">No orders yet.</p>
            </div>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h3 class="mb-4 text-lg font-black">Customer Reviews</h3>
            <div class="space-y-3">
                <template x-for="review in data.tables.reviews" :key="review.product + review.user + review.rating">
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <p class="text-sm font-bold" x-text="review.product"></p>
                        <p class="mt-1 text-xs text-slate-500" x-text="review.user"></p>
                        <p class="mt-2 text-xs text-amber-600" x-text="`Rating: ${review.rating}/5`"></p>
                        <p class="mt-1 text-sm text-slate-600" x-text="review.comment || 'No comment'"></p>
                    </div>
                </template>
                <p class="text-sm text-slate-500" x-show="!data.tables.reviews.length">No reviews yet.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function vendorDashboard(initial, endpoint) {
    return {
        data: initial,
        salesChart: null,
        topChart: null,
        init() {
            this.renderCharts();
            setInterval(() => this.refresh(), 60000);
        },
        currency(v){ return `GHS ${Number(v).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`; },
        renderCharts() {
            const trendLabels = this.data.charts.sales_trend.map(i => i.label);
            const trendValues = this.data.charts.sales_trend.map(i => i.value);
            const topLabels = this.data.charts.top_products.map(i => i.label);
            const topValues = this.data.charts.top_products.map(i => i.value);
            if (this.salesChart) this.salesChart.destroy();
            if (this.topChart) this.topChart.destroy();
            this.salesChart = new Chart(document.getElementById('vendorSalesTrend'), {
                type: 'line',
                data: { labels: trendLabels, datasets: [{ data: trendValues, borderColor:'#365e32', backgroundColor:'rgba(54,94,50,0.15)', fill:true, tension:0.3 }]},
                options: { plugins: { legend: { display:false }}}
            });
            this.topChart = new Chart(document.getElementById('vendorTopProducts'), {
                type: 'bar',
                data: { labels: topLabels, datasets: [{ data: topValues, backgroundColor:'#6fbc5f' }]},
                options: { plugins: { legend: { display:false }}}
            });
        },
        async refresh() {
            const res = await fetch(endpoint);
            if (!res.ok) return;
            const payload = await res.json();
            this.data = payload.data;
            this.renderCharts();
        },
        async updateOrder(orderId, status) {
            await fetch(`/vendor-panel/orders/${orderId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status }),
            });
            this.refresh();
        }
    }
}
</script>
@endsection
