<script setup>
import { computed, ref, watch } from 'vue';
import { useForm, router, Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    channels:      Array,
    stats:         Object,
    bot_username:  String,
    filters:       Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// Filter state
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const sort   = ref(props.filters.sort   || 'latest');

function updateFilters() {
    router.get('/dashboard', {
        search: search.value,
        status: status.value,
        sort:   sort.value,
    }, {
        preserveScroll: true,
        preserveState:  true,
        replace:        true,
    });
}

// Watch for changes to trigger filter update
watch([search, status, sort], () => {
    handleSearch();
});

// Watchers for immediate filtering
let debounceTimeout;
function handleSearch() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(updateFilters, 300);
}

const addChannelUrl = `https://t.me/${props.bot_username}?start=add_channel`;

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num;
}

// Sparkline SVG path generator
function getSparklinePath(data) {
    if (!data || data.length < 2) return '';
    const width = 120;
    const height = 40;
    const min = Math.min(...data);
    const max = Math.max(...data);
    const range = max - min || 1;
    
    const points = data.map((val, i) => {
        const x = (i / (data.length - 1)) * width;
        const y = height - ((val - min) / range) * height;
        return `${x},${y}`;
    });
    
    return `M ${points.join(' L ')}`;
}

const statsConfig = [
    { label: 'Network Reach', value: formatNumber(props.stats.total_members), badge: props.stats.total_members_change + '%', icon: 'users', color: 'indigo', glow: 'rgba(99, 102, 241, 0.15)' },
    { label: 'Active Tracking', value: props.stats.active_channels, badge: null, icon: 'radar', color: 'emerald', glow: 'rgba(16, 185, 129, 0.15)' },
    { label: 'Portfolio Size', value: props.stats.total_channels, badge: null, icon: 'grid', color: 'purple', glow: 'rgba(168, 85, 247, 0.15)' },
    { label: 'Avg Engagement', value: props.stats.avg_engagement + '%', badge: null, icon: 'zap', color: 'amber', glow: 'rgba(245, 158, 11, 0.15)' },
];
</script>

<template>
    <DashboardLayout>
        
        <!-- Premium Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-12">
            <div 
                v-for="stat in statsConfig" 
                :key="stat.label"
                class="relative group overflow-hidden bg-[#111118] border border-white/[0.05] rounded-[2rem] p-8 transition-all hover:border-white/10 hover:-translate-y-1"
                :style="{ boxShadow: `inset 0 0 40px ${stat.glow}` }"
            >
                <div class="flex items-start justify-between mb-4">
                    <div :class="`w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center`" :style="{ color: `var(--${stat.color}-400)` }">
                        <!-- Dynamic Icons -->
                        <svg v-if="stat.icon === 'users'" class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg v-if="stat.icon === 'radar'" class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <svg v-if="stat.icon === 'grid'" class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <svg v-if="stat.icon === 'zap'" class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div v-if="stat.badge" class="px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-black tracking-widest uppercase">
                        ↑ {{ stat.badge }}
                    </div>
                </div>
                <h3 class="text-3xl font-black text-white tracking-tighter mb-1">{{ stat.value }}</h3>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">{{ stat.label }}</p>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="flex flex-col lg:flex-row gap-6 mb-10 items-end lg:items-center">
            <div class="relative flex-1 group w-full">
                <svg class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-600 transition-colors group-focus-within:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    v-model="search"
                    @input="handleSearch"
                    type="text"
                    placeholder="Search channels..."
                    class="w-full bg-[#111118]/80 border-white/[0.05] border-2 focus:border-indigo-500/50 focus:ring-0 rounded-2xl pl-12 pr-6 py-3.5 text-sm font-bold text-white placeholder-gray-700 transition-all"
                >
            </div>

            <div class="flex gap-4 w-full lg:w-auto">
                <select v-model="status" @change="updateFilters" class="flex-1 lg:min-w-[160px] bg-[#111118]/80 border-white/[0.05] border-2 focus:border-indigo-500/50 focus:ring-0 rounded-2xl px-5 py-3.5 text-xs font-black uppercase tracking-widest text-gray-400 cursor-pointer transition-all">
                    <option value="all">Status: All</option>
                    <option value="active">Status: Live</option>
                    <option value="paused">Status: Paused</option>
                </select>
                <select v-model="sort" @change="updateFilters" class="flex-1 lg:min-w-[160px] bg-[#111118]/80 border-white/[0.05] border-2 focus:border-indigo-500/50 focus:ring-0 rounded-2xl px-5 py-3.5 text-xs font-black uppercase tracking-widest text-gray-400 cursor-pointer transition-all">
                    <option value="latest">Sort: Latest</option>
                    <option value="score">Sort: Potential</option>
                    <option value="members">Sort: Reach</option>
                    <option value="engagement">Sort: Viral</option>
                </select>
            </div>
        </div>

        <!-- Grid -->
        <div v-if="channels.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <Link
                v-for="ch in channels"
                :key="ch.id"
                :href="`/dashboard/channels/${ch.id}`"
                class="group relative bg-[#111118] border border-white/[0.05] rounded-[2.5rem] p-10 transition-all hover:border-white/10 hover:shadow-2xl hover:shadow-black/50"
            >
                <div class="absolute top-6 right-6 flex items-center gap-2">
                    <span v-if="ch.is_active" class="flex h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-gray-600 group-hover:text-indigo-400/80 transition-colors">
                        {{ user.plan === 'pro' ? 'Premium' : 'Free Tier' }}
                    </span>
                </div>

                <div class="flex items-center gap-5 mb-10">
                    <div class="w-16 h-16 rounded-[1.25rem] bg-gradient-to-br from-white/5 to-white/[0.02] border border-white/5 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                        {{ ch.title[0] }}
                    </div>
                    <div class="min-w-0 pr-10">
                        <h3 class="text-xl font-black text-white truncate tracking-tight mb-1">{{ ch.title }}</h3>
                        <p class="text-xs font-bold text-gray-600 truncate">@{{ ch.username }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-10 h-10">
                    <div class="space-y-1">
                        <p class="text-2xl font-black text-white tracking-tighter">{{ formatNumber(ch.member_count) }}</p>
                        <p class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Network Size</p>
                    </div>
                    
                    <div class="w-24 overflow-visible">
                        <svg width="100" height="30" viewBox="0 0 120 40" class="overflow-visible">
                            <path 
                                :d="getSparklinePath(ch.sparkline)" 
                                fill="none" 
                                stroke="url(#lineGradient)" 
                                stroke-width="4" 
                                stroke-linecap="round" 
                                stroke-linejoin="round"
                                class="drop-shadow-[0_0_8px_rgba(99,102,241,0.5)]"
                            />
                            <defs>
                                <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#6366f1" />
                                    <stop offset="100%" stop-color="#a855f7" />
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-6 border-t border-white/[0.03]">
                    <div class="bg-black/20 rounded-2xl p-4 text-center border border-white/[0.02]">
                        <p class="text-sm font-black text-white">{{ ch.potential_score }}%</p>
                        <p class="text-[9px] font-bold text-gray-700 uppercase tracking-widest mt-1">Growth Score</p>
                    </div>
                    <div class="bg-black/20 rounded-2xl p-4 text-center border border-white/[0.02]">
                        <p class="text-sm font-black text-white">{{ ch.engagement_rate }}%</p>
                        <p class="text-[9px] font-bold text-gray-700 uppercase tracking-widest mt-1">Viral Power</p>
                    </div>
                </div>
            </Link>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-32 bg-[#111118]/40 border-2 border-dashed border-white/[0.05] rounded-[3rem]">
            <div class="w-24 h-24 rounded-[2rem] bg-indigo-500/10 flex items-center justify-center text-4xl mb-8 shadow-2xl shadow-indigo-500/10">✨</div>
            <h2 class="text-3xl font-black text-white mb-3 tracking-tight text-center px-4">Ready to reach millions?</h2>
            <p class="text-gray-500 text-sm text-center max-w-sm mb-12 leading-relaxed px-6">
                Start tracking your first Telegram channel to unlock real-time growth analytics and viral detection.
            </p>
            <a
                :href="addChannelUrl"
                target="_blank"
                class="inline-flex items-center gap-4 px-12 py-5 rounded-2xl text-sm font-black bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-2xl shadow-indigo-600/30 hover:-translate-y-1 transition-all"
            >
                Launch Tracker Bot
            </a>
        </div>

    </DashboardLayout>
</template>
