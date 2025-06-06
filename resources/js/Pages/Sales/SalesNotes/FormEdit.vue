<script setup>
import { ref, computed } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { router } from "@inertiajs/vue3";

// Props del back
const props = defineProps({
    customers: Array,
    warehouses: Array,
    medicamentsList: Array,
    salesNote: Object,
    salesNoteDetails: Array,
});

const form = ref({
    customer_id: props.salesNote.customer_id,
    warehouse_id: props.salesNote.warehouse_id,
    medicaments: props.salesNoteDetails.map((detail) => ({
        detail_id: detail.id,
        id: detail.medicament_id,
        quantity: detail.quantity,
        sale_price: detail.sale_price,
        subtotal: detail.subtotal,
    })),
    total_amount: props.salesNote.total_amount,
    processing: false,
});

const actionTitle = computed(() => "Editar");

function addMedicament() {
    form.value.medicaments.push({
        id: "",
        quantity: 1,
        sale_price: 0,
        subtotal: 0,
    });
}

function removeMedicament(index) {
    form.value.medicaments.splice(index, 1);
    updateTotalAmount();
}

function updateTotalAmount() {
    let total = 0;
    form.value.medicaments.forEach((m) => {
        m.subtotal = m.quantity * m.sale_price;
        total += m.subtotal;
    });
    form.value.total_amount = total;
}

function submit() {
    form.value.processing = true;
    router.put(route("sales-note.update", props.salesNote.id), form.value, {
        onSuccess: () => {
            alert("Nota de venta actualizada exitosamente");
            form.value.processing = false;
        },
        onError: () => {
            form.value.processing = false;
        },
    });
}

function cancel() {
    router.visit(route("sales-note.index"));
}
</script>

<template>
    <AdminLayout :title="actionTitle + ' Nota de Venta'">
        <div class="container mx-auto p-6">
            <h2 class="text-2xl font-semibold mb-4">
                {{ actionTitle }} Nota de Venta
            </h2>
            <form @submit.prevent="submit">
                <!-- Cliente -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-600"
                        >Cliente</label
                    >
                    <select
                        v-model="form.customer_id"
                        class="mt-1 block w-full p-3 border rounded-md focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar</option>
                        <option
                            v-for="c in customers"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.first_name }} {{ c.last_name }}
                        </option>
                    </select>
                </div>

                <!-- Almacén -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-600"
                        >Almacén</label
                    >
                    <select
                        v-model="form.warehouse_id"
                        class="mt-1 block w-full p-3 border rounded-md focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar</option>
                        <option
                            v-for="w in warehouses"
                            :key="w.id"
                            :value="w.id"
                        >
                            {{ w.name }}
                        </option>
                    </select>
                </div>

                <!-- Medicamentos -->
                <div class="my-6">
                    <h3 class="text-xl font-semibold mb-4">Medicamentos</h3>
                    <div
                        v-for="(med, idx) in form.medicaments"
                        :key="idx"
                        class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4"
                    >
                        <!-- Selector -->
                        <select
                            v-model="med.id"
                            @change="updateTotalAmount"
                            class="mt-1 block w-full p-3 border rounded-md focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">Seleccionar</option>
                            <option
                                v-for="m in medicamentsList"
                                :key="m.id"
                                :value="m.id"
                            >
                                {{ m.name }}
                            </option>
                        </select>

                        <!-- Cantidad -->
                        <input
                            v-model.number="med.quantity"
                            type="number"
                            min="1"
                            @input="updateTotalAmount"
                            class="mt-1 block w-full p-3 border rounded-md focus:ring-2 focus:ring-indigo-500"
                        />

                        <!-- Precio de Venta -->
                        <input
                            v-model.number="med.sale_price"
                            type="number"
                            min="0"
                            step="0.01"
                            @input="updateTotalAmount"
                            class="mt-1 block w-full p-3 border rounded-md focus:ring-2 focus:ring-indigo-500"
                        />

                        <!-- Subtotal + Eliminar -->
                        <div class="flex items-center justify-between mt-4">
                            <span class="font-medium"
                                >{{ med.subtotal.toFixed(2) }} Bs</span
                            >
                            <button
                                type="button"
                                @click="removeMedicament(idx)"
                                class="text-red-600 hover:text-red-800"
                            >
                                Eliminar
                            </button>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="addMedicament"
                        class="text-green-600 hover:text-green-800"
                    >
                        + Agregar Medicamento
                    </button>
                </div>

                <!-- Total General -->
                <div class="my-6">
                    <label class="block text-sm font-medium text-gray-600"
                        >Total General</label
                    >
                    <input
                        :value="form.total_amount.toFixed(2)"
                        readonly
                        class="mt-1 block w-full p-3 border rounded-md focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <!-- Botones -->
                <div class="flex justify-end space-x-4">
                    <button
                        type="button"
                        @click="cancel"
                        class="bg-gray-300 text-black px-6 py-2 rounded-md hover:bg-gray-700 hover:text-white"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-indigo-700 text-white px-6 py-2 rounded-md hover:bg-violet-900"
                    >
                        {{ actionTitle }} Nota de Venta
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
