<template>
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Crear Estrategia</h1>
        <Link :href="route('strategies.index')" class="text-blue-500 hover:underline mb-4 inline-block">Volver a Estrategias</Link>
        <form @submit.prevent="form.post(route('strategies.store'))" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input id="name" v-model="form.name" class="border p-2 rounded w-full" required />
            </div>
            <div>
                <label for="buy_threshold" class="block text-sm font-medium text-gray-700">Umbral de Compra (0-1)</label>
                <input id="buy_threshold" v-model="form.buy_threshold" type="number" step="0.01" min="0" max="1" class="border p-2 rounded w-full" required />
            </div>
            <div>
                <label for="sell_threshold" class="block text-sm font-medium text-gray-700">Umbral de Venta</label>
                <input id="sell_threshold" v-model="form.sell_threshold" type="number" step="0.01" min="0" class="border p-2 rounded w-full" required />
            </div>
            <div>
                <label for="techo_threshold" class="block text-sm font-medium text-gray-700">Umbral de Techo (0-1)</label>
                <input id="techo_threshold" v-model="form.techo_threshold" type="number" step="0.01" min="0" max="1" class="border p-2 rounded w-full" required />
            </div>
            <div>
                <label for="minimum_open_operations" class="block text-sm font-medium text-gray-700">Mínimo de Operaciones Abiertas</label>
                <input id="minimum_open_operations" v-model="form.minimum_open_operations" type="number" min="1" class="border p-2 rounded w-full" required />
            </div>
            <div>
                <label for="comments" class="block text-sm font-medium text-gray-700">Comentarios</label>
                <textarea id="comments" v-model="form.comments" class="border p-2 rounded w-full"></textarea>
            </div>
            <button type="submit" class="bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Guardar</button>
        </form>
    </div>
</template>

<script>
import { defineComponent } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

export default defineComponent({
    components: { Link },
    setup() {
        const form = useForm({
            name: '',
            buy_threshold: '',
            sell_threshold: '',
            techo_threshold: '',
            minimum_open_operations: '',
            comments: ''
        });

        return { form };
    }
});
</script>