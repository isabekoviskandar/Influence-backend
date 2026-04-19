<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    latest_posts: Array,
    viral_posts: Array,
});

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return String(num ?? 0);
}

function mediaIcon(type) {
    if (!type) return null;
    if (type.startsWith('photo')) return '🖼';
    if (type.startsWith('video')) return '🎬';
    if (type.startsWith('document')) return '📎';
    return '📄';
}
</script>

<template>
    <DashboardLayout>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-[20px] font-semibold text-[#f2f2f3] tracking-tight">Posts</h1>
            <p class="text-[13px] text-[#4a4a50] mt-0.5">Latest and most viral content across all channels</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Latest 5 Posts -->
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-[13px] font-medium text-[#f2f2f3]">Latest Posts</span>
                    <span class="text-[11px] text-[#4a4a50]">(5)</span>
                </div>

                <div class="space-y-2">
                    <div
                        v-for="post in latest_posts"
                        :key="post.id"
                        class="bg-[#111112] border border-[#222224] rounded-[10px] p-4 hover:border-[#2e2e32] transition-all duration-[120ms]"
                    >
                        <!-- Post header -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-[11px] font-medium text-[#6366f1]">{{ post.channel_title }}</span>
                                <span class="text-[11px] text-[#4a4a50]">@{{ post.channel_username }}</span>
                            </div>
                            <span class="text-[10px] text-[#4a4a50] shrink-0">{{ post.posted_at_ago }}</span>
                        </div>

                        <!-- Post text -->
                        <p class="text-[13px] text-[#8a8a8f] leading-relaxed mb-3 line-clamp-2">
                            <span v-if="post.media_type" class="mr-1">{{ mediaIcon(post.media_type) }}</span>
                            {{ post.text }}
                        </p>

                        <!-- Metrics -->
                        <div class="flex items-center gap-5">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#4a4a50]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="text-[12px] font-semibold text-[#f2f2f3]" style="font-family: 'Geist Mono', monospace;">{{ formatNumber(post.views) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#4a4a50]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 1.1 2.25 2.25 0 00-3.935-1.1zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"/></svg>
                                <span class="text-[12px] font-semibold text-[#f2f2f3]" style="font-family: 'Geist Mono', monospace;">{{ formatNumber(post.forwards) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#4a4a50]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                                <span class="text-[12px] font-semibold text-[#f2f2f3]" style="font-family: 'Geist Mono', monospace;">{{ formatNumber(post.reactions) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div v-if="latest_posts.length === 0" class="bg-[#111112] border border-[#222224] border-dashed rounded-[10px] py-12 text-center">
                        <p class="text-[13px] text-[#4a4a50]">No posts tracked yet</p>
                    </div>
                </div>
            </div>

            <!-- Most Viral 5 Posts -->
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-[13px] font-medium text-[#f2f2f3]">Most Viral</span>
                    <span class="text-[11px] text-[#4a4a50]">by views</span>
                </div>

                <div class="space-y-2">
                    <div
                        v-for="(post, idx) in viral_posts"
                        :key="post.id"
                        class="bg-[#111112] border border-[#222224] rounded-[10px] p-4 hover:border-[#2e2e32] transition-all duration-[120ms]"
                    >
                        <!-- Rank + header -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="text-[18px] font-bold text-[#222224] tabular-nums" style="font-family: 'Geist Mono', monospace;">{{ String(idx + 1).padStart(2, '0') }}</span>
                                <div class="min-w-0">
                                    <span class="text-[11px] font-medium text-[#6366f1]">{{ post.channel_title }}</span>
                                    <p class="text-[10px] text-[#4a4a50]">{{ post.posted_at }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[16px] font-bold text-[#f2f2f3]" style="font-family: 'Geist Mono', monospace;">{{ formatNumber(post.views) }}</p>
                                <p class="text-[9px] text-[#4a4a50] uppercase tracking-wider">VIEWS</p>
                            </div>
                        </div>

                        <!-- Post text -->
                        <p class="text-[12px] text-[#8a8a8f] leading-relaxed line-clamp-2">
                            <span v-if="post.media_type" class="mr-1">{{ mediaIcon(post.media_type) }}</span>
                            {{ post.text }}
                        </p>

                        <!-- Bottom metrics -->
                        <div class="flex items-center gap-4 mt-2.5 pt-2.5 border-t border-[#222224]">
                            <span class="text-[11px] text-[#4a4a50]">
                                <span class="text-[#8a8a8f]" style="font-family: 'Geist Mono', monospace;">{{ formatNumber(post.forwards) }}</span> fwd
                            </span>
                            <span class="text-[11px] text-[#4a4a50]">
                                <span class="text-[#8a8a8f]" style="font-family: 'Geist Mono', monospace;">{{ formatNumber(post.reactions) }}</span> react
                            </span>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div v-if="viral_posts.length === 0" class="bg-[#111112] border border-[#222224] border-dashed rounded-[10px] py-12 text-center">
                        <p class="text-[13px] text-[#4a4a50]">No posts tracked yet</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
