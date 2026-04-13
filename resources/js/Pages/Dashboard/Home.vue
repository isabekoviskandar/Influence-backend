<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    channels:    Array,
    stats:       Object,
    bot_username: String,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

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
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white">
                    Hey, {{ user?.name?.split(' ')[0] }} 👋
                </h1>
                <p class="text-gray-500 text-sm mt-1">Here's your channel overview</p>
            </div>
            <a
                :href="addChannelUrl"
                target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white transition-all shadow-lg shadow-indigo-500/20"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Channel
            </a>
        </div>

        <!-- Stats row -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div
                v-for="(stat, key) in [
                    { label: 'Total Channels',   value: stats.total_channels,   icon: '📡', color: 'from-indigo-500 to-indigo-600' },
                    { label: 'Active Channels',  value: stats.active_channels,  icon: '✅', color: 'from-green-500 to-emerald-600' },
                    { label: 'Total Members',    value: formatNumber(stats.total_members), icon: '👥', color: 'from-purple-500 to-purple-600' },
                    { label: 'Avg Engagement',   value: stats.avg_engagement + '%', icon: '📈', color: 'from-amber-500 to-orange-500' },
                ]"
                :key="key"
                class="bg-[#16161f] border border-white/5 rounded-xl p-5 hover:border-white/10 transition-all"
            >
                <p class="text-2xl mb-1">{{ stat.icon }}</p>
                <p class="text-2xl font-bold text-white">{{ stat.value }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ stat.label }}</p>
            </div>
        </div>

        <!-- Channels grid -->
        <div v-if="channels.length > 0" class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
            <Link
                v-for="ch in channels"
                :key="ch.id"
                :href="`/dashboard/channels/${ch.id}`"
                class="group bg-[#16161f] border border-white/5 rounded-xl p-5 hover:border-indigo-500/30 hover:shadow-lg hover:shadow-indigo-500/5 transition-all duration-200 cursor-pointer"
            >
                <!-- Channel header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold text-sm">
                            {{ ch.title?.[0]?.toUpperCase() ?? '#' }}
                        </div>
                        <div>
                            <p class="font-semibold text-white text-sm leading-tight">{{ ch.title }}</p>
                            <p class="text-xs text-gray-500">{{ ch.username ? '@' + ch.username : 'Private' }}</p>
                        </div>
                    </div>
                    <span
                        class="text-xs px-2 py-0.5 rounded-full font-medium"
                        :class="ch.is_active
                            ? 'bg-green-500/15 text-green-400'
                            : 'bg-gray-500/15 text-gray-400'"
                    >
                        {{ ch.is_active ? 'Active' : 'Paused' }}
                    </span>
                </div>

                <!-- Metrics -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="text-center">
                        <p class="text-base font-bold text-white">{{ formatNumber(ch.member_count) }}</p>
                        <p class="text-[10px] text-gray-500 mt-0.5">Members</p>
                    </div>
                    <div class="text-center border-x border-white/5">
                        <p class="text-base font-bold text-white">{{ formatNumber(ch.avg_views) }}</p>
                        <p class="text-[10px] text-gray-500 mt-0.5">Avg Views</p>
                    </div>
                    <div class="text-center">
                        <p class="text-base font-bold text-white">{{ ch.engagement_rate ? ch.engagement_rate + '%' : '—' }}</p>
                        <p class="text-[10px] text-gray-500 mt-0.5">Engagement</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-4 pt-3 border-t border-white/5 flex items-center justify-between">
                    <span class="text-xs text-gray-600">{{ ch.posts_count }} posts tracked</span>
                    <span class="text-xs text-gray-600">{{ ch.last_synced_at ?? 'Never synced' }}</span>
                </div>
            </Link>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center justify-center py-24 bg-[#16161f] border border-white/5 rounded-2xl">
            <div class="text-5xl mb-4">📡</div>
            <h2 class="text-xl font-bold text-white mb-2">No channels yet</h2>
            <p class="text-gray-500 text-sm text-center max-w-xs mb-6">
                Add our bot as an admin to your Telegram channel and we'll automatically start tracking analytics.
            </p>
            <a
                :href="addChannelUrl"
                target="_blank"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/20 hover:from-indigo-500 hover:to-purple-500 transition-all"
            >
                Open Telegram Bot
            </a>
            <p class="text-xs text-gray-600 mt-3">
                Then add @{{ bot_username }} as an admin to your channel
            </p>
        </div>

    </DashboardLayout>
</template>
