<template>
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Mis Portafolios</h1>
        <Link :href="route('dashboard')" class="text-blue-500 hover:underline mb-4 inline-block">Volver al Dashboard</Link>
        <div v-if="flash && flash.message" class="bg-green-100 text-green-700 p-4 mb-6 rounded-lg shadow fixed top-4 right-4 z-50 animate-fade-in">
            {{ flash.message }}
        </div>
        <form @submit.prevent="form.post(route('portfolios.store'))" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input v-model="form.name" placeholder="Nombre" class="border p-2 rounded" required />
                <textarea v-model="form.description" placeholder="Descripción" class="border p-2 rounded"></textarea>
                <select v-model="form.status" class="border p-2 rounded">
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                </select>
            </div>
            <button type="submit" class="mt-2 bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Crear Portafolio</button>
        </form>
        <div v-if="portfolios && portfolios.length" class="space-y-4">
            <div v-for="portfolio in portfolios" :key="portfolio.id" class="border p-4 rounded flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold">{{ portfolio.name || 'Sin nombre' }}</h2>
                    <p>{{ portfolio.description || 'Sin descripción' }}</p>
                    <span class="text-sm text-gray-500">Estado: {{ portfolio.status || 'Desconocido' }}</span>
                </div>
                <div class="space-x-2">
                    <!-- Cambiar assets.index por portfolios.assets.index -->
                    <Link :href="route('portfolios.assets.index', portfolio.id)" class="text-green-500 hover:underline">Ver Activos</Link>
                    <button @click="editPortfolio(portfolio)" class="text-blue-500 hover:underline">Editar</button>
                    <button @click="deletePortfolio(portfolio)" class="text-red-500 hover:underline">Eliminar</button>
                </div>
            </div>
        </div>
        <div v-else class="text-gray-500">No hay portafolios para mostrar.</div>
        <div v-if="editingPortfolio" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-xl mb-4">Editar Portafolio</h2>
                <form @submit.prevent="updatePortfolio">
                    <input
                        ref="nameInput"
                        v-model="editForm.name"
                        placeholder="Nombre"
                        class="border p-2 rounded w-full mb-2"
                        required
                    />
                    <textarea v-model="editForm.description" placeholder="Descripción" class="border p-2 rounded w-full mb-2"></textarea>
                    <select v-model="editForm.status" class="border p-2 rounded w-full mb-2">
                        <option value="active">Activo</option>
                        <option value="inactive">Inactivo</option>
                    </select>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="editingPortfolio = null" class="text-gray-500 hover:underline">Cancelar</button>
                        <button type="submit" class="bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
// Resto del script sin cambios
import { defineComponent } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

export default defineComponent({
    props: ['portfolios', 'flash'],
    components: { Link },
    setup() {
        const form = useForm({
            name: '',
            description: '',
            status: 'active'
        });

        const editForm = useForm({
            name: '',
            description: '',
            status: 'active'
        });

        return { form, editForm };
    },
    data() {
        return {
            editingPortfolio: null
        };
    },
    mounted() {
        console.log('Portfolios recibidos:', this.portfolios);
        console.log('Flash:', this.flash);
    },
    methods: {
        editPortfolio(portfolio) {
            console.log('Editando portfolio:', portfolio);
            this.editingPortfolio = portfolio;
            this.editForm.name = portfolio.name || '';
            this.editForm.description = portfolio.description || '';
            this.editForm.status = portfolio.status || 'active';

            this.$nextTick(() => {
                const input = this.$refs.nameInput;
                input.focus();
                input.setSelectionRange(input.value.length, input.value.length);
            });
        },
        updatePortfolio() {
            this.editForm.put(route('portfolios.update', this.editingPortfolio.id), {
                onSuccess: () => {
                    this.editingPortfolio = null;
                },
                onError: (errors) => {
                    console.log('Errores al actualizar:', errors);
                }
            });
        },
        deletePortfolio(portfolio) {
            if (confirm('¿Estás seguro de eliminar este portafolio?')) {
                console.log('Eliminando Portfolio ID:', portfolio.id);
                this.$inertia.delete(route('portfolios.destroy', portfolio.id));
            }
        }
    }
});
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