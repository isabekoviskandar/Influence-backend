<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);

const sidebarOpen = ref(false);

const navItems = [
    { label: 'Platform', items: [
        { name: 'Dashboard',  href: '/dashboard',          icon: 'grid', badge: user.value?.active_channels_count },
    ]},
    { label: 'Management', items: [
        { name: 'Settings',   href: '/dashboard/settings',  icon: 'settings' },
    ]},
];

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-[#0a0a0f] text-white flex font-sans selection:bg-indigo-500/30">

        <!-- Sidebar overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 w-60 flex flex-col bg-[#111118] border-r border-white/5 transition-all duration-300',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center gap-3 px-6 py-6 h-20">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-lg font-bold shadow-lg shadow-indigo-500/20">
                    I
                </div>
                <span class="text-xl font-black tracking-tight bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
                    Influence
                </span>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-4 space-y-8 overflow-y-auto pt-4">
                <div v-for="section in navItems" :key="section.label">
                    <p class="px-3 mb-3 text-[10px] font-bold uppercase tracking-[0.2em] text-gray-600">
                        {{ section.label }}
                    </p>
                    <div class="space-y-1">
                        <Link
                            v-for="item in section.items"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center group px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200"
                            :class="$page.url.startsWith(item.href) && item.href !== '/dashboard'
                                ? 'bg-white/5 text-white'
                                : $page.url === item.href
                                    ? 'bg-white/5 text-white'
                                    : 'text-gray-500 hover:text-white hover:bg-white/[0.02]'"
                        >
                            <div class="flex items-center gap-3 flex-1">
                                <!-- Icons -->
                                <svg v-if="item.icon === 'grid'" class="w-4 h-4 transition-colors group-hover:text-indigo-400" :class="$page.url === item.href ? 'text-indigo-400' : 'text-current'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <svg v-if="item.icon === 'settings'" class="w-4 h-4 transition-colors group-hover:text-indigo-400" :class="$page.url === item.href ? 'text-indigo-400' : 'text-current'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ item.name }}
                            </div>
                            <span v-if="item.badge" class="bg-indigo-500/10 text-indigo-400 text-[10px] px-2 py-0.5 rounded-full font-bold border border-indigo-500/20">
                                {{ item.badge }}
                            </span>
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Bottom Profile -->
            <div class="p-4 border-t border-white/5 bg-black/20">
                <div class="flex items-center gap-3 px-2 py-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-sm font-bold shadow-lg shadow-indigo-500/10 border border-white/10 shrink-0 overflow-hidden">
                        <img v-if="user?.avatar" :src="`/storage/${user.avatar}`" class="w-full h-full object-cover" />
                        <span v-else>{{ user?.name?.[0] ?? 'U' }}</span>
                    </div>
                    <div class="flex-1 min-w-0 pr-2">
                        <p class="text-xs font-bold truncate text-white uppercase tracking-wider">{{ user?.name }}</p>
                        <p class="text-[10px] text-gray-500 truncate leading-tight mt-0.5">{{ user?.email }}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-2 mt-2 px-1 pb-2">
                    <button @click="logout" class="py-2 rounded-lg text-[10px] font-bold text-gray-500 hover:text-white hover:bg-white/5 transition-all text-center border border-white/5">
                        Logout
                    </button>
                    <div class="py-2 rounded-lg text-[10px] font-bold text-center border border-white/5 flex items-center justify-center gap-1.5"
                         :class="user?.plan === 'pro' ? 'text-amber-400' : 'text-indigo-400'">
                         {{ user?.plan === 'pro' ? 'Pro' : 'Free' }}
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 lg:ml-60 min-h-screen flex flex-col">

            <!-- Global Sticky Topbar -->
            <header class="sticky top-0 z-40 bg-[#0a0a0f]/80 backdrop-blur-xl border-b border-white/[0.05] h-20 flex items-center justify-between px-6 lg:px-10">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-gray-400 hover:text-white p-2 bg-white/5 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h2 class="text-lg font-bold text-white hidden lg:block tracking-tight opacity-50">Influence / {{ $page.component.split('/').pop() }}</h2>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-white/5 rounded-full border border-white/5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Network Live</span>
                    </div>
                </div>
            </header>

            <!-- Flash messages -->
            <transition name="fade">
                <div v-if="flash?.success || flash?.error" class="fixed top-24 right-6 z-50 flex flex-col gap-2 max-w-sm pointer-events-none">
                    <div v-if="flash?.success" class="px-5 py-3 bg-emerald-500/10 border border-emerald-500/20 backdrop-blur-md rounded-2xl text-emerald-400 text-xs font-bold shadow-xl shadow-emerald-500/5 pointer-events-auto">
                        {{ flash.success }}
                    </div>
                    <div v-if="flash?.error" class="px-5 py-3 bg-red-500/10 border border-red-500/20 backdrop-blur-md rounded-2xl text-red-400 text-xs font-bold shadow-xl shadow-red-500/5 pointer-events-auto">
                        {{ flash.error }}
                    </div>
                </div>
            </transition>

            <!-- Page slot -->
            <main class="flex-1 p-6 lg:p-10 max-w-[1600px] w-full mx-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
