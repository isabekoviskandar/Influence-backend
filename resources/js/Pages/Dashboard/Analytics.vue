<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    stats_history: Array,
    summary: Object,
    heatmap: Array,
    peak_chips: Array,
    insights: Object,
    period: String,
    user_plan: String,
    max_stats_days: Number,
    channels: Array,
    selected_channel: [String, Number],
});

function formatNumber(num) {
    if (num == null) return '0';
    return Number(num).toLocaleString('en-US'); // Comma separated as requested
}

function setFilter(p, c) {
    router.get('/dashboard/analytics', { period: p, channel_id: c }, { preserveState: true, replace: true, preserveScroll: true });
}

// Chart generators unified to strictly indigo, 1.5px stroke, and subtle surface fills
function getChartOptions(customOpts = {}) {
    return {
        chart: {
            type: 'area',
            toolbar: { show: false },
            zoom: { enabled: false },
            animations: { enabled: true, easing: 'easeinout', speed: 400 },
            background: 'transparent',
            parentHeightOffset: 0,
            ...customOpts.chart
        },
        colors: ['#6366f1'],
        fill: customOpts.fill || {
            type: 'solid',
            opacity: 0.06
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 1.5, ...customOpts.stroke },
        grid: {
            borderColor: '#1a1a1c',
            strokeDashArray: 0,
            position: 'back',
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } },
            padding: { left: 15, right: 15, top: 10, bottom: 0 },
            ...customOpts.grid
        },
        xaxis: {
            categories: props.stats_history.map(s => s.date ?? ''),
            labels: { style: { colors: '#71717a', fontSize: '11px', fontFamily: 'Geist Mono, monospace' } },
            axisBorder: { show: false },
            axisTicks: { show: false },
            tooltip: { enabled: false },
            ...customOpts.xaxis
        },
        yaxis: {
            labels: { 
                style: { colors: '#71717a', fontSize: '11px', fontFamily: 'Geist Mono, monospace' },
                formatter: (v) => formatNumber(Math.round(v))
            },
            ...customOpts.yaxis
        },
        tooltip: {
            theme: 'dark',
            style: { fontSize: '11px', fontFamily: 'Geist Mono, monospace' },
            marker: { show: true },
            x: { show: true },
            y: {
                formatter: (v) => formatNumber(Math.round(v)),
                title: { formatter: (sName) => sName + ':' }
            },
            ...customOpts.tooltip
        },
        annotations: customOpts.annotations || {}
    };
}

const membersOpts = computed(() => getChartOptions());
const membersSeries = computed(() => [{ name: 'Members', data: props.stats_history.map(s => s.members) }]);

const viewsOpts = computed(() => getChartOptions());
const viewsSeries = computed(() => [{ name: 'Views', data: props.stats_history.map(s => s.views) }]);

const engagementOpts = computed(() => {
    let avg = Math.round(props.summary.avg_engagement);
    if (avg > 1000) avg = 1000;
    return getChartOptions({
        yaxis: {
            labels: { 
                style: { colors: '#71717a', fontSize: '11px', fontFamily: 'Geist Mono, monospace' },
                formatter: (v) => Math.round(v) + '%'
            }
        },
        tooltip: {
            y: { formatter: (v) => Math.round(v) + '%' }
        },
        annotations: {
            yaxis: [{
                y: avg,
                borderColor: '#4a4a50',
                label: {
                    borderColor: 'transparent',
                    style: { color: '#a1a1aa', background: 'transparent', fontSize: '10px', fontFamily: 'Geist Mono, monospace' },
                    text: `avg: ${formatNumber(avg)}%`
                },
                strokeDashArray: 4
            }]
        }
    });
});
const engagementSeries = computed(() => [{ name: 'Engagement Rate', data: props.stats_history.map(s => s.engagement > 1000 ? 1000 : s.engagement) }]);

const postsOpts = computed(() => getChartOptions({
    chart: { type: 'bar' },
    stroke: { show: false },
    fill: { type: 'solid', opacity: 1 },
    plotOptions: {
        bar: { borderRadius: 3, columnWidth: '4px' }
    },
    grid: {
        yaxis: { lines: { show: false } }
    }
}));
const postsSeries = computed(() => [{ name: 'Posts Published', data: props.stats_history.map(s => s.posts_count) }]);

// Sparklines config
function getSparklineOpts() {
    return {
        chart: { type: 'line', sparkline: { enabled: true }, animations: { enabled: false } },
        stroke: { curve: 'smooth', width: 1.5 },
        colors: ['#6366f1'],
        tooltip: { fixed: { enabled: false }, x: { show: false }, y: { title: { formatter: () => '' } }, marker: { show: false } }
    };
}

const sparklines = computed(() => {
    const data = props.stats_history.slice(-7);
    return {
        members: [{ data: data.map(s => s.members) }],
        views: [{ data: data.map(s => s.views) }],
        engagement: [{ data: data.map(s => s.engagement) }],
        posts: [{ data: data.map(s => s.posts_count) }]
    };
});

function getChangeBadgeClass(change) {
    if (change > 0) return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
    if (change < 0) return 'bg-red-500/10 text-red-500 border-red-500/20';
    return 'bg-gray-500/10 text-[#71717a] border-gray-500/20';
}
function getChangeText(change) {
    if (change > 0) return `+${change}%`;
    if (change < 0) return `${change}%`;
    return '0%';
}

function getHeatmapColor(index) {
    switch(index) {
        case 0: return '#1a1a1c';
        case 1: return 'rgba(99,102,241,0.15)';
        case 2: return 'rgba(99,102,241,0.45)';
        case 3: return 'rgba(99,102,241,0.75)';
        case 4: return '#6366f1';
        default: return '#1a1a1c';
    }
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-[1400px] pb-10">
            <!-- Header Row -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-[20px] font-[600] text-white tracking-tight" style="font-family: 'Geist', sans-serif;">Analytics</h1>
                    <p class="text-[13px] text-[#71717a] mt-0.5" style="font-family: 'Geist', sans-serif;">
                        Aggregated across {{ selected_channel === 'all' ? 'all channels' : 'selected channel' }} · Last {{ period.replace('d', '') }} days
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <select :value="selected_channel" @change="e => setFilter(period, e.target.value)" 
                            class="bg-[#111112] border border-[#222224] text-white text-[13px] rounded-[7px] h-[36px] px-3 w-[160px] outline-none">
                        <option v-for="c in channels" :key="c.id" :value="c.id">{{ c.title }}</option>
                    </select>

                    <div class="flex items-center p-[2px] bg-[#111112] border border-[#222224] rounded-[7px] h-[36px]">
                        <button v-for="p in ['7d', '30d', '90d']" :key="p" @click="setFilter(p, selected_channel)"
                            :disabled="p === '90d' && max_stats_days < 90 || p === '30d' && max_stats_days < 30"
                            class="px-4 py-1 h-full rounded-[5px] text-[12px] font-medium transition-all"
                            :class="period === p ? 'bg-[#222224] text-white shadow-sm border border-[#333336]' : 'text-[#71717a] hover:text-[#a1a1aa] border border-transparent disabled:opacity-30'">
                            {{ p.toUpperCase() }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stat Cards Row -->
            <div class="grid grid-cols-4 gap-[14px] mb-8">
                <!-- Members -->
                <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-4 flex flex-col justify-between h-[130px]">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[12px] text-[#71717a] font-medium tracking-tight" style="font-family: 'Geist', sans-serif;">Total Members</p>
                            <p class="text-[24px] font-[600] text-white mt-1" style="font-family: 'Geist Mono', monospace;">{{ formatNumber(summary.total_members) }}</p>
                        </div>
                        <div class="w-[60px] h-[30px] opacity-60">
                            <VueApexCharts type="line" height="30" :options="getSparklineOpts()" :series="sparklines.members" />
                        </div>
                    </div>
                    <div class="inline-flex items-center px-2 py-0.5 rounded-[5px] border text-[10px] font-medium w-max tracking-wide" 
                         :class="getChangeBadgeClass(summary.members_change)" 
                         style="font-family: 'Geist Mono', monospace;">
                        {{ getChangeText(summary.members_change) }} vs last period
                    </div>
                </div>

                <!-- Views -->
                <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-4 flex flex-col justify-between h-[130px]">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[12px] text-[#71717a] font-medium tracking-tight" style="font-family: 'Geist', sans-serif;">Total Views</p>
                            <p class="text-[24px] font-[600] text-white mt-1" style="font-family: 'Geist Mono', monospace;">{{ formatNumber(summary.total_views) }}</p>
                        </div>
                        <div class="w-[60px] h-[30px] opacity-60">
                            <VueApexCharts type="line" height="30" :options="getSparklineOpts()" :series="sparklines.views" />
                        </div>
                    </div>
                    <div class="inline-flex items-center px-2 py-0.5 rounded-[5px] border text-[10px] font-medium w-max tracking-wide" 
                         :class="getChangeBadgeClass(summary.views_change)" 
                         style="font-family: 'Geist Mono', monospace;">
                        {{ getChangeText(summary.views_change) }} vs last period
                    </div>
                </div>

                <!-- Engagement -->
                <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-4 flex flex-col justify-between h-[130px]">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[12px] text-[#71717a] font-medium tracking-tight" style="font-family: 'Geist', sans-serif;">Avg Engagement</p>
                            <p class="text-[24px] font-[600] text-white mt-1" style="font-family: 'Geist Mono', monospace;">{{ formatNumber(summary.avg_engagement > 1000 ? 1000 : summary.avg_engagement) }}%</p>
                        </div>
                        <div class="w-[60px] h-[30px] opacity-60">
                            <VueApexCharts type="line" height="30" :options="getSparklineOpts()" :series="sparklines.engagement" />
                        </div>
                    </div>
                    <div class="inline-flex items-center px-2 py-0.5 rounded-[5px] border text-[10px] font-medium w-max tracking-wide" 
                         :class="getChangeBadgeClass(summary.engagement_change)" 
                         style="font-family: 'Geist Mono', monospace;">
                        {{ getChangeText(summary.engagement_change) }} vs last period
                    </div>
                </div>

                <!-- Posts -->
                <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-4 flex flex-col justify-between h-[130px]">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[12px] text-[#71717a] font-medium tracking-tight" style="font-family: 'Geist', sans-serif;">Total Posts</p>
                            <p class="text-[24px] font-[600] text-white mt-1" style="font-family: 'Geist Mono', monospace;">{{ formatNumber(summary.total_posts) }}</p>
                        </div>
                        <div class="w-[60px] h-[30px] opacity-60">
                            <VueApexCharts type="line" height="30" :options="getSparklineOpts()" :series="sparklines.posts" />
                        </div>
                    </div>
                    <div class="inline-flex items-center px-2 py-0.5 rounded-[5px] border text-[10px] font-medium w-max tracking-wide" 
                         :class="getChangeBadgeClass(summary.posts_change)" 
                         style="font-family: 'Geist Mono', monospace;">
                        {{ getChangeText(summary.posts_change) }} vs last period
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="space-y-[14px]">
                
                <!-- Chart 1: Member Growth -->
                <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-5">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-[13px] font-[500] uppercase text-[#71717a] tracking-wider" style="font-family: 'Geist', sans-serif;">Member Growth</p>
                        <p class="text-[14px] text-white font-[600]" style="font-family: 'Geist Mono', monospace;">{{ formatNumber(summary.total_members) }} members</p>
                    </div>
                    <div class="h-[220px] w-full -ml-[4px]" v-if="stats_history.length > 1">
                        <VueApexCharts width="100%" height="220" :options="membersOpts" :series="membersSeries" />
                    </div>
                    <div v-else class="h-[220px] flex items-center justify-center">
                        <p class="text-[12px] text-[#71717a]">Not enough data</p>
                    </div>
                </div>

                <!-- Row 2: Views & Engagement -->
                <div class="grid grid-cols-2 gap-[14px]">
                    <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-5">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-[13px] font-[500] uppercase text-[#71717a] tracking-wider" style="font-family: 'Geist', sans-serif;">Total Views</p>
                        </div>
                        <div class="h-[200px] w-full -ml-[4px]" v-if="stats_history.length > 1">
                            <VueApexCharts width="100%" height="200" :options="viewsOpts" :series="viewsSeries" />
                        </div>
                        <div v-else class="h-[200px] flex items-center justify-center">
                            <p class="text-[12px] text-[#71717a]">Not enough data</p>
                        </div>
                    </div>
                    
                    <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-5">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-[13px] font-[500] uppercase text-[#71717a] tracking-wider" style="font-family: 'Geist', sans-serif;">Avg Engagement Rate</p>
                        </div>
                        <div class="h-[200px] w-full -ml-[4px]" v-if="stats_history.length > 1">
                            <VueApexCharts width="100%" height="200" :options="engagementOpts" :series="engagementSeries" />
                        </div>
                        <div v-else class="h-[200px] flex items-center justify-center">
                            <p class="text-[12px] text-[#71717a]">Not enough data</p>
                        </div>
                    </div>
                </div>

                <!-- Chart 3: Post Frequency -->
                <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-5">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-[13px] font-[500] uppercase text-[#71717a] tracking-wider" style="font-family: 'Geist', sans-serif;">Posting Activity</p>
                    </div>
                    <div class="h-[120px] w-full -ml-[4px]" v-if="stats_history.length > 1">
                        <VueApexCharts width="100%" height="120" :options="postsOpts" :series="postsSeries" />
                    </div>
                    <div v-else class="h-[120px] flex items-center justify-center">
                        <p class="text-[12px] text-[#71717a]">Not enough data</p>
                    </div>
                </div>
            </div>

            <!-- Best Time to Post Heatmap -->
            <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-6 mt-[14px]">
                <div class="mb-6">
                    <p class="text-[11px] font-[500] uppercase tracking-wider text-[#71717a]">Best Time To Post</p>
                    <p class="text-[13px] text-[#a1a1aa] mt-1">Based on your historical post performance · {{ summary.total_posts }} posts analyzed</p>
                </div>
                
                <div class="flex mb-6">
                    <!-- Y Axis Labels -->
                    <div class="w-[40px] flex flex-col justify-between pr-3 pt-1 pb-4" style="height: 226px;">
                        <span v-for="dayData in heatmap" :key="dayData[0].day" class="text-[11px] text-[#71717a] text-right" style="font-family: 'Geist Mono', monospace;">
                            {{ dayData[0].day }}
                        </span>
                    </div>

                    <!-- Grid -->
                    <div class="flex-1">
                        <div class="flex flex-col gap-[2px]">
                            <!-- Rows (Days) -->
                            <div v-for="dayData in heatmap" :key="dayData[0].day" class="flex gap-[2px]">
                                <!-- Cells (Hours) -->
                                <div v-for="cell in dayData" :key="cell.hour_formatted" 
                                     class="flex-1 h-[28px] rounded-[3px] group relative cursor-pointer"
                                     :style="{ backgroundColor: getHeatmapColor(cell.heat_index) }">
                                    
                                     <!-- Heatmap Tooltip -->
                                     <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-max bg-[#111112] border border-[#222224] rounded-[8px] px-3 py-2 opacity-0 group-hover:opacity-100 transition-opacity z-10 pointer-events-none shadow-xl">
                                         <p class="text-[11px] text-white" style="font-family: 'Geist Mono', monospace;">
                                             {{ cell.day }} {{ cell.hour_formatted }} — avg <span class="text-[#6366f1]">{{ formatNumber(cell.avg_views) }}</span> views across {{ cell.posts }} posts
                                         </p>
                                     </div>
                                </div>
                            </div>
                        </div>

                        <!-- X Axis Labels -->
                        <div class="flex w-full mt-2">
                            <div v-for="h in 24" :key="h" class="flex-1 flex justify-center">
                                <span v-if="(h-1) % 3 === 0" class="text-[11px] text-[#71717a] -translate-x-1/2" style="font-family: 'Geist Mono', monospace;">
                                    {{ h-1 }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Peak Chips -->
                <div class="flex items-center gap-3" v-if="peak_chips.length > 0">
                    <div v-for="(chip, idx) in peak_chips" :key="idx" 
                         class="bg-[#6366f1]/10 border border-[#6366f1]/30 rounded-[7px] px-4 py-2 flex items-center gap-2">
                        <span class="text-[11px] text-[#a5b4fc] font-bold">
                            {{ idx === 0 ? '①' : idx === 1 ? '②' : '③' }}
                        </span>
                        <span class="text-[13px] text-white" style="font-family: 'Geist Mono', monospace;">
                            {{ chip.hour }} <span class="text-[#71717a] mx-1">·</span> {{ formatNumber(chip.avg_views) }} avg views
                        </span>
                    </div>
                </div>
                <div v-else class="text-[12px] text-[#71717a]">
                    Not enough posts to determine peaks.
                </div>
            </div>

            <!-- Insight Strip -->
            <div class="grid grid-cols-4 gap-[14px] mt-[14px]">
                <div class="bg-[#111112] border border-[#222224] rounded-[8px] p-[14px] flex items-center gap-3">
                    <svg class="w-4 h-4 text-[#6366f1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <div>
                        <p class="text-[10px] text-[#71717a] uppercase tracking-wider font-medium">Best day to post</p>
                        <p class="text-[13px] text-white mt-0.5" style="font-family: 'Geist Mono', monospace;">{{ insights.best_day }}</p>
                    </div>
                </div>
                <div class="bg-[#111112] border border-[#222224] rounded-[8px] p-[14px] flex items-center gap-3">
                    <svg class="w-4 h-4 text-[#6366f1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="text-[10px] text-[#71717a] uppercase tracking-wider font-medium">Most consistent hour</p>
                        <p class="text-[13px] text-white mt-0.5" style="font-family: 'Geist Mono', monospace;">{{ insights.consistent_hour }}</p>
                    </div>
                </div>
                <div class="bg-[#111112] border border-[#222224] rounded-[8px] p-[14px] flex items-center gap-3">
                    <svg class="w-4 h-4 text-[#6366f1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    <div>
                        <p class="text-[10px] text-[#71717a] uppercase tracking-wider font-medium">Highest single post</p>
                        <p class="text-[13px] text-white mt-0.5" style="font-family: 'Geist Mono', monospace;">{{ formatNumber(insights.highest_post) }} views</p>
                    </div>
                </div>
                <!-- Posting streak -->
                <div class="bg-[#111112] border border-[#222224] rounded-[8px] p-[14px] flex items-center gap-3">
                    <svg class="w-4 h-4 text-[#6366f1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <div>
                        <p class="text-[10px] text-[#71717a] uppercase tracking-wider font-medium">Posting streak</p>
                        <p class="text-[13px] text-white mt-0.5" style="font-family: 'Geist Mono', monospace;">{{ insights.current_streak }} days current / {{ insights.best_streak }} days best</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
