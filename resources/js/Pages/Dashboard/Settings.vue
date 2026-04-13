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

function submit() {
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

        <div class="max-w-6xl">

            <div class="mb-12">
                <h1 class="text-3xl font-black text-white tracking-tight">Account Intelligence</h1>
                <p class="text-gray-600 text-sm mt-1 uppercase font-bold tracking-widest">Manage your neural identity and integrations</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">
                
                <!-- Profile Section (2/3 width) -->
                <div class="xl:col-span-2 space-y-8">
                    <form @submit.prevent="submit" class="bg-[#111118] border border-white/[0.05] rounded-[2.5rem] p-10">
                        <div class="flex items-center justify-between mb-10">
                            <h2 class="text-lg font-black text-white tracking-tight uppercase">Profile Matrix</h2>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-8 py-3 rounded-xl text-xs font-black uppercase tracking-widest bg-indigo-600 hover:bg-indigo-500 text-white transition-all shadow-xl shadow-indigo-600/20 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Syncing...' : 'Save Changes' }}
                            </button>
                        </div>

                        <!-- Avatar Row -->
                        <div class="flex items-center gap-8 mb-12 pb-10 border-b border-white/[0.03]">
                            <div class="relative group">
                                <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-3xl font-black text-white shadow-2xl border border-white/10 overflow-hidden">
                                     <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover" />
                                     <span v-else>{{ user?.name?.[0] }}</span>
                                </div>
                                <label class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-3xl cursor-pointer backdrop-blur-sm">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <input type="file" class="hidden" @change="onAvatarChange" accept="image/*" />
                                </label>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-white tracking-tight">{{ user?.name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md" :class="user?.plan === 'pro' ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20'">
                                        {{ user?.plan === 'pro' ? '⭐ Pro Intel' : '🆓 Standard' }}
                                    </span>
                                    <p class="text-[10px] font-bold text-gray-700 uppercase tracking-widest">{{ user?.email }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- 2-Column Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-600 uppercase tracking-[0.2em] ml-1">Username</label>
                                <input v-model="form.username" type="text" class="w-full bg-black/40 border-white/[0.05] border focus:border-indigo-500/50 focus:ring-0 rounded-2xl px-5 py-3.5 text-sm font-bold text-white transition-all" />
                                <p v-if="form.errors.username" class="text-[10px] text-red-500 font-bold ml-1">{{ form.errors.username }}</p>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-600 uppercase tracking-[0.2em] ml-1">Email Node</label>
                                <input v-model="form.email" type="email" class="w-full bg-black/40 border-white/[0.05] border focus:border-indigo-500/50 focus:ring-0 rounded-2xl px-5 py-3.5 text-sm font-bold text-white transition-all" />
                                <p v-if="form.errors.email" class="text-[10px] text-red-500 font-bold ml-1">{{ form.errors.email }}</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-600 uppercase tracking-[0.2em] ml-1">Primary Phone</label>
                                <input v-model="form.phone" type="tel" class="w-full bg-black/40 border-white/[0.05] border focus:border-indigo-500/50 focus:ring-0 rounded-2xl px-5 py-3.5 text-sm font-bold text-white transition-all" />
                                <p v-if="form.errors.phone" class="text-[10px] text-red-500 font-bold ml-1">{{ form.errors.phone }}</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-600 uppercase tracking-[0.2em] ml-1">Account Bio</label>
                                <textarea v-model="form.bio" rows="1" class="w-full bg-black/40 border-white/[0.05] border focus:border-indigo-500/50 focus:ring-0 rounded-2xl px-5 py-3.5 text-sm font-bold text-white transition-all resize-none"></textarea>
                            </div>
                        </div>
                    </form>

                    <!-- Split Bottom Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Security Card -->
                        <div class="bg-[#111118] border border-white/[0.05] rounded-[2.5rem] p-8">
                            <h3 class="text-sm font-black text-white uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                Security Shield
                            </h3>
                            <form @submit.prevent="submit" class="space-y-4">
                                <input v-model="form.password" type="password" placeholder="New Neural Key" class="w-full bg-black/40 border-white/[0.05] border focus:border-indigo-500/50 focus:ring-0 rounded-xl px-5 py-3 text-xs font-bold text-white" />
                                <input v-model="form.password_confirmation" type="password" placeholder="Confirm Key" class="w-full bg-black/40 border-white/[0.05] border focus:border-indigo-500/50 focus:ring-0 rounded-xl px-5 py-3 text-xs font-bold text-white" />
                                <button type="submit" class="w-full py-3 bg-white/5 border border-white/5 rounded-xl text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-white hover:bg-white/10 transition-all">Update Key</button>
                            </form>
                        </div>

                        <!-- Integration Card (Static View) -->
                        <div class="bg-[#111118] border border-white/[0.05] rounded-[2.5rem] p-8">
                            <h3 class="text-sm font-black text-white uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                Telegram Node
                            </h3>
                            <div v-if="user?.telegram_linked" class="space-y-4">
                                <div class="p-4 bg-emerald-500/5 border border-emerald-500/10 rounded-2xl flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Status: Connected</p>
                                        <p class="text-xs font-bold text-white mt-0.5">@{{ user.telegram_username }}</p>
                                    </div>
                                    <div class="text-xl">✅</div>
                                </div>
                                <button class="w-full py-3 bg-white/5 border border-white/5 rounded-xl text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-white hover:bg-white/10 transition-all">Deep Settings</button>
                            </div>
                            <div v-else class="text-center py-6">
                                <p class="text-[10px] font-bold text-gray-600 uppercase mb-4 tracking-widest">No Node Connected</p>
                                <button @click="refreshLink" class="px-6 py-2 bg-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest text-white">Initialize</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone (1/3 width) -->
                <div class="space-y-8">
                    <div class="bg-red-500/[0.03] border border-red-500/10 rounded-[2.5rem] p-8">
                        <h3 class="text-sm font-black text-red-500 uppercase tracking-widest mb-6">Danger Zone</h3>
                        <p class="text-xs font-bold text-gray-600 leading-relaxed mb-6">
                            Disconnecting your Telegram node will stop all live tracking and viral detection for your associated channels.
                        </p>
                        
                        <div class="space-y-3">
                            <button v-if="user?.telegram_linked" class="w-full py-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-[10px] font-black uppercase tracking-widest text-red-500 hover:bg-red-500 hover:text-white transition-all">
                                Unlink Telegram Node
                            </button>
                            <button class="w-full py-4 rounded-2xl bg-white/5 border border-white/5 text-[10px] font-black uppercase tracking-widest text-gray-600 hover:text-red-400 hover:bg-red-500/5 transition-all">
                                Wipe Local Cache
                            </button>
                        </div>
                    </div>

                    <div class="bg-[#111118] border border-white/[0.05] rounded-[2.5rem] p-8">
                        <h3 class="text-sm font-black text-white uppercase tracking-widest mb-4">Neural Plan</h3>
                        <p class="text-2xl font-black text-white tracking-tighter mb-1">{{ user.plan === 'pro' ? 'Elite Pulse' : 'Standard Node' }}</p>
                        <p class="text-[10px] font-bold text-gray-600 uppercase tracking-widest mb-6">Active subscription</p>
                        
                        <div v-if="user.plan === 'free'" class="p-4 bg-indigo-500/10 border border-indigo-500/20 rounded-2xl mb-6">
                            <p class="text-[10px] font-bold text-indigo-400 leading-tight">Unlock viral detection and hourly sync with Elite Pulse.</p>
                        </div>
                        
                        <button class="w-full py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-[10px] font-black uppercase tracking-widest text-white shadow-xl shadow-indigo-600/20">
                            {{ user.plan === 'pro' ? 'Manage Billing' : 'Upgrade to Elite' }}
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>
