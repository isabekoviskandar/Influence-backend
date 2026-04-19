<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    stats_history: Array,
    summary: Object,
    period: String,
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
    { label: '7D', value: '7d' },
    { label: '30D', value: '30d' },
    { label: '90D', value: '90d' },
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
                    class="px-3 py-1 rounded-[5px] text-[11px] font-semibold uppercase tracking-[0.04em] transition-all duration-[120ms]"
                    :class="period === p.value ? 'bg-[#6366f1] text-white' : 'text-[#4a4a50] hover:text-[#8a8a8f]'"
                    style="font-family: 'Geist Mono', monospace;"
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
        </div>
    </DashboardLayout>
</template>
