<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Encabezado -->
        <header class="bg-white shadow">
            <div class="w-full p-6 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Gestión de Portafolios</h1>
                <nav class="space-x-4">
                    <Link :href="route('portfolios.index')" class="text-blue-600 hover:underline">Portafolios</Link>
                    <Link :href="route('strategies.index')" class="text-blue-600 hover:underline">Estrategias</Link>
                    <Link :href="route('logout')" method="post" as="button" class="text-red-600 hover:underline">Cerrar Sesión</Link>
                </nav>
            </div>
        </header>

        <!-- Mensaje Flash -->
        <div v-if="$page.props.flash && $page.props.flash.message" class="fixed top-4 right-4 bg-green-100 text-green-700 p-4 rounded-lg shadow z-50 animate-fade-in">
            {{ $page.props.flash.message }}
        </div>

        <!-- Contenido Principal -->
        <main class="w-full p-6">
            <slot />
        </main>

        <!-- Pie de Página -->
        <footer class="bg-white shadow-inner mt-6 p-4 text-center text-gray-600">
            <p>&copy; 2025 Gestión de Portafolios. Todos los derechos reservados.</p>
        </footer>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    components: { Link }
};
</script>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.animate-fade-in {
    animation: fadeIn 0.5s ease-in;
}
</style>