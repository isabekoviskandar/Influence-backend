<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    email: '',
    password: '',
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    });
};
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
                    <Link href="/register" class="rounded-full border border-[#30363d] bg-[#161b22] px-4 py-2 text-[#79c0ff] transition hover:border-[#58a6ff] hover:text-[#e6edf3]">Create account</Link>
                </div>
            </header>

            <div class="mt-10 grid gap-8 lg:grid-cols-[1fr_0.95fr]">
                <section class="rounded-[28px] border border-[#30363d] bg-[#161b22] p-8 lg:p-10">
                    <div class="space-y-4">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[#30363d] bg-[#0f161f] px-3 py-1 text-[0.75rem] uppercase tracking-[0.26em] text-[#79c0ff]" style="font-family: 'JetBrains Mono', ui-monospace, monospace;">Secure access</span>
                        <h1 class="text-4xl font-[800] leading-[1.02] tracking-[-0.03em] text-[#e6edf3]">Sign in to your dashboard.</h1>
                        <p class="max-w-2xl text-[#8b949e]">Access live Telegram analytics, peak windows, and channel health from a terminal-inspired workspace.</p>
                    </div>

                    <form @submit.prevent="submit" class="mt-10 space-y-5">
                        <div>
                            <label for="email" class="block text-[12px] font-[500] text-[#8b949e] mb-2">Email address</label>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                placeholder="you@example.com"
                                class="w-full rounded-[16px] border border-[#30363d] bg-[#0f161f] px-4 py-3 text-sm text-[#e6edf3] placeholder:text-[#6e7681] outline-none transition focus:border-[#58a6ff]"
                                required
                                autofocus
                            />
                            <div v-if="form.errors.email" class="mt-2 text-[12px] text-[#f87171]">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2 text-[12px] font-[500] text-[#8b949e]">
                                <label for="password">Password</label>
                                <Link href="#" class="text-[#58a6ff] hover:text-[#79c0ff]">Forgot password?</Link>
                            </div>
                            <div class="relative">
                                <input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password"
                                    placeholder="••••••••"
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
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-full bg-[#58a6ff] px-6 py-3 text-sm font-semibold text-[#0d1117] transition hover:bg-[#3f8cee] disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            <span v-if="form.processing">Signing in...</span>
                            <span v-else>Sign in</span>
                        </button>
                    </form>

                    <div class="mt-6 border-t border-[#30363d] pt-6 text-sm text-[#8b949e]">
                        <p>New to Influence? <Link href="/register" class="text-[#58a6ff] hover:text-[#79c0ff]">Create an account</Link></p>
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="rounded-[28px] border border-[#30363d] bg-[#161b22] p-6">
                        <div class="flex items-center justify-between text-[0.72rem] uppercase tracking-[0.28em] text-[#79c0ff] font-[700]">
                            <span>Live snapshot</span>
                            <span class="text-[#8b949e]">Real-time</span>
                        </div>
                        <div class="mt-7 space-y-4 text-sm text-[#e6edf3] font-[500] leading-7">
                            <div class="rounded-3xl bg-[#0d1117] p-4">
                                <div class="text-[#79c0ff]">@uzbekistan_news</div>
                                <div class="mt-2 text-[#8b949e]">Subscribers ████████ 128K</div>
                            </div>
                            <div class="rounded-3xl bg-[#0d1117] p-4">
                                <div class="text-[#3fb950]">▲ 2.4x spike</div>
                                <div class="mt-2 text-[#8b949e]">Best window 19:00 – 21:00</div>
                            </div>
                            <div class="rounded-3xl bg-[#0d1117] p-4">
                                <div class="text-[#79c0ff]">Forward ratio</div>
                                <div class="mt-2 text-[#8b949e]">Real virality signals, not guesswork.</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-[#30363d] bg-[#161b22] p-6">
                        <p class="text-[0.72rem] uppercase tracking-[0.28em] text-[#79c0ff] font-[700]">Why it matters</p>
                        <ul class="mt-6 space-y-4 text-sm text-[#8b949e] leading-7">
                            <li>See actual peak hours instead of posting blind.</li>
                            <li>Track channel velocity with live metrics.</li>
                            <li>Compare real forward ratio across posts.</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</template>
