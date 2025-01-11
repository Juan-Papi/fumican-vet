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
                Inventarios de {{ props.warehouse.name }}
            </h2>
            <FwbButton
                :href="route('warehouse.show', props.warehouse.id)"
                type="button"
                color="purple"
            >
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
                        <span class="sr-only">Edit</span>
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
                            <FwbA href="#" class="hover:underline"> Edit </FwbA>
                        </FwbTableCell>
                    </FwbTableRow>
                </FwbTableBody>
            </FwbTable>
        </div>
        <div v-else>
            <p>No hay inventarios para este almacén.</p>
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
