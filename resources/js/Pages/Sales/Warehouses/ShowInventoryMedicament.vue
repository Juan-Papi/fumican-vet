<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { ref, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import {
    FwbA,
    FwbButton,
    FwbTable,
    FwbTableBody,
    FwbTableCell,
    FwbTableHead,
    FwbTableHeadCell,
    FwbTableRow,
    FwbPagination,
} from "flowbite-vue";

const props = defineProps({
    warehouse: Object,
    medicament: Object,
    inventories: Object,
});

const currentPage = ref(props.inventories.current_page || 1);

// Watch para manejar la paginación
watch(currentPage, (newPage) => {
    router.get(
        route("warehouse.medicament.inventory", {
            warehouseId: props.warehouse.id,
            medicamentId: props.medicament.id,
        }),
        { page: newPage },
        { preserveState: true }
    );
});

console.log(props.inventories);
</script>

<template>
    <AdminLayout :title="`Inventarios de ${props.warehouse.name}`">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Inventarios (Lotes de medicamento) de
                {{ props.warehouse.name }}
            </h2>
            <FwbButton
                :href="route('warehouse.create')"
                type="button"
                color="purple"
                class="flex items-center gap-2"
            >
                <i class="fa-solid fa-plus"></i>
                Agregar nuevo lote
            </FwbButton>
        </div>
        <div class="h-12">
            <FwbButton
                :href="route('warehouse.show', props.warehouse.id)"
                type="button"
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
                Volver a Almacén
            </FwbButton>
        </div>
        <div v-if="props.inventories && props.inventories.data.length > 0">
            <FwbTable>
                <FwbTableHead>
                    <FwbTableHeadCell>Medicamento</FwbTableHeadCell>
                    <FwbTableHeadCell>Stock</FwbTableHeadCell>
                    <FwbTableHeadCell>Precio</FwbTableHeadCell>
                    <FwbTableHeadCell>Almacén</FwbTableHeadCell>
                    <FwbTableHeadCell>
                        <span class="sr-only">Acciones</span>
                    </FwbTableHeadCell>
                </FwbTableHead>
                <FwbTableBody>
                    <FwbTableRow
                        v-for="(inventory, index) in props.inventories.data"
                        :key="index"
                    >
                        <FwbTableCell>
                            {{ inventory.medicament.name }}
                        </FwbTableCell>
                        <FwbTableCell>
                            {{ inventory.stock }}
                        </FwbTableCell>
                        <FwbTableCell> ${{ inventory.price }} </FwbTableCell>
                        <FwbTableCell>
                            {{ inventory.warehouse.name }}
                        </FwbTableCell>
                        <FwbTableCell>
                            <div class="flex space-x-2">
                                <FwbA
                                    href="#"
                                    class="p-1 hover:bg-gray-100 rounded"
                                >
                                    <i
                                        class="fa-solid fa-eye text-black hover:text-blue-600"
                                    />
                                </FwbA>
                                <FwbA
                                    href="#"
                                    class="p-1 hover:bg-gray-100 rounded"
                                >
                                    <i
                                        class="fa-solid fa-pencil text-black hover:text-blue-600"
                                    />
                                </FwbA>
                                <FwbA
                                    href="#"
                                    class="p-1 hover:bg-gray-100 rounded"
                                >
                                    <i
                                        class="fa-solid fa-trash text-black hover:text-blue-600"
                                    ></i>
                                </FwbA>
                            </div>
                        </FwbTableCell>
                    </FwbTableRow>
                </FwbTableBody>
            </FwbTable>
        </div>
        <div v-else>
            <p>No hay inventarios (Lotes de medicamento) para este almacén.</p>
        </div>
        <div class="flex justify-center my-4">
            <FwbPagination
                v-model="currentPage"
                :total-items="props.inventories.total"
                :per-page="props.inventories.per_page"
                large
            ></FwbPagination>
        </div>
    </AdminLayout>
</template>
