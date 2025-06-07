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

// Props recibidas desde el controlador
const props = defineProps({ warehouses: Object });
const currentPage = ref(props.warehouses.current_page || 1);

// Watch para manejar la paginación
watch(currentPage, (newPage) => {
    router.get(
        route("warehouse.index"),
        { page: newPage },
        { preserveState: true }
    );
});
</script>

<template>
    <AdminLayout title="Almacenes">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Almacenes
            </h2>
            <FwbButton
                :href="route('warehouse.create')"
                type="button"
                color="purple"
            >
                Agregar Almacén
            </FwbButton>
        </div>
        <div>
            <FwbTable>
                <FwbTableHead>
                    <FwbTableHeadCell>Nombre</FwbTableHeadCell>
                    <FwbTableHeadCell>Ubicación</FwbTableHeadCell>
                    <FwbTableHeadCell>Descripción</FwbTableHeadCell>
                    <FwbTableHeadCell>Última modificación</FwbTableHeadCell>
                    <FwbTableHeadCell>
                        <span class="sr-only">Ver</span>
                    </FwbTableHeadCell>
                    <FwbTableHeadCell>
                        <span class="sr-only">Editar</span>
                    </FwbTableHeadCell>
                    <FwbTableHeadCell>
                        <span class="sr-only">Eliminar</span>
                    </FwbTableHeadCell>
                </FwbTableHead>
                <FwbTableBody>
                    <FwbTableRow
                        v-for="warehouse in warehouses.data"
                        :key="warehouse.id"
                    >
                        <FwbTableCell>{{ warehouse.name }}</FwbTableCell>
                        <FwbTableCell>{{ warehouse.location }}</FwbTableCell>
                        <FwbTableCell>{{
                            warehouse.description || "-"
                        }}</FwbTableCell>
                        <FwbTableCell>{{ warehouse.updated_at }}</FwbTableCell>
                        <FwbTableCell>
                            <FwbA :href="route('warehouse.show', warehouse.id)">
                                <i class="fa-solid fa-eye lg:mr-2 text-black hover:text-blue-600" />
                            </FwbA>
                        </FwbTableCell>

                        <FwbTableCell>
                            <FwbA href="#">
                                <i class="fa-solid fa-pencil lg:mr-2 text-black hover:text-blue-600" />
                            </FwbA>
                        </FwbTableCell>
                        <FwbTableCell>
                            <FwbA href="#">
                                <i class="fa-solid fa-trash lg:mr-2 text-black hover:text-blue-600"></i>
                            </FwbA>
                        </FwbTableCell>
                    </FwbTableRow>
                </FwbTableBody>
            </FwbTable>

            <div class="flex justify-center my-4">
                <FwbPagination
                    v-model="currentPage"
                    :total-items="warehouses.total"
                    :per-page="warehouses.per_page"
                    large
                ></FwbPagination>
            </div>
        </div>
    </AdminLayout>
</template>
