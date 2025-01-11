<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    FwbButton,
    FwbModal,
    FwbPagination,
    FwbTable,
    FwbTableBody,
    FwbTableCell,
    FwbTableHead,
    FwbTableHeadCell,
    FwbTableRow,
} from "flowbite-vue";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({ purchases: Object });
const currentPage = ref(props.purchases.current_page || 1);

watch(currentPage, (newPage) => {
    router.get(
        route("purchase.index"),
        { page: newPage },
        { preserveState: true }
    );
});

const isShowModal = ref(false);
const selectedPurchase = ref(null);

const showModal = (show = true) => {
    isShowModal.value = show;
};

const viewPurchase = (purchase) => {
    selectedPurchase.value = purchase;
    showModal();
};

const printPurchase = (purchaseId) => {
    window.open(route('purchase.pdf', purchaseId), '_blank');
};
</script>

<template>
    <AdminLayout title="Notas de Compra">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Notas de Compra
            </h2>
            <FwbButton
                @click="router.get(route('purchase.create'))"
                type="button"
                color="purple"
            >
                <i class="fa-solid fa-plus mr-2"></i> Agregar Compras
            </FwbButton>
        </div>
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <FwbTable class="min-w-full">
                <FwbTableHead>
                    <FwbTableHeadCell>Fecha de Compra</FwbTableHeadCell>
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
                        v-for="purchase in purchases.data"
                        :key="purchase.id"
                    >
                        <FwbTableCell>{{ purchase.purchase_date }}</FwbTableCell>
                        <FwbTableCell>{{ purchase.total_amount }} Bs</FwbTableCell>
                        <FwbTableCell>{{ new Date(purchase.updated_at).toISOString().slice(0, 19).replace('T', ' ') }}</FwbTableCell>
                        <FwbTableCell class="flex justify-end gap-x-4">
                            <FwbButton
                                @click="router.get(route('purchase.show', purchase.id))"
                                color="alternative"
                                square
                            >
                                <i class="fa-solid fa-eye lg:mr-2" />
                                <span class="hidden lg:inline">Ver</span>
                            </FwbButton>
                            <FwbButton
                                @click="router.get(route('purchase.edit', purchase.id))"
                                color="alternative"
                                square
                            >
                                <i class="fa-solid fa-pencil lg:mr-2" />
                                <span class="hidden lg:inline">Editar</span>
                            </FwbButton>
                            <FwbButton
                                @click="printPurchase(purchase.id)"
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
                :total-items="purchases.total"
                :per-page="purchases.per_page"
                large
            ></FwbPagination>
        </div>
    </AdminLayout>
</template>
