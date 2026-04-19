<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    channels: Array,
    bot_username: String,
});

const page = usePage();
const user = page.props.auth.user;

const addChannelUrl = `https://t.me/${props.bot_username}?start=add_channel`;

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return String(num ?? 0);
}

function getSparklinePath(data, width = 80, height = 24) {
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
</script>

<template>
    <DashboardLayout>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-[20px] font-semibold text-[#f2f2f3] tracking-tight">Channels</h1>
                <p class="text-[13px] text-[#4a4a50] mt-0.5">Manage all your tracked Telegram channels</p>
            </div>
            <a
                :href="addChannelUrl"
                target="_blank"
                class="inline-flex items-center gap-1.5 h-[30px] px-3 text-[13px] font-medium text-[#6366f1] bg-[rgba(99,102,241,0.12)] border border-[rgba(99,102,241,0.2)] rounded-[7px] hover:bg-[rgba(99,102,241,0.18)] transition-all duration-[120ms]"
            >
                Connect new channel +
            </a>
        </div>

        <!-- Channels table -->
        <div v-if="channels.length > 0" class="bg-[#111112] border border-[#222224] rounded-[10px] overflow-hidden">
            <!-- Table header -->
            <div class="grid grid-cols-[1fr_100px_100px_100px_80px_100px] gap-4 px-5 py-3 border-b border-[#222224] text-[10px] font-medium uppercase tracking-[0.06em] text-[#4a4a50]">
                <span>Channel</span>
                <span class="text-right">Members</span>
                <span class="text-right">Engagement</span>
                <span class="text-right">Growth</span>
                <span class="text-center">Trend</span>
                <span class="text-right">Status</span>
            </div>

            <!-- Rows -->
            <Link
                v-for="ch in channels"
                :key="ch.id"
                :href="`/dashboard/channels/${ch.id}`"
                class="grid grid-cols-[1fr_100px_100px_100px_80px_100px] gap-4 px-5 py-3.5 border-b border-[#222224] last:border-b-0 items-center hover:bg-[#18181a] transition-all duration-[120ms] group"
            >
                <!-- Channel info -->
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-[8px] bg-[#18181a] flex items-center justify-center text-[14px] font-medium text-[#f2f2f3] shrink-0 group-hover:bg-[#222224] transition-all duration-[120ms]">
                        {{ ch.title?.[0] ?? '?' }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-[13px] font-semibold text-[#f2f2f3] truncate">{{ ch.title }}</p>
                            <span
                                v-if="ch.member_count < 10"
                                class="text-[9px] font-semibold uppercase px-1 py-0.5 rounded bg-[rgba(245,158,11,0.10)] text-[#f59e0b] shrink-0"
                            >⚠</span>
                        </div>
                        <p class="text-[11px] text-[#4a4a50] truncate">@{{ ch.username }}</p>
                    </div>
                </div>

                <!-- Members -->
                <p class="text-[14px] font-semibold text-[#f2f2f3] text-right" style="font-family: 'Geist Mono', monospace;">
                    {{ formatNumber(ch.member_count) }}
                </p>

                <!-- Engagement -->
                <p class="text-[14px] font-semibold text-right" :style="{ color: ch.engagement_rate > 100 ? '#22c55e' : '#f2f2f3', fontFamily: '\'Geist Mono\', monospace' }">
                    {{ ch.engagement_rate ?? 0 }}%
                </p>

                <!-- Growth score -->
                <div class="text-right">
                    <p class="text-[14px] font-semibold text-[#f2f2f3]" style="font-family: 'Geist Mono', monospace;">
                        {{ ch.potential_score ?? 0 }}%
                    </p>
                </div>

                <!-- Sparkline -->
                <div class="flex justify-center">
                    <svg width="60" height="20" viewBox="0 0 80 24" class="overflow-visible">
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

                <!-- Status -->
                <div class="flex items-center justify-end gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full" :class="ch.is_active ? 'bg-[#22c55e]' : 'bg-[#ef4444]'"></span>
                    <span class="text-[11px] font-medium" :class="ch.is_active ? 'text-[#22c55e]' : 'text-[#ef4444]'">
                        {{ ch.is_active ? 'LIVE' : 'OFF' }}
                    </span>
                </div>
            </Link>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center justify-center py-20 bg-[#111112] border border-[#222224] border-dashed rounded-[10px]">
            <div class="w-14 h-14 rounded-[10px] bg-[rgba(99,102,241,0.12)] flex items-center justify-center text-xl mb-5">📡</div>
            <h2 class="text-lg font-semibold text-[#f2f2f3] mb-1.5">No channels yet</h2>
            <p class="text-[13px] text-[#8a8a8f] text-center max-w-xs mb-6">Connect your first Telegram channel to start tracking.</p>
            <a :href="addChannelUrl" target="_blank" class="inline-flex items-center gap-2 px-5 py-2 rounded-[7px] text-[13px] font-semibold bg-[#6366f1] text-white hover:bg-[#5558e8] transition-all duration-[120ms]">
                Connect Channel
            </a>
        </div>
    </DashboardLayout>
</template>
