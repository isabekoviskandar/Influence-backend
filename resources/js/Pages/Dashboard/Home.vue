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

watch([search, status, sort], () => {
    handleSearch();
});

let debounceTimeout;
function handleSearch() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(updateFilters, 300);
}

const addChannelUrl = `https://t.me/${props.bot_username}?start=add_channel`;

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return String(num);
}

// Sparkline SVG path generator — 7-point version for stat cards
function getMiniSparklinePath(data, width = 100, height = 28) {
    if (!data || data.length < 2) return '';
    // Take last 7 points
    const pts = data.slice(-7);
    const min = Math.min(...pts);
    const max = Math.max(...pts);
    const range = max - min || 1;

    const points = pts.map((val, i) => {
        const x = (i / (pts.length - 1)) * width;
        const y = height - ((val - min) / range) * (height - 4) - 2;
        return `${x},${y}`;
    });

    return `M ${points.join(' L ')}`;
}

// Full sparkline for channel cards
function getSparklinePath(data, width = 80, height = 28) {
    if (!data || data.length < 2) return '';
    const min = Math.min(...data);
    const max = Math.max(...data);
    const range = max - min || 1;

    const points = data.map((val, i) => {
        const x = (i / (data.length - 1)) * width;
        const y = height - ((val - min) / range) * (height - 4) - 2;
        return `${x},${y}`;
    });

    return `M ${points.join(' L ')}`;
}

// Viral power color
function viralColor(val) {
    const n = parseFloat(val);
    if (n > 100) return '#22c55e';
    if (n >= 50) return '#f2f2f3';
    return '#4a4a50';
}

// Stat cards config — using actual data
const statsConfig = computed(() => [
    {
        label: 'NETWORK REACH',
        value: formatNumber(props.stats.total_members),
        change: props.stats.total_members_change ? `+${props.stats.total_members_change}%` : null,
        icon: 'users',
    },
    {
        label: 'ACTIVE TRACKING',
        value: String(props.stats.active_channels),
        change: null,
        icon: 'activity',
    },
    {
        label: 'PORTFOLIO SIZE',
        value: String(props.stats.total_channels),
        change: null,
        icon: 'layers',
    },
    {
        label: 'AVG ENGAGEMENT',
        value: props.stats.avg_engagement + '%',
        change: null,
        icon: 'zap',
    },
]);

// Static sparkline data for stat cards (simulated 7-point trend)
const statSparklines = [
    [40, 45, 42, 55, 60, 58, 70],
    [3, 3, 4, 4, 4, 4, 4],
    [2, 3, 3, 3, 4, 4, 4],
    [80, 85, 90, 88, 92, 95, 96],
];
</script>

<template>
    <DashboardLayout>

        <!-- ═══ STAT CARDS ROW ═══ -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5 mb-4">
            <div
                v-for="(stat, i) in statsConfig"
                :key="stat.label"
                class="bg-[#111112] border border-[#222224] rounded-[10px] p-5 transition-all duration-[120ms] hover:border-[#2e2e32]"
            >
                <!-- Top: icon + change badge -->
                <div class="flex items-start justify-between mb-3">
                    <div class="w-8 h-8 rounded-[7px] bg-[#18181a] flex items-center justify-center">
                        <svg v-if="stat.icon === 'users'" class="w-[18px] h-[18px] text-[#8a8a8f]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        </svg>
                        <svg v-if="stat.icon === 'activity'" class="w-[18px] h-[18px] text-[#8a8a8f]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                        </svg>
                        <svg v-if="stat.icon === 'layers'" class="w-[18px] h-[18px] text-[#8a8a8f]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L12 12.75 6.429 9.75m11.142 0l4.179 2.25-9.75 5.25-9.75-5.25 4.179-2.25"/>
                        </svg>
                        <svg v-if="stat.icon === 'zap'" class="w-[18px] h-[18px] text-[#8a8a8f]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                        </svg>
                    </div>
                    <span
                        v-if="stat.change"
                        class="text-[11px] font-semibold px-1.5 py-0.5 rounded-[6px] bg-[rgba(34,197,94,0.10)] text-[#22c55e]"
                        style="font-family: 'Geist Mono', monospace;"
                    >
                        {{ stat.change }}
                    </span>
                </div>

                <!-- Middle: big number + label -->
                <div class="mb-3">
                    <p class="text-[32px] font-bold text-[#f2f2f3] leading-none tracking-tight" style="font-family: 'Geist Mono', monospace;">
                        {{ stat.value }}
                    </p>
                    <p class="text-[11px] font-medium uppercase tracking-[0.06em] text-[#4a4a50] mt-1.5">
                        {{ stat.label }}
                    </p>
                </div>

                <!-- Bottom: micro sparkline -->
                <div class="h-8 w-full">
                    <svg :width="'100%'" height="32" :viewBox="`0 0 100 28`" preserveAspectRatio="none" class="overflow-visible">
                        <path
                            :d="getMiniSparklinePath(statSparklines[i])"
                            fill="none"
                            stroke="#6366f1"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>
            </div>
        </div>

        <!-- ═══ SEARCH + FILTER BAR ═══ -->
        <div class="flex items-center gap-2.5 mt-4 mb-4">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-[14px] h-[14px] text-[#4a4a50]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input
                    v-model="search"
                    @input="handleSearch"
                    type="text"
                    placeholder="Search channels..."
                    class="w-full bg-[#18181a] border border-[#222224] focus:border-[#6366f1] focus:ring-0 focus:outline-none rounded-[7px] h-9 pl-9 pr-4 text-[13px] text-[#f2f2f3] placeholder-[#4a4a50] transition-all duration-[120ms]"
                >
            </div>
            <select
                v-model="status"
                @change="updateFilters"
                class="bg-[#18181a] border border-[#222224] focus:border-[#6366f1] focus:ring-0 focus:outline-none rounded-[7px] h-9 px-3 text-[11px] font-medium uppercase tracking-[0.04em] text-[#8a8a8f] cursor-pointer transition-all duration-[120ms] w-[120px]"
            >
                <option value="all">STATUS: ALL</option>
                <option value="active">STATUS: LIVE</option>
                <option value="paused">STATUS: PAUSED</option>
            </select>
            <select
                v-model="sort"
                @change="updateFilters"
                class="bg-[#18181a] border border-[#222224] focus:border-[#6366f1] focus:ring-0 focus:outline-none rounded-[7px] h-9 px-3 text-[11px] font-medium uppercase tracking-[0.04em] text-[#8a8a8f] cursor-pointer transition-all duration-[120ms] w-[130px]"
            >
                <option value="latest">SORT: LATEST</option>
                <option value="score">SORT: POTENTIAL</option>
                <option value="members">SORT: REACH</option>
                <option value="engagement">SORT: VIRAL</option>
            </select>
        </div>

        <!-- ═══ SECTION HEADER ═══ -->
        <div class="flex items-center justify-between mb-3.5">
            <div class="flex items-center gap-1.5">
                <span class="text-[13px] font-medium text-[#f2f2f3]">Channels</span>
                <span class="text-[13px] text-[#4a4a50]">({{ channels.length }})</span>
            </div>
            <a
                :href="addChannelUrl"
                target="_blank"
                class="inline-flex items-center gap-1.5 h-[30px] px-3 text-[13px] font-medium text-[#6366f1] bg-[rgba(99,102,241,0.12)] border border-[rgba(99,102,241,0.2)] rounded-[7px] hover:bg-[rgba(99,102,241,0.18)] transition-all duration-[120ms]"
            >
                Connect new channel +
            </a>
        </div>

        <!-- ═══ CHANNEL CARDS GRID ═══ -->
        <div v-if="channels.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
            <Link
                v-for="ch in channels"
                :key="ch.id"
                :href="`/dashboard/channels/${ch.id}`"
                class="group bg-[#111112] border border-[#222224] rounded-[10px] overflow-hidden transition-all duration-[120ms] hover:border-[#2e2e32] hover:-translate-y-px relative"
            >
                <!-- Top section: avatar + name + tier -->
                <div class="flex items-center gap-3 p-5 border-b border-[#222224]">
                    <div class="w-10 h-10 rounded-[9px] bg-[#18181a] flex items-center justify-center text-[15px] font-medium text-[#f2f2f3] shrink-0">
                        {{ ch.title[0] }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-[14px] font-semibold text-[#f2f2f3] truncate">{{ ch.title }}</h3>
                            <!-- Low data warning -->
                            <span
                                v-if="ch.member_count < 10"
                                class="text-[9px] font-semibold uppercase px-1.5 py-0.5 rounded-[4px] bg-[rgba(245,158,11,0.10)] text-[#f59e0b] border border-[rgba(245,158,11,0.2)] shrink-0"
                            >
                                ⚠ Low data
                            </span>
                        </div>
                        <p class="text-[12px] text-[#8a8a8f] truncate">@{{ ch.username }}</p>
                    </div>
                    <span class="text-[10px] font-semibold uppercase px-2 py-1 rounded-[6px] bg-[#18181a] text-[#4a4a50] border border-[#222224] shrink-0">
                        {{ user.plan === 'pro' ? 'PRO' : 'FREE TIER' }}
                    </span>
                </div>

                <!-- Metrics section -->
                <div class="p-5 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[28px] font-semibold text-[#f2f2f3] leading-none tracking-tight" style="font-family: 'Geist Mono', monospace;">
                            {{ formatNumber(ch.member_count) }}
                        </p>
                        <p class="text-[10px] font-medium uppercase tracking-[0.06em] text-[#4a4a50] mt-1.5">NETWORK SIZE</p>
                    </div>
                    <div class="w-20 h-7 shrink-0">
                        <svg width="80" height="28" viewBox="0 0 80 28" class="overflow-visible">
                            <path
                                :d="getSparklinePath(ch.sparkline)"
                                fill="none"
                                stroke="#6366f1"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Bottom section: growth + viral -->
                <div class="bg-[#18181a] border-t border-[#222224] px-5 py-4 flex">
                    <!-- Growth Score -->
                    <div class="flex-1 pr-4">
                        <p class="text-[15px] font-semibold text-[#f2f2f3]" style="font-family: 'Geist Mono', monospace;">
                            {{ ch.potential_score }}%
                        </p>
                        <p class="text-[10px] font-medium uppercase tracking-[0.06em] text-[#4a4a50] mt-0.5 mb-2">GROWTH SCORE</p>
                        <div class="w-full h-[3px] bg-[#222224] rounded-full overflow-hidden">
                            <div class="h-full bg-[#6366f1] rounded-full transition-all duration-500" :style="{ width: Math.min(ch.potential_score, 100) + '%' }"></div>
                        </div>
                    </div>

                    <!-- Vertical divider -->
                    <div class="w-px h-8 bg-[#222224] self-center mx-1"></div>

                    <!-- Viral Power -->
                    <div class="flex-1 pl-4">
                        <p class="text-[15px] font-semibold" :style="{ color: viralColor(ch.engagement_rate), fontFamily: '\'Geist Mono\', monospace' }">
                            {{ ch.engagement_rate }}%
                        </p>
                        <p class="text-[10px] font-medium uppercase tracking-[0.06em] text-[#4a4a50] mt-0.5 mb-2">VIRAL POWER</p>
                        <div class="w-full h-[3px] bg-[#222224] rounded-full overflow-hidden">
                            <div class="h-full bg-[#6366f1] rounded-full transition-all duration-500" :style="{ width: Math.min(ch.engagement_rate, 100) + '%' }"></div>
                        </div>
                    </div>
                </div>

                <!-- Footer: live status + sync time -->
                <div class="px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span v-if="ch.is_active" class="w-1.5 h-1.5 rounded-full bg-[#22c55e] animate-pulse-dot"></span>
                        <span v-else class="w-1.5 h-1.5 rounded-full bg-[#ef4444]"></span>
                        <span class="text-[11px] font-medium" :class="ch.is_active ? 'text-[#22c55e]' : 'text-[#ef4444]'">
                            {{ ch.is_active ? 'LIVE' : 'PAUSED' }}
                        </span>
                    </div>
                    <span class="text-[11px] text-[#4a4a50]">
                        {{ ch.last_synced_at ? `synced ${ch.last_synced_at}` : 'never synced' }}
                    </span>
                </div>

                <!-- Sync overlay -->
                <div v-if="ch.sync_status === 'syncing'" class="absolute inset-x-0 bottom-0 bg-[#0a0a0b]/95 p-4 rounded-b-[10px]">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-semibold text-[#6366f1] uppercase tracking-[0.04em] animate-pulse" style="font-family: 'Geist Mono', monospace;">Syncing...</span>
                        <span class="text-[10px] font-medium text-[#4a4a50]" style="font-family: 'Geist Mono', monospace;">{{ Math.round((ch.sync_current / ch.sync_total) * 100) }}%</span>
                    </div>
                    <div class="w-full h-1 bg-[#222224] rounded-full overflow-hidden">
                        <div
                            class="h-full bg-[#6366f1] rounded-full transition-all duration-1000 ease-out"
                            :style="{ width: `${(ch.sync_current / ch.sync_total) * 100}%` }"
                        ></div>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center justify-center py-24 bg-[#111112] border border-[#222224] border-dashed rounded-[10px]">
            <div class="w-16 h-16 rounded-[10px] bg-[rgba(99,102,241,0.12)] flex items-center justify-center text-2xl mb-6">📡</div>
            <h2 class="text-xl font-semibold text-[#f2f2f3] mb-2 tracking-tight">Start tracking your channels</h2>
            <p class="text-[#8a8a8f] text-[13px] text-center max-w-sm mb-8 leading-relaxed">
                Add the Influence bot to your Telegram channel to unlock real-time growth analytics.
            </p>
            <a
                :href="addChannelUrl"
                target="_blank"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-[7px] text-[13px] font-semibold bg-[#6366f1] text-white hover:bg-[#5558e8] transition-all duration-[120ms]"
            >
                Connect Channel
            </a>
        </div>

    </DashboardLayout>
</template>
