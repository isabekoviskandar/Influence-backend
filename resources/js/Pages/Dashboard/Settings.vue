<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    bot_username:  String,
    telegram_link: String,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const form = useForm({
    username: user.value?.username ?? '',
    phone:    user.value?.phone    ?? '',
    bio:      user.value?.bio      ?? '',
});

function saveProfile() {
    form.patch('/dashboard/settings');
}

// Telegram linking
const telegramLink = ref(props.telegram_link);
const linkCopied   = ref(false);
const linkLoading  = ref(false);

async function refreshLink() {
    linkLoading.value = true;
    try {
        const res  = await fetch('/dashboard/settings/telegram-link');
        const data = await res.json();
        telegramLink.value = data.url;
    } finally {
        linkLoading.value = false;
    }
}

function copyLink() {
    if (telegramLink.value) {
        navigator.clipboard.writeText(telegramLink.value);
        linkCopied.value = true;
        setTimeout(() => (linkCopied.value = false), 2000);
    }
}
</script>

<template>
    <DashboardLayout>

        <div class="max-w-2xl">

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-white">Settings</h1>
                <p class="text-gray-500 text-sm mt-1">Manage your profile and integrations</p>
            </div>

            <!-- Profile card -->
            <div class="bg-[#16161f] border border-white/5 rounded-2xl p-6 mb-6">
                <h2 class="text-base font-semibold text-white mb-5">Profile</h2>

                <!-- Avatar -->
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-xl font-bold text-white">
                        {{ user?.name?.[0]?.toUpperCase() ?? 'U' }}
                    </div>
                    <div>
                        <p class="font-semibold text-white">{{ user?.name }}</p>
                        <p class="text-sm text-gray-500">{{ user?.email }}</p>
                        <span class="text-xs px-2 py-0.5 rounded-full mt-1 inline-block"
                              :class="user?.plan === 'pro' ? 'bg-amber-500/20 text-amber-400' : 'bg-indigo-500/20 text-indigo-400'">
                            {{ user?.plan === 'pro' ? '⭐ Pro' : '🆓 Free Plan' }}
                        </span>
                    </div>
                </div>

                <form @submit.prevent="saveProfile" class="space-y-4">
                    <!-- Username -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Username</label>
                        <input
                            v-model="form.username"
                            type="text"
                            placeholder="yourusername"
                            class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 text-sm outline-none transition-all focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50"
                        />
                        <p v-if="form.errors.username" class="mt-1 text-xs text-red-400">{{ form.errors.username }}</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Phone</label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            placeholder="+998901234567"
                            class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 text-sm outline-none transition-all focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50"
                        />
                        <p v-if="form.errors.phone" class="mt-1 text-xs text-red-400">{{ form.errors.phone }}</p>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Bio</label>
                        <textarea
                            v-model="form.bio"
                            rows="3"
                            placeholder="Tell us a bit about yourself…"
                            class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 text-sm outline-none transition-all focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 resize-none"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 rounded-lg text-sm font-semibold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white transition-all shadow-lg shadow-indigo-500/20 disabled:opacity-60"
                    >
                        {{ form.processing ? 'Saving…' : 'Save changes' }}
                    </button>
                </form>
            </div>

            <!-- Telegram integration card -->
            <div class="bg-[#16161f] border border-white/5 rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-sky-500/20 flex items-center justify-center text-sky-400">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-2.04 9.607c-.148.658-.557.818-1.126.508l-3.107-2.29-1.5 1.445c-.165.165-.305.305-.625.305l.222-3.157 5.75-5.193c.25-.222-.054-.345-.386-.123L7.25 14.77l-3.056-.955c-.663-.21-.678-.663.138-.98l11.934-4.602c.553-.2 1.035.137.296.015z"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-semibold text-white">Telegram Integration</h2>
                </div>

                <!-- Already linked -->
                <div v-if="user?.telegram_linked" class="flex items-center gap-3 p-4 bg-green-500/10 border border-green-500/20 rounded-xl">
                    <span class="text-green-400 text-xl">✅</span>
                    <div>
                        <p class="text-sm font-medium text-green-400">Telegram connected</p>
                        <p class="text-xs text-gray-500">
                            {{ user.telegram_username ? '@' + user.telegram_username : 'Account linked' }}
                        </p>
                    </div>
                </div>

                <!-- Not linked -->
                <div v-else>
                    <p class="text-sm text-gray-400 mb-4">
                        Connect your Telegram account to receive notifications and manage channels directly from the bot.
                    </p>

                    <div v-if="telegramLink" class="space-y-3">
                        <!-- Link input -->
                        <div class="flex gap-2">
                            <input
                                :value="telegramLink"
                                readonly
                                class="flex-1 px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-xs text-gray-400 outline-none truncate"
                            />
                            <button
                                @click="copyLink"
                                class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-xs text-gray-400 hover:text-white hover:border-white/20 transition-all"
                            >
                                {{ linkCopied ? '✅' : '📋' }}
                            </button>
                        </div>

                        <!-- CTA buttons -->
                        <div class="flex gap-2">
                            <a
                                :href="telegramLink"
                                target="_blank"
                                class="flex-1 py-2.5 text-center rounded-lg text-sm font-semibold bg-sky-500 hover:bg-sky-400 text-white transition-all"
                            >
                                Open in Telegram
                            </a>
                            <button
                                @click="refreshLink"
                                :disabled="linkLoading"
                                class="px-4 py-2.5 rounded-lg text-sm text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 transition-all"
                            >
                                {{ linkLoading ? '…' : '🔄' }}
                            </button>
                        </div>

                        <p class="text-xs text-gray-600">Link expires in 15 minutes. Click 🔄 to refresh.</p>
                    </div>
                </div>

                <!-- Add channel instructions -->
                <div class="mt-6 p-4 bg-white/[0.03] rounded-xl border border-white/5">
                    <p class="text-xs font-semibold text-gray-400 mb-2">📡 How to add a channel</p>
                    <ol class="text-xs text-gray-500 space-y-1 list-decimal list-inside">
                        <li>Open your Telegram channel settings</li>
                        <li>Go to Administrators → Add Administrator</li>
                        <li>Search for <span class="text-indigo-400 font-medium">@{{ bot_username }}</span></li>
                        <li>Grant at minimum "Post Messages" permission</li>
                        <li>Analytics will start tracking automatically ✅</li>
                    </ol>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>
