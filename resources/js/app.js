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

Alpine.data('landingChat', () => ({
    open: false,
    isTyping: false,
    quickPrompts: [
        'How do I start as a farmer?',
        'I need expert advice',
        'Show me top products',
        'How do I become a vendor?',
    ],
    responses: {
        'How do I start as a farmer?':
            'Create an account, choose Farmer onboarding, add your crop and location, then we will suggest tools and insights for your farm.',
        'I need expert advice':
            'Open Advisory to book an agronomist session. You can choose crop specialists and pay securely before confirmation.',
        'Show me top products':
            'Go to Marketplace and filter by crop type or region. Our most purchased tools include irrigation kits, soil enhancers, and pest control packs.',
        'How do I become a vendor?':
            'Register first, then apply for the Vendor role from onboarding. Once approved, you can list products and manage orders from your dashboard.',
    },
    messages: [
        {
            from: 'agent',
            text: 'Hello. Welcome to Neolifeporium support. Choose a quick question to get started.',
        },
    ],

    sendPrompt(prompt) {
        if (this.isTyping) return;

        this.open = true;
        this.messages.push({ from: 'user', text: prompt });
        this.scrollToBottom();
        this.isTyping = true;

        window.setTimeout(() => {
            this.messages.push({
                from: 'agent',
                text: this.responses[prompt] ?? 'Thanks for reaching out. Our team will guide you shortly.',
            });
            this.isTyping = false;
            this.scrollToBottom();
        }, 550);
    },

    scrollToBottom() {
        this.$nextTick(() => {
            if (!this.$refs.chatScroll) return;
            this.$refs.chatScroll.scrollTop = this.$refs.chatScroll.scrollHeight;
        });
    },
}));

Alpine.start();
