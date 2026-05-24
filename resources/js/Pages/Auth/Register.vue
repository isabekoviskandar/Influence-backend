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
    let s = 1;
    if (p.length >= 8) s = 2;
    if (p.length >= 8 && /[A-Z]/.test(p) && /[0-9]/.test(p)) s = 3;
    if (p.length >= 8 && /[A-Z]/.test(p) && /[0-9]/.test(p) && /[^A-Za-z0-9]/.test(p)) s = 4;
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
    if (strength === 3 && index <= 3) return 'bg-[#58a6ff]/60';
    if (strength === 4 && index <= 4) return 'bg-[#58a6ff]';
    return 'bg-[#222224]';
}
</script>

<template>
    <div class="min-h-screen bg-[#0d1117] text-[#e6edf3]">
        <div class="container-custom mx-auto px-4 py-8">
            <header class="flex flex-col gap-4 border-b border-[#30363d] pb-6 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-[700] uppercase tracking-[0.35em] text-[#e6edf3]" style="font-family: 'JetBrains Mono', ui-monospace, monospace;">INFLUENCE</span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#30363d] bg-[#161b22] px-3 py-1 text-[0.72rem] uppercase tracking-[0.18em] text-[#79c0ff]" style="font-family: 'JetBrains Mono', ui-monospace, monospace;">
                        <span class="h-2 w-2 rounded-full bg-[#3fb950]"></span>
                        LIVE
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-sm text-[#8b949e]">
                    <a href="/" class="transition hover:text-[#e6edf3]">Home</a>
                    <Link href="/login" class="rounded-full border border-[#30363d] bg-[#161b22] px-4 py-2 text-[#79c0ff] transition hover:border-[#58a6ff] hover:text-[#e6edf3]">Sign in</Link>
                </div>
            </header>

            <div class="mt-10 grid gap-8 lg:grid-cols-[1fr_0.95fr]">
                <section class="rounded-[28px] border border-[#30363d] bg-[#161b22] p-8 lg:p-10">
                    <div class="space-y-4">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[#30363d] bg-[#0f161f] px-3 py-1 text-[0.75rem] uppercase tracking-[0.26em] text-[#79c0ff]" style="font-family: 'JetBrains Mono', ui-monospace, monospace;">Start tracking</span>
                        <h1 class="text-4xl font-[800] leading-[1.02] tracking-[-0.03em] text-[#e6edf3]">Create your account.</h1>
                        <p class="max-w-2xl text-[#8b949e]">Connect your Telegram channel and get live analytics, velocity charts, and viral signals in one place.</p>
                    </div>

                    <form @submit.prevent="submit" class="mt-10 space-y-5">
                        <div>
                            <label for="username" class="block text-[12px] font-[500] text-[#8b949e] mb-2">Username</label>
                            <input
                                id="username"
                                type="text"
                                v-model="form.username"
                                placeholder="johndoe"
                                class="w-full rounded-[16px] border border-[#30363d] bg-[#0f161f] px-4 py-3 text-sm text-[#e6edf3] placeholder:text-[#6e7681] outline-none transition focus:border-[#58a6ff]"
                                required
                                autofocus
                            />
                            <div v-if="form.errors.username" class="mt-2 text-[12px] text-[#f87171]">{{ form.errors.username }}</div>
                        </div>

                        <div>
                            <label for="email" class="block text-[12px] font-[500] text-[#8b949e] mb-2">Email address</label>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                placeholder="you@example.com"
                                class="w-full rounded-[16px] border border-[#30363d] bg-[#0f161f] px-4 py-3 text-sm text-[#e6edf3] placeholder:text-[#6e7681] outline-none transition focus:border-[#58a6ff]"
                                required
                            />
                            <div v-if="form.errors.email" class="mt-2 text-[12px] text-[#f87171]">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2 text-[12px] font-[500] text-[#8b949e]">
                                <label for="password">Password</label>
                                <span class="text-[#6e7681]">Min. 8 characters</span>
                            </div>
                            <div class="relative">
                                <input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password"
                                    placeholder="Enter password"
                                    class="w-full rounded-[16px] border border-[#30363d] bg-[#0f161f] px-4 py-3 pr-12 text-sm text-[#e6edf3] placeholder:text-[#6e7681] outline-none transition focus:border-[#58a6ff]"
                                    required
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[#8b949e] hover:text-[#e6edf3]"
                                >
                                    <span v-if="!showPassword">Show</span>
                                    <span v-else>Hide</span>
                                </button>
                            </div>
                            <div class="flex gap-1 mt-3 mb-1 w-full h-[3px]">
                                <div class="flex-1 rounded-[2px] transition-colors duration-300" :class="getStrengthClasses(1, passwordStrength)"></div>
                                <div class="flex-1 rounded-[2px] transition-colors duration-300" :class="getStrengthClasses(2, passwordStrength)"></div>
                                <div class="flex-1 rounded-[2px] transition-colors duration-300" :class="getStrengthClasses(3, passwordStrength)"></div>
                                <div class="flex-1 rounded-[2px] transition-colors duration-300" :class="getStrengthClasses(4, passwordStrength)"></div>
                            </div>
                            <div v-if="form.errors.password" class="mt-2 text-[12px] text-[#f87171]">{{ form.errors.password }}</div>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-[12px] font-[500] text-[#8b949e] mb-2">Confirm password</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                placeholder="Repeat password"
                                :class="[
                                    'w-full rounded-[16px] border px-4 py-3 text-sm text-[#e6edf3] placeholder:text-[#6e7681] outline-none transition focus:ring-0',
                                    confirmMatch === false ? 'border-red-500/50 focus:border-red-500 bg-[#1f151d]' : 'border-[#30363d] bg-[#0f161f] focus:border-[#58a6ff]'
                                ]"
                                required
                            />
                            <div v-if="form.errors.password_confirmation" class="mt-2 text-[12px] text-[#f87171]">{{ form.errors.password_confirmation }}</div>
                        </div>

                        <p class="text-[11px] text-[#8b949e]">By creating an account you agree to our <a href="#" class="text-[#58a6ff] hover:text-[#79c0ff]">Terms</a> and <a href="#" class="text-[#58a6ff] hover:text-[#79c0ff]">Privacy Policy</a>.</p>

                        <button type="submit" :disabled="form.processing" class="w-full rounded-full bg-[#58a6ff] px-6 py-3 text-sm font-semibold text-[#0d1117] transition hover:bg-[#3f8cee] disabled:cursor-not-allowed disabled:opacity-70">
                            <span v-if="form.processing">Creating account...</span>
                            <span v-else>Create account</span>
                        </button>
                    </form>

                    <div class="mt-6 border-t border-[#30363d] pt-6 text-sm text-[#8b949e]">
                        <p>Already have an account? <Link href="/login" class="text-[#58a6ff] hover:text-[#79c0ff]">Sign in</Link></p>
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="rounded-[28px] border border-[#30363d] bg-[#161b22] p-6">
                        <div class="flex items-center justify-between text-[0.72rem] uppercase tracking-[0.28em] text-[#79c0ff] font-[700]">
                            <span>Onboarding</span>
                            <span class="text-[#8b949e]">Fast</span>
                        </div>
                        <div class="mt-7 space-y-4 text-sm text-[#8b949e] leading-7">
                            <div class="rounded-3xl bg-[#0d1117] p-4">Connect your Telegram channel and verify ownership.</div>
                            <div class="rounded-3xl bg-[#0d1117] p-4">Sync historical posts for peak-hour and velocity insights.</div>
                            <div class="rounded-3xl bg-[#0d1117] p-4">Get a heatmap, forward ratio, and viral score in minutes.</div>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-[#30363d] bg-[#161b22] p-6">
                        <p class="text-[0.72rem] uppercase tracking-[0.28em] text-[#79c0ff] font-[700]">Why Influence</p>
                        <ul class="mt-6 space-y-4 text-sm text-[#8b949e] leading-7">
                            <li>Built for Telegram metrics, not generic advice.</li>
                            <li>Real-time channel signals with no guesswork.</li>
                            <li>Monospace-friendly dashboards for fast reading.</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</template>
