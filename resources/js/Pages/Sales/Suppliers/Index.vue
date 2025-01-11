<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
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
import { ref, watch } from "vue";

const props = defineProps({ suppliers: Object });
const currentPage = ref(props.suppliers.current_page || 1);

watch(currentPage, (newPage) => {
    router.get(
        route("supplier.index"),
        { page: newPage },
        { preserveState: true }
    );
});
</script>

<template>
    <AdminLayout title="Proveedores">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Proveedores
            </h2>
            <FwbButton
                :href="route('supplier.create')"
                type="button"
                color="purple"
            >
                Agregar proveedor
            </FwbButton>
        </div>
        <div>
            <FwbTable>
                <FwbTableHead>
                    <FwbTableHeadCell>Nombre</FwbTableHeadCell>
                    <FwbTableHeadCell>País</FwbTableHeadCell>
                    <FwbTableHeadCell>Teléfono</FwbTableHeadCell>
                    <FwbTableHeadCell>Email</FwbTableHeadCell>
                    <FwbTableHeadCell>Dirección</FwbTableHeadCell>
                    <FwbTableHeadCell>Última modificación</FwbTableHeadCell>
                    <FwbTableHeadCell>
                        <span class="sr-only">Editar</span>
                    </FwbTableHeadCell>
                </FwbTableHead>
                <FwbTableBody>
                    <FwbTableRow
                        v-for="supplier in suppliers.data"
                        :key="supplier.id"
                    >
                        <FwbTableCell>{{ supplier.name }}</FwbTableCell>
                        <FwbTableCell>{{ supplier.country }}</FwbTableCell>
                        <FwbTableCell>{{ supplier.phoneNumber }}</FwbTableCell>
                        <FwbTableCell>{{ supplier.email }}</FwbTableCell>
                        <FwbTableCell>{{ supplier.address }}</FwbTableCell>
                        <FwbTableCell>{{ supplier.updated_at }}</FwbTableCell>
                        <FwbTableCell>
                            <FwbA :href="route('supplier.edit', supplier.id)">
                                Editar
                            </FwbA>
                        </FwbTableCell>
                    </FwbTableRow>
                </FwbTableBody>
            </FwbTable>

            <div class="flex justify-center my-4">
                <FwbPagination
                    v-model="currentPage"
                    :total-items="suppliers.total"
                    :per-page="suppliers.per_page"
                    large
                ></FwbPagination>
            </div>
        </div>
    </AdminLayout>
</template>
