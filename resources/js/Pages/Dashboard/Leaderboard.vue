<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    channels: Array,
});

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return String(num ?? 0);
}

function medalColor(idx) {
    if (idx === 0) return '#f59e0b'; // gold
    if (idx === 1) return '#8a8a8f'; // silver
    if (idx === 2) return '#b45309'; // bronze
    return '#4a4a50';
}
</script>

<template>
    <DashboardLayout>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-[20px] font-semibold text-[#f2f2f3] tracking-tight">Leaderboard</h1>
            <p class="text-[13px] text-[#4a4a50] mt-0.5">Your channels ranked by growth potential</p>
        </div>

        <!-- Leaderboard table -->
        <div v-if="channels.length > 0" class="bg-[#111112] border border-[#222224] rounded-[10px] overflow-hidden">
            <!-- Header -->
            <div class="grid grid-cols-[50px_1fr_100px_100px_100px_80px_80px] gap-3 px-5 py-3 border-b border-[#222224] text-[10px] font-medium uppercase tracking-[0.06em] text-[#4a4a50]">
                <span class="text-center">#</span>
                <span>Channel</span>
                <span class="text-right">Members</span>
                <span class="text-right">Engagement</span>
                <span class="text-right">Growth Score</span>
                <span class="text-right">Posts</span>
                <span class="text-center">Status</span>
            </div>

            <!-- Rows -->
            <Link
                v-for="(ch, idx) in channels"
                :key="ch.id"
                :href="`/dashboard/channels/${ch.id}`"
                class="grid grid-cols-[50px_1fr_100px_100px_100px_80px_80px] gap-3 px-5 py-3.5 border-b border-[#222224] last:border-b-0 items-center hover:bg-[#18181a] transition-all duration-[120ms] group"
            >
                <!-- Rank -->
                <div class="flex items-center justify-center">
                    <span
                        class="text-[16px] font-bold tabular-nums"
                        :style="{ color: medalColor(idx), fontFamily: '\'Geist Mono\', monospace' }"
                    >
                        {{ String(idx + 1).padStart(2, '0') }}
                    </span>
                </div>

                <!-- Channel info -->
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-[7px] bg-[#18181a] flex items-center justify-center text-[13px] font-medium text-[#f2f2f3] shrink-0 group-hover:bg-[#222224] transition-all duration-[120ms]">
                        {{ ch.title?.[0] ?? '?' }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-[13px] font-semibold text-[#f2f2f3] truncate">{{ ch.title }}</p>
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

                <!-- Growth Score with bar -->
                <div class="text-right">
                    <p class="text-[14px] font-semibold text-[#f2f2f3] mb-1" style="font-family: 'Geist Mono', monospace;">
                        {{ ch.potential_score ?? 0 }}%
                    </p>
                    <div class="w-full h-[3px] bg-[#222224] rounded-full overflow-hidden">
                        <div class="h-full bg-[#6366f1] rounded-full" :style="{ width: Math.min(ch.potential_score ?? 0, 100) + '%' }"></div>
                    </div>
                </div>

                <!-- Posts count -->
                <p class="text-[14px] font-semibold text-[#8a8a8f] text-right" style="font-family: 'Geist Mono', monospace;">
                    {{ ch.posts_count }}
                </p>

                <!-- Status -->
                <div class="flex items-center justify-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full" :class="ch.is_active ? 'bg-[#22c55e]' : 'bg-[#ef4444]'"></span>
                    <span class="text-[11px] font-medium" :class="ch.is_active ? 'text-[#22c55e]' : 'text-[#ef4444]'">
                        {{ ch.is_active ? 'LIVE' : 'OFF' }}
                    </span>
                </div>
            </Link>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center justify-center py-20 bg-[#111112] border border-[#222224] border-dashed rounded-[10px]">
            <div class="w-14 h-14 rounded-[10px] bg-[rgba(99,102,241,0.12)] flex items-center justify-center text-xl mb-5">🏆</div>
            <h2 class="text-lg font-semibold text-[#f2f2f3] mb-1.5">No channels to rank</h2>
            <p class="text-[13px] text-[#8a8a8f] text-center max-w-xs mb-6">Connect channels to see how they compare.</p>
        </div>
    </DashboardLayout>
</template>
