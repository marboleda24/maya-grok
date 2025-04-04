<template>
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
        <p>Bienvenido, {{ $page.props.auth.user.name }}</p>
        <div class="mt-4">
            <Link :href="route('portfolios.index')" class="text-blue-500 hover:underline mr-4">Ver Portafolios</Link>
            <button @click="logout" class="text-red-500 hover:underline">Cerrar Sesión</button>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';

export default defineComponent({
    components: { Link },
    setup() {
        const form = useForm({});
        return { form };
    },
    methods: {
        logout() {
            this.form.post(route('logout'), {
                onSuccess: () => {
                    this.$inertia.visit(route('welcome'));
                }
            });
        }
    }
});
</script>