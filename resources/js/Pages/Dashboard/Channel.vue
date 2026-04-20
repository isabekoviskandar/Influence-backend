<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    channel: Object,
    posts: Array,
    stats_history: Array,
    period: String,
    max_stats_days: Number,
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

// Chart Configuration
const activeTab = ref('views');

const chartSeries = computed(() => [{
    name: activeTab.value === 'views' ? 'Avg Views' : 'Members',
    data: props.stats_history.map(s => activeTab.value === 'views' ? s.avg_views : s.member_count)
}]);

const chartOptions = computed(() => ({
    chart: {
        type: 'area',
        toolbar: { show: false },
        zoom: { enabled: false },
        animations: { enabled: true, easing: 'easeinout', speed: 800 },
        background: 'transparent',
    },
    colors: [activeTab.value === 'views' ? '#6366f1' : '#a855f7'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [0, 100]
        }
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 4 },
    grid: {
        borderColor: 'rgba(255, 255, 255, 0.03)',
        strokeDashArray: 4,
        padding: { left: 20, right: 20 }
    },
    xaxis: {
        categories: props.stats_history.map(s => s.date),
        labels: { show: true, style: { colors: '#4b5563', fontSize: '10px', fontWeight: 'bold' } },
        axisBorder: { show: false },
        axisTicks: { show: false }
    },
    yaxis: {
        labels: { show: true, style: { colors: '#4b5563', fontSize: '10px', fontWeight: 'bold' }, formatter: (v) => formatNumber(v) }
    },
    tooltip: {
        theme: 'dark',
        x: { show: true },
        marker: { show: true },
    },
    markers: {
        size: 0,
        hover: { size: 6, strokeWidth: 3, strokeColor: '#fff' }
    }
}));

const metrics = [
    { label: 'Subscribers', value: formatNumber(props.channel.member_count), icon: 'users', color: 'indigo' },
    { label: 'Lifetime Avg', value: formatNumber(props.channel.avg_views), icon: 'eye', color: 'emerald' },
    { label: 'Recent Avg', value: formatNumber(props.channel.avg_views_recent || props.channel.avg_views), icon: 'eye', color: 'emerald' },
    { label: 'Engagement', value: (props.channel.engagement_rate ?? 0) + '%', icon: 'trending-up', color: 'purple' },
    { label: 'Growth Score', value: (props.channel.potential_score ?? 0) + '%', icon: 'zap', color: 'amber' },
];
</script>

<template>
    <DashboardLayout>

        <!-- Back & Actions -->
        <div class="flex items-center justify-between mb-8">
            <Link href="/dashboard"
                class="group flex items-center gap-2 text-xs font-black uppercase tracking-[0.2em] text-gray-500 hover:text-white transition-all">
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                Back to network
            </Link>

            <div class="flex items-center gap-2">
                <span v-if="channel.is_active"
                    class="flex h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-500">Live Intel</p>
            </div>
        </div>

        <!-- Premium Hero Strip -->
        <div
            class="bg-[#111118] border border-white/[0.05] rounded-[2.5rem] p-10 mb-8 border-b-4 border-b-indigo-500/20">
            <div class="flex flex-col lg:flex-row lg:items-center gap-10">
                <!-- Branding -->
                <div class="flex items-center gap-6 pr-10 lg:border-r border-white/5 min-w-[300px]">
                    <div
                        class="w-20 h-20 rounded-3xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-white/5 flex items-center justify-center text-3xl font-black text-white shadow-inner">
                        {{ channel.title?.[0]?.toUpperCase() ?? '#' }}
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-white tracking-tighter">{{ channel.title }}</h1>
                        <p class="text-sm font-bold text-gray-600 mt-1">@{{ channel.username || 'private' }}</p>
                    </div>
                </div>

                <!-- Horizontal metrics -->
                <div class="flex-1 grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-8">
                    <div v-for="m in metrics" :key="m.label" class="flex items-center gap-4 group">
                        <div
                            :class="`w-10 h-10 rounded-xl bg-${m.color}-500/10 flex items-center justify-center text-${m.color}-400 group-hover:scale-110 transition-transform`">
                            <svg v-if="m.icon === 'users'" class="w-5 h-5 text-indigo-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg v-if="m.icon === 'eye'" class="w-5 h-5 text-emerald-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-if="m.icon === 'trending-up'" class="w-5 h-5 text-purple-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <svg v-if="m.icon === 'zap'" class="w-5 h-5 text-amber-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xl font-black text-white tracking-tight">{{ m.value }}</p>
                            <p class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">{{ m.label }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            <!-- Analytics Main -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Chart Card -->
                <div class="bg-[#111118] border border-white/[0.05] rounded-[2.5rem] p-8">
                    <div class="flex items-center justify-between mb-10 px-4">
                        <div>
                            <h2 class="text-lg font-black text-white tracking-tight">Channel Performance</h2>
                            <p class="text-xs font-bold text-gray-600 mt-1 uppercase tracking-widest">Growth metrics
                                over time</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex p-1 bg-black/40 rounded-xl border border-white/5">
                                <button v-for="t in ['views', 'members']" :key="t" @click="activeTab = t"
                                    class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all"
                                    :class="activeTab === t ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-gray-500 shadow-none hover:text-white'">
                                    {{ t }}
                                </button>
                            </div>
                            <div class="flex p-1 bg-black/40 rounded-xl border border-white/5">
                                <button v-for="p in [{v:'7d', d:7}, {v:'30d', d:30}, {v:'all', d:9999}]" :key="p.v" @click="setPeriod(p.v)"
                                    :disabled="p.d > (max_stats_days || 7)"
                                    class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                                    :class="period === p.v ? 'bg-white/10 text-white' : 'text-gray-500 shadow-none hover:text-white disabled:hover:text-gray-500'"
                                    :title="p.d > max_stats_days ? 'Upgrade your plan to view more history' : ''">
                                    {{ p.v }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="min-h-[300px] w-full">
                        <VueApexCharts width="100%" height="320" :options="chartOptions" :series="chartSeries" />
                    </div>
                </div>

                <!-- Activity list -->
                <div class="bg-[#111118] border border-white/[0.05] rounded-[2.5rem] overflow-hidden">
                    <div class="px-10 py-8 border-b border-white/[0.03] flex items-center justify-between">
                        <h2 class="text-lg font-black text-white tracking-tight">Recent Engagement</h2>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-700">{{ posts.length }}
                            Events Tracked</span>
                    </div>

                    <div v-if="posts.length > 0" class="divide-y divide-white/[0.02]">
                        <div v-for="post in posts" :key="post.id"
                            class="px-10 py-6 hover:bg-white/[0.01] transition-all group">
                            <div class="flex items-center justify-between gap-10">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div
                                            class="px-2 py-0.5 rounded-md bg-white/5 border border-white/5 text-[9px] font-black uppercase tracking-widest text-gray-500 border border-white/10">
                                            {{ post.media_type || 'POST' }}
                                        </div>
                                        <p class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">{{
                                            post.posted_at_ago }}</p>
                                    </div>
                                    <p class="text-sm font-bold text-gray-400 leading-relaxed line-clamp-2 pr-6">
                                        {{ post.text || 'Engagement blast sent to network...' }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-10 shrink-0 pr-4">
                                    <div class="text-right">
                                        <p class="text-sm font-black text-white">{{ formatNumber(post.views) }}</p>
                                        <p class="text-[9px] font-bold text-gray-700 uppercase tracking-widest">Views
                                        </p>
                                    </div>
                                    <div class="text-right min-w-[60px]">
                                        <p class="text-sm font-black text-red-500">{{ formatNumber(post.reactions) }}
                                        </p>
                                        <p class="text-[9px] font-bold text-gray-700 uppercase tracking-widest">Hype</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-8">
                <!-- Sync Progress Card -->
                <div v-if="channel.sync_status === 'syncing'"
                    class="bg-[#111118] border border-white/[0.05] rounded-[2.5rem] p-8 bg-gradient-to-b from-indigo-500/[0.03] to-transparent">
                    <h3
                        class="text-sm font-black text-indigo-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        Full Sync in Progress
                    </h3>

                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3 px-1">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Progress</span>
                            <span class="text-xs font-black text-white">{{ Math.round((channel.sync_current /
                                channel.sync_total) * 100) }}%</span>
                        </div>
                        <div
                            class="w-full h-2 bg-white/5 rounded-full overflow-hidden p-0.5 border border-white/[0.05]">
                            <div class="h-full bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(99,102,241,0.5)]"
                                :style="{ width: `${(channel.sync_current / channel.sync_total) * 100}%` }"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between px-1">
                        <span class="text-[9px] font-bold text-gray-600 uppercase tracking-widest">Total Intake</span>
                        <span class="text-[10px] font-black text-indigo-300">{{ formatNumber(channel.sync_current) }} /
                            {{ formatNumber(channel.sync_total) }}</span>
                    </div>
                </div>

                <div class="bg-[#111118] border border-white/[0.05] rounded-[2.5rem] p-8">
                    <h3 class="text-sm font-black text-white uppercase tracking-widest mb-6">Channel Metadata</h3>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Status</span>
                            <span
                                class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-500 text-[10px] font-black uppercase tracking-widest border border-emerald-500/10">Tracking</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Network
                                Score</span>
                            <span class="text-xs font-black text-white">{{ channel.potential_score }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Last Intel
                                Sync</span>
                            <span class="text-[10px] font-black text-gray-400">{{ channel.last_synced_at }}</span>
                        </div>
                    </div>

                    <div class="mt-10 pt-8 border-t border-white/[0.03]">
                        <button
                            class="w-full py-4 rounded-2xl bg-white/5 border border-white/5 text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-white hover:bg-white/10 transition-all cursor-not-allowed">
                            Request Manual Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>
