<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    FwbButton,
    FwbTable,
    FwbTableBody,
    FwbTableCell,
    FwbTableHead,
    FwbTableHeadCell,
    FwbTableRow,
} from "flowbite-vue";

const { props } = usePage();
const purchaseNote = ref(props.purchaseNote);
const purchaseNoteDetails = ref(props.purchaseNoteDetails);
</script>

<template>
    <AdminLayout title="Detalles de la Nota de Compra">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Detalles de la Nota de Compra
            </h2>
            <FwbButton
                @click="$inertia.get(route('purchase.index'))"
                color="yellow"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="inline w-4 h-4 mr-2"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
                Volver a la lista
            </FwbButton>
        </div>
        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold mb-2">Información General</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <p>
                        <strong>Almacén:</strong>
                        {{ purchaseNote.warehouse.name }}
                    </p>
                    <p>
                        <strong>Proveedor:</strong>
                        {{ purchaseNote.supplier.name }}
                    </p>
                    <p>
                        <strong>Usuario:</strong>
                        {{ purchaseNote.user.first_name }}
                        {{ purchaseNote.user.last_name }}
                    </p>
                    <p>
                        <strong>Fecha de Compra:</strong>
                        {{ purchaseNote.purchase_date }}
                    </p>
                    <p>
                        <strong>Total:</strong>
                        {{ purchaseNote.total_amount }} Bs
                    </p>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold">Detalles de la Compra</h3>
                <FwbTable class="min-w-full">
                    <FwbTableHead>
                        <FwbTableHeadCell>Medicamento</FwbTableHeadCell>
                        <FwbTableHeadCell>Cantidad</FwbTableHeadCell>
                        <FwbTableHeadCell>Precio de Compra</FwbTableHeadCell>
                        <FwbTableHeadCell>Subtotal</FwbTableHeadCell>
                        <FwbTableHeadCell>
                            <span class="sr-only"></span>
                        </FwbTableHeadCell>
                    </FwbTableHead>
                    <FwbTableBody>
                        <FwbTableRow
                            v-for="detail in purchaseNoteDetails"
                            :key="detail.id"
                        >
                            <FwbTableCell>{{
                                detail.medicament.name
                            }}</FwbTableCell>
                            <FwbTableCell>{{ detail.quantity }}</FwbTableCell>
                            <FwbTableCell
                                >{{ detail.purchase_price }} Bs</FwbTableCell
                            >
                            <FwbTableCell
                                >{{ detail.subtotal }} Bs</FwbTableCell
                            >
                            <FwbTableCell></FwbTableCell>
                        </FwbTableRow>
                    </FwbTableBody>
                </FwbTable>
            </div>
        </div>
    </AdminLayout>
</template>
