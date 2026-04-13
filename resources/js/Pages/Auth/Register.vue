<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    username: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register');
}
</script>

<template>
    <div class="min-h-screen bg-[#0f0f14] flex items-center justify-center p-4">

        <!-- Background glow -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/3 right-1/3 w-80 h-80 bg-purple-600/10 rounded-full blur-3xl" />
            <div class="absolute bottom-1/3 left-1/4 w-64 h-64 bg-indigo-600/10 rounded-full blur-3xl" />
        </div>

        <div class="relative w-full max-w-md">

            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30 mb-4">
                    <span class="text-white font-bold text-xl">I</span>
                </div>
                <h1 class="text-2xl font-bold text-white">Create your account</h1>
                <p class="text-sm text-gray-500 mt-1">Start tracking your Telegram channels</p>
            </div>

            <!-- Card -->
            <div class="bg-[#16161f] border border-white/5 rounded-2xl p-8 shadow-2xl">

                <form @submit.prevent="submit" class="space-y-4">

                    <!-- Username -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Username</label>
                        <input
                            v-model="form.username"
                            type="text"
                            autocomplete="username"
                            placeholder="johndoe"
                            class="w-full px-4 py-2.5 bg-white/5 border rounded-lg text-white placeholder-gray-600 text-sm outline-none transition-all focus:ring-2 focus:ring-indigo-500/50"
                            :class="form.errors.username ? 'border-red-500/50' : 'border-white/10 focus:border-indigo-500/50'"
                        />
                        <p v-if="form.errors.username" class="mt-1 text-xs text-red-400">{{ form.errors.username }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            placeholder="you@example.com"
                            class="w-full px-4 py-2.5 bg-white/5 border rounded-lg text-white placeholder-gray-600 text-sm outline-none transition-all focus:ring-2 focus:ring-indigo-500/50"
                            :class="form.errors.email ? 'border-red-500/50' : 'border-white/10 focus:border-indigo-500/50'"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">{{ form.errors.email }}</p>
                    </div>

                    <!-- Phone (optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">
                            Phone <span class="text-gray-600">(optional)</span>
                        </label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            placeholder="+998901234567"
                            class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 text-sm outline-none transition-all focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Min. 8 characters"
                            class="w-full px-4 py-2.5 bg-white/5 border rounded-lg text-white placeholder-gray-600 text-sm outline-none transition-all focus:ring-2 focus:ring-indigo-500/50"
                            :class="form.errors.password ? 'border-red-500/50' : 'border-white/10 focus:border-indigo-500/50'"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Confirm Password</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            placeholder="••••••••"
                            class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 text-sm outline-none transition-all focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50"
                        />
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-2.5 rounded-lg font-semibold text-sm text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 transition-all shadow-lg shadow-indigo-500/20 disabled:opacity-60 disabled:cursor-not-allowed mt-2"
                    >
                        <span v-if="form.processing">Creating account…</span>
                        <span v-else>Create account</span>
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Already have an account?
                    <Link href="/login" class="text-indigo-400 hover:text-indigo-300 font-medium">Sign in</Link>
                </p>
            </div>
        </div>
    </div>
</template>
