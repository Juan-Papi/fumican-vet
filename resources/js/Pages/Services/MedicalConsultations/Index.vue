<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PermissionEnum from "@/Utils/Enums/PermissionEnum";
import { usePage } from "@inertiajs/vue3";
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

const props = defineProps({ medicalConsultations: Object });
const currentPage = ref(props.medicalConsultations.current_page || 1);

watch(currentPage, (newPage) => {
    router.get(
        route("medical-consultations.index"),
        { page: newPage },
        { preserveState: true }
    );
});

const isEmptyData = computed(() => {
    return props.medicalConsultations.data.length === 0;
});

// PERMISSIONS
const page = usePage(); // Debe ser importado
const canCreateMedCons = page.props.auth.user_permissions.some(
    (permission) => permission.name === PermissionEnum.CREAR_CONSULTAS_MEDICAS
);
const canEditMedCons = page.props.auth.user_permissions.some(
    (permission) => permission.name === PermissionEnum.EDITAR_CONSULTAS_MEDICAS
);
</script>

<template>
    <AdminLayout title="Consultas Médicas">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Consultas Médicas
            </h2>
            <FwbButton
                v-if="canCreateMedCons"
                :href="route('medical-consultations.create')"
                type="button"
                color="purple"
            >
                Agregar consulta
            </FwbButton>
        </div>
        <fwb-table>
            <FwbTableHead class="th">
                <FwbTableHeadCell>ID</FwbTableHeadCell>
                <FwbTableHeadCell>fecha</FwbTableHeadCell>
                <FwbTableHeadCell>Propietario</FwbTableHeadCell>
                <FwbTableHeadCell>Mascota</FwbTableHeadCell>
                <FwbTableHeadCell>Motivo</FwbTableHeadCell>
                <FwbTableHeadCell>Última actualización</FwbTableHeadCell>
                <FwbTableHeadCell v-if="canEditMedCons">
                    <span class="sr-only">Edit</span>
                </FwbTableHeadCell>
            </FwbTableHead>
            <FwbTableBody>
                <FwbTableRow v-if="isEmptyData">
                    <FwbTableCell colspan="7" class="text-center">
                        <div class="text-center text-gray-500">No hay consultas médicas registradas</div>
                    </FwbTableCell>
                </FwbTableRow>
                <FwbTableRow v-for="consultation in medicalConsultations.data">
                    <FwbTableCell>{{ consultation.id }}</FwbTableCell>
                    <FwbTableCell>{{ consultation.created_at }}</FwbTableCell>
                    <FwbTableCell>{{ consultation.pet_owner }}</FwbTableCell>
                    <FwbTableCell>{{ consultation.pet_name }}</FwbTableCell>
                    <FwbTableCell>{{ consultation.reason }}</FwbTableCell>
                    <FwbTableCell>{{ consultation.updated_at }}</FwbTableCell>
                    <FwbTableCell v-if="canEditMedCons">
                        <fwb-a :href="route('medical-consultations.edit', consultation.id)"> Edit </fwb-a>
                    </FwbTableCell>
                </FwbTableRow>
            </FwbTableBody>
        </fwb-table>

        <div class="flex justify-center my-4">
            <FwbPagination
                v-model="currentPage"
                :total-items="medicalConsultations.total"
                :per-page="medicalConsultations.per_page"
                large
            ></FwbPagination>
        </div>
    </AdminLayout>
</template>
