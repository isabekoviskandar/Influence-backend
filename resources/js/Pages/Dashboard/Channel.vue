<script setup>
import { computed, ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    channel:       Object,
    posts:         Array,
    stats_history: Array,
});

function formatNumber(n) {
    if (!n) return '0';
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M';
    if (n >= 1_000)     return (n / 1_000).toFixed(1) + 'K';
    return n.toString();
}

// Simple SVG chart built from stats_history
const chartWidth  = 600;
const chartHeight = 140;

const viewsPoints = computed(() => {
    const data = props.stats_history;
    if (!data?.length) return '';
    const max = Math.max(...data.map(s => s.avg_views ?? 0), 1);
    return data.map((s, i) => {
        const x = (i / (data.length - 1 || 1)) * chartWidth;
        const y = chartHeight - ((s.avg_views ?? 0) / max) * chartHeight;
        return `${x},${y}`;
    }).join(' ');
});

const membersPoints = computed(() => {
    const data = props.stats_history;
    if (!data?.length) return '';
    const max = Math.max(...data.map(s => s.member_count ?? 0), 1);
    return data.map((s, i) => {
        const x = (i / (data.length - 1 || 1)) * chartWidth;
        const y = chartHeight - ((s.member_count ?? 0) / max) * chartHeight;
        return `${x},${y}`;
    }).join(' ');
});

const labels = computed(() =>
    props.stats_history?.filter((_, i) => i % Math.ceil(props.stats_history.length / 5) === 0)
        .map(s => s.date) ?? []
);

// Active chart tab
const activeTab = ref('views');
</script>

<template>
    <DashboardLayout>

        <!-- Back -->
        <Link href="/dashboard" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-white transition-colors mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Dashboard
        </Link>

        <!-- Channel header card -->
        <div class="bg-[#16161f] border border-white/5 rounded-2xl p-6 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-500/20 flex items-center justify-center text-2xl font-bold text-indigo-400">
                    {{ channel.title?.[0]?.toUpperCase() ?? '#' }}
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-bold text-white">{{ channel.title }}</h1>
                        <span
                            class="text-xs px-2 py-0.5 rounded-full font-medium"
                            :class="channel.is_active
                                ? 'bg-green-500/15 text-green-400'
                                : 'bg-gray-500/15 text-gray-400'"
                        >
                            {{ channel.is_active ? 'Active' : 'Paused' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ channel.username ? '@' + channel.username : 'Private channel' }}
                        <span class="mx-1">·</span>
                        Last synced: {{ channel.last_synced_at ?? 'Never' }}
                    </p>
                </div>
            </div>

            <!-- Metric strip -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-white/5">
                <div v-for="metric in [
                    { label: 'Subscribers', value: formatNumber(channel.member_count), icon: '👥' },
                    { label: 'Avg Views',   value: formatNumber(channel.avg_views),   icon: '👁️' },
                    { label: 'Engagement',  value: (channel.engagement_rate ?? 0) + '%', icon: '📈' },
                    { label: 'Score',       value: channel.potential_score ?? '—',    icon: '⭐' },
                ]" :key="metric.label">
                    <div>
                        <p class="text-lg font-bold text-white">{{ metric.icon }} {{ metric.value }}</p>
                        <p class="text-xs text-gray-500">{{ metric.label }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart card -->
        <div class="bg-[#16161f] border border-white/5 rounded-2xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-white">Analytics Trend</h2>
                <div class="flex gap-1 bg-black/30 rounded-lg p-1">
                    <button
                        v-for="tab in ['views', 'members']"
                        :key="tab"
                        @click="activeTab = tab"
                        class="px-3 py-1 rounded-md text-xs font-medium transition-all"
                        :class="activeTab === tab
                            ? 'bg-indigo-600 text-white'
                            : 'text-gray-500 hover:text-white'"
                    >
                        {{ tab === 'views' ? 'Avg Views' : 'Members' }}
                    </button>
                </div>
            </div>

            <div v-if="stats_history?.length > 1" class="relative">
                <svg
                    :viewBox="`0 0 ${600} ${140}`"
                    class="w-full h-32"
                    preserveAspectRatio="none"
                >
                    <!-- Grid lines -->
                    <line x1="0" y1="35"  x2="600" y2="35"  stroke="rgba(255,255,255,0.04)" stroke-width="1"/>
                    <line x1="0" y1="70"  x2="600" y2="70"  stroke="rgba(255,255,255,0.04)" stroke-width="1"/>
                    <line x1="0" y1="105" x2="600" y2="105" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>

                    <!-- Fill area -->
                    <defs>
                        <linearGradient id="grad-views"   x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#6366f1" stop-opacity="0.3"/>
                            <stop offset="100%" stop-color="#6366f1" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="grad-members" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#a855f7" stop-opacity="0.3"/>
                            <stop offset="100%" stop-color="#a855f7" stop-opacity="0"/>
                        </linearGradient>
                    </defs>

                    <!-- Views line -->
                    <template v-if="activeTab === 'views'">
                        <polygon
                            :points="viewsPoints + ` 600,${140} 0,${140}`"
                            fill="url(#grad-views)"
                        />
                        <polyline
                            :points="viewsPoints"
                            fill="none"
                            stroke="#6366f1"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </template>

                    <!-- Members line -->
                    <template v-else>
                        <polygon
                            :points="membersPoints + ` 600,${140} 0,${140}`"
                            fill="url(#grad-members)"
                        />
                        <polyline
                            :points="membersPoints"
                            fill="none"
                            stroke="#a855f7"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </template>
                </svg>
            </div>
            <div v-else class="h-32 flex items-center justify-center text-gray-600 text-sm">
                Not enough data yet — check back after a few syncs.
            </div>
        </div>

        <!-- Posts list -->
        <div class="bg-[#16161f] border border-white/5 rounded-2xl">
            <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
                <h2 class="text-base font-semibold text-white">Recent Posts</h2>
                <span class="text-xs text-gray-600">{{ posts.length }} posts</span>
            </div>

            <div v-if="posts.length > 0" class="divide-y divide-white/5">
                <div
                    v-for="post in posts"
                    :key="post.id"
                    class="px-6 py-4 hover:bg-white/[0.02] transition-colors"
                >
                    <div class="flex items-start justify-between gap-4">
                        <!-- Text preview -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-300 line-clamp-2 leading-relaxed">
                                <span v-if="post.media_type" class="inline-block mr-1 text-xs bg-white/10 rounded px-1 py-0.5 text-gray-400">
                                    {{ post.media_type }}
                                </span>
                                {{ post.text || '(no text)' }}
                            </p>
                            <p class="text-xs text-gray-600 mt-1">{{ post.posted_at_ago }}</p>
                        </div>
                        <!-- Metrics -->
                        <div class="flex items-center gap-4 shrink-0 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                {{ formatNumber(post.views) }}
                            </span>
                            <span v-if="post.forwards">↗ {{ post.forwards }}</span>
                            <span v-if="post.reactions">❤️ {{ post.reactions }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="py-16 text-center text-gray-600 text-sm">
                No posts tracked yet.
            </div>
        </div>

    </DashboardLayout>
</template>
