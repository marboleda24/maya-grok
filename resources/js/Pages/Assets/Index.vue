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
                <select v-model="form.strategy_id" class="border p-2 rounded">
                    <option value="">Sin estrategia</option>
                    <option v-for="strategy in strategies" :key="strategy.id" :value="strategy.id">{{ strategy.name }}</option>
                </select>
                <textarea v-model="form.comments" placeholder="Comentarios" class="border p-2 rounded"></textarea>
            </div>
            <button type="submit" class="mt-2 bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Crear Activo</button>
        </form>
        <div class="space-y-4">
            <div v-for="asset in assets" :key="asset.id" class="border p-4 rounded flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold">{{ asset.name }} ({{ asset.symbol }})</h2>
                    <p>Valor Actual: ${{ asset.current_price }}</p>
                    <p>Techo: ${{ asset.highest_price_reached }}</p>
                    <p>Piso: ${{ asset.lowest_price_bought }}</p>
                    <p>Monitoreo: ${{ asset.monitoring_point }}</p>
                    <p>Comentarios: {{ asset.comments || 'Sin comentarios' }}</p>
                    <p v-if="asset.strategy">Estrategia: {{ asset.strategy.name }} (Comprar a {{ asset.strategy.buy_threshold * 100 }}%, Vender a {{ asset.strategy.sell_threshold * 100 }}%, Techo {{ asset.strategy.techo_threshold * 100 }}%, Mínimo Operaciones: {{ asset.strategy.minimum_open_operations }})</p>
                    <div v-if="asset.operations.length" class="mt-2">
                        <p>Operaciones:</p>
                        <ul>
                            <li v-for="op in asset.operations" :key="op.id">
                                {{ op.status === 'open' ? 'Abierta' : 'Cerrada' }} - 
                                Compra: ${{ op.purchase_price }} ({{ op.quantity }}) en {{ op.exchange }} el {{ op.created_at }} 
                                <span v-if="op.status === 'closed'"> - Venta: ${{ op.sale_price }} el {{ op.closed_at }} (Rentabilidad: {{ (op.profitability * 100).toFixed(2) }}%)</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="space-x-2">
                    <button @click="editAsset(asset)" class="text-blue-500 hover:underline">Editar</button>
                    <button @click="operateAsset(asset)" class="text-green-500 hover:underline">Operar</button>
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
                    <select v-model="editForm.strategy_id" class="border p-2 rounded w-full mb-2">
                        <option value="">Sin estrategia</option>
                        <option v-for="strategy in strategies" :key="strategy.id" :value="strategy.id">{{ strategy.name }}</option>
                    </select>
                    <textarea v-model="editForm.comments" placeholder="Comentarios" class="border p-2 rounded w-full mb-2"></textarea>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="editingAsset = null" class="text-gray-500 hover:underline">Cancelar</button>
                        <button type="submit" class="bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
        <div v-if="operatingAsset" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-xl mb-4">Registrar Compra de {{ operatingAsset.name }}</h2>
                <form @submit.prevent="submitOperation">
                    <input v-model="operationForm.purchase_price" type="number" step="0.01" placeholder="Precio de Compra" class="border p-2 rounded w-full mb-2" required />
                    <input v-model="operationForm.quantity" type="number" step="0.01" placeholder="Cantidad" class="border p-2 rounded w-full mb-2" required />
                    <input v-model="operationForm.exchange" placeholder="Exchange/Broker" class="border p-2 rounded w-full mb-2" required />
                    <input v-model="operationForm.buy_commission" type="number" step="0.01" placeholder="Comisión de Compra" class="border p-2 rounded w-full mb-2" />
                    <textarea v-model="operationForm.comments" placeholder="Comentarios" class="border p-2 rounded w-full mb-2"></textarea>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="operatingAsset = null" class="text-gray-500 hover:underline">Cancelar</button>
                        <button type="submit" class="bg-green-500 text-white p-2 rounded hover:bg-green-600">Registrar Compra</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div v-for="asset in assets" :key="asset.id" class="border p-4 rounded flex justify-between items-center">
        <div>
            <!-- ... Otros datos del activo ... -->
            <div v-if="asset.operations.length" class="mt-2">
                <p>Operaciones Abiertas:</p>
                <ul>
                    <li v-for="op in asset.operations.filter(o => o.status === 'open')" :key="op.id">
                        Compra: ${{ op.purchase_price }} ({{ op.quantity }}) en {{ op.exchange }} el {{ op.created_at }}
                        <button @click="sellOperation(op)" class="text-red-500 hover:underline ml-2">Vender</button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="space-x-2">
            <button @click="editAsset(asset)" class="text-blue-500 hover:underline">Editar</button>
            <button @click="operateAsset(asset)" class="text-green-500 hover:underline">Comprar</button>
            <button @click="deleteAsset(asset)" class="text-red-500 hover:underline">Eliminar</button>
        </div>
    </div>
    <div v-if="sellingOperation" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
            <h2 class="text-xl mb-4">Vender Operación de {{ assets.find(a => a.id === sellingOperation.asset_id).name }}</h2>
            <form @submit.prevent="submitSale">
                <p>Compra: ${{ sellingOperation.purchase_price }} ({{ sellingOperation.quantity }})</p>
                <input v-model="sellForm.sale_price" type="number" step="0.01" placeholder="Precio de Venta" class="border p-2 rounded w-full mb-2" required />
                <input v-model="sellForm.sell_commission" type="number" step="0.01" placeholder="Comisión de Venta" class="border p-2 rounded w-full mb-2" />
                <textarea v-model="sellForm.comments" placeholder="Comentarios" class="border p-2 rounded w-full mb-2"></textarea>
                <div class="flex justify-end space-x-2">
                    <button type="button" @click="sellingOperation = null" class="text-gray-500 hover:underline">Cancelar</button>
                    <button type="submit" class="bg-red-500 text-white p-2 rounded hover:bg-red-600">Registrar Venta</button>
                </div>
            </form>
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
            console.log('Enviando compra:', this.operationForm.data());
            this.operationForm.post(route('assets.operate', this.operatingAsset.id), {
                onSuccess: () => {
                    console.log('Compra registrada');
                    this.operatingAsset = null;
                },
                onError: (errors) => {
                    console.log('Errores:', errors);
                    alert('Errores: ' + JSON.stringify(errors));
                }
            });
        },
        deleteAsset(asset) {
            if (confirm('¿Estás seguro de eliminar este activo?')) {
                this.$inertia.delete(route('assets.destroy', asset.id));
            }
        },
        sellOperation(operation) {
            this.sellingOperation = operation;
            this.sellForm.sale_price = null;
            this.sellForm.sell_commission = null;
            this.sellForm.comments = '';
            this.sellForm.operation_id = operation.id; // Esto debería funcionar ahora
            console.log('Preparando venta con operation_id:', this.sellForm.operation_id);
        },
        submitSale() {
            console.log('Enviando venta:', this.sellForm.data());
            this.sellForm.post(route('assets.operate', this.sellingOperation.asset_id), {
                onSuccess: () => {
                    console.log('Venta registrada');
                    this.sellingOperation = null;
                },
                onError: (errors) => {
                    console.log('Errores:', errors);
                    alert('Errores: ' + JSON.stringify(errors));
                }
            });
        },
    }
});
</script>