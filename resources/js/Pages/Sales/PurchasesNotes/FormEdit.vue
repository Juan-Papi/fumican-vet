<script setup>
import { ref, computed, onMounted } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { router } from "@inertiajs/vue3";

// Recibir las props del backend
const props = defineProps({
    suppliers: Array,

    warehouses: Array,

    medicamentsList: Array,

    purchaseNote: Object,

    purchaseNoteDetails: Array,
});

const form = ref({

    supplier_id: props.purchaseNote.supplier_id,

    warehouse_id: props.purchaseNote.warehouse_id,

    medicaments: props.purchaseNoteDetails.map(detail => ({
        id: detail.medicament_id,
        quantity: detail.quantity,
        purchase_price: detail.purchase_price,
        subtotal: detail.subtotal,
    })),

    total_amount: props.purchaseNote.total_amount,

    processing: false,

});


// Computed para el título dinámico
const actionTitle = computed(() => "Editar");

// Funciones
const addMedicament = () => {
    form.value.medicaments.push({
        id: "",
        quantity: 1,
        purchase_price: 0,
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
        medicament.subtotal = medicament.quantity * medicament.purchase_price;
        total += medicament.subtotal;
    });
    form.value.total_amount = total;
};

const submit = () => {
    form.value.processing = true; // Marcar como en proceso
    router.put(route("purchase.update", props.purchaseNote.id), form.value, {
        onSuccess: () => {
            alert("Nota de compra actualizada exitosamente");
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
    router.visit(route("purchase.index")); // Esto puede ser reemplazado por la ruta que desees
};
</script>

<template>
    <AdminLayout :title="actionTitle + ' Nota de Compra'">
        <div class="container mx-auto p-6">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">
                {{ actionTitle }} Nota de Compra
            </h2>

            <form @submit.prevent="submit">
                <!-- Proveedor -->
                <div class="mb-6">
                    <label
                        for="supplier_id"
                        class="block text-sm font-medium text-gray-600"
                    >
                        Proveedor
                    </label>
                    <select
                        v-model="form.supplier_id"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar</option>
                        <option
                            v-for="supplier in suppliers"
                            :key="supplier.id"
                            :value="supplier.id"
                        >
                            {{ supplier.name }}
                        </option>
                    </select>
                </div>

                <!-- Almacén -->
                <div class="mb-6">
                    <label
                        for="warehouse_id"
                        class="block text-sm font-medium text-gray-600"
                    >
                        Almacén
                    </label>
                    <select
                        v-model="form.warehouse_id"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar</option>
                        <option
                            v-for="warehouse in warehouses"
                            :key="warehouse.id"
                            :value="warehouse.id"
                        >
                            {{ warehouse.name }}
                        </option>
                    </select>
                </div>

                <!-- Medicamentos -->
                <div class="my-6">
                    <h3 class="text-xl font-semibold text-gray-600 mb-4">
                        Medicamentos
                    </h3>
                    <div
                        v-for="(medicament, index) in form.medicaments"
                        :key="index"
                        class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4"
                    >
                        <!-- Medicamento -->
                        <div>
                            <label
                                for="medicament_id"
                                class="block text-sm font-medium text-gray-600"
                            >
                                Medicamento
                            </label>
                            <select
                                v-model="medicament.id"
                                class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="">Seleccionar</option>
                                <option
                                    v-for="med in medicamentsList"
                                    :key="med.id"
                                    :value="med.id"
                                >
                                    {{ med.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Cantidad -->
                        <div>
                            <label
                                for="quantity"
                                class="block text-sm font-medium text-gray-600"
                            >
                                Cantidad
                            </label>
                            <input
                                v-model="medicament.quantity"
                                type="number"
                                class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                @input="updateTotalAmount"
                            />
                        </div>

                        <!-- Precio de compra -->
                        <div>
                            <label
                                for="purchase_price"
                                class="block text-sm font-medium text-gray-600"
                            >
                                Precio de compra
                            </label>
                            <input
                                v-model="medicament.purchase_price"
                                type="number"
                                class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                @input="updateTotalAmount"
                            />
                        </div>

                        <!-- Subtotal por Medicamento -->
                        <div class="flex items-center justify-between mt-4">
                            <span class="text-sm font-medium text-gray-600">
                                Subtotal: {{ medicament.subtotal }}
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
                        :value="form.total_amount"
                    />
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-end mt-6">
                    <button
                        type="button"
                        @click="cancel"
                        class="mr-4 bg-gray-300 text-black px-6 py-2 rounded-md hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="bg-indigo-700 text-white px-6 py-2 rounded-md hover:bg-violet-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        :disabled="form.processing"
                    >
                        {{ actionTitle }} Nota de Compra
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
.container {
    max-width: 900px;
    margin: 0 auto;
}
</style>
