import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // ── Scroll reveal directive ───────────────────────────────────────
        app.directive('reveal', {
            mounted(el, binding) {
                const delay  = binding.value?.delay  ?? 0;
                const from   = binding.value?.from   ?? 'bottom'; // bottom | left | right | scale
                const amount = binding.value?.amount ?? 28;

                const transforms = {
                    bottom: `translateY(${amount}px)`,
                    left:   `translateX(-${amount}px)`,
                    right:  `translateX(${amount}px)`,
                    scale:  `scale(0.92)`,
                };

                el.style.opacity   = '0';
                el.style.transform = transforms[from] ?? transforms.bottom;
                el.style.transition = `opacity 0.7s cubic-bezier(.4,0,.2,1) ${delay}ms, transform 0.7s cubic-bezier(.4,0,.2,1) ${delay}ms`;

                const obs = new IntersectionObserver(([entry]) => {
                    if (entry.isIntersecting) {
                        el.style.opacity   = '1';
                        el.style.transform = 'none';
                        obs.disconnect();
                    }
                }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

                obs.observe(el);
            },
        });

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
