<template>
    <div class="w-full p-6 bg-gray-100 min-h-screen">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Activos de {{ portfolio.name }}</h1>
        <Link :href="route('portfolios.index')" class="text-blue-600 hover:underline mb-6 inline-block">Volver a Portafolios</Link>
        <div v-if="flash && flash.message" class="bg-green-100 text-green-700 p-4 mb-6 rounded-lg shadow">
            {{ flash.message }}
        </div>

        <!-- Formulario de creación de activo -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <form @submit.prevent="form.post(route('assets.store', portfolio.id))" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input v-model="form.name" placeholder="Nombre" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                <input v-model="form.symbol" placeholder="Símbolo (ej. AAPL)" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                <select v-model="form.strategy_id" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Sin estrategia</option>
                    <option v-for="strategy in strategies" :key="strategy.id" :value="strategy.id">{{ strategy.name }}</option>
                </select>
                <textarea v-model="form.comments" placeholder="Comentarios" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 col-span-1 md:col-span-4"></textarea>
                <button type="submit" class="bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition col-span-1 md:col-span-4">Crear Activo</button>
            </form>
        </div>

        <!-- Grid de Activos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="asset in assets" :key="asset.id" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                <h2 class="text-xl font-semibold text-gray-800">{{ asset.name }} ({{ asset.symbol }})</h2>
                <p class="text-gray-600">Valor Actual: <span class="font-medium">${{ asset.current_price }}</span></p>
                <p class="text-gray-600">Techo: <span class="font-medium">${{ asset.highest_price_reached }}</span></p>
                <p class="text-gray-600">Piso: <span class="font-medium">${{ asset.lowest_price_bought }}</span></p>
                <p class="text-gray-600">Monitoreo: <span class="font-medium">${{ asset.monitoring_point }}</span></p>
                <p class="text-gray-600">Comentarios: <span class="italic">{{ asset.comments || 'Sin comentarios' }}</span></p>
                <p v-if="asset.strategy" class="text-gray-600">Estrategia: <span class="font-medium">{{ asset.strategy.name }}</span></p>

                <!-- Grid de Operaciones -->
                <div v-if="asset.operations.length" class="mt-4">
                    <p class="text-gray-700 font-medium">Operaciones:</p>
                    <div class="grid grid-cols-1 gap-3 mt-2">
                        <div v-for="op in asset.operations" :key="op.id" class="bg-gray-50 p-3 rounded-lg border">
                            <p class="text-sm text-gray-600">
                                {{ op.status === 'open' ? 'Abierta' : 'Cerrada' }} - 
                                Compra: ${{ op.purchase_price }} ({{ op.quantity }}) en {{ op.exchange }} el {{ op.created_at }}
                                <span v-if="op.status === 'closed'"> - Venta: ${{ op.sale_price }} el {{ op.closed_at }} (Rentabilidad: {{ (op.profitability * 100).toFixed(2) }}%)</span>
                            </p>
                            <button v-if="op.status === 'open'" @click="sellOperation(op)" class="text-red-500 hover:underline text-sm mt-1">Vender</button>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="mt-4 flex space-x-3">
                    <button @click="editAsset(asset)" class="text-blue-500 hover:underline text-sm">Editar</button>
                    <button @click="operateAsset(asset)" class="text-green-500 hover:underline text-sm">Comprar</button>
                    <button @click="deleteAsset(asset)" class="text-red-500 hover:underline text-sm">Eliminar</button>
                </div>
            </div>
        </div>

        <!-- Popup de Edición -->
        <div v-if="editingAsset" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Editar Activo</h2>
                <form @submit.prevent="updateAsset" class="grid grid-cols-1 gap-4">
                    <input ref="nameInput" v-model="editForm.name" placeholder="Nombre" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                    <input v-model="editForm.symbol" placeholder="Símbolo" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                    <select v-model="editForm.strategy_id" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Sin estrategia</option>
                        <option v-for="strategy in strategies" :key="strategy.id" :value="strategy.id">{{ strategy.name }}</option>
                    </select>
                    <textarea v-model="editForm.comments" placeholder="Comentarios" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="editingAsset = null" class="text-gray-600 hover:underline">Cancelar</button>
                        <button type="submit" class="bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Popup de Compra -->
        <div v-if="operatingAsset" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Registrar Compra de {{ operatingAsset.name }}</h2>
                <form @submit.prevent="submitOperation" class="grid grid-cols-1 gap-4">
                    <input v-model="operationForm.purchase_price" type="number" step="0.01" placeholder="Precio de Compra" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required />
                    <input v-model="operationForm.quantity" type="number" step="0.01" placeholder="Cantidad" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required />
                    <input v-model="operationForm.exchange" placeholder="Exchange/Broker" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required />
                    <input v-model="operationForm.buy_commission" type="number" step="0.01" placeholder="Comisión de Compra" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" />
                    <textarea v-model="operationForm.comments" placeholder="Comentarios" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="operatingAsset = null" class="text-gray-600 hover:underline">Cancelar</button>
                        <button type="submit" class="bg-green-600 text-white p-3 rounded-lg hover:bg-green-700 transition">Registrar Compra</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Popup de Venta -->
        <div v-if="sellingOperation" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Vender Operación de {{ assets.find(a => a.id === sellingOperation.asset_id).name }}</h2>
                <form @submit.prevent="submitSale" class="grid grid-cols-1 gap-4">
                    <p class="text-gray-600">Compra: ${{ sellingOperation.purchase_price }} ({{ sellingOperation.quantity }})</p>
                    <input v-model="sellForm.sale_price" type="number" step="0.01" placeholder="Precio de Venta" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" required />
                    <input v-model="sellForm.sell_commission" type="number" step="0.01" placeholder="Comisión de Venta" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" />
                    <textarea v-model="sellForm.comments" placeholder="Comentarios" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="sellingOperation = null" class="text-gray-600 hover:underline">Cancelar</button>
                        <button type="submit" class="bg-red-600 text-white p-3 rounded-lg hover:bg-red-700 transition">Registrar Venta</button>
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
    props: ['portfolio', 'assets', 'strategies', 'flash'],
    components: { Link },
    setup() {
        const form = useForm({
            name: '',
            symbol: '',
            strategy_id: '',
            comments: ''
        });

        const editForm = useForm({
            name: '',
            symbol: '',
            strategy_id: '',
            comments: ''
        });

        const operationForm = useForm({
            purchase_price: null,
            quantity: null,
            exchange: '',
            buy_commission: null,
            comments: ''
        });

        const sellForm = useForm({
            sale_price: null,
            sell_commission: null,
            comments: '',
            operation_id: null
        });

        return { form, editForm, operationForm, sellForm };
    },
    data() {
        return {
            editingAsset: null,
            operatingAsset: null,
            sellingOperation: null
        };
    },
    methods: {
        editAsset(asset) {
            this.editingAsset = asset;
            this.editForm.name = asset.name;
            this.editForm.symbol = asset.symbol;
            this.editForm.strategy_id = asset.strategy_id;
            this.editForm.comments = asset.comments;
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
                }
            });
        },
        operateAsset(asset) {
            this.operatingAsset = asset;
            this.operationForm.purchase_price = asset.current_price;
            this.operationForm.quantity = null;
            this.operationForm.exchange = '';
            this.operationForm.buy_commission = null;
            this.operationForm.comments = '';
        },
        submitOperation() {
            this.operationForm.post(route('assets.operate', this.operatingAsset.id), {
                onSuccess: () => {
                    this.operatingAsset = null;
                }
            });
        },
        sellOperation(operation) {
            this.sellingOperation = operation;
            this.sellForm.sale_price = null;
            this.sellForm.sell_commission = null;
            this.sellForm.comments = '';
            this.sellForm.operation_id = operation.id;
        },
        submitSale() {
            this.sellForm.post(route('assets.operate', this.sellingOperation.asset_id), {
                onSuccess: () => {
                    this.sellingOperation = null;
                }
            });
        },
        deleteAsset(asset) {
            if (confirm('¿Estás seguro de eliminar este activo?')) {
                this.$inertia.delete(route('assets.destroy', asset.id), {
                    onSuccess: () => {
                        console.log('Activo eliminado con éxito');
                    },
                    onError: (errors) => {
                        console.log('Error al eliminar:', errors);
                        alert('Error al eliminar el activo: ' + JSON.stringify(errors));
                    }
                });
            }
        },
    }
});
</script>