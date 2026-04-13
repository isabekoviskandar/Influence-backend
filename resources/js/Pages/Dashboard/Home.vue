<script setup>
import { computed, ref, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

function debounce(fn, wait) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), wait);
    };
}

const props = defineProps({
    channels:     Array,
    stats:        Object,
    bot_username: String,
    filters:      Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const search = ref(props.filters.search);
const status = ref(props.filters.status);
const sort   = ref(props.filters.sort);

const updateFilters = debounce(() => {
    router.get(route('dashboard.index'), {
        search: search.value,
        status: status.value,
        sort:   sort.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 300);

watch([search, status, sort], () => {
    updateFilters();
});

const addChannelUrl = computed(() =>
    `https://t.me/${props.bot_username}`
);

function formatNumber(n) {
    if (!n) return '0';
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M';
    if (n >= 1_000)     return (n / 1_000).toFixed(1) + 'K';
    return n.toString();
}
</script>

<template>
    <DashboardLayout>

        <!-- Page header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
                    Welcome back, {{ user?.name?.split(' ')[0] }}
                </h1>
                <p class="text-gray-500 text-sm mt-1.5 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    System is active and tracking {{ stats.total_channels }} channels
                </p>
            </div>
            <a
                :href="addChannelUrl"
                target="_blank"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-bold bg-white text-black hover:bg-gray-200 transition-all shadow-xl shadow-white/5 group"
            >
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Connect New Channel
            </a>
        </div>

        <!-- Stats row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div
                v-for="(stat, key) in [
                    { label: 'Total Channels',   value: stats.total_channels,   icon: '📡', glow: 'shadow-indigo-500/10' },
                    { label: 'Active Channels',  value: stats.active_channels,  icon: '⚡', glow: 'shadow-emerald-500/10' },
                    { label: 'Total Members',    value: formatNumber(stats.total_members), icon: '👥', glow: 'shadow-purple-500/10' },
                    { label: 'Avg Engagement',   value: stats.avg_engagement + '%', icon: '📈', glow: 'shadow-amber-500/10' },
                ]"
                :key="key"
                class="group relative bg-[#111118] border border-white/[0.03] rounded-2xl p-6 transition-all duration-300 hover:border-white/10 hover:-translate-y-1 overflow-hidden"
                :class="stat.glow"
            >
                <!-- Background Glow -->
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/[0.01] rounded-full blur-2xl group-hover:bg-white/[0.03] transition-colors"></div>

                <div class="flex items-center justify-between mb-4">
                    <span class="text-2xl">{{ stat.icon }}</span>
                    <svg class="w-5 h-5 text-gray-700 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <p class="text-3xl font-black text-white tracking-tight">{{ stat.value }}</p>
                <p class="text-xs font-medium text-gray-500 mt-1 uppercase tracking-wider">{{ stat.label }}</p>
            </div>
        </div>

        <!-- Control Bar (Filters) -->
        <div class="bg-[#111118]/60 backdrop-blur-xl border border-white/[0.05] rounded-2xl p-4 mb-8 flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="relative flex-1">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by name or username..."
                    class="w-full bg-black/40 border-white/[0.05] border focus:border-indigo-500/50 focus:ring-0 rounded-xl pl-11 pr-4 py-2.5 text-sm text-white placeholder-gray-600 transition-all"
                >
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Status dropdown -->
                <select
                    v-model="status"
                    class="bg-black/40 border-white/[0.05] border focus:border-indigo-500/50 focus:ring-0 rounded-xl px-4 py-2.5 text-sm text-white transition-all cursor-pointer min-w-[140px]"
                >
                    <option value="all">All Status</option>
                    <option value="active">Active Only</option>
                    <option value="paused">Paused Only</option>
                </select>

                <!-- Sort dropdown -->
                <select
                    v-model="sort"
                    class="bg-black/40 border-white/[0.05] border focus:border-indigo-500/50 focus:ring-0 rounded-xl px-4 py-2.5 text-sm text-white transition-all cursor-pointer min-w-[140px]"
                >
                    <option value="latest">Latest Added</option>
                    <option value="score">Highest Score</option>
                    <option value="members">Top Members</option>
                    <option value="engagement">Best Engagement</option>
                </select>
            </div>
        </div>

        <!-- Channels grid -->
        <div v-if="channels.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <Link
                v-for="ch in channels"
                :key="ch.id"
                :href="`/dashboard/channels/${ch.id}`"
                class="group relative bg-[#111118] border border-white/[0.03] rounded-2xl p-6 transition-all duration-500 hover:border-indigo-500/30 hover:shadow-[0_0_30px_-10px_rgba(99,102,241,0.2)]"
            >
                <!-- Score Badge -->
                <div class="absolute top-4 right-4 flex flex-col items-end">
                    <div class="w-10 h-10 rounded-full border-2 border-indigo-500/20 flex items-center justify-center relative">
                         <svg class="absolute inset-0 w-full h-full -rotate-90">
                            <circle
                                cx="20" cy="20" r="18"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3"
                                class="text-indigo-500/10"
                            />
                            <circle
                                cx="20" cy="20" r="18"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3"
                                :stroke-dasharray="113"
                                :stroke-dashoffset="113 - (113 * (ch.potential_score || 0)) / 100"
                                class="text-indigo-500 transition-all duration-1000"
                            />
                        </svg>
                        <span class="text-[10px] font-bold text-white">{{ ch.potential_score || 0 }}</span>
                    </div>
                    <span class="text-[8px] uppercase tracking-tighter text-gray-600 mt-1 font-bold">Potential</span>
                </div>

                <!-- Channel info -->
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-white/5 flex items-center justify-center text-2xl font-bold text-white group-hover:scale-110 transition-transform duration-500">
                        {{ ch.title?.[0]?.toUpperCase() ?? '#' }}
                    </div>
                    <div class="pr-12">
                        <h3 class="font-bold text-white group-hover:text-indigo-400 transition-colors line-clamp-1">{{ ch.title }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ ch.username ? '@' + ch.username : 'Private Channel' }}</p>
                    </div>
                </div>

                <!-- Metrics grid -->
                <div class="grid grid-cols-3 gap-4 p-4 bg-black/20 rounded-2xl border border-white/[0.02]">
                    <div class="text-center">
                        <p class="text-sm font-black text-white">{{ formatNumber(ch.member_count) }}</p>
                        <p class="text-[9px] uppercase tracking-widest text-gray-500 mt-1">Fans</p>
                    </div>
                    <div class="text-center border-x border-white/5">
                        <p class="text-sm font-black text-white">{{ formatNumber(ch.avg_views) }}</p>
                        <p class="text-[9px] uppercase tracking-widest text-gray-500 mt-1">Views</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-black text-white">{{ ch.engagement_rate ? ch.engagement_rate + '%' : '—' }}</p>
                        <p class="text-[9px] uppercase tracking-widest text-gray-500 mt-1">Engage</p>
                    </div>
                </div>

                <!-- Status & Sync Info -->
                <div class="mt-6 pt-4 border-t border-white/[0.03] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span :class="ch.is_active ? 'bg-green-500' : 'bg-red-500'" class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                            <span :class="ch.is_active ? 'bg-green-500' : 'bg-red-500'" class="relative inline-flex rounded-full h-2 w-2"></span>
                        </span>
                        <span class="text-[10px] font-bold uppercase tracking-tight" :class="ch.is_active ? 'text-green-500/80' : 'text-red-500/80'">
                            {{ ch.is_active ? 'Live Tracking' : 'Paused' }}
                        </span>
                    </div>
                    <span class="text-[10px] font-medium text-gray-600 italic">
                        {{ ch.last_synced_at ?? 'Pending sync' }}
                    </span>
                </div>
            </Link>
        </div>

        <!-- No Results state -->
        <div v-else-if="search" class="flex flex-col items-center justify-center py-20 bg-[#111118] border border-white/[0.03] rounded-3xl">
            <div class="text-4xl mb-4">🔍</div>
            <h2 class="text-xl font-bold text-white mb-1">No channels found</h2>
            <p class="text-gray-500 text-sm">Try adjusting your search or filters</p>
            <button @click="search = ''; status = 'all'; sort = 'latest'" class="mt-6 text-indigo-400 text-xs font-bold hover:underline">Clear all filters</button>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center justify-center py-24 bg-[#111118]/40 border border-dashed border-white/10 rounded-3xl">
            <div class="w-20 h-20 rounded-3xl bg-white/5 flex items-center justify-center text-4xl mb-6">📡</div>
            <h2 class="text-2xl font-bold text-white mb-2">Grow your influence</h2>
            <p class="text-gray-500 text-sm text-center max-w-sm mb-8 leading-relaxed">
                Connect your first Telegram channel to unlock deep insights, engagement tracking, and viral post detection.
            </p>
            <a
                :href="addChannelUrl"
                target="_blank"
                class="inline-flex items-center gap-3 px-8 py-4 rounded-2xl text-sm font-bold bg-indigo-600 text-white shadow-2xl shadow-indigo-600/20 hover:bg-indigo-500 hover:-translate-y-1 transition-all"
            >
                Launch Telegram Bot
            </a>
        </div>

    </DashboardLayout>
</template>

<style scoped>
/* Custom progress ring animation */
@keyframes ring-dash {
    from { stroke-dashoffset: 113; }
}
circle:last-child {
    animation: ring-dash 1.5s ease-out forwards;
}
</style>
