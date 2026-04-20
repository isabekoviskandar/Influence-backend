<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const form = useForm({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);

const passwordStrength = computed(() => {
    const p = form.password;
    if (!p) return 0;
    let s = 1; // 1 segment red if typed anything
    if (p.length >= 8) s = 2; // amber if length OK
    if (p.length >= 8 && /[A-Z]/.test(p) && /[0-9]/.test(p)) s = 3; // indigo dim if complexity starts
    if (p.length >= 8 && /[A-Z]/.test(p) && /[0-9]/.test(p) && /[^A-Za-z0-9]/.test(p)) s = 4; // full indigo if secure
    return s;
});

const confirmMatch = computed(() => {
    if (!form.password_confirmation || !form.password) return null;
    return form.password === form.password_confirmation;
});

const submit = () => {
    form.post(route('register.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

function getStrengthClasses(index, strength) {
    if (strength === 0) return 'bg-[#222224]';
    if (strength === 1 && index === 1) return 'bg-red-500';
    if (strength === 2 && index <= 2) return 'bg-amber-500';
    if (strength === 3 && index <= 3) return 'bg-[#6366f1]/60';
    if (strength === 4 && index <= 4) return 'bg-[#6366f1]';
    return 'bg-[#222224]';
}
</script>

<template>
    <div class="min-h-screen w-full flex bg-[#0a0a0b] text-[#f2f2f3]" style="font-family: 'Geist', sans-serif;">
        <!-- Left Column: Form -->
        <div class="w-full md:w-[420px] flex flex-col justify-center px-[24px] md:px-[48px] py-12 mx-auto md:mx-0 shrink-0">
            <!-- Logo Row -->
            <div class="flex items-center gap-3 mb-[32px]">
                <div class="w-6 h-6 rounded-[6px] bg-[#6366f1] flex items-center justify-center shadow-sm">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <span class="text-[16px] font-[600] text-white tracking-tight">Publyc</span>
            </div>

            <!-- Heading -->
            <h1 class="text-[24px] font-[700] text-white leading-tight tracking-tight mb-2">Create your account</h1>
            <p class="text-[14px] text-[#71717a] mb-[24px]">Start tracking your Telegram channels in minutes.</p>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-[12px] font-[500] text-[#8a8a8f] mb-[6px]">Username</label>
                    <input 
                        id="username" 
                        type="text" 
                        v-model="form.username" 
                        placeholder="johndoe"
                        class="w-full h-[40px] bg-[#111112] border border-[#222224] rounded-[7px] px-3 text-[14px] text-white placeholder:text-[#4a4a50] focus:border-[#6366f1] focus:ring-0 outline-none transition-colors duration-120 block"
                        required 
                        autofocus 
                    />
                    <p class="text-[11px] text-[#71717a] mt-1.5">This will be your public handle</p>
                    <div v-if="form.errors.username" class="text-red-500 text-[11px] mt-1">{{ form.errors.username }}</div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-[12px] font-[500] text-[#8a8a8f] mb-[6px]">Email address</label>
                    <input 
                        id="email" 
                        type="email" 
                        v-model="form.email" 
                        placeholder="you@example.com"
                        class="w-full h-[40px] bg-[#111112] border border-[#222224] rounded-[7px] px-3 text-[14px] text-white placeholder:text-[#4a4a50] focus:border-[#6366f1] focus:ring-0 outline-none transition-colors duration-120 block"
                        required 
                    />
                    <div v-if="form.errors.email" class="text-red-500 text-[11px] mt-1">{{ form.errors.email }}</div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-[12px] font-[500] text-[#8a8a8f] mb-[6px]">Password</label>
                    <div class="relative">
                        <input 
                            id="password" 
                            :type="showPassword ? 'text' : 'password'" 
                            v-model="form.password" 
                            placeholder="Min. 8 characters"
                            class="w-full h-[40px] bg-[#111112] border border-[#222224] rounded-[7px] px-3 pr-10 text-[14px] text-white placeholder:text-[#4a4a50] focus:border-[#6366f1] focus:ring-0 outline-none transition-colors duration-120 block"
                            required 
                        />
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#71717a] hover:text-[#a1a1aa] transition-colors focus:outline-none">
                            <svg v-if="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </button>
                    </div>
                    <!-- Strength meter -->
                    <div class="flex gap-1 mt-2 mb-1 w-full h-[3px]">
                        <div class="flex-1 rounded-[2px] transition-colors duration-300" :class="getStrengthClasses(1, passwordStrength)"></div>
                        <div class="flex-1 rounded-[2px] transition-colors duration-300" :class="getStrengthClasses(2, passwordStrength)"></div>
                        <div class="flex-1 rounded-[2px] transition-colors duration-300" :class="getStrengthClasses(3, passwordStrength)"></div>
                        <div class="flex-1 rounded-[2px] transition-colors duration-300" :class="getStrengthClasses(4, passwordStrength)"></div>
                    </div>
                    <div v-if="form.errors.password" class="text-red-500 text-[11px] mt-1">{{ form.errors.password }}</div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-[12px] font-[500] text-[#8a8a8f] mb-[6px]">Confirm Password</label>
                    <div class="relative">
                        <input 
                            id="password_confirmation" 
                            type="password" 
                            v-model="form.password_confirmation" 
                            placeholder="Repeat password"
                            :class="[
                                'w-full h-[40px] bg-[#111112] border rounded-[7px] px-3 pr-10 text-[14px] text-white placeholder:text-[#4a4a50] focus:ring-0 outline-none transition-colors duration-120 block',
                                confirmMatch === false ? 'border-red-500/50 focus:border-red-500' : 'border-[#222224] focus:border-[#6366f1]'
                            ]"
                            required 
                        />
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                            <svg v-if="confirmMatch === true" class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </div>
                    <div v-if="form.errors.password_confirmation" class="text-red-500 text-[11px] mt-1">{{ form.errors.password_confirmation }}</div>
                </div>

                <div class="h-[8px]"></div>
                <!-- Terms -->
                <p class="text-[11px] text-[#71717a] text-center">
                    By creating an account you agree to our 
                    <a href="#" class="text-[#6366f1] hover:text-white transition-colors">Terms</a> and 
                    <a href="#" class="text-[#6366f1] hover:text-white transition-colors">Privacy Policy</a>
                </p>

                <!-- Submit -->
                <button type="submit" :disabled="form.processing" class="w-full h-[40px] bg-[#6366f1] hover:bg-[#5558e3] active:scale-[0.99] text-white text-[14px] font-[500] rounded-[7px] transition-all duration-120 flex items-center justify-center outline-none mt-2">
                    <span v-if="form.processing">Creating account...</span>
                    <span v-else>Create account</span>
                </button>
            </form>

            <div class="mt-4 text-center">
                <span class="text-[13px] text-[#71717a]">Already have an account? </span>
                <Link :href="route('login')" class="text-[13px] text-[#6366f1] hover:text-white transition-colors duration-200">Sign in</Link>
            </div>
        </div>

        <!-- Right Column: Product Preview Panel -->
        <div class="hidden md:flex flex-1 flex-col bg-[#0d0d10] border-l border-[#222224] p-[48px] overflow-y-auto relative">
            <div class="max-w-[480px] mt-10">
                <p class="text-[10px] uppercase text-[#71717a] tracking-[0.15em] font-bold" style="font-family: 'Geist Mono', monospace;">PUBLYC ANALYTICS</p>
                <h2 class="text-[22px] font-[600] text-white mt-3 mb-2 tracking-tight">Your Telegram channels, fully understood.</h2>
                <p class="text-[14px] text-[#71717a] leading-relaxed mb-10">Views, reactions, growth, and engagement — all tracked automatically the moment you add the bot.</p>
                
                <div class="flex flex-col gap-[16px] mb-[32px]">
                    <div class="flex gap-4">
                        <div class="mt-1"><svg class="w-4 h-4 text-[#6366f1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></div>
                        <div>
                            <p class="text-[13px] text-white font-[500]">Bot syncs automatically</p>
                            <p class="text-[12px] text-[#71717a] mt-0.5">No manual exports. Historical data captured from day one.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="mt-1"><svg class="w-4 h-4 text-[#6366f1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg></div>
                        <div>
                            <p class="text-[13px] text-white font-[500]">Live engagement tracking</p>
                            <p class="text-[12px] text-[#71717a] mt-0.5">Views and reactions updated every hour on Pro.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="mt-1"><svg class="w-4 h-4 text-[#6366f1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        <div>
                            <p class="text-[13px] text-white font-[500]">Best time to post</p>
                            <p class="text-[12px] text-[#71717a] mt-0.5">Heatmap built from your own channel's data.</p>
                        </div>
                    </div>
                </div>

                <!-- Mini Dashboard Mockup -->
                <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-[16px] shadow-2xl mb-4 pointer-events-none select-none">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-[#6366f1]/20 flex items-center justify-center border border-[#6366f1]/10">
                                <span class="text-[12px] font-bold text-[#a5b4fc]">I</span>
                            </div>
                            <div class="leading-none">
                                <p class="text-[13px] text-white font-[600] tracking-tight">Isabekoff log</p>
                                <p class="text-[11px] text-[#71717a] mt-0.5">@isabekoff_lo</p>
                            </div>
                        </div>
                        <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)] relative">
                            <div class="absolute inset-0 rounded-full bg-emerald-500 animate-ping opacity-75"></div>
                        </div>
                    </div>
                    
                    <!-- Stats Grid -->
                    <div class="flex items-center justify-between px-2 mb-4">
                        <div class="text-center flex-1">
                            <p class="text-[20px] font-[600] text-white" style="font-family: 'Geist Mono', monospace;">79</p>
                            <p class="text-[9px] text-[#71717a] uppercase tracking-widest mt-1">MEMBERS</p>
                        </div>
                        <div class="w-[1px] h-[30px] bg-[#222224]"></div>
                        <div class="text-center flex-1">
                            <p class="text-[20px] font-[600] text-white" style="font-family: 'Geist Mono', monospace;">88.61%</p>
                            <p class="text-[9px] text-[#71717a] uppercase tracking-widest mt-1">VIRAL POWER</p>
                        </div>
                        <div class="w-[1px] h-[30px] bg-[#222224]"></div>
                        <div class="text-center flex-1">
                            <p class="text-[20px] font-[600] text-[#22c55e]" style="font-family: 'Geist Mono', monospace;">53%</p>
                            <p class="text-[9px] text-[#71717a] uppercase tracking-widest mt-1">GROWTH</p>
                        </div>
                    </div>

                    <!-- Mini Sparkline -->
                    <div class="h-[40px] w-full">
                        <svg viewBox="0 0 100 40" class="w-full h-full overflow-visible" preserveAspectRatio="none">
                            <path d="M0 35 L20 32 L40 28 L60 30 L80 15 L100 5" fill="none" stroke="#6366f1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>

                <!-- Heatmap Mockup Card -->
                <div class="bg-[#111112] border border-[#222224] rounded-[10px] p-[16px] flex flex-col justify-between pointer-events-none select-none">
                    <div class="flex gap-[2px]">
                        <!-- Simulate 8 cols (hours), 5 rows (days) -->
                        <div v-for="col in 8" :key="col" class="flex flex-col gap-[2px] flex-1">
                            <div v-for="row in 5" :key="row" class="w-full aspect-square rounded-[1px]" :class="[
                                'bg-[#1a1a1c]',
                                (row === 2 && col === 3) || (row === 4 && col === 7) ? '!bg-[#6366f1]' : '',
                                (row === 2 && col === 4) || (row === 3 && col === 3) ? '!bg-indigo-500/75' : '',
                                (row === 1 && col === 5) || (row === 5 && col === 6) ? '!bg-indigo-500/40' : '',
                                (row === 4 && col === 2) || (row === 1 && col === 2) ? '!bg-indigo-500/15' : ''
                            ]"></div>
                        </div>
                    </div>
                    <p class="text-[10px] text-[#71717a] uppercase tracking-wider mt-3">Your peak hours, visualized.</p>
                </div>
            </div>

            <!-- Quote Strip Bottom -->
            <div class="mt-auto pt-10 pb-4">
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-full bg-[#6366f1]/10 flex items-center justify-center shrink-0 border border-[#6366f1]/20">
                        <span class="text-[13px] font-bold text-[#a5b4fc]">I</span>
                    </div>
                    <div>
                        <p class="text-[13px] text-[#a1a1aa] italic leading-relaxed">"I never knew Tuesday 11am was my golden window until Publyc showed me."</p>
                        <p class="text-[12px] text-[#71717a] mt-2 font-medium">— @isabekoff_thoughts <span class="text-[#4a4a50] mx-1">·</span> Tech creator</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
