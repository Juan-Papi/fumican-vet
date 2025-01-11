<script setup>
import { ref, computed } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { router } from "@inertiajs/vue3";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";

// Recibir las props del backend
defineProps({
    customers: Array,
    warehouses: Array,
    medicamentsList: Array,
});

// Formulario
const form = ref({
    customer_id: null,
    warehouse_id: null,
    medicaments: [{ id: null, quantity: 1, sale_price: 0, subtotal: 0 }],
    total_amount: 0,
    processing: false,
});

// Computed para el título dinámico
const actionTitle = computed(() => "Agregar");

// Funciones
const addMedicament = () => {
    form.value.medicaments.push({
        id: null,
        quantity: 1,
        sale_price: 0,
        subtotal: 0,
    });
};

const removeMedicament = (index) => {
    form.value.medicaments.splice(index, 1);
    updateTotalAmount();
};

const updateTotalAmount = () => {
    let total = 0;
    form.value.medicaments.forEach((medicament) => {
        medicament.subtotal = medicament.quantity * medicament.sale_price;
        total += medicament.subtotal;
    });
    form.value.total_amount = total;
};

const submit = () => {
    form.value.processing = true; // Marcar como en proceso
    router.post(route("sales-note.store"), {
        customer_id: form.value.customer_id.id,
        warehouse_id: form.value.warehouse_id.id,
        medicaments: form.value.medicaments.map(m => ({
            id: m.id.id,
            quantity: m.quantity,
            sale_price: m.sale_price,
            subtotal: m.subtotal,
        })),
        total_amount: form.value.total_amount,
    }, {
        onSuccess: () => {
            alert("Nota de venta creada exitosamente");
            form.value.processing = false; // Finalizar el proceso
        },
        onError: (errors) => {
            console.error(errors);
            form.value.processing = false; // Finalizar el proceso
        },
    });
};

// Función para cancelar el formulario (ir a la lista o limpiar)
const cancel = () => {
    // Puedes redirigir o limpiar el formulario
    router.visit(route("sales-note.index")); // Esto puede ser reemplazado por la ruta que desees
};
</script>

<template>
    <AdminLayout :title="actionTitle + ' Nota de Venta'">
        <div class="container mx-auto p-6">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">
                {{ actionTitle }} Nota de Venta
            </h2>

            <form @submit.prevent="submit">
                <!-- Cliente -->
                <div class="mb-6">
                    <label
                        for="customer_id"
                        class="block text-sm font-medium text-gray-600"
                    >
                        Cliente
                    </label>
                    <multiselect
                        v-model="form.customer_id"
                        :options="customers"
                        label="first_name"
                        track-by="id"
                        placeholder="Seleccionar Cliente"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        :class="{
                            multiselect: true,
                            'multiselect--active': form.customer_id,
                        }"
                    />
                </div>

                <!-- Almacén -->
                <div class="mb-6">
                    <label
                        for="warehouse_id"
                        class="block text-sm font-medium text-gray-600"
                    >
                        Almacén
                    </label>
                    <multiselect
                        v-model="form.warehouse_id"
                        :options="warehouses"
                        label="name"
                        track-by="id"
                        placeholder="Seleccionar Almacén"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        :class="{
                            multiselect: true,
                            'multiselect--active': form.warehouse_id,
                        }"
                    />
                </div>

                <!-- Medicamentos -->
                <div class="my-6">
                    <h3 class="text-xl font-semibold text-gray-600 mb-4">
                        Medicamentos
                    </h3>
                    <div
                        v-for="(medicament, index) in form.medicaments"
                        :key="index"
                        class="space-y-4 mb-4"
                    >
                        <div class="flex flex-col sm:flex-row sm:space-x-4">
                            <!-- Medicamento -->
                            <div class="flex-1">
                                <label
                                    for="medicament_id"
                                    class="block text-sm font-medium text-gray-600"
                                >
                                    Medicamento
                                </label>
                                <multiselect
                                    v-model="medicament.id"
                                    :options="medicamentsList"
                                    label="name"
                                    track-by="id"
                                    placeholder="Seleccionar Medicamento"
                                    class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    :class="{
                                        multiselect: true,
                                        'multiselect--active': medicament.id,
                                    }"
                                    @change="updateTotalAmount"
                                />
                            </div>

                            <!-- Cantidad -->
                            <div class="flex-1">
                                <label
                                    for="quantity"
                                    class="block text-sm font-medium text-gray-600 ml-5"
                                >
                                    Cantidad
                                </label>
                                <input
                                    v-model="medicament.quantity"
                                    type="number"
                                    class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 ml-5"
                                    min="1"
                                    @input="updateTotalAmount"
                                />
                            </div>

                            <!-- Precio de venta -->
                            <div class="flex-1">
                                <label
                                    for="sale_price"
                                    class="block text-sm font-medium text-gray-600 ml-5"
                                >
                                    Precio de venta
                                </label>
                                <input
                                    v-model="medicament.sale_price"
                                    type="number"
                                    class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 ml-5"
                                    step="0.01"
                                    min="0"
                                    @input="updateTotalAmount"
                                />
                            </div>
                        </div>

                        <!-- Subtotal por Medicamento -->
                        <div
                            class="flex items-center justify-between mt-4"
                        >
                            <span class="font-semibold text-gray-700">
                                {{ medicament.subtotal.toFixed(2) }}
                            </span>
                            <button
                                type="button"
                                @click="removeMedicament(index)"
                                class="text-red-600 hover:text-red-800"
                            >
                                Eliminar
                            </button>
                        </div>
                    </div>

                    <!-- Agregar medicamento -->
                    <button
                        type="button"
                        @click="addMedicament"
                        class="mt-4 text-green-600 hover:text-green-800"
                    >
                        + Agregar Medicamento
                    </button>
                </div>

                <!-- Total General -->
                <div class="my-6">
                    <label
                        for="total_amount"
                        class="block text-sm font-medium text-gray-600"
                    >
                        Total General
                    </label>
                    <input
                        v-model="form.total_amount"
                        type="number"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        :readonly="true"
                        :value="form.total_amount.toFixed(2)"
                    />
                </div>

                <!-- Botones -->
                <div class="flex flex-col sm:flex-row items-center justify-end mt-6">
                    <button
                        type="button"
                        @click="cancel"
                        class="mb-4 sm:mb-0 sm:mr-4 bg-gray-300 text-black px-6 py-2 rounded-md hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="bg-indigo-700 text-white px-6 py-2 rounded-md hover:bg-violet-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        :disabled="form.processing"
                    >
                        {{ actionTitle }} Nota de Venta
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
.multiselect__content {
  position: absolute !important;  /* Esto fuerza la posición de la lista */
  z-index: 20 !important;         /* Asegúrate de que esté encima de otros elementos */
}
</style>
