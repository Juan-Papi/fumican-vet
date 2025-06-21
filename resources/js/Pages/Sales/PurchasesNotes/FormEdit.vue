<script setup>
import { ref, computed } from "vue";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { router } from "@inertiajs/vue3";
import { FwbToast, FwbButton } from "flowbite-vue";

// Props
const props = defineProps({
    suppliers: Array,
    warehouses: Array,
    medicamentsList: Array,
    purchaseNote: Object,
    purchaseNoteDetails: Array,
});

// Ensure numeric total
const initialTotal = parseFloat(props.purchaseNote.total_amount) || 0;

// Form state populated from props
const form = ref({
    id: props.purchaseNote.id,
    supplier_id: props.purchaseNote.supplier_id,
    warehouse_id: props.purchaseNote.warehouse_id,
    medicaments: props.purchaseNoteDetails.map((d) => ({
        detail_id: d.id,
        id: d.medicament_id,
        quantity: Number(d.quantity),
        purchase_price: Number(d.purchase_price),
        subtotal: Number(d.subtotal),
    })),
    total_amount: initialTotal,
    processing: false,
});

// Toast state
const showToast = ref(false);
const toastMsg = ref("");
const toastType = ref("success");
const actionTitle = computed(() => "Editar");

// Helpers
const updateTotal = () => {
    let total = 0;
    form.value.medicaments.forEach((m) => {
        m.subtotal = Number(m.quantity) * Number(m.purchase_price);
        total += m.subtotal;
    });
    form.value.total_amount = total;
};

const addMed = () => {
    form.value.medicaments.push({
        id: "",
        quantity: 1,
        purchase_price: 0,
        subtotal: 0,
    });
    updateTotal();
};

const removeMed = (i) => {
    form.value.medicaments.splice(i, 1);
    updateTotal();
};

const cancel = () => router.get(route("purchase.index"));

const submit = async () => {
    form.value.processing = true;
    try {
        const { data } = await axios.put(
            route("purchase.update", form.value.id),
            form.value
        );
        toastType.value = "success";
        toastMsg.value = data.message;
        showToast.value = true;
        setTimeout(() => router.get(route("purchase.index")), 800);
    } catch (e) {
        toastType.value = "danger";
        toastMsg.value =
            e.response?.data?.message || "Error al actualizar nota";
        showToast.value = true;
    } finally {
        form.value.processing = false;
    }
};
</script>

<template>
    <AdminLayout :title="actionTitle + ' Nota de Compra'">
        <div class="fixed top-4 right-4 z-50">
            <FwbToast v-if="showToast" :type="toastType">{{
                toastMsg
            }}</FwbToast>
        </div>

        <div class="container mx-auto p-6">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">
                {{ actionTitle }} Nota de Compra
            </h2>
            <form @submit.prevent="submit">
                <!-- Proveedor -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-600"
                        >Proveedor</label
                    >
                    <select
                        v-model="form.supplier_id"
                        class="mt-1 block w-full p-3 border rounded-md focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar</option>
                        <option
                            v-for="s in suppliers"
                            :key="s.id"
                            :value="s.id"
                        >
                            {{ s.name }}
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
                    <h3 class="text-xl font-semibold text-gray-600 mb-4">
                        Medicamentos
                    </h3>
                    <div
                        v-for="(med, i) in form.medicaments"
                        :key="i"
                        class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4"
                    >
                        <select
                            v-model="med.id"
                            @change="updateTotal"
                            class="block w-full p-3 border rounded-md focus:ring-2 focus:ring-indigo-500"
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
                        <input
                            type="number"
                            v-model.number="med.quantity"
                            min="1"
                            @input="updateTotal"
                            class="block w-full p-3 border rounded-md focus:ring-2 focus:ring-indigo-500"
                        />
                        <input
                            type="number"
                            v-model.number="med.purchase_price"
                            step="0.01"
                            min="0"
                            @input="updateTotal"
                            class="block w-full p-3 border rounded-md focus:ring-2 focus:ring-indigo-500"
                        />
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">{{
                                med.subtotal.toFixed(2)
                            }}</span>
                            <button
                                type="button"
                                @click="removeMed(i)"
                                class="text-red-600 hover:text-red-800"
                            >
                                Eliminar
                            </button>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="addMed"
                        class="text-green-600 hover:text-green-800"
                    >
                        + Agregar Medicamento
                    </button>
                </div>
                <!-- Total general -->
                <div class="my-6">
                    <label class="block text-sm font-medium text-gray-600"
                        >Total General</label
                    >
                    <input
                        type="number"
                        :value="form.total_amount.toFixed(2)"
                        readonly
                        class="mt-1 block w-full p-3 border rounded-md focus:ring-2 focus:ring-indigo-500"
                    />
                </div>
                <!-- Botones -->
                <div class="flex justify-end space-x-4">
                    <FwbButton color="alternative" @click="cancel"
                        >Cancelar</FwbButton
                    >
                    <FwbButton type="submit" :disabled="form.processing">{{
                        actionTitle
                    }}</FwbButton>
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
