@extends('layouts.dashboard', [
    'title' => 'Admin Dashboard | Neolifeporium',
    'dashboardTitle' => 'Enterprise Admin Command Center',
    'dashboardSubtitle' => 'Analytics, controls, automation, and system intelligence',
    'sidebarLinks' => [
        ['label' => 'Command Center', 'href' => route('admin.panel')],
        ['label' => 'Users & Roles', 'href' => route('admin.users')],
        ['label' => 'Vendors', 'href' => route('admin.vendors')],
        ['label' => 'Products', 'href' => route('admin.products')],
        ['label' => 'Experts', 'href' => route('admin.experts')],
        ['label' => 'Settings', 'href' => route('admin.settings')],
    ],
])

@section('dashboard_content')
<div class="space-y-6" x-data="adminCommandCenter(@js($dashboard), @js(route('admin.data')))">
    <section class="rounded-3xl bg-[linear-gradient(135deg,#0f172a,#111827_55%,#1f2937)] p-6 text-white shadow-xl shadow-black/20">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-300/80">Central Command</p>
                <h1 class="mt-2 text-2xl font-black sm:text-3xl">Neolifeporium Enterprise Operations</h1>
                <p class="mt-2 max-w-3xl text-sm text-white/70">Real-time intelligence across analytics, moderation, finance, advisory, content, and system health.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a :href="`{{ route('admin.export.orders') }}?from=${range.from}&to=${range.to}`" class="rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-white hover:bg-white/20">Export Orders</a>
                <a :href="`{{ route('admin.export.users') }}?from=${range.from}&to=${range.to}`" class="rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-white hover:bg-white/20">Export Users</a>
                <a :href="`{{ route('admin.export.payments') }}?from=${range.from}&to=${range.to}`" class="rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-white hover:bg-white/20">Export Payments</a>
            </div>
        </div>
        <div class="mt-5 grid gap-3 md:grid-cols-[1fr_1fr_auto]">
            <input type="date" x-model="range.from" class="rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white outline-none">
            <input type="date" x-model="range.to" class="rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white outline-none">
            <button type="button" @click="refreshWithRange" class="rounded-xl bg-emerald-400 px-4 py-2 text-sm font-semibold text-slate-900">Apply Range</button>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <template x-for="card in cards" :key="card.key">
            <article class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500" x-text="card.label"></p>
                <p class="mt-2 text-2xl font-black text-slate-900" x-text="card.value"></p>
                <p class="mt-1 text-xs font-semibold" :class="card.delta >= 0 ? 'text-emerald-600' : 'text-red-600'" x-show="card.delta !== null" x-text="`${card.delta >= 0 ? '+' : ''}${card.delta}%`"></p>
            </article>
        </template>
    </section>

    <section class="flex flex-wrap gap-2">
        <template x-for="item in tabs" :key="item.key">
            <button type="button" @click="tab = item.key" class="rounded-full px-4 py-2 text-sm font-semibold transition" :class="tab === item.key ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100'" x-text="item.label"></button>
        </template>
    </section>

    <section class="space-y-6" x-show="tab === 'analytics'" x-cloak>
        <div class="grid gap-6 xl:grid-cols-2">
            <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5"><h2 class="text-lg font-black text-slate-900">Revenue Over Time</h2><canvas id="adminRevenueChart" height="130"></canvas></article>
            <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5"><h2 class="text-lg font-black text-slate-900">Orders by Category</h2><canvas id="adminCategoryChart" height="130"></canvas></article>
        </div>
        <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="text-lg font-black text-slate-900">Marketplace Intelligence: Top Products</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead><tr class="border-b border-slate-100 text-xs uppercase tracking-[0.14em] text-slate-500"><th class="py-2 pr-3">Product</th><th class="py-2 pr-3">Units</th><th class="py-2">Revenue</th></tr></thead>
                    <tbody><template x-for="product in data.analytics.top_selling_products" :key="product.id"><tr class="border-b border-slate-50"><td class="py-2 pr-3 font-semibold text-slate-900" x-text="product.name"></td><td class="py-2 pr-3 text-slate-600" x-text="number(product.units_sold)"></td><td class="py-2 text-slate-900" x-text="currency(product.revenue)"></td></tr></template></tbody>
                </table>
            </div>
        </article>
        <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="text-lg font-black text-slate-900">System Insights</h2>
            <div class="mt-4 space-y-3">
                <template x-for="(insight, idx) in data.insights" :key="`insight-${idx}`">
                    <div class="rounded-2xl border p-4" :class="insight.severity === 'high' ? 'border-red-200 bg-red-50' : (insight.severity === 'positive' ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50')">
                        <p class="text-sm font-bold text-slate-900" x-text="insight.title"></p>
                        <p class="mt-1 text-xs text-slate-700" x-text="insight.message"></p>
                    </div>
                </template>
                <template x-if="!data.insights.length">
                    <p class="text-sm text-slate-500">No additional insights right now.</p>
                </template>
            </div>
        </article>
    </section>

    <section class="space-y-6" x-show="tab === 'users'" x-cloak>
        <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-black text-slate-900">User & Role Management Control</h2>
                <div class="flex gap-2">
                    <a href="{{ route('admin.users') }}" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">Manage Users</a>
                    <a href="{{ route('admin.settings') }}" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">Assign Roles</a>
                </div>
            </div>
            <p class="mt-2 text-sm text-slate-600">Suspend/activate accounts, assign roles, and use impersonation from the user management module.</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead><tr class="border-b border-slate-100 text-xs uppercase tracking-[0.14em] text-slate-500"><th class="py-2 pr-3">Order</th><th class="py-2 pr-3">Farmer</th><th class="py-2 pr-3">Vendor</th><th class="py-2">Status</th></tr></thead>
                    <tbody><template x-for="order in data.tables.recent_orders" :key="`usr-${order.id}`"><tr class="border-b border-slate-50"><td class="py-2 pr-3 font-semibold text-slate-900" x-text="order.order_number"></td><td class="py-2 pr-3 text-slate-600" x-text="order.farmer"></td><td class="py-2 pr-3 text-slate-600" x-text="order.vendor"></td><td class="py-2 text-slate-700" x-text="order.status"></td></tr></template></tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="space-y-6" x-show="tab === 'marketplace'" x-cloak>
        <div class="grid gap-6 xl:grid-cols-2">
            <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <h2 class="text-lg font-black text-slate-900">Real-time Order Stream</h2>
                <div class="mt-4 space-y-3"><template x-for="order in data.operations.order_stream" :key="`stream-${order.id}`"><div class="rounded-2xl border border-slate-100 p-4"><div class="flex items-center justify-between gap-3"><p class="text-sm font-bold text-slate-900" x-text="order.order_number"></p><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600" x-text="order.status"></span></div><p class="mt-2 text-sm font-semibold text-slate-900" x-text="currency(order.total_amount)"></p></div></template></div>
            </article>
            <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <h2 class="text-lg font-black text-slate-900">Fraud & Risk Detection</h2>
                <div class="mt-4 space-y-3"><div class="rounded-2xl border border-slate-100 p-4"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Unusual vendor volume</p><template x-for="vendor in data.risk_flags.unusual_vendor_volume" :key="`risk-v-${vendor.vendor_id}`"><p class="mt-2 text-sm text-slate-700"><span class="font-semibold" x-text="vendor.vendor_name"></span> - <span x-text="number(vendor.order_count)"></span> orders</p></template></div><div class="rounded-2xl border border-slate-100 p-4"><p class="text-sm text-slate-700">Suspicious payments: <span class="font-semibold" x-text="number(data.risk_flags.suspicious_payments.length)"></span></p><p class="text-sm text-slate-700">Fake vendor signals: <span class="font-semibold" x-text="number(data.risk_flags.fake_vendor_signals.length)"></span></p></div></div>
            </article>
        </div>
    </section>

    <section class="space-y-6" x-show="tab === 'finance'" x-cloak>
        <div class="grid gap-4 md:grid-cols-2"><article class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Platform Earnings</p><p class="mt-2 text-2xl font-black text-slate-900" x-text="currency(data.kpis.platform_earnings)"></p></article><article class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Vendor Earnings</p><p class="mt-2 text-2xl font-black text-slate-900" x-text="currency(data.kpis.vendor_earnings)"></p></article></div>
        <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5"><h2 class="text-lg font-black text-slate-900">Transaction Ledger</h2><div class="mt-4 overflow-x-auto"><table class="min-w-full text-left text-sm"><thead><tr class="border-b border-slate-100 text-xs uppercase tracking-[0.14em] text-slate-500"><th class="py-2 pr-3">Reference</th><th class="py-2 pr-3">Provider</th><th class="py-2 pr-3">Status</th><th class="py-2">Amount</th></tr></thead><tbody><template x-for="item in data.finance.ledger" :key="`ledger-${item.id}`"><tr class="border-b border-slate-50"><td class="py-2 pr-3 font-semibold text-slate-900" x-text="item.provider_reference"></td><td class="py-2 pr-3 text-slate-600" x-text="item.provider"></td><td class="py-2 pr-3 text-slate-600" x-text="item.status"></td><td class="py-2 text-slate-900" x-text="currency(item.amount)"></td></tr></template></tbody></table></div></article>
    </section>

    <section class="space-y-6" x-show="tab === 'advisory'" x-cloak>
        <div class="grid gap-4 md:grid-cols-3"><article class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Completed Sessions</p><p class="mt-2 text-2xl font-black text-slate-900" x-text="number(data.advisory.sessions_completed)"></p></article><article class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Pending Sessions</p><p class="mt-2 text-2xl font-black text-slate-900" x-text="number(data.advisory.sessions_pending)"></p></article><article class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Average Rating</p><p class="mt-2 text-2xl font-black text-slate-900" x-text="data.advisory.average_rating"></p></article></div>
    </section>

    <section class="space-y-6" x-show="tab === 'content'" x-cloak>
        <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="text-lg font-black text-slate-900">Knowledge Hub Intelligence</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2"><div><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Most viewed articles</p><template x-for="article in data.content.most_viewed_articles" :key="`v-${article.id}`"><p class="mt-2 text-sm text-slate-700"><span class="font-semibold" x-text="article.title"></span> (<span x-text="number(article.views)"></span>)</p></template></div><div><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Recent content</p><template x-for="article in data.content.recent_articles" :key="`r-${article.id}`"><p class="mt-2 text-sm text-slate-700" x-text="article.title"></p></template></div></div>
        </article>
    </section>

    <section class="space-y-6" x-show="tab === 'notifications'" x-cloak>
        <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <h2 class="text-lg font-black text-slate-900">Broadcast Engine</h2>
                <form method="POST" action="{{ route('admin.broadcast') }}" class="mt-4 space-y-3">@csrf<input name="title" required placeholder="Campaign title" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm"><textarea name="message" required rows="4" placeholder="Notification message" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm"></textarea><select name="role" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm"><option value="">All roles</option><option value="farmer">Farmers</option><option value="vendor">Vendors</option><option value="agronomist">Agronomists</option><option value="admin">Admins</option></select><input name="region" placeholder="Optional region filter" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm"><select name="channel" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm"><option value="in_app">In-app</option><option value="email">Email-ready</option><option value="sms">SMS-ready</option></select><button class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Send Broadcast</button></form>
            </article>
            <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5"><h2 class="text-lg font-black text-slate-900">Recent Notification Activity</h2><div class="mt-4 space-y-3"><template x-for="item in data.notifications.recent" :key="`noti-${item.id}`"><div class="rounded-2xl border border-slate-100 p-4"><p class="text-sm font-bold text-slate-900" x-text="item.title"></p><p class="mt-1 text-xs text-slate-600" x-text="item.message"></p></div></template></div></article>
        </div>
    </section>

    <section class="space-y-6" x-show="tab === 'monitoring'" x-cloak>
        <div class="grid gap-4 md:grid-cols-3"><article class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Failed Payments (24h)</p><p class="mt-2 text-2xl font-black text-slate-900" x-text="number(data.monitoring.system_health.failed_payments_24h)"></p></article><article class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Stuck Orders</p><p class="mt-2 text-2xl font-black text-slate-900" x-text="number(data.monitoring.system_health.stuck_orders)"></p></article><article class="rounded-2xl bg-white p-5 shadow-lg shadow-black/5"><p class="text-xs uppercase tracking-[0.14em] text-slate-500">Pending Vendor KYC</p><p class="mt-2 text-2xl font-black text-slate-900" x-text="number(data.monitoring.system_health.pending_vendor_kyc)"></p></article></div>
        <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5"><h2 class="text-lg font-black text-slate-900">System Error Logs</h2><div class="mt-4 overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs text-emerald-200"><template x-if="!data.monitoring.log_tail.length"><p>No recent errors found.</p></template><template x-for="(line, idx) in data.monitoring.log_tail" :key="`log-${idx}`"><p class="mb-1 whitespace-pre-wrap break-all" x-text="line"></p></template></div></article>
    </section>

    <section class="space-y-6" x-show="tab === 'automation'" x-cloak>
        <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="text-lg font-black text-slate-900">Automation & Rules Engine</h2>
            <form method="POST" action="{{ route('admin.automation.rules') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <label class="rounded-xl border border-slate-200 p-4"><input type="checkbox" name="stuck_order_alert" value="1" class="mr-2" @checked($dashboard['automation']['stuck_order_alert'])><span class="text-sm font-semibold text-slate-800">Stuck Order Alerts</span></label>
                <label class="rounded-xl border border-slate-200 p-4"><input type="checkbox" name="fraud_guard_enabled" value="1" class="mr-2" @checked($dashboard['automation']['fraud_guard_enabled'])><span class="text-sm font-semibold text-slate-800">Fraud Guard Engine</span></label>
                <label class="rounded-xl border border-slate-200 p-4"><input type="checkbox" name="weekly_report_enabled" value="1" class="mr-2" @checked($dashboard['automation']['weekly_report_enabled'])><span class="text-sm font-semibold text-slate-800">Weekly Scheduled Reports</span></label>
                <label class="rounded-xl border border-slate-200 p-4"><input type="checkbox" name="auto_suspend_vendor_enabled" value="1" class="mr-2" @checked($dashboard['automation']['auto_suspend_vendor_enabled'])><span class="text-sm font-semibold text-slate-800">Auto Suspend High-Risk Vendors</span></label>
                <div><label class="text-xs uppercase tracking-[0.14em] text-slate-500">Stuck Order Threshold (hours)</label><input type="number" name="stuck_order_hours" min="1" max="240" value="{{ $dashboard['automation']['stuck_order_hours'] }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm"></div>
                <div><label class="text-xs uppercase tracking-[0.14em] text-slate-500">Unusual Order Multiplier</label><input type="number" step="0.1" name="unusual_order_multiplier" min="1.2" max="10" value="{{ $dashboard['automation']['unusual_order_multiplier'] }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm"></div>
                <button class="md:col-span-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Save Automation Rules</button>
            </form>
        </article>
        <article class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="text-lg font-black text-slate-900">Scheduled Report Delivery</h2>
            <p class="mt-1 text-sm text-slate-600">Configure recurring admin reports for operations visibility.</p>
            <form method="POST" action="{{ route('admin.reporting.schedule') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                @csrf
                <div>
                    <label class="text-xs uppercase tracking-[0.14em] text-slate-500">Frequency</label>
                    <select name="frequency" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        <option value="weekly" @selected(($dashboard['reporting']['schedule']['frequency'] ?? 'weekly') === 'weekly')>Weekly</option>
                        <option value="monthly" @selected(($dashboard['reporting']['schedule']['frequency'] ?? 'weekly') === 'monthly')>Monthly</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.14em] text-slate-500">Delivery Channel</label>
                    <select name="delivery_channel" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        <option value="email" @selected(($dashboard['reporting']['schedule']['delivery_channel'] ?? 'email') === 'email')>Email</option>
                        <option value="in_app" @selected(($dashboard['reporting']['schedule']['delivery_channel'] ?? 'email') === 'in_app')>In-app</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs uppercase tracking-[0.14em] text-slate-500">Recipient Email (optional)</label>
                    <input type="email" name="recipient_email" value="{{ $dashboard['reporting']['schedule']['recipient_email'] ?? '' }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm" placeholder="ops@neolifeporium.com">
                </div>
                <label class="md:col-span-2 rounded-xl border border-slate-200 p-4">
                    <input type="checkbox" name="enabled" value="1" class="mr-2" @checked(($dashboard['reporting']['schedule']['enabled'] ?? false) === true)>
                    <span class="text-sm font-semibold text-slate-800">Enable scheduled delivery</span>
                </label>
                <button class="md:col-span-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Save Report Schedule</button>
            </form>
            @if(!empty($dashboard['reporting']['schedule']['updated_at']))
                <p class="mt-3 text-xs text-slate-500">Last updated: {{ $dashboard['reporting']['schedule']['updated_at'] }}</p>
            @endif
        </article>
    </section>

    @if(session()->has('impersonator_id'))
        <form method="POST" action="{{ route('impersonation.leave') }}" class="fixed bottom-4 right-4 z-40">@csrf<button class="rounded-full bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-900 shadow-xl">Leave Impersonation</button></form>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function adminCommandCenter(initial, endpoint) {
    return {
        data: initial, endpoint, tab: 'analytics', range: { from: initial.range.from, to: initial.range.to },
        tabs: [{ key: 'analytics', label: 'Analytics Engine' }, { key: 'users', label: 'User & Role Mgmt' }, { key: 'marketplace', label: 'Marketplace Control' }, { key: 'finance', label: 'Financial System' }, { key: 'advisory', label: 'Advisory Mgmt' }, { key: 'content', label: 'Knowledge Hub' }, { key: 'notifications', label: 'Notification Engine' }, { key: 'monitoring', label: 'Monitoring & Logs' }, { key: 'automation', label: 'Automation Rules' }],
        cards: [], revenueChart: null, categoryChart: null,
        init() { this.syncCards(); this.renderCharts(); setInterval(() => this.refresh(), 60000); },
        number(value) { return Number(value ?? 0).toLocaleString(); },
        currency(value) { return `GHS ${Number(value ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`; },
        syncCards() {
            this.cards = [
                { key: 'revenue', label: 'Total Revenue', value: this.currency(this.data.kpis.total_revenue), delta: this.data.kpis.revenue_delta },
                { key: 'mrr', label: 'MRR (30d)', value: this.currency(this.data.kpis.mrr), delta: null },
                { key: 'dau', label: 'Daily Active Users', value: this.number(this.data.kpis.dau), delta: null },
                { key: 'conversion', label: 'Conversion Rate', value: `${this.data.kpis.conversion_rate}%`, delta: null },
                { key: 'aov', label: 'Avg Order Value', value: this.currency(this.data.kpis.aov), delta: this.data.kpis.orders_delta },
            ];
        },
        renderCharts() {
            const revenueLabels = this.data.charts.revenue_over_time.map((item) => item.label);
            const revenueValues = this.data.charts.revenue_over_time.map((item) => Number(item.value));
            const categoryLabels = this.data.charts.orders_by_category.map((item) => item.label);
            const categoryValues = this.data.charts.orders_by_category.map((item) => Number(item.value));
            if (this.revenueChart) this.revenueChart.destroy();
            if (this.categoryChart) this.categoryChart.destroy();
            this.revenueChart = new Chart(document.getElementById('adminRevenueChart'), { type: 'line', data: { labels: revenueLabels, datasets: [{ data: revenueValues, borderColor: '#22c55e', backgroundColor: 'rgba(34, 197, 94, 0.16)', fill: true, tension: 0.32 }] }, options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
            this.categoryChart = new Chart(document.getElementById('adminCategoryChart'), { type: 'bar', data: { labels: categoryLabels, datasets: [{ data: categoryValues, backgroundColor: '#3b82f6', borderRadius: 8 }] }, options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
        },
        async refresh() {
            const response = await fetch(`${this.endpoint}?from=${encodeURIComponent(this.range.from)}&to=${encodeURIComponent(this.range.to)}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) return;
            const payload = await response.json();
            this.data = payload.data;
            this.syncCards();
            this.renderCharts();
        },
        async refreshWithRange() { await this.refresh(); },
    };
}
</script>
@endsection
