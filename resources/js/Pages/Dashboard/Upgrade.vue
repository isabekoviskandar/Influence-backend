<script setup>
import { usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const authUser = usePage().props.auth.user;

const plans = [
    {
        name: 'Free',
        price: '$0',
        period: '/mo',
        features: [
            '1 channel',
            '7-day analytics history',
            'Basic post tracking',
            'Daily data sync',
        ],
        current: authUser.plan === 'free',
        color: 'text-indigo-400',
        bg: 'bg-indigo-500/10'
    },
    {
        name: 'Pro',
        price: '$12',
        period: '/mo',
        popular: true,
        features: [
            '5 channels',
            '1-year analytics history',
            'Engagement Rate + Potential Score',
            'Best Time to Post tracking',
            'Priority sync (every 6 hours)'
        ],
        current: authUser.plan === 'pro',
        color: 'text-purple-400',
        bg: 'bg-purple-500/10'
    },
    {
        name: 'Premium',
        price: '$49',
        period: '/mo',
        features: [
            'Unlimited channels',
            'Unlimited analytics history',
            'Everything in Pro',
            'Hourly live sync (every 1 hr)',
            'Dedicated fast support'
        ],
        current: authUser.plan === 'premium',
        color: 'text-emerald-400',
        bg: 'bg-emerald-500/10'
    }
];
</script>

<template>
    <DashboardLayout>
        <div class="max-w-5xl mx-auto py-8">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold text-[#f2f2f3] tracking-tight mb-3">Upgrade Your Plan</h1>
                <p class="text-[#8a8a8f] max-w-lg mx-auto">Get extended analytics, connect more channels, and unlock
                    deeper insights.</p>
            </div>

            <!-- Payment Notice Banner -->
            <div
                class="mb-14 p-6 bg-[#6366f1]/10 border border-[#6366f1]/30 rounded-xl max-w-3xl mx-auto flex flex-col md:flex-row items-center gap-6">
                <div class="flex-shrink-0 w-12 h-12 bg-[#6366f1]/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#6366f1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h3 class="text-white font-semibold mb-2">Automated Online Payments Coming Soon!</h3>
                    <p class="text-[#8a8a8f] text-sm leading-relaxed mb-4">
                        We are currently integrating a secure online payment gateway. For now, if you'd like to upgrade
                        to Pro or Premium, please process your payment directly with the admin via Telegram.
                    </p>
                    <a href="https://t.me/isabekov_iskandar" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-[#2481cc] hover:bg-[#1f6db0] text-white text-sm font-semibold transition-colors duration-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a5.962 5.962 0 0 0-.056 0zm4.962 7.224c.1-.002.32.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.888-.662 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
                        </svg>
                        Write to @isabekov_iskandar
                    </a>
                </div>
            </div>

            <!-- Pricing Grid -->
            <div class="grid md:grid-cols-3 gap-6">
                <div v-for="plan in plans" :key="plan.name"
                    class="bg-[#111118] border rounded-2xl p-8 relative flex flex-col"
                    :class="[plan.popular ? 'border-[#6366f1] shadow-[0_0_30px_rgba(99,102,241,0.1)]' : 'border-[#222224]']">

                    <div v-if="plan.popular"
                        class="absolute top-0 right-8 -translate-y-1/2 bg-[#6366f1] text-white text-[10px] uppercase tracking-wider font-bold px-3 py-1 rounded-full">
                        MOST POPULAR
                    </div>

                    <h3 class="text-lg font-medium text-white mb-2">{{ plan.name }}</h3>
                    <div class="flex items-baseline mb-8">
                        <span class="text-4xl font-bold text-white">{{ plan.price }}</span>
                        <span class="text-[#8a8a8f] ml-1">{{ plan.period }}</span>
                    </div>

                    <ul class="space-y-4 mb-10 flex-1">
                        <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-3">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" :class="plan.color" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-[#a1a1aa] text-[14px]">{{ feature }}</span>
                        </li>
                    </ul>

                    <div v-if="plan.current"
                        class="w-full py-2.5 rounded-lg border border-[#222224] bg-[#222224]/50 text-[#8a8a8f] text-center text-sm font-medium cursor-default">
                        Current Plan
                    </div>
                    <a v-else href="https://t.me/isabekov_iskandar" target="_blank" rel="noopener noreferrer"
                        class="w-full py-2.5 rounded-lg text-center text-sm font-medium transition-all duration-200"
                        :class="plan.popular ? 'bg-[#6366f1] text-white hover:bg-[#4f46e5]' : 'bg-[#222224] text-white hover:bg-[#27272a]'">
                        Upgrade to {{ plan.name }}
                    </a>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
