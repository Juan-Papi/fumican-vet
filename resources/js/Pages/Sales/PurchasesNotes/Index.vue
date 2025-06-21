<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import axios from "axios";
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
    FwbToast,
} from "flowbite-vue";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

// Props
const props = defineProps({ purchases: Object });

// Pagination state
const currentPage = ref(props.purchases.current_page || 1);
watch(currentPage, (newPage) => {
    router.get(
        route("purchase.index"),
        { page: newPage },
        { preserveState: true }
    );
});

// Toast state
const showToast = ref(false);
const toastMsg = ref("");
const toastType = ref("success");

// Delete modal state
const isDeleteModal = ref(false);
const deleteTarget = ref(null);

function confirmDelete(purchase) {
    deleteTarget.value = purchase;
    isDeleteModal.value = true;
}

async function deletePurchase() {
    if (!deleteTarget.value) return;
    try {
        const { data } = await axios.delete(
            route("purchase.destroy", deleteTarget.value.id)
        );
        toastType.value = "success";
        toastMsg.value = data.message;
        showToast.value = true;
        isDeleteModal.value = false;
        router.reload();
    } catch (e) {
        toastType.value = "danger";
        toastMsg.value = e.response?.data?.message || "Error eliminando nota";
        showToast.value = true;
        isDeleteModal.value = false;
    }
}

// View modal state
const isShowModal = ref(false);
const selectedPurchase = ref(null);
const selectedDetails = ref([]);

function showModal(show = true) {
    isShowModal.value = show;
}

async function viewPurchase(purchase) {
    try {
        const { data } = await axios.get(route("purchase.show", purchase.id), {
            headers: { Accept: "application/json" },
        });
        selectedPurchase.value = data.purchaseNote;
        selectedDetails.value = data.purchaseNoteDetails;
        showModal();
    } catch (e) {
        toastType.value = "danger";
        toastMsg.value = "Error cargando detalle";
        showToast.value = true;
    }
}

function printPurchase(id) {
    window.open(route("purchase.pdf", id), "_blank");
}
</script>

<template>
    <AdminLayout title="Notas de Compra">
        <!-- Toast -->
        <div class="fixed top-4 right-4 z-50">
            <FwbToast v-if="showToast" :type="toastType">
                {{ toastMsg }}
            </FwbToast>
        </div>

        <!-- Header -->
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold">Notas de Compra</h2>
            <FwbButton
                color="purple"
                @click="router.get(route('purchase.create'))"
            >
                <i class="fa-solid fa-plus mr-2"></i> Agregar Compras
            </FwbButton>
        </div>

        <!-- Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <FwbTable>
                <FwbTableHead>
                    <FwbTableHeadCell>ID</FwbTableHeadCell>
                    <FwbTableHeadCell>Fecha</FwbTableHeadCell>
                    <FwbTableHeadCell>Total (Bs)</FwbTableHeadCell>
                    <FwbTableHeadCell>Modificado</FwbTableHeadCell>
                    <FwbTableHeadCell
                        ><span class="sr-only">Acciones</span></FwbTableHeadCell
                    >
                </FwbTableHead>
                <FwbTableBody>
                    <FwbTableRow v-for="p in purchases.data" :key="p.id">
                        <FwbTableCell>{{ p.id }}</FwbTableCell>
                        <FwbTableCell>{{ p.purchase_date }}</FwbTableCell>
                        <FwbTableCell>{{
                            parseFloat(p.total_amount).toFixed(2)
                        }}</FwbTableCell>
                        <FwbTableCell>{{
                            new Date(p.updated_at).toLocaleString()
                        }}</FwbTableCell>
                        <FwbTableCell class="flex gap-2 justify-end">
                            <FwbButton
                                color="green"
                                square
                                @click="viewPurchase(p)"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </FwbButton>
                            <FwbButton
                                color="yellow"
                                square
                                @click="
                                    router.get(route('purchase.edit', p.id))
                                "
                            >
                                <i class="fa-solid fa-pencil"></i>
                            </FwbButton>
                            <FwbButton
                                color="blue"
                                square
                                @click="printPurchase(p.id)"
                            >
                                <i class="fa-solid fa-print"></i>
                            </FwbButton>
                            <FwbButton
                                color="red"
                                square
                                @click="confirmDelete(p)"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </FwbButton>
                        </FwbTableCell>
                    </FwbTableRow>
                </FwbTableBody>
            </FwbTable>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center my-4">
            <FwbPagination
                v-model="currentPage"
                :total-items="purchases.total"
                :per-page="purchases.per_page"
                large
            />
        </div>

        <!-- Delete Modal -->
        <FwbModal v-if="isDeleteModal" @close="isDeleteModal = false">
            <template #header>Confirmar eliminación</template>
            <template #body>
                <p>¿Eliminar nota de compra #{{ deleteTarget.id }}?</p>
            </template>
            <template #footer>
                <FwbButton color="alternative" @click="isDeleteModal = false"
                    >Cancelar</FwbButton
                >
                <FwbButton color="red" @click="deletePurchase"
                    >Eliminar</FwbButton
                >
            </template>
        </FwbModal>

        <!-- View Modal -->
        <FwbModal v-if="isShowModal" @close="showModal(false)">
            <template #header>
                Detalle de Compra #{{ selectedPurchase.id }}
            </template>
            <template #body>
                <p>
                    <strong>Fecha:</strong>
                    {{ selectedPurchase.purchase_date }} &nbsp;&nbsp;
                    <strong>Proveedor:</strong>
                    {{ selectedPurchase.supplier.name }}
                </p>
                <div class="overflow-x-auto mt-4">
                    <FwbTable class="min-w-full">
                        <FwbTableHead>
                            <FwbTableHeadCell>Médicamento</FwbTableHeadCell>
                            <FwbTableHeadCell class="text-right"
                                >Cant.</FwbTableHeadCell
                            >
                            <FwbTableHeadCell class="text-right"
                                >Precio</FwbTableHeadCell
                            >
                            <FwbTableHeadCell class="text-right"
                                >Subtotal</FwbTableHeadCell
                            >
                        </FwbTableHead>
                        <FwbTableBody>
                            <FwbTableRow
                                v-for="d in selectedDetails"
                                :key="d.id"
                            >
                                <FwbTableCell>{{
                                    d.medicament.name
                                }}</FwbTableCell>
                                <FwbTableCell class="text-right">{{
                                    d.quantity
                                }}</FwbTableCell>
                                <FwbTableCell class="text-right"
                                    >{{ d.purchase_price }} Bs</FwbTableCell
                                >
                                <FwbTableCell class="text-right"
                                    >{{ d.subtotal }} Bs</FwbTableCell
                                >
                            </FwbTableRow>
                        </FwbTableBody>
                    </FwbTable>
                </div>
                <div class="mt-4 text-right">
                    <span class="font-semibold text-lg">
                        Total:
                        {{
                            parseFloat(selectedPurchase.total_amount).toFixed(2)
                        }}
                        Bs
                    </span>
                </div>
            </template>
            <template #footer>
                <FwbButton color="alternative" @click="showModal(false)"
                    >Cerrar</FwbButton
                >
            </template>
        </FwbModal>
    </AdminLayout>
</template>

<style scoped>
.container {
    max-width: 900px;
    margin: 0 auto;
}
</style>
