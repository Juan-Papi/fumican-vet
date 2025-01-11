<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    FwbButton,
    FwbTable,
    FwbTableBody,
    FwbTableCell,
    FwbTableHead,
    FwbTableHeadCell,
    FwbTableRow,
    FwbPagination,
} from "flowbite-vue";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({ sales: Object });
const currentPage = ref(props.sales.current_page || 1);

watch(currentPage, (newPage) => {
    router.get(
        route("sales-note.index"),
        { page: newPage },
        { preserveState: true }
    );
});

const printSale = (saleId) => {
    window.open(route('sales-note.pdf', saleId), '_blank');
};
</script>

<template>
    <AdminLayout title="Ventas">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Ventas
            </h2>
            <FwbButton
                @click="router.get(route('sales-note.create'))"
                type="button"
                color="purple"
            >
                <i class="fa-solid fa-plus mr-2"></i> Agregar Venta
            </FwbButton>
        </div>
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <FwbTable class="min-w-full">
                <FwbTableHead>
                    <FwbTableHeadCell>Fecha de Venta</FwbTableHeadCell>
                    <FwbTableHeadCell>Total</FwbTableHeadCell>
                    <FwbTableHeadCell>Última modificación</FwbTableHeadCell>
                    <FwbTableHeadCell>
                        <span class="sr-only">Ver</span>
                    </FwbTableHeadCell>
                    <FwbTableHeadCell>
                        <span class="sr-only">Editar</span>
                    </FwbTableHeadCell>
                    <FwbTableHeadCell>
                        <span class="sr-only">Imprimir</span>
                    </FwbTableHeadCell>
                </FwbTableHead>
                <FwbTableBody>
                    <FwbTableRow
                        v-for="sale in sales.data"
                        :key="sale.id"
                    >
                        <FwbTableCell>{{ sale.sale_date }}</FwbTableCell>
                        <FwbTableCell>{{ sale.total_amount }} Bs</FwbTableCell>
                        <FwbTableCell>{{ new Date(sale.updated_at).toISOString().slice(0, 19).replace('T', ' ') }}</FwbTableCell>
                        <FwbTableCell class="flex justify-end gap-x-4">
                            <FwbButton
                                @click="router.get(route('sales-note.show', sale.id))"
                                color="alternative"
                                square
                            >
                                <i class="fa-solid fa-eye lg:mr-2" />
                                <span class="hidden lg:inline">Ver</span>
                            </FwbButton>
                            <FwbButton
                                @click="router.get(route('sales-note.edit', sale.id))"
                                color="alternative"
                                square
                            >
                                <i class="fa-solid fa-pencil lg:mr-2" />
                                <span class="hidden lg:inline">Editar</span>
                            </FwbButton>
                            <FwbButton
                                @click="printSale(sale.id)"
                                color="alternative"
                                square
                            >
                                <i class="fa-solid fa-print lg:mr-2" />
                                <span class="hidden lg:inline">Imprimir</span>
                            </FwbButton>
                        </FwbTableCell>
                    </FwbTableRow>
                </FwbTableBody>
            </FwbTable>
        </div>

        <div class="flex justify-center my-4">
            <FwbPagination
                v-model="currentPage"
                :total-items="sales.total"
                :per-page="sales.per_page"
                large
            ></FwbPagination>
        </div>
    </AdminLayout>
</template>
