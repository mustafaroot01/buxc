import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
// Echo (Reverb WebSocket) initialized lazily after page load to avoid blocking first render
const initEcho = () => {
    import('@laravel/echo-vue').then(({ configureEcho }) => {
        configureEcho({ broadcaster: 'reverb' });
    });
};

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: false }),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);

        // Initialize Echo lazily after render, using idle time
        if ('requestIdleCallback' in window) {
            requestIdleCallback(initEcho);
        } else {
            setTimeout(initEcho, 500);
        }
    },
    progress: {
        delay: 250,
        color: '#4f46e5',
        includeCSS: true,
        showSpinner: true,
    },
});

// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(registration => {
            if (import.meta.env.DEV) console.log('SW registered:', registration);

            // Handle updates
            registration.onupdatefound = () => {
                const installingWorker = registration.installing;
                if (installingWorker) {
                    installingWorker.onstatechange = () => {
                        if (installingWorker.state === 'installed') {
                            if (navigator.serviceWorker.controller) {
                                if (import.meta.env.DEV) console.log('New content available; please refresh.');
                            } else {
                                if (import.meta.env.DEV) console.log('Content cached for offline use.');
                            }
                        }
                    };
                }
            };
        }).catch(registrationError => {
            if (import.meta.env.DEV) console.log('SW registration failed:', registrationError);
        });
    });

    // Reload the page when the service worker changes (e.g., skipWaiting was called)
    let refreshing = false;
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (!refreshing) {
            window.location.reload();
            refreshing = true;
        }
    });
}
