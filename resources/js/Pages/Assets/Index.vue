<template>
    <AppLayout>
        <div class="space-y-6">
            <h1 class="text-3xl font-bold text-gray-800">Activos de {{ portfolio.name }}</h1>

            <!-- Formulario de Creación de Activo -->
            <div class="bg-white p-6 rounded-lg shadow">
                <form @submit.prevent="submitAsset" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input v-model="form.name" placeholder="Nombre" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                    <input v-model="form.symbol" placeholder="Símbolo (ej. AAPL)" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                    <select v-model="form.strategy_id" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Sin estrategia</option>
                        <option v-for="strategy in strategies" :key="strategy.id" :value="strategy.id">{{ strategy.name }}</option>
                    </select>
                    <textarea v-model="form.comments" placeholder="Comentarios" class="border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 col-span-1 md:col-span-4"></textarea>
                    <button type="submit" class="bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition col-span-1 md:col-span-4">Crear Activo</button>
                </form>
                <div v-if="form.errors.symbol" class="text-red-500 mt-2">{{ form.errors.symbol }}</div>
            </div>

            <!-- Tabla de Activos -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Lista de Activos</h2>
                <div ref="assetsGrid"></div>
            </div>

            <!-- Popups -->
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
    </AppLayout>
</template>
<script>
import { defineComponent } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Grid, h } from 'gridjs'; // Importar 'h' de gridjs
import 'gridjs/dist/theme/mermaid.css';

export default defineComponent({
    props: ['portfolio', 'assets', 'strategies'],
    components: { Link, AppLayout },
    setup() {
        const form = useForm({ name: '', symbol: '', strategy_id: '', comments: '' });
        const editForm = useForm({ name: '', symbol: '', strategy_id: '', comments: '' });
        const operationForm = useForm({ purchase_price: null, quantity: null, exchange: '', buy_commission: null, comments: '' });
        const sellForm = useForm({ sale_price: null, sell_commission: null, comments: '', operation_id: null });
        return { form, editForm, operationForm, sellForm };
    },
    data() {
        return {
            editingAsset: null,
            operatingAsset: null,
            sellingOperation: null,
            assetsGrid: null,
            vueInstance: null
        };
    },
    mounted() {
        this.vueInstance = this;
        this.renderAssetsGrid();
    },
    watch: {
        assets: {
            handler() {
                this.renderAssetsGrid();
            },
            deep: true
        }
    },
    methods: {
        renderAssetsGrid() {
            if (this.assetsGrid) {
                this.assetsGrid.destroy();
            }

            const operationsFormatter = (cell, row) => {
                const operations = row.cells[7].data || [];
                return operations.map(op => `
                    <div class="py-1">
                        ${op.status === 'open' ? 'Abierta' : 'Cerrada'} - 
                        Compra: $${op.purchase_price} (${op.quantity}) en ${op.exchange}
                        ${op.status === 'closed' ? ` - Venta: $${op.sale_price} (Rent: ${(op.profitability * 100).toFixed(2)}%)` : ''}
                        ${op.status === 'open' ? `<button class="text-red-500 hover:underline ml-2" data-operation-id="${op.id}">Vender</button>` : ''}
                    </div>
                `).join('');
            };

            this.assetsGrid = new Grid({
                columns: [
                    { id: 'name', name: 'Nombre' },
                    { id: 'symbol', name: 'Símbolo' },
                    { id: 'current_price', name: 'Valor Actual', formatter: cell => `$${cell || 'N/A'}` },
                    { id: 'highest_price_reached', name: 'Techo', formatter: cell => `$${cell || 'N/A'}` },
                    { id: 'lowest_price_bought', name: 'Piso', formatter: cell => `$${cell || 'N/A'}` },
                    { id: 'monitoring_point', name: 'Monitoreo', formatter: cell => `$${cell || 'N/A'}` },
                    { id: 'comments', name: 'Comentarios', formatter: cell => cell || 'Sin comentarios' },
                    { id: 'operations', name: 'Operaciones', formatter: operationsFormatter, width: '300px' },
                    {
                        name: 'Acciones',
                        formatter: (_, row) => {
                            const asset = this.assets.find(a => a.name === row.cells[0].data);
                            if (!asset) return h('div', { className: 'text-gray-500' }, 'Cargando...');

                            return h('div', { className: 'flex space-x-2' }, [
                                h('button', {
                                    className: 'text-blue-500 hover:underline',
                                    'data-action': 'edit',
                                    'data-asset-id': asset.id,
                                }, 'Editar'),
                                h('button', {
                                    className: 'text-green-500 hover:underline',
                                    'data-action': 'buy',
                                    'data-asset-id': asset.id,
                                }, 'Comprar'),
                                h('button', {
                                    className: 'text-red-500 hover:underline',
                                    'data-action': 'delete',
                                    'data-asset-id': asset.id,
                                }, 'Eliminar')
                            ]);
                        },
                        width: '150px'
                    }
                ],
                data: this.assets,
                search: true,
                sort: true,
                pagination: { enabled: true, limit: 10 }
            });

            this.assetsGrid.render(this.$refs.assetsGrid);

            this.$nextTick(() => {
                const sellButtons = this.$refs.assetsGrid.querySelectorAll('[data-operation-id]');
                sellButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const operationId = button.getAttribute('data-operation-id');
                        const operation = this.assets.flatMap(a => a.operations || []).find(op => op.id == operationId);
                        if (operation) this.sellOperation(operation);
                    });
                });

                const actionButtons = this.$refs.assetsGrid.querySelectorAll('[data-action]');
                actionButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const action = button.getAttribute('data-action');
                        const assetId = button.getAttribute('data-asset-id');
                        const asset = this.assets.find(a => a.id == assetId);
                        if (asset) {
                            if (action === 'edit') this.editAsset(asset);
                            if (action === 'buy') this.operateAsset(asset);
                            if (action === 'delete') this.deleteAsset(asset);
                        }
                    });
                });
            });
        },
        submitAsset() {
            console.log('Ruta generada:', route('portfolios.assets.store', this.portfolio.id));
            this.form.post(route('portfolios.assets.store', this.portfolio.id), {
                onSuccess: () => {
                    console.log('Activo creado con éxito');
                    this.form.reset();
                    this.renderAssetsGrid();
                },
                onError: (errors) => {
                    console.log('Errores al crear activo:', errors);
                }
            });
        },
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
        }
    }
});
</script>