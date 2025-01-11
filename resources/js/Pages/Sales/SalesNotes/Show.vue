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
const salesNote = ref(props.salesNote);
const salesNoteDetails = ref(props.salesNoteDetails);
</script>

<template>
    <AdminLayout title="Detalles de la Nota de Venta">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Detalles de la Nota de Venta
            </h2>
            <FwbButton
                @click="$inertia.get(route('sales-note.index'))"
                color="purple"
            >
                Volver a la lista
            </FwbButton>
        </div>
        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold mb-2">Información General</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <p>
                        <strong>Almacén:</strong> {{ salesNote.warehouse.name }}
                    </p>
                    <p>
                        <strong>Cliente:</strong> {{ salesNote.customer.first_name }} {{ salesNote.customer.last_name }}
                    </p>
                    <p>
                        <strong>Usuario:</strong>
                        {{ salesNote.user.first_name }} {{ salesNote.user.last_name }}
                    </p>
                    <p>
                        <strong>Fecha de Venta:</strong> {{ salesNote.sale_date }}
                    </p>
                    <p>
                        <strong>Total:</strong> {{ salesNote.total_amount }} Bs
                    </p>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold">Detalles de la Venta</h3>
                <FwbTable class="min-w-full">
                    <FwbTableHead>
                        <FwbTableHeadCell>Medicamento</FwbTableHeadCell>
                        <FwbTableHeadCell>Cantidad</FwbTableHeadCell>
                        <FwbTableHeadCell>Precio de Venta</FwbTableHeadCell>
                        <FwbTableHeadCell>Subtotal</FwbTableHeadCell>
                        <FwbTableHeadCell>
                            <span class="sr-only"></span>
                        </FwbTableHeadCell>
                    </FwbTableHead>
                    <FwbTableBody>
                        <FwbTableRow
                            v-for="detail in salesNoteDetails"
                            :key="detail.id"
                        >
                            <FwbTableCell>{{
                                detail.medicament.name
                            }}</FwbTableCell>
                            <FwbTableCell>{{ detail.quantity }}</FwbTableCell>
                            <FwbTableCell>{{
                                detail.sale_price
                            }} Bs</FwbTableCell>
                            <FwbTableCell>{{ detail.subtotal }} Bs</FwbTableCell>
                            <FwbTableCell></FwbTableCell>
                        </FwbTableRow>
                    </FwbTableBody>
                </FwbTable>
            </div>
        </div>
    </AdminLayout>
</template>
