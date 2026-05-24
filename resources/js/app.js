import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';

createInertiaApp({
    title: (title) => title ? `${title} — Influence` : 'Influence',
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        if (!el) return;
        
        const ziggy = window.Ziggy || props.initialPage.props.ziggy || {};
        
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, ziggy)  // ← window.Ziggy ishlamasa fallback
            .mount(el);
    },
    progress: {
        color: '#6366f1',
        showSpinner: true,
    },
});