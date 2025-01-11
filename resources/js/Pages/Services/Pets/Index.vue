<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PermissionEnum from "@/Utils/Enums/PermissionEnum";
import { router, usePage } from "@inertiajs/vue3";
import {
    FwbA,
    FwbButton,
    FwbPagination,
    FwbTable,
    FwbTableBody,
    FwbTableCell,
    FwbTableHead,
    FwbTableHeadCell,
    FwbTableRow,
} from "flowbite-vue";
import { computed, ref, watch } from "vue";

const props = defineProps({ pets: Object });
const currentPage = ref(props.pets.current_page || 1);

watch(currentPage, (newPage) => {
    router.get(route("pets.index"), { page: newPage }, { preserveState: true });
});

const isEmptyData = computed(() => {
    return props.pets.data.length === 0;
});

// PERMISSIONS
const page = usePage(); // Debe ser importado
const canCreatePets = page.props.auth.user_permissions.some(
    (permission) => permission.name === PermissionEnum.CREAR_MASCOTAS
);
const canEditPets = page.props.auth.user_permissions.some(
    (permission) => permission.name === PermissionEnum.EDITAR_MASCOTAS
);
</script>

<template>
    <AdminLayout title="Mascotas">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Mascotas
            </h2>
            <FwbButton
                v-if="canCreatePets"
                :href="route('pets.create')"
                type="button"
                color="purple"
            >
                Agregar mascota
            </FwbButton>
        </div>
        <fwb-table>
            <FwbTableHead>
                <FwbTableHeadCell>Nombre</FwbTableHeadCell>
                <FwbTableHeadCell>Propietario</FwbTableHeadCell>
                <FwbTableHeadCell>Especie</FwbTableHeadCell>
                <FwbTableHeadCell>Raza</FwbTableHeadCell>
                <FwbTableHeadCell>Fecha de registro</FwbTableHeadCell>
                <FwbTableHeadCell>Fecha actualización</FwbTableHeadCell>
                <FwbTableHeadCell v-if="canEditPets">
                    <span class="sr-only">Edit</span>
                </FwbTableHeadCell>
            </FwbTableHead>
            <FwbTableBody>
                <FwbTableRow v-if="isEmptyData">
                    <FwbTableCell colspan="7" class="text-center py-8">
                        <div class="text-center text-gray-500">
                            No hay consultas médicas registradas
                        </div>
                    </FwbTableCell>
                </FwbTableRow>
                <FwbTableRow v-for="pet in pets.data">
                    <FwbTableCell>{{ pet.name }}</FwbTableCell>
                    <FwbTableCell>{{
                        pet.owner?.first_name + " " + pet.owner?.last_name
                    }}</FwbTableCell>
                    <FwbTableCell>{{ pet.breed?.specie?.name }}</FwbTableCell>
                    <FwbTableCell>{{ pet.breed?.name }}</FwbTableCell>
                    <FwbTableCell>{{ pet.created_at }}</FwbTableCell>
                    <FwbTableCell>{{ pet.updated_at }}</FwbTableCell>
                    <FwbTableCell v-if="canEditPets">
                        <fwb-a :href="route('pets.edit', pet.id)"> Edit </fwb-a>
                    </FwbTableCell>
                </FwbTableRow>
            </FwbTableBody>
        </fwb-table>

        <div class="flex justify-center my-4">
            <FwbPagination
                v-model="currentPage"
                :total-items="pets.total"
                :per-page="pets.per_page"
                large
            ></FwbPagination>
        </div>
    </AdminLayout>
</template>
