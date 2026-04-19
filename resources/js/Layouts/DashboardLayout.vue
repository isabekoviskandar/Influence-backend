<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);

const sidebarOpen = ref(false);
const userDropdownOpen = ref(false);

// Current page name from Inertia component path
const currentPage = computed(() => {
    const comp = page.component;
    return comp.split('/').pop() ?? 'Dashboard';
});

const navSections = [
    {
        label: 'PLATFORM',
        items: [
            { name: 'Dashboard', href: '/dashboard', icon: 'grid', badge: user.value?.active_channels_count },
        ],
    },
    {
        label: 'MANAGEMENT',
        items: [
            { name: 'Channels',    href: '/dashboard/channels',    icon: 'signal' },
            { name: 'Analytics',   href: '/dashboard/analytics',   icon: 'chart' },
            { name: 'Posts',       href: '/dashboard/posts',       icon: 'document' },
            { name: 'Leaderboard', href: '/dashboard/leaderboard', icon: 'trophy' },
        ],
    },
    {
        label: 'ACCOUNT',
        items: [
            { name: 'Settings', href: '/dashboard/settings', icon: 'settings' },
            { name: 'Upgrade',  href: '/dashboard/upgrade',  icon: 'star', amber: true },
        ],
    },
];

function isActive(href) {
    if (href === '/dashboard') return page.url === '/dashboard';
    return page.url.startsWith(href);
}

function logout() {
    router.post('/logout');
}

function closeDropdown() {
    userDropdownOpen.value = false;
}
</script>

<template>
    <div class="min-h-screen bg-[#0a0a0b] text-[#f2f2f3] flex" style="font-family: 'Geist', -apple-system, sans-serif;">

        <!-- Sidebar overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/60 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- ─── SIDEBAR ─── -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 w-[220px] flex flex-col bg-[#0a0a0b] border-r border-[#222224] transition-transform duration-200',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center gap-2.5 px-5 h-[56px] shrink-0">
                <div class="w-7 h-7 rounded-lg bg-[#6366f1] flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 16 16">
                        <rect x="2" y="2" width="5" height="5" rx="1"/>
                        <rect x="9" y="2" width="5" height="5" rx="1"/>
                        <rect x="2" y="9" width="5" height="5" rx="1"/>
                        <rect x="9" y="9" width="5" height="5" rx="1" opacity="0.4"/>
                    </svg>
                </div>
                <span class="text-[15px] font-semibold text-[#f2f2f3] tracking-[-0.01em]">Influence</span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 pt-2 space-y-6 scrollbar-none">
                <div v-for="section in navSections" :key="section.label">
                    <p class="px-2 mb-2 text-[11px] font-medium uppercase tracking-[0.06em] text-[#4a4a50]">
                        {{ section.label }}
                    </p>
                    <div class="space-y-0.5">
                        <Link
                            v-for="item in section.items"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-2.5 px-2 py-[7px] text-[13px] transition-all duration-[120ms] group relative"
                            :class="isActive(item.href)
                                ? 'text-[#6366f1] bg-[rgba(99,102,241,0.12)] rounded-r-[7px] font-medium'
                                : 'text-[#8a8a8f] hover:bg-[#18181a] rounded-[7px]'"
                        >
                            <!-- Active left border -->
                            <div
                                v-if="isActive(item.href)"
                                class="absolute left-0 top-1 bottom-1 w-[2px] bg-[#6366f1] rounded-r"
                            />

                            <!-- Icons -->
                            <svg v-if="item.icon === 'grid'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                            </svg>
                            <svg v-if="item.icon === 'signal'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.652a3.75 3.75 0 010-5.304m5.304 0a3.75 3.75 0 010 5.304m-7.425 2.121a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.807-3.808-9.98 0-13.788m13.788 0c3.808 3.807 3.808 9.98 0 13.788M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                            </svg>
                            <svg v-if="item.icon === 'chart'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                            </svg>
                            <svg v-if="item.icon === 'document'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                            <svg v-if="item.icon === 'trophy'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.996.178-1.768-.767-1.559-1.746l.09-.42A1.125 1.125 0 014.883 1.5h1.242a.75.75 0 01.662.397l.13.26a.75.75 0 00.662.397h8.841a.75.75 0 00.662-.397l.13-.26a.75.75 0 01.662-.397h1.243a1.125 1.125 0 011.103.569l.09.42c.208.98-.564 1.924-1.56 1.746"/>
                            </svg>
                            <svg v-if="item.icon === 'settings'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <svg v-if="item.icon === 'star'" class="w-4 h-4 shrink-0" :class="item.amber ? 'text-[#f59e0b]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                            </svg>

                            <span :class="item.amber && !isActive(item.href) ? 'text-[#f59e0b]' : ''">{{ item.name }}</span>

                            <!-- Badge -->
                            <span
                                v-if="item.badge"
                                class="ml-auto text-[10px] font-semibold px-[6px] py-[1px] rounded-full bg-[rgba(99,102,241,0.12)] text-[#6366f1] border border-[rgba(99,102,241,0.2)]"
                            >
                                {{ item.badge }}
                            </span>
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Bottom user card -->
            <div class="p-3 shrink-0">
                <div class="flex items-center gap-2.5 p-2.5 bg-[#18181a] border border-[#222224] rounded-lg">
                    <div class="w-8 h-8 rounded-full bg-[#6366f1] flex items-center justify-center text-[12px] font-semibold text-white shrink-0 overflow-hidden">
                        <img v-if="user?.avatar" :src="`/storage/${user.avatar}`" class="w-full h-full object-cover" />
                        <span v-else>{{ user?.name?.[0] ?? 'U' }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[12px] font-medium text-[#f2f2f3] truncate">{{ user?.name }}</p>
                    </div>
                    <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-[rgba(245,158,11,0.10)] text-[#f59e0b] border border-[rgba(245,158,11,0.2)] shrink-0">
                        {{ user?.plan === 'pro' ? 'PRO' : 'FREE' }}
                    </span>
                </div>
            </div>
        </aside>

        <!-- ─── MAIN AREA ─── -->
        <div class="flex-1 lg:ml-[220px] min-h-screen flex flex-col">

            <!-- ─── TOPBAR ─── -->
            <header class="sticky top-0 z-40 bg-[#0a0a0b] border-b border-[#222224] h-[52px] flex items-center justify-between px-5 lg:px-7 shrink-0">
                <div class="flex items-center gap-3">
                    <!-- Mobile menu -->
                    <button @click="sidebarOpen = true" class="lg:hidden text-[#8a8a8f] hover:text-[#f2f2f3] p-1.5 rounded-[7px] hover:bg-[#18181a] transition-all duration-[120ms]">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                        </svg>
                    </button>

                    <!-- Breadcrumb -->
                    <div class="hidden lg:flex items-center gap-0 text-[13px]" style="font-family: 'Geist Mono', monospace;">
                        <span class="text-[#4a4a50]">influence.uz</span>
                        <span class="text-[#4a4a50] mx-2">/</span>
                        <span class="text-[#f2f2f3] font-medium">{{ currentPage.toLowerCase() }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Network Live chip -->
                    <div class="hidden sm:flex items-center gap-2 px-2.5 py-1 bg-[rgba(34,197,94,0.10)] border border-[rgba(34,197,94,0.2)] rounded-[6px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#22c55e] animate-pulse-dot"></span>
                        <span class="text-[11px] font-medium text-[#22c55e] uppercase tracking-[0.04em]" style="font-family: 'Geist Mono', monospace;">NETWORK LIVE</span>
                    </div>

                    <!-- Bell icon -->
                    <button class="text-[#8a8a8f] hover:text-[#f2f2f3] p-1.5 rounded-[7px] hover:bg-[#18181a] transition-all duration-[120ms] relative">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                    </button>

                    <!-- User avatar + dropdown -->
                    <div class="relative">
                        <button
                            @click="userDropdownOpen = !userDropdownOpen"
                            class="w-7 h-7 rounded-full bg-[#6366f1] flex items-center justify-center text-[11px] font-semibold text-white cursor-pointer hover:ring-2 hover:ring-[#6366f1]/30 transition-all duration-[120ms] overflow-hidden"
                        >
                            <img v-if="user?.avatar" :src="`/storage/${user.avatar}`" class="w-full h-full object-cover" />
                            <span v-else>{{ user?.name?.[0] ?? 'I' }}</span>
                        </button>

                        <!-- Dropdown -->
                        <div
                            v-if="userDropdownOpen"
                            class="absolute right-0 top-full mt-2 w-[180px] bg-[#111112] border border-[#222224] rounded-[10px] py-1.5 z-50"
                        >
                            <a href="/dashboard/settings" class="block px-3 py-2 text-[13px] text-[#8a8a8f] hover:text-[#f2f2f3] hover:bg-[#18181a] transition-all duration-[120ms]">Profile</a>
                            <a href="/dashboard/settings" class="block px-3 py-2 text-[13px] text-[#8a8a8f] hover:text-[#f2f2f3] hover:bg-[#18181a] transition-all duration-[120ms]">Settings</a>
                            <div class="my-1.5 border-t border-[#222224]"></div>
                            <button @click="logout" class="block w-full text-left px-3 py-2 text-[13px] text-[#8a8a8f] hover:text-[#ef4444] hover:bg-[rgba(239,68,68,0.05)] transition-all duration-[120ms]">Sign out</button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Close dropdown on outside click -->
            <div v-if="userDropdownOpen" class="fixed inset-0 z-30" @click="closeDropdown" />

            <!-- Flash messages -->
            <transition name="fade">
                <div v-if="flash?.success || flash?.error" class="fixed top-16 right-6 z-50 flex flex-col gap-2 max-w-sm pointer-events-none">
                    <div v-if="flash?.success" class="px-4 py-2.5 bg-[rgba(34,197,94,0.10)] border border-[rgba(34,197,94,0.2)] rounded-[10px] text-[#22c55e] text-[12px] font-medium pointer-events-auto">
                        {{ flash.success }}
                    </div>
                    <div v-if="flash?.error" class="px-4 py-2.5 bg-[rgba(239,68,68,0.10)] border border-[rgba(239,68,68,0.2)] rounded-[10px] text-[#ef4444] text-[12px] font-medium pointer-events-auto">
                        {{ flash.error }}
                    </div>
                </div>
            </transition>

            <!-- Page slot -->
            <main class="flex-1 p-5 lg:p-7">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
