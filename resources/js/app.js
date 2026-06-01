import './bootstrap';
import '../css/app.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    withApp(app) {
        app.use(ZiggyVue);
    },
    progress: {
        color: '#4B5563',
    },
});
