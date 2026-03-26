import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('dashboardShell', () => ({
    sidebarOpen: true,
    mobileMenuOpen: false,
    notificationsOpen: false,
    searchOpen: false,
    searchQuery: '',
    unreadCount: 0,
    notifications: [],
    searchResults: { products: [], users: [], orders: [] },

    init() {
        const stored = localStorage.getItem('dashboard_sidebar_open');
        if (stored !== null) {
            this.sidebarOpen = stored === '1';
        }

        this.fetchNotifications();
        setInterval(() => this.fetchNotifications(), 60000);
    },

    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
        localStorage.setItem('dashboard_sidebar_open', this.sidebarOpen ? '1' : '0');
    },

    async fetchNotifications() {
        const url = window.dashboardNotificationsUrl;
        if (!url) return;

        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!response.ok) return;

        const payload = await response.json();
        this.notifications = payload.data ?? [];
        this.unreadCount = this.notifications.filter((item) => !item.read_at).length;
    },

    async markRead(id) {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        await fetch(`/dashboard/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                Accept: 'application/json',
            },
        });

        this.fetchNotifications();
    },

    async search() {
        if (!this.searchQuery.trim()) {
            this.searchResults = { products: [], users: [], orders: [] };
            this.searchOpen = false;
            return;
        }

        const response = await fetch(`/dashboard/search?q=${encodeURIComponent(this.searchQuery)}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) return;

        const payload = await response.json();
        this.searchResults = payload.data ?? { products: [], users: [], orders: [] };
        this.searchOpen = true;
    },
}));

Alpine.start();
