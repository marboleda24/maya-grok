<template>
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-bold">Bienvenido a la Gestión de Portafolios</h1>
        <p class="mt-4">Por favor, inicia sesión o regístrate para comenzar.</p>
        <div class="mt-4">
            <Link href="/login" class="text-blue-500 hover:underline mr-4">Iniciar Sesión</Link>
            <Link href="/register" class="text-blue-500 hover:underline">Registrarse</Link>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue';
import { Link } from '@inertiajs/vue3';

export default defineComponent({
    components: { Link }
});
</script>