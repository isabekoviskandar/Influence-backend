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
    email:    user.value?.email    ?? '',
    phone:    user.value?.phone    ?? '',
    bio:      user.value?.bio      ?? '',
    avatar:   null,
    password: '',
    password_confirmation: '',
});

const avatarPreview = ref(user.value?.avatar ? `/storage/${user.value.avatar}` : null);

function onAvatarChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.avatar = file;
        const reader = new FileReader();
        reader.onload = (e) => (avatarPreview.value = e.target.result);
        reader.readAsDataURL(file);
    }
}

function saveProfile() {
    form.post('/dashboard/settings', {
        forceFormData: true,
        onSuccess: () => {
            form.password = '';
            form.password_confirmation = '';
        },
        queryParams: {
            _method: 'PATCH'
        }
    });
}

// Alternatively, Inertia has a method to explicitly spoof PATCH
function saveProfileV2() {
    form.transform((data) => ({
        ...data,
        _method: 'PATCH',
    })).post('/dashboard/settings', {
        forceFormData: true,
        onSuccess: () => {
            form.password = '';
            form.password_confirmation = '';
        }
    });
}
// Using the transformation approach
const submit = saveProfileV2;

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

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Avatar -->
                    <div class="flex items-center gap-5 mb-8">
                        <div class="relative group">
                            <img v-if="avatarPreview" :src="avatarPreview" class="w-20 h-20 rounded-2xl object-cover border border-white/10" />
                            <div v-else class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-2xl font-bold text-white border border-white/10">
                                {{ user?.name?.[0]?.toUpperCase() ?? 'U' }}
                            </div>
                            
                            <label class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl cursor-pointer">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <input type="file" class="hidden" @change="onAvatarChange" accept="image/*" />
                            </label>
                        </div>
                        <div>
                            <p class="font-bold text-white text-lg">{{ user?.name }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full mt-1 inline-block"
                                  :class="user?.plan === 'pro' ? 'bg-amber-500/20 text-amber-400' : 'bg-indigo-500/20 text-indigo-400'">
                                {{ user?.plan === 'pro' ? '⭐ Pro Account' : '🆓 Free Plan' }}
                            </span>
                            <p v-if="form.errors.avatar" class="mt-1 text-xs text-red-400">{{ form.errors.avatar }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="you@example.com"
                                class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 text-sm outline-none transition-all focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">{{ form.errors.email }}</p>
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
                        <p v-if="form.errors.bio" class="mt-1 text-xs text-red-400">{{ form.errors.bio }}</p>
                    </div>

                    <div class="pt-6 border-t border-white/5">
                        <h3 class="text-sm font-semibold text-white mb-4">Security</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1.5">New Password</label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="••••••••"
                                    class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 text-sm outline-none transition-all focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50"
                                />
                                <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">{{ form.errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1.5">Confirm Password</label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="••••••••"
                                    class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 text-sm outline-none transition-all focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-8 py-3 rounded-xl text-sm font-bold bg-indigo-600 hover:bg-indigo-500 text-white transition-all shadow-xl shadow-indigo-600/20 disabled:opacity-60"
                        >
                            {{ form.processing ? 'Syncing Profile…' : 'Save Changes' }}
                        </button>
                    </div>
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
