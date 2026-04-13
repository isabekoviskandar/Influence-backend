<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);

const sidebarOpen = ref(false);

const navItems = [
    { name: 'Dashboard',  href: '/dashboard',          icon: 'grid' },
    { name: 'Settings',   href: '/dashboard/settings',  icon: 'settings' },
];

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-[#0f0f14] text-white flex">

        <!-- Sidebar overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-20 bg-black/60 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-30 w-64 flex flex-col bg-[#16161f] border-r border-white/5 transition-transform duration-300',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center gap-3 px-6 py-5 border-b border-white/5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-sm font-bold">
                    I
                </div>
                <span class="text-lg font-bold tracking-tight bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
                    Influence
                </span>
            </div>

            <!-- Nav -->
            <nav class="flex-1 py-6 px-3 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150"
                    :class="$page.url.startsWith(item.href) && item.href !== '/dashboard'
                        ? 'bg-indigo-500/15 text-indigo-400'
                        : $page.url === item.href
                            ? 'bg-indigo-500/15 text-indigo-400'
                            : 'text-gray-400 hover:text-white hover:bg-white/5'"
                >
                    <!-- Grid icon -->
                    <svg v-if="item.icon === 'grid'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <!-- Settings icon -->
                    <svg v-if="item.icon === 'settings'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User info + logout -->
            <div class="border-t border-white/5 p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-xs font-bold uppercase">
                        {{ user?.name?.[0] ?? 'U' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ user?.name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                    </div>
                </div>
                <span class="inline-block px-2 py-0.5 text-xs rounded-full mb-3"
                      :class="user?.plan === 'pro' ? 'bg-amber-500/20 text-amber-400' : 'bg-indigo-500/20 text-indigo-400'">
                    {{ user?.plan === 'pro' ? '⭐ Pro' : '🆓 Free' }}
                </span>
                <button
                    @click="logout"
                    class="w-full text-left text-xs text-gray-500 hover:text-red-400 transition-colors"
                >
                    Sign out
                </button>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 lg:ml-64 min-h-screen flex flex-col">

            <!-- Top bar (mobile) -->
            <header class="lg:hidden flex items-center justify-between px-4 py-3 border-b border-white/5 bg-[#16161f]">
                <button @click="sidebarOpen = true" class="text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <span class="text-sm font-semibold text-indigo-400">Influence</span>
                <div class="w-5" />
            </header>

            <!-- Flash messages -->
            <div v-if="flash?.success" class="mx-6 mt-4 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-lg text-green-400 text-sm">
                {{ flash.success }}
            </div>
            <div v-if="flash?.error" class="mx-6 mt-4 px-4 py-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm">
                {{ flash.error }}
            </div>

            <!-- Page slot -->
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
