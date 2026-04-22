<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    channel: Object,
    insights: Object,
    posts: Array,
    stats_history: Array,
    period: String,
});

function formatNumber(n) {
    if (!n) return '0';
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M';
    if (n >= 1_000) return (n / 1_000).toFixed(1) + 'K';
    return n.toString();
}

function setPeriod(p) {
    router.get(`/dashboard/channels/${props.channel.id}`, { period: p }, { preserveScroll: true });
}

const activeTab = ref('views');

const chartSeries = computed(() => [{
    name: activeTab.value === 'views' ? 'Views' : 'Members',
    data: props.stats_history.map(s => activeTab.value === 'views' ? s.avg_views : s.member_count)
}]);

const chartOptions = computed(() => ({
    chart: {
        type: 'area',
        toolbar: { show: false },
        zoom: { enabled: false },
        animations: { enabled: true, easing: 'easeinout', speed: 400 },
        background: 'transparent',
        fontFamily: 'Geist, sans-serif'
    },
    colors: ['#6366f1'],
    fill: {
        type: 'solid',
        opacity: 0.05
    },
    stroke: { curve: 'smooth', width: 1.5, lineCap: 'round' },
    dataLabels: { enabled: false },
    grid: {
        show: true,
        borderColor: '#1a1a1c',
        strokeDashArray: 0,
        position: 'back',
        xaxis: { lines: { show: false } },
        yaxis: { lines: { show: true } },
        padding: { top: 0, right: 0, bottom: 0, left: 10 }
    },
    xaxis: {
        categories: props.stats_history.map(s => s.date),
        labels: { 
            show: true, 
            style: { colors: '#737373', fontSize: '11px', fontFamily: 'Geist Mono' },
            offsetY: 0
        },
        axisBorder: { show: false },
        axisTicks: { show: false },
        crosshairs: {
            show: true,
            stroke: { color: '#2e2e32', width: 1, dashArray: 4 }
        }
    },
    yaxis: {
        min: 0,
        tickAmount: 4,
        labels: { 
            show: true, 
            style: { colors: '#737373', fontSize: '11px', fontFamily: 'Geist Mono' },
            formatter: (v) => formatNumber(Math.round(v))
        }
    },
    tooltip: {
        theme: 'dark',
        custom: function({ series, seriesIndex, dataPointIndex, w }) {
            const val = series[seriesIndex][dataPointIndex];
            const date = w.globals.categoryLabels[dataPointIndex];
            return `
                <div class="bg-[#111112] border border-[#222224] rounded-lg p-3 shadow-xl">
                    <div class="text-[10px] uppercase text-[#737373] mb-1 font-mono">${date}</div>
                    <div class="text-white text-sm font-mono font-bold">${formatNumber(val)} ${activeTab.value}</div>
                </div>
            `;
        }
    },
    markers: {
        size: 0,
        strokeColors: '#6366f1',
        strokeWidth: 2,
        fillColors: '#6366f1',
        hover: { size: 4, sizeOffset: 3 }
    }
}));

const headerStats = computed(() => [
    { label: 'Subscribers', value: formatNumber(props.channel.member_count) },
    { 
        label: 'Avg Views', 
        value: formatNumber(props.channel.avg_views_recent || props.channel.avg_views),
        subValue: props.channel.is_views_diff ? `= ${formatNumber(props.channel.avg_views)} lifetime avg` : null
    },
    { label: 'Engagement', value: props.channel.engagement_rate + '%' },
    { 
        label: 'Network Score', 
        value: props.channel.potential_score + '%',
        progress: props.channel.potential_score 
    },
    { label: 'Status', value: 'Active', green: true },
]);
</script>

<template>
    <DashboardLayout>
        <div class="max-w-[1200px] mx-auto px-4 lg:px-6 pb-20">
            <!-- TOPBAR BREADCRUMBS -->
            <nav class="flex items-center gap-2 mb-8 mt-2">
                <Link href="/dashboard" class="text-[13px] text-[#737373] hover:text-[#f2f2f3] transition-colors">influence.uz</Link>
                <span class="text-[#737373] text-[10px]">/</span>
                <Link href="/dashboard" class="text-[13px] text-[#737373] hover:text-[#f2f2f3] transition-colors">channels</Link>
                <span class="text-[#737373] text-[10px]">/</span>
                <span class="text-[13px] text-[#f2f2f3] font-medium">{{ channel.username || 'detail' }}</span>
            </nav>

            <!-- BACK NAVIGATION -->
            <Link href="/dashboard" class="group flex items-center gap-1.5 text-[13px] text-[#737373] hover:text-[#f2f2f3] transition-all duration-[120ms] mb-5">
                <span class="group-hover:-translate-x-0.5 transition-transform">←</span>
                Channels
            </Link>

            <!-- HERO STRIP -->
            <div class="w-full bg-[#111112] border border-[#222224] rounded-[10px] p-5 lg:px-6 flex flex-col md:flex-row items-center gap-0 mb-4">
                <!-- Identity Section -->
                <div class="w-full md:w-[300px] flex items-center gap-4 mb-5 md:mb-0 md:pr-6">
                    <div class="w-[52px] h-[52px] bg-[#18181a] rounded-[10px] flex items-center justify-center text-xl font-semibold text-white border border-[#222224] shrink-0">
                        {{ channel.title?.[0]?.toUpperCase() ?? '#' }}
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-[20px] font-bold text-white leading-tight truncate">{{ channel.title }}</h1>
                        <p class="text-[13px] text-[#737373] truncate">@{{ channel.username || 'private' }}</p>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-[#18181a] border border-[#222224] rounded-[6px] text-[11px] text-[#f2f2f3] font-medium">
                                {{ channel.category || 'Uncategorized' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats Divider Column -->
                <div class="hidden md:block w-px h-[60px] bg-[#222224]"></div>

                <!-- Stats Strip -->
                <div class="flex-1 w-full grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 px-0 md:px-6 gap-y-4 md:gap-y-0">
                    <div v-for="(stat, idx) in headerStats" :key="stat.label" class="flex flex-col items-center justify-center relative">
                        <div class="text-[22px] font-mono font-bold leading-none" :class="stat.green ? 'text-[#22c55e]' : 'text-white'">
                            {{ stat.value }}
                        </div>
                        <div class="text-[10px] font-mono text-[#737373] uppercase tracking-wider mt-1.5">
                            {{ stat.label }}
                        </div>
                        <div v-if="stat.subValue" class="text-[10px] text-[#737373] mt-0.5">
                            {{ stat.subValue }}
                        </div>
                        <div v-if="stat.progress !== undefined" class="mt-2 w-[40px] h-[3px] bg-[#222224] rounded-full overflow-hidden">
                            <div class="h-full bg-[#6366f1]" :style="{ width: stat.progress + '%' }"></div>
                        </div>
                        
                        <!-- Divider -->
                        <div v-if="idx < headerStats.length - 1" class="hidden lg:block absolute right-[-1px] top-1/2 -translate-y-1/2 w-px h-8 bg-[#222224]"></div>
                    </div>
                </div>
            </div>

            <!-- MAIN CONTENT (V-STACK) -->
            <div class="w-full space-y-[14px]">
                
                <!-- CHART CARD -->
                <div class="bg-[#111112] border border-[#222224] rounded-[10px] overflow-hidden">
                    <div class="p-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-[14px] font-semibold text-white">Channel Performance</h2>
                            <p class="text-[12px] text-[#737373] mt-0.5">Growth metrics over time</p>
                        </div>
                        <div class="flex items-center gap-[10px]">
                            <!-- Metric Toggle -->
                            <div class="flex p-[3px] bg-[#0a0a0b] border border-[#222224] rounded-[8px] h-[30px]">
                                <button @click="activeTab = 'views'" 
                                    class="px-3 rounded-[6px] text-[11px] font-medium transition-all"
                                    :class="activeTab === 'views' ? 'bg-[#18181a] text-white border border-[#222224]' : 'text-[#737373] hover:text-[#f2f2f3]'">
                                    Views
                                </button>
                                <button @click="activeTab = 'members'" 
                                    class="px-3 rounded-[6px] text-[11px] font-medium transition-all"
                                    :class="activeTab === 'members' ? 'bg-[#18181a] text-white border border-[#222224]' : 'text-[#737373] hover:text-[#f2f2f3]'">
                                    Members
                                </button>
                            </div>
                            <!-- Period Toggle -->
                            <div class="flex p-[3px] bg-[#0a0a0b] border border-[#222224] rounded-[8px] h-[30px]">
                                <button v-for="p in ['7d', '30d', 'all']" :key="p" @click="setPeriod(p)"
                                    class="px-2.5 rounded-[6px] text-[11px] font-medium uppercase transition-all"
                                    :class="period === p ? 'bg-[#18181a] text-white border border-[#222224]' : 'text-[#737373] hover:text-[#f2f2f3]'">
                                    {{ p }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 pb-5 h-[230px]">
                        <VueApexCharts width="100%" height="220" :options="chartOptions" :series="chartSeries" />
                    </div>

                    <!-- Insight Bar -->
                    <div class="w-full bg-[#18181a] border-t border-[#222224] px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="text-[12px] font-mono text-[#737373]">Peak: {{ formatNumber(Math.max(...stats_history.map(s => s.avg_views))) }} views</span>
                            <span class="w-px h-3 bg-[#222224]"></span>
                            <span class="text-[12px] font-mono text-[#737373]">Avg daily: {{ formatNumber(channel.avg_views) }}</span>
                            <span class="w-px h-3 bg-[#222224]"></span>
                            <span class="text-[12px] font-mono text-[#737373] text-indigo-400">Trend: Stable</span>
                        </div>
                    </div>
                </div>

                <!-- INTELLIGENCE STRIP (REPOSITIONED FROM SIDEBAR) -->
                <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-5">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-[13px] font-semibold text-white">Channel Intelligence</h3>
                        <Link href="/dashboard/upgrade" class="text-[11px] text-[#737373] hover:text-indigo-400 transition-colors uppercase tracking-widest font-mono">
                            PRO UNLOCK →
                        </Link>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="(val, key) in { 'Best time': insights.best_time, 'Top content': insights.top_post_val, 'Consistency': insights.consistency, 'Reach rate': insights.read_rate }" :key="key" 
                            class="bg-[#18181a] border border-[#222224] rounded-lg p-4">
                            <p class="text-[11px] text-[#737373] uppercase tracking-wider mb-2 font-mono">{{ key }}</p>
                            <p class="text-[14px] text-white font-bold">{{ val }}</p>
                        </div>
                    </div>
                </div>

                <!-- RECENT POSTS SECTION -->
                <div class="bg-[#111112] border border-[#222224] rounded-[10px] overflow-hidden">
                    <div class="px-5 py-4 border-b border-[#222224] flex items-center justify-between">
                        <h3 class="text-[13px] font-semibold text-white">Recent Posts</h3>
                        <span class="text-[12px] text-[#737373] font-mono">{{ channel.total_posts_count }} tracked</span>
                    </div>

                    <div class="divide-y divide-[#222224]">
                        <div v-for="post in posts" :key="post.id" class="px-5 py-3.5 hover:bg-[#18181a] transition-colors flex items-center justify-between gap-6">
                            <div class="flex-1 flex items-center gap-3 min-w-0">
                                <div :class="[
                                    'w-[1.5px] h-6 shrink-0',
                                    post.media_type && post.media_type !== 'text' ? 'bg-[#6366f1]' : 'bg-[#404040]'
                                ]"></div>
                                <p class="text-[13px] text-white truncate max-w-[calc(100%-40px)] leading-tight">
                                    {{ post.text }}
                                </p>
                            </div>
                            <div class="flex items-center gap-6 shrink-0">
                                <div class="flex flex-col items-end">
                                    <div class="flex items-center gap-1.5 text-[13px] font-mono text-white">
                                        <span class="text-[#737373] text-[11px]">👁</span>
                                        {{ formatNumber(post.views) }}
                                    </div>
                                    <div class="mt-1 w-[48px] h-[3px] bg-[#222224] rounded-full overflow-hidden">
                                        <div :class="['h-full rounded-full', post.is_above_avg ? 'bg-[#6366f1]' : 'bg-[#404040]']" 
                                            :style="{ width: post.ratio + '%' }"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 text-[13px] font-mono text-white min-w-[45px]">
                                    <span class="text-[#737373] text-[11px]">♥</span>
                                    {{ formatNumber(post.reactions) }}
                                </div>
                                <div class="text-[12px] text-[#737373] font-mono min-w-[60px] text-right">
                                    {{ post.posted_at_ago }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <Link href="#" class="block w-full text-center py-3 text-[13px] text-indigo-400 font-medium hover:text-indigo-300 transition-colors border-t border-[#222224]">
                        View all {{ channel.total_posts_count }} posts →
                    </Link>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style>
/* Forcing ApexCharts to be monochromatic and fix labels */
.apexcharts-canvas {
    font-family: 'Geist Mono', monospace !important;
}
.apexcharts-tooltip {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
}
.apexcharts-xaxistooltip {
    display: none !important;
}
.apexcharts-gridline {
    stroke: #1a1a1c !important;
}
</style>
