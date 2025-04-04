import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy'; //Asi estaba nativamente
//import { ZiggyVue } from 'ziggy-js'; //asi me sugiere Grok

// Función de resolución ajustada para subcarpetas
const pages = import.meta.glob('./Pages/**/*.vue', { eager: false });

createInertiaApp({
    resolve: (name) => {
        // Aseguramos que el nombre se convierta en una ruta válida
        const pagePath = `./Pages/${name}.vue`;
        const importPage = pages[pagePath];
        if (!importPage) {
            throw new Error(`Unknown page ${name}. Is it located in resources/js/Pages with a .vue extension?`);
        }
        return importPage();
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue) //Adicionado cuando se configuró Ziggy, que sirve para utilizar route() como enrutador en lugar de link estaticos            
            .mount(el);
    },
});