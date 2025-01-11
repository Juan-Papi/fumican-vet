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
import { ref, watch } from "vue";

const props = defineProps({ users: Object });
const currentPage = ref(props.users.current_page || 1);
const page = usePage();

watch(currentPage, (newPage) => {
    router.get(
        route("users.index"),
        { page: newPage },
        { preserveState: true }
    );
});

const canCreateUsers = page.props.auth.user_permissions.some(
    (permission) => permission.name === PermissionEnum.CREAR_USUARIOS
);
const canEditUsers = page.props.auth.user_permissions.some(
    (permission) => permission.name === PermissionEnum.EDITAR_USUARIOS
);
</script>

<template>
    <AdminLayout title="Usuarios">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Usuarios
            </h2>
            <FwbButton
                v-if="canCreateUsers"
                :href="route('users.create')"
                type="button"
                color="purple"
            >
                Agregar usuario
            </FwbButton>
        </div>
        <fwb-table>
            <FwbTableHead class="th">
                <FwbTableHeadCell>Nombre</FwbTableHeadCell>
                <FwbTableHeadCell>Apellido</FwbTableHeadCell>
                <FwbTableHeadCell>Rol</FwbTableHeadCell>
                <FwbTableHeadCell>Correo</FwbTableHeadCell>
                <FwbTableHeadCell>Fecha creación</FwbTableHeadCell>
                <FwbTableHeadCell>Fecha actualización</FwbTableHeadCell>
                <FwbTableHeadCell v-if="canEditUsers">
                    <span class="sr-only">Edit</span>
                </FwbTableHeadCell>
            </FwbTableHead>
            <FwbTableBody>
                <FwbTableRow v-for="user in users.data">
                    <FwbTableCell>{{ user.first_name }}</FwbTableCell>
                    <FwbTableCell>{{ user.last_name }}</FwbTableCell>
                    <FwbTableCell>{{ user.role?.name }}</FwbTableCell>
                    <FwbTableCell>{{ user.email }}</FwbTableCell>
                    <FwbTableCell>{{ user.created_at }}</FwbTableCell>
                    <FwbTableCell>{{ user.updated_at }}</FwbTableCell>
                    <FwbTableCell v-if="canEditUsers">
                        <fwb-a :href="route('users.edit', user.id)">
                            Edit
                        </fwb-a>
                    </FwbTableCell>
                </FwbTableRow>
            </FwbTableBody>
        </fwb-table>

        <div class="flex justify-center my-4">
            <FwbPagination
                v-model="currentPage"
                :total-items="users.total"
                :per-page="users.per_page"
                large
            ></FwbPagination>
        </div>
    </AdminLayout>
</template>
