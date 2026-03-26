<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Neolifeporium Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f2f5ef] text-slate-900" x-data="dashboardShell" x-init="init()">
    <script>
        window.dashboardNotificationsUrl = @json(route('dashboard.notifications'));
    </script>
    <div class="flex min-h-screen">
        <aside :class="sidebarOpen ? 'w-72' : 'w-20'" class="hidden shrink-0 border-r border-slate-200 bg-white transition-all duration-200 lg:block">
            <div class="flex h-16 items-center gap-2 px-4">
                <a href="{{ route('home') }}" class="text-sm font-black uppercase tracking-[0.15em] text-palm" x-show="sidebarOpen">Neolifeporium</a>
                <span x-show="!sidebarOpen" class="text-lg font-black text-palm">N</span>
                <button type="button" @click="toggleSidebar" class="ml-auto rounded-lg border border-slate-200 p-2 text-slate-600">
                    <span class="sr-only">Toggle sidebar</span>
                    <svg class="h-4 w-4 transition-transform duration-200" :class="sidebarOpen ? '' : 'rotate-180'" viewBox="0 0 24 24" fill="none"><path d="M15 6L9 12L15 18" stroke="currentColor" stroke-width="2"/></svg>
                </button>
            </div>
            <nav class="space-y-1 px-3 py-4">
                @foreach(($sidebarLinks ?? []) as $link)
                    <a href="{{ $link['href'] }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold {{ request()->url() === $link['href'] ? 'bg-palm text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                        <span class="h-2 w-2 rounded-full {{ request()->url() === $link['href'] ? 'bg-white' : 'bg-slate-400' }}"></span>
                        <span x-show="sidebarOpen">{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>

        <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-40 lg:hidden">
            <div class="absolute inset-0 bg-black/40" @click="mobileMenuOpen = false"></div>
            <aside class="absolute left-0 top-0 h-full w-72 border-r border-slate-200 bg-white p-4 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <a href="{{ route('home') }}" class="text-sm font-black uppercase tracking-[0.15em] text-palm">Neolifeporium</a>
                    <button type="button" @click="mobileMenuOpen = false" class="rounded-lg border border-slate-200 p-2 text-slate-600">
                        <span class="sr-only">Close menu</span>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="2"/></svg>
                    </button>
                </div>
                <nav class="space-y-1">
                    @foreach(($sidebarLinks ?? []) as $link)
                        <a href="{{ $link['href'] }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold {{ request()->url() === $link['href'] ? 'bg-palm text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                            <span class="h-2 w-2 rounded-full {{ request()->url() === $link['href'] ? 'bg-white' : 'bg-slate-400' }}"></span>
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </aside>
        </div>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <button type="button" class="rounded-lg border border-slate-200 p-2 text-slate-600 lg:hidden" @click="mobileMenuOpen = !mobileMenuOpen">
                        <span class="sr-only">Open menu</span>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2"/></svg>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-lg font-black text-slate-900">{{ $dashboardTitle ?? 'Dashboard' }}</p>
                        <p class="truncate text-xs uppercase tracking-[0.14em] text-slate-500">{{ $dashboardSubtitle ?? 'Operational view' }}</p>
                    </div>

                    <div class="relative w-full max-w-sm">
                        <input type="text" x-model.debounce.300ms="searchQuery" @input="search" placeholder="Search products, users, orders" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:border-palm/40">
                        <div x-show="searchOpen" @click.outside="searchOpen = false" class="absolute right-0 top-12 z-50 w-full rounded-xl border border-slate-200 bg-white p-3 shadow-xl">
                            <template x-if="!searchResults.products.length && !searchResults.users.length && !searchResults.orders.length">
                                <p class="text-xs text-slate-500">No results</p>
                            </template>
                            <template x-if="searchResults.products.length">
                                <div class="mb-2">
                                    <p class="mb-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Products</p>
                                    <template x-for="item in searchResults.products" :key="'p'+item.id">
                                        <p class="truncate text-sm text-slate-700" x-text="item.name"></p>
                                    </template>
                                </div>
                            </template>
                            <template x-if="searchResults.users.length">
                                <div class="mb-2">
                                    <p class="mb-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Users</p>
                                    <template x-for="item in searchResults.users" :key="'u'+item.id">
                                        <p class="truncate text-sm text-slate-700" x-text="item.name"></p>
                                    </template>
                                </div>
                            </template>
                            <template x-if="searchResults.orders.length">
                                <div>
                                    <p class="mb-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Orders</p>
                                    <template x-for="item in searchResults.orders" :key="'o'+item.id">
                                        <p class="truncate text-sm text-slate-700" x-text="item.order_number + ' - ' + item.status"></p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <button type="button" @click="notificationsOpen = !notificationsOpen" class="relative rounded-xl border border-slate-200 p-2 text-slate-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M15 17H9M18 9A6 6 0 0 0 6 9C6 14 4 16 4 16H20C20 16 18 14 18 9Z" stroke="currentColor" stroke-width="1.8"/></svg>
                        <span class="absolute -right-1 -top-1 rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white" x-show="unreadCount > 0" x-text="unreadCount"></span>
                    </button>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">Logout</button>
                    </form>
                </div>
            </header>

            <div class="relative flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <div x-show="notificationsOpen" @click.outside="notificationsOpen = false" class="absolute right-8 top-4 z-40 w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl">
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-sm font-bold text-slate-900">Notifications</p>
                        <button type="button" @click="fetchNotifications" class="text-xs font-semibold text-palm">Refresh</button>
                    </div>
                    <div class="space-y-2">
                        <template x-if="!notifications.length">
                            <p class="text-xs text-slate-500">No alerts</p>
                        </template>
                        <template x-for="item in notifications" :key="item.id">
                            <div class="rounded-xl border border-slate-100 p-3">
                                <p class="text-sm font-semibold text-slate-900" x-text="item.title"></p>
                                <p class="mt-1 text-xs text-slate-600" x-text="item.message"></p>
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="text-[11px] text-slate-500" x-text="item.created_at"></p>
                                    <button type="button" x-show="!item.read_at" @click="markRead(item.id)" class="text-[11px] font-semibold text-palm">Mark read</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
                @endif

                @yield('dashboard_content')
            </div>
        </div>
    </div>

</body>
</html>
