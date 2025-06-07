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

const props = defineProps({ categories: Object });
const currentPage = ref(props.categories.current_page || 1);

watch(currentPage, (newPage) => {
    router.get(
        route("category.index"),
        { page: newPage },
        { preserveState: true }
    );
});
</script>

<template>
    <AdminLayout title="Categorías">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Categorías
            </h2>
            <FwbButton
                :href="route('category.create')"
                type="button"
                color="purple"
            >
                <i class="fa-solid fa-plus"></i>
                Agregar categoría
            </FwbButton>
        </div>
        <div>
            <FwbTable>
                <FwbTableHead>
                    <FwbTableHeadCell>Nombre</FwbTableHeadCell>
                    <FwbTableHeadCell>Última modificación</FwbTableHeadCell>
                    <FwbTableHeadCell>
                        <span class="sr-only">Acciones</span>
                    </FwbTableHeadCell>
                </FwbTableHead>
                <FwbTableBody>
                    <FwbTableRow
                        v-for="category in categories.data"
                        :key="category.id"
                    >
                        <FwbTableCell>{{ category.name }}</FwbTableCell>
                        <FwbTableCell>{{ category.updated_at }}</FwbTableCell>
                        <FwbTableCell class="flex justify-end gap-x-3">
                            <FwbButton
                                @click="
                                    router.get(
                                        route('purchase.show', purchase.id)
                                    )
                                "
                                color="alternative"
                                square
                            >
                                <i class="fa-solid fa-eye lg:mr-2" />
                                <span class="hidden lg:inline">Ver</span>
                            </FwbButton>
                            <FwbButton
                                @click="
                                    router.get(
                                        route('purchase.edit', purchase.id)
                                    )
                                "
                                color="alternative"
                                square
                            >
                                <i class="fa-solid fa-pencil lg:mr-2" />
                                <span class="hidden lg:inline">Editar</span>
                            </FwbButton>
                            <FwbButton
                                @click="
                                    router.delete(
                                        route('purchase.destroy', purchase.id)
                                    )
                                "
                                color="alternative"
                                square
                            >
                                <i class="fa-solid fa-trash lg:mr-2"></i>
                                <span class="hidden lg:inline">Eliminar</span>
                            </FwbButton>
                        </FwbTableCell>
                    </FwbTableRow>
                </FwbTableBody>
            </FwbTable>

            <div class="flex justify-center my-4">
                <FwbPagination
                    v-model="currentPage"
                    :total-items="categories.total"
                    :per-page="categories.per_page"
                    large
                ></FwbPagination>
            </div>
        </div>
    </AdminLayout>
</template>
