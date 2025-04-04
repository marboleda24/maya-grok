<template>
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Activos de {{ portfolio.name }}</h1>
        <Link :href="route('portfolios.index')" class="text-blue-500 hover:underline mb-4 inline-block">Volver a Portafolios</Link>
        <div v-if="flash && flash.message" class="bg-green-100 text-green-700 p-4 mb-4 rounded">
            {{ flash.message }}
        </div>
        <form @submit.prevent="form.post(route('assets.store', portfolio.id))" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input v-model="form.name" placeholder="Nombre" class="border p-2 rounded" required />
                <input v-model="form.symbol" placeholder="Símbolo (ej. AAPL)" class="border p-2 rounded" required />
                <textarea v-model="form.description" placeholder="Descripción" class="border p-2 rounded"></textarea>
                <input v-model="form.value" type="number" step="0.01" placeholder="Valor Inicial" class="border p-2 rounded" />
                <textarea v-model="form.comments" placeholder="Comentarios" class="border p-2 rounded"></textarea>
            </div>
            <button type="submit" class="mt-2 bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Crear Activo</button>
        </form>
        <div class="space-y-4">
            <div v-for="asset in assets" :key="asset.id" class="border p-4 rounded flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold">{{ asset.name }} ({{ asset.symbol }})</h2>
                    <p>{{ asset.description || 'Sin descripción' }}</p>
                    <p>Valor Actual: ${{ asset.value }}</p>
                    <p>Máximo: ${{ asset.max_value || 'N/A' }}</p>
                    <p>Base: ${{ asset.base_value || 'N/A' }}</p>
                    <p>Comentarios: {{ asset.comments || 'Sin comentarios' }}</p>
                    <p v-if="asset.strategy">Estrategia: Comprar a ${{ asset.strategy.buy_threshold }}, Vender a ${{ asset.strategy.sell_threshold }}</p>
                </div>
                <div class="space-x-2">
                    <button @click="editAsset(asset)" class="text-blue-500 hover:underline">Editar</button>
                    <button @click="deleteAsset(asset)" class="text-red-500 hover:underline">Eliminar</button>
                </div>
            </div>
        </div>
        <div v-if="editingAsset" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-xl mb-4">Editar Activo</h2>
                <form @submit.prevent="updateAsset">
                    <input ref="nameInput" v-model="editForm.name" placeholder="Nombre" class="border p-2 rounded w-full mb-2" required />
                    <input v-model="editForm.symbol" placeholder="Símbolo" class="border p-2 rounded w-full mb-2" required />
                    <textarea v-model="editForm.description" placeholder="Descripción" class="border p-2 rounded w-full mb-2"></textarea>
                    <input v-model="editForm.value" type="number" step="0.01" placeholder="Valor" class="border p-2 rounded w-full mb-2" />
                    <textarea v-model="editForm.comments" placeholder="Comentarios" class="border p-2 rounded w-full mb-2"></textarea>
                    <input v-model="editForm.buy_threshold" type="number" step="0.01" placeholder="Umbral de Compra" class="border p-2 rounded w-full mb-2" />
                    <input v-model="editForm.sell_threshold" type="number" step="0.01" placeholder="Umbral de Venta" class="border p-2 rounded w-full mb-2" />
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="editingAsset = null" class="text-gray-500 hover:underline">Cancelar</button>
                        <button type="submit" class="bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

export default defineComponent({
    props: ['portfolio', 'assets', 'flash'],
    components: { Link },
    setup() {
        const form = useForm({
            name: '',
            symbol: '',
            description: '',
            value: '',
            comments: ''
        });

        const editForm = useForm({
            name: '',
            symbol: '',
            description: '',
            value: '',
            comments: '',
            buy_threshold: '',
            sell_threshold: ''
        });

        return { form, editForm };
    },
    data() {
        return {
            editingAsset: null
        };
    },
    methods: {
        editAsset(asset) {
            this.editingAsset = asset;
            this.editForm.name = asset.name;
            this.editForm.symbol = asset.symbol;
            this.editForm.description = asset.description;
            this.editForm.value = asset.value;
            this.editForm.comments = asset.comments;
            this.editForm.buy_threshold = asset.strategy?.buy_threshold || '';
            this.editForm.sell_threshold = asset.strategy?.sell_threshold || '';

            this.$nextTick(() => {
                const input = this.$refs.nameInput;
                input.focus();
                input.setSelectionRange(input.value.length, input.value.length);
            });
        },
        updateAsset() {
            this.editForm.put(route('assets.update', this.editingAsset.id), {
                onSuccess: () => {
                    this.editingAsset = null;
                    // Actualizar estrategia
                    this.$inertia.post(route('assets.index', this.editingAsset.portfolio_id), {
                        strategy: {
                            asset_id: this.editingAsset.id,
                            buy_threshold: this.editForm.buy_threshold,
                            sell_threshold: this.editForm.sell_threshold
                        }
                    });
                }
            });
        },
        deleteAsset(asset) {
            if (confirm('¿Estás seguro de eliminar este activo?')) {
                this.$inertia.delete(route('assets.destroy', asset.id));
            }
        }
    }
});
</script>