<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    FwbButton,
    FwbListGroup,
    FwbListGroupItem,
    FwbModal,
    FwbPagination,
    FwbTable,
    FwbTableBody,
    FwbTableCell,
    FwbTableHead,
    FwbTableHeadCell,
    FwbTableRow,
    FwbToggle,
} from "flowbite-vue";
import { computed, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import RoleEnum from "@/Utils/Enums/RoleEnum";
import PermissionEnum from "@/Utils/Enums/PermissionEnum";

const props = defineProps({ roles: Object, permissions: Array });
const currentPage = ref(props.roles.current_page || 1);

watch(currentPage, (newPage) => {
    router.get(
        route("customers.index"),
        { with_permissions: 1, page: newPage },
        { preserveState: true }
    );
});

const isShowModal = ref(false);
const modalAction = ref(null);
const showModal = (show = true) => {
    isShowModal.value = show;
};

const selectedRole = ref(null);
const setAction = (action, role) => {
    modalAction.value = action;
    if (action === "create") {
        selectedRole.value = { name: "", permissions: [] };
        rolePermissions.value.forEach((permission) => {
            permission.checked = false;
        });
        showModal();
        return;
    }
    selectedRole.value = role;
    rolePermissions.value.forEach((permission) => {
        permission.checked = role.permissions.some(
            (rolePermission) => rolePermission.id === permission.id
        );
    });
    showModal();
};
const rolePermissions = ref(props.permissions);
const isShowingRole = computed(() => modalAction.value === "show");
const isEditingRole = computed(() => modalAction.value === "edit");
const isCreatingRole = computed(() => modalAction.value === "create");
const esGerentePropietario = computed(
    () => selectedRole.value?.name === RoleEnum.GERENTE_PROPIETARIO
);

const isSaving = ref(false);
const save = async () => {
    isSaving.value = true;
    try {
        if (isCreatingRole.value) {
            await router.post(route("roles.store"), {
                name: selectedRole.value.name,
                permissions: rolePermissions.value
                    .filter((p) => p.checked)
                    .map((p) => p.id),
            });
        } else if (isEditingRole.value) {
            await router.put(route("roles.update", selectedRole.value.id), {
                name: selectedRole.value.name,
                permissions: rolePermissions.value
                    .filter((p) => p.checked)
                    .map((p) => p.id),
            });
        }
        showModal(false);
    } catch (error) {
        console.error(error);
    } finally {
        isSaving.value = false;
    }
};

// PERMISSIONS
const page = usePage(); // Debe ser importado
const canCreateRoles = page.props.auth.user_permissions.some(
    (permission) => permission.name === PermissionEnum.CREAR_ROLES
);
const canEditRoles = page.props.auth.user_permissions.some(
    (permission) => permission.name === PermissionEnum.EDITAR_ROLES
);
const canViewRoles = page.props.auth.user_permissions.some(
    (permission) => permission.name === PermissionEnum.VER_ROLES
);
</script>

<template>
    <AdminLayout title="Roles">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Roles
            </h2>
            <FwbButton
                v-if="canCreateRoles"
                @click="setAction('create')"
                type="button"
                color="purple"
            >
                Agregar Role
            </FwbButton>
        </div>
        <div>
            <FwbTable>
                <FwbTableHead class="th">
                    <FwbTableHeadCell>Nombre</FwbTableHeadCell>
                    <FwbTableHeadCell>Fecha de creación</FwbTableHeadCell>
                    <FwbTableHeadCell>Última modificación</FwbTableHeadCell>
                    <FwbTableHeadCell>
                        <span class="sr-only">Edit</span>
                    </FwbTableHeadCell>
                </FwbTableHead>
                <FwbTableBody>
                    <FwbTableRow v-for="role in roles.data" :key="role.id">
                        <FwbTableCell>{{ role.name }}</FwbTableCell>
                        <FwbTableCell>{{ role.created_at }}</FwbTableCell>
                        <FwbTableCell>{{ role.updated_at }}</FwbTableCell>
                        <FwbTableCell class="flex justify-end gap-x-4">
                            <FwbButton
                                v-if="canViewRoles"
                                @click="setAction('show', role)"
                                color="alternative"
                                square
                            >
                                <i class="fa-solid fa-eye lg:mr-2" />
                                <span class="hidden lg:inline">Ver</span>
                            </FwbButton>
                            <FwbButton
                                v-if="canEditRoles"
                                @click="setAction('edit', role)"
                                color="alternative"
                                square
                            >
                                <i class="fa-solid fa-pencil lg:mr-2" />
                                <span class="hidden lg:inline">Editar</span>
                            </FwbButton>
                        </FwbTableCell>
                    </FwbTableRow>
                </FwbTableBody>
            </FwbTable>

            <div class="flex justify-center my-4">
                <FwbPagination
                    v-model="currentPage"
                    :total-items="roles.total"
                    :per-page="roles.per_page"
                    large
                ></FwbPagination>
            </div>
        </div>
    </AdminLayout>

    <FwbModal v-if="isShowModal && !isCreatingRole" @close="showModal(false)">
        <template #header>
            <div class="flex items-center text-lg">
                Rol {{ selectedRole.name }}
            </div>
        </template>
        <template #body>
            <div
                class="flex flex-col px-2 gap-2 h-72 md:h-[34rem] overflow-auto"
            >
                <div class="text-sm">
                    <span class="font-medium mr-2">Creado en: </span>
                    <span
                        class="leading-relaxed text-gray-500 dark:text-gray-400"
                    >
                        {{ selectedRole.created_at }}
                    </span>
                </div>
                <div class="text-sm">
                    <span class="font-medium mr-2">Última modificación: </span>
                    <span
                        class="leading-relaxed text-gray-500 dark:text-gray-400"
                    >
                        {{ selectedRole.updated_at }}
                    </span>
                </div>

                <div class="">
                    <InputLabel for="name" value="Nombre" />
                    <TextInput
                        id="name"
                        v-model="selectedRole.name"
                        class="mt-1 block w-full"
                        required
                        autocomplete="off"
                    />
                    <InputError
                        class="mt-2"
                        :message="selectedRole.errors?.name"
                    />
                </div>

                <hr />

                <div>
                    <span class="font-medium mr-2">Permisos </span>
                    <FwbListGroup class="w-full">
                        <FwbListGroupItem
                            v-for="permission in rolePermissions"
                            class="w-full flex justify-between"
                            hover
                        >
                            <span>{{ permission.name }}</span>
                            <div>
                                <FwbToggle
                                    v-model="permission.checked"
                                    :disabled="
                                        isShowingRole || esGerentePropietario
                                    "
                                />
                            </div>
                        </FwbListGroupItem>
                    </FwbListGroup>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-between">
                <FwbButton @click="showModal(false)" color="alternative">
                    Volver
                </FwbButton>
                <FwbButton v-if="isEditingRole" @click="save" color="dark">
                    Guardar
                </FwbButton>
            </div>
        </template>
    </FwbModal>

    <FwbModal v-if="isShowModal && isCreatingRole" @close="showModal(false)">
        <template #header>
            <div class="flex items-center text-lg">Crear nuevo rol</div>
        </template>
        <template #body>
            <div
                class="flex flex-col px-2 gap-2 h-72 md:h-[34rem] overflow-auto"
            >
                <div class="">
                    <InputLabel for="name" value="Nombre" />
                    <TextInput
                        id="name"
                        v-model="selectedRole.name"
                        class="mt-1 block w-full"
                        required
                        autocomplete="off"
                    />
                    <InputError
                        class="mt-2"
                        :message="selectedRole.errors?.name"
                    />
                </div>

                <hr class="mt-2" />

                <div>
                    <span class="font-medium mr-2">Permisos </span>
                    <FwbListGroup class="w-full mt-2">
                        <FwbListGroupItem
                            v-for="permission in rolePermissions"
                            class="w-full flex justify-between"
                            hover
                        >
                            <span>{{ permission.name }}</span>
                            <div>
                                <FwbToggle
                                    v-model="permission.checked"
                                    :disabled="isShowingRole"
                                />
                            </div>
                        </FwbListGroupItem>
                    </FwbListGroup>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-between">
                <FwbButton @click="showModal(false)" color="alternative">
                    Volver
                </FwbButton>
                <FwbButton @click="save" color="dark"> Guardar </FwbButton>
            </div>
        </template>
    </FwbModal>
</template>
