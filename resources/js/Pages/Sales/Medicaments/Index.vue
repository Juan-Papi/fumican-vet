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

const props = defineProps({ medicaments: Object });
const currentPage = ref(props.medicaments.current_page || 1);

watch(currentPage, (newPage) => {
    router.get(
        route("medicament.index"),
        { page: newPage },
        { preserveState: true }
    );
});
</script>

<template>
    <AdminLayout title="Medicamentos">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Medicamentos
            </h2>
            <FwbButton
                :href="route('medicament.create')"
                type="button"
                color="purple"
            >
                <i class="fa-solid fa-plus"></i>
                Agregar medicamento
            </FwbButton>
        </div>
        <div>
            <FwbTable>
                <FwbTableHead>
                    <FwbTableHeadCell>Nombre</FwbTableHeadCell>
                    <FwbTableHeadCell>Dosificación</FwbTableHeadCell>
                    <FwbTableHeadCell>Fabricante</FwbTableHeadCell>
                    <FwbTableHeadCell>Fecha de expiración</FwbTableHeadCell>
                    <FwbTableHeadCell>Sustancia controlada</FwbTableHeadCell>
                    <FwbTableHeadCell>Categoría</FwbTableHeadCell>
                    <FwbTableHeadCell>Última modificación</FwbTableHeadCell>
                    <FwbTableHeadCell>
                        <span class="sr-only">Acciones</span>
                    </FwbTableHeadCell>
                </FwbTableHead>
                <FwbTableBody>
                    <FwbTableRow
                        v-for="medicament in medicaments.data"
                        :key="medicament.id"
                    >
                        <FwbTableCell>{{ medicament.name }}</FwbTableCell>
                        <FwbTableCell>{{ medicament.dosage }}</FwbTableCell>
                        <FwbTableCell>{{
                            medicament.manufacturer
                        }}</FwbTableCell>
                        <FwbTableCell>
                            {{ medicament.expiration_date }}
                        </FwbTableCell>
                        <FwbTableCell>
                            {{
                                medicament.controlled_substance === "yes"
                                    ? "Sí"
                                    : "No"
                            }}
                        </FwbTableCell>
                        <FwbTableCell>
                            {{ medicament.category.name }}
                        </FwbTableCell>
                        <FwbTableCell>{{ medicament.updated_at }}</FwbTableCell>
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

            <div class="flex justify-center my-4">
                <FwbPagination
                    v-model="currentPage"
                    :total-items="medicaments.total"
                    :per-page="medicaments.per_page"
                    large
                ></FwbPagination>
            </div>
        </div>
    </AdminLayout>
</template>
