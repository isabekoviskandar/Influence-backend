<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    stats_history: Array,
    summary: Object,
    period: String,
    best_timings: Array,
    user_plan: String,
    max_stats_days: Number,
});

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return String(num ?? 0);
}

function setPeriod(p) {
    router.get('/dashboard/analytics', { period: p }, { preserveState: true, replace: true });
}

// Build SVG chart path from stats_history
function getChartPath(key, width = 600, height = 120) {
    const data = props.stats_history.map(s => s[key] ?? 0);
    if (data.length < 2) return '';
    const min = Math.min(...data);
    const max = Math.max(...data);
    const range = max - min || 1;
    const points = data.map((val, i) => {
        const x = (i / (data.length - 1)) * width;
        const y = height - ((val - min) / range) * (height - 8) - 4;
        return `${x},${y}`;
    });
    return `M ${points.join(' L ')}`;
}

// Build area fill path
function getAreaPath(key, width = 600, height = 120) {
    const line = getChartPath(key, width, height);
    if (!line) return '';
    return `${line} L ${width},${height} L 0,${height} Z`;
}

const summaryCards = computed(() => [
    { label: 'TOTAL MEMBERS', value: formatNumber(props.summary.total_members), icon: 'users' },
    { label: 'TOTAL VIEWS', value: formatNumber(props.summary.total_views), icon: 'eye' },
    { label: 'AVG ENGAGEMENT', value: props.summary.avg_engagement + '%', icon: 'zap' },
    { label: 'TOTAL POSTS', value: String(props.summary.total_posts), icon: 'doc' },
]);

const periods = [
    { label: '7D', value: '7d', days: 7 },
    { label: '30D', value: '30d', days: 30 },
    { label: '90D', value: '90d', days: 90 },
];
</script>

<template>
    <DashboardLayout>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-[20px] font-semibold text-[#f2f2f3] tracking-tight">Analytics</h1>
                <p class="text-[13px] text-[#4a4a50] mt-0.5">Aggregated performance across all channels</p>
            </div>
            <!-- Period toggle -->
            <div class="flex items-center gap-1 p-1 bg-[#18181a] border border-[#222224] rounded-[7px]">
                <button
                    v-for="p in periods"
                    :key="p.value"
                    @click="setPeriod(p.value)"
                    :disabled="p.days > (max_stats_days || 7)"
                    class="px-3 py-1 rounded-[5px] text-[11px] font-semibold uppercase tracking-[0.04em] transition-all duration-[120ms] disabled:opacity-30 disabled:cursor-not-allowed"
                    :class="period === p.value ? 'bg-[#6366f1] text-white' : 'text-[#4a4a50] hover:text-[#8a8a8f]'"
                    style="font-family: 'Geist Mono', monospace;"
                    :title="p.days > max_stats_days ? 'Upgrade your plan to view more history' : ''"
                >
                    {{ p.label }}
                </button>
            </div>
        </div>

        <!-- Summary cards -->
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-3.5 mb-6">
            <div v-for="card in summaryCards" :key="card.label" class="bg-[#111112] border border-[#222224] rounded-[10px] p-5">
                <p class="text-[10px] font-medium uppercase tracking-[0.06em] text-[#4a4a50] mb-2">{{ card.label }}</p>
                <p class="text-[28px] font-bold text-[#f2f2f3] leading-none tracking-tight" style="font-family: 'Geist Mono', monospace;">
                    {{ card.value }}
                </p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">
            <!-- Members chart -->
            <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-5">
                <p class="text-[11px] font-medium uppercase tracking-[0.06em] text-[#4a4a50] mb-4">MEMBER GROWTH</p>
                <div class="h-[140px] w-full" v-if="stats_history.length > 1">
                    <svg width="100%" height="140" :viewBox="`0 0 600 120`" preserveAspectRatio="none" class="overflow-visible">
                        <path :d="getAreaPath('members')" fill="rgba(99,102,241,0.08)" />
                        <path :d="getChartPath('members')" fill="none" stroke="#6366f1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div v-else class="h-[140px] flex items-center justify-center">
                    <p class="text-[12px] text-[#4a4a50]">Not enough data yet</p>
                </div>
            </div>

            <!-- Views chart -->
            <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-5">
                <p class="text-[11px] font-medium uppercase tracking-[0.06em] text-[#4a4a50] mb-4">TOTAL VIEWS</p>
                <div class="h-[140px] w-full" v-if="stats_history.length > 1">
                    <svg width="100%" height="140" :viewBox="`0 0 600 120`" preserveAspectRatio="none" class="overflow-visible">
                        <path :d="getAreaPath('views')" fill="rgba(34,197,94,0.06)" />
                        <path :d="getChartPath('views')" fill="none" stroke="#22c55e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div v-else class="h-[140px] flex items-center justify-center">
                    <p class="text-[12px] text-[#4a4a50]">Not enough data yet</p>
                </div>
            </div>

            <!-- Engagement chart -->
            <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-5 lg:col-span-2">
                <p class="text-[11px] font-medium uppercase tracking-[0.06em] text-[#4a4a50] mb-4">ENGAGEMENT RATE TREND</p>
                <div class="h-[140px] w-full" v-if="stats_history.length > 1">
                    <svg width="100%" height="140" :viewBox="`0 0 600 120`" preserveAspectRatio="none" class="overflow-visible">
                        <path :d="getAreaPath('engagement')" fill="rgba(245,158,11,0.06)" />
                        <path :d="getChartPath('engagement')" fill="none" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div v-else class="h-[140px] flex items-center justify-center">
                    <p class="text-[12px] text-[#4a4a50]">Not enough data yet</p>
                </div>
            </div>

            <!-- Best Timings widget -->
            <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-5 lg:col-span-2 mt-4">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-[11px] font-medium uppercase tracking-[0.06em] text-[#4a4a50]">BEST TIME TO POST</p>
                    <span v-if="user_plan === 'free'" class="px-2 py-0.5 rounded-full bg-[#18181b] border border-[#27272a] text-[9px] font-medium text-[#a1a1aa] tracking-[0.04em]">
                        PRO/PREMIUM FEATURE
                    </span>
                </div>
                
                <div v-if="best_timings && best_timings.length > 0" class="flex flex-col gap-[2px]">
                    <div v-for="(time, idx) in best_timings" :key="time.hour" 
                         class="group flex items-center justify-between py-2.5 px-3 rounded-[8px] hover:bg-[#18181a] transition-all duration-200 border border-transparent hover:border-[#222224]">
                        <div class="flex items-center gap-4">
                            <div class="w-8 flex justify-center">
                                <span v-if="idx === 0" class="text-[16px]">🔥</span>
                                <span v-else-if="idx === 1" class="text-[16px]">⭐</span>
                                <span v-else class="text-[13px] font-bold text-[#4a4a50] opacity-50" style="font-family: 'Geist Mono', monospace;">{{ idx + 1 }}</span>
                            </div>
                            <div class="bg-[#6366f1]/10 text-[#6366f1] text-[13px] font-bold py-1 px-3 rounded-[6px]" style="font-family: 'Geist Mono', monospace;">
                                {{ time.hour }}
                            </div>
                            <span class="text-[12px] text-[#8a8a8f] group-hover:text-[#a0a0ab] transition-colors">Based on {{ time.total_posts }} posts</span>
                        </div>
                        <div class="text-right">
                            <p class="text-[15px] text-[#f2f2f3] font-bold leading-none mb-1.5 flex items-center justify-end gap-1" style="font-family: 'Geist Mono', monospace;">
                                {{ formatNumber(time.avg_views) }}
                                <svg class="w-3 h-3 text-[#22c55e]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            </p>
                            <p class="text-[9px] text-[#4a4a50] uppercase tracking-[0.06em] font-medium">AVG VIEWS</p>
                        </div>
                    </div>
                </div>
                <div v-else class="h-[100px] flex flex-col items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-[#4a4a50]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-[12px] text-[#4a4a50]">Not enough active posts to analyze.</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
