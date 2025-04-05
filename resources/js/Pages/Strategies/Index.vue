<template>
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Estrategias</h1>
        <Link :href="route('strategies.create')" class="bg-blue-500 text-white p-2 rounded hover:bg-blue-600 mb-4 inline-block">Crear Estrategia</Link>
        <div v-if="flash && flash.message" class="bg-green-100 text-green-700 p-4 mb-4 rounded">
            {{ flash.message }}
        </div>
        <div class="space-y-4">
            <div v-for="strategy in strategies" :key="strategy.id" class="border p-4 rounded flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold">{{ strategy.name }}</h2>
                    <p>Comprar a: {{ strategy.buy_threshold * 100 }}%</p>
                    <p>Vender a: {{ strategy.sell_threshold * 100 }}%</p>
                    <p>Techo: {{ strategy.techo_threshold * 100 }}%</p>
                    <p>Mínimo Operaciones Abiertas: {{ strategy.minimum_open_operations }}</p>
                    <p>Comentarios: {{ strategy.comments || 'Sin comentarios' }}</p>
                </div>
                <div class="space-x-2">
                    <Link :href="route('strategies.edit', strategy.id)" class="text-blue-500 hover:underline">Editar</Link>
                    <button @click="deleteStrategy(strategy)" class="text-red-500 hover:underline">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue';
import { Link } from '@inertiajs/vue3';

export default defineComponent({
    props: ['strategies', 'flash'],
    components: { Link },
    methods: {
        deleteStrategy(strategy) {
            if (confirm('¿Estás seguro de eliminar esta estrategia?')) {
                this.$inertia.delete(route('strategies.destroy', strategy.id));
            }
        }
    }
});
</script>