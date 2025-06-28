<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { usePage, router } from "@inertiajs/vue3";
import {
    FwbButton,
    FwbPagination,
    FwbTable,
    FwbTableBody,
    FwbTableCell,
    FwbTableHead,
    FwbTableHeadCell,
    FwbTableRow,
    FwbModal,
    FwbToast,
    FwbToggle,
} from "flowbite-vue";
import { computed, ref, watch } from "vue";
import axios from "axios";
import RoleEnum from "@/Utils/Enums/RoleEnum";

// Components
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";

// --- PROPS & FILTERS ---
const props = defineProps({
    roles: Object,
    permissions: Array,
    filters: Object,
});

const filters = ref({ search_term: props.filters?.search_term || "" });
function applyFilters() {
    router.get(route("roles.search"), filters.value, {
        preserveState: true,
        replace: true,
    });
}
function resetFilters() {
    filters.value = { search_term: "" };
    router.get(route("roles.index"));
}

// --- PAGINATION ---
const currentPage = ref(props.roles.current_page || 1);
watch(currentPage, (newPage) => {
    router.get(
        route("roles.search"),
        { ...filters.value, page: newPage },
        { preserveState: true, replace: true }
    );
});

// --- STATE MANAGEMENT ---
const loading = ref(false);
const showToast = ref(false);
const toastMsg = ref("");
const toastType = ref("success");

// --- MODALS ---
const modalMode = ref("create");
const isCreateOrEditModal = ref(false);
const isViewModal = ref(false);
const selectedRole = ref(null);

// --- FORM ---
const defaultFormState = { id: null, name: "", permissions: [] };
const form = ref({ ...defaultFormState });
const formErrors = ref({});
const allPermissions = ref([]);

// --- HELPERS & PERMISSIONS ---
const page = usePage();
const canCreateRoles = true;
const canEditRoles = true;
const canViewRoles = true;
const esGerentePropietario = computed(
    () => selectedRole.value?.name === RoleEnum.GERENTE_PROPIETARIO
);

// --- FUNCTIONS ---
function displayToast(type, message) {
    toastType.value = type;
    toastMsg.value = message;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 3000);
}

function setupPermissions(rolePermissions = []) {
    allPermissions.value = props.permissions.map((p) => ({
        ...p,
        checked: rolePermissions.some((rp) => rp.id === p.id),
    }));
}

function openCreateModal() {
    modalMode.value = "create";
    selectedRole.value = null; // Ensure selectedRole is clean for computed properties
    form.value = { ...defaultFormState };
    setupPermissions();
    formErrors.value = {};
    isCreateOrEditModal.value = true;
}

function openEditModal(role) {
    modalMode.value = "edit";
    selectedRole.value = role;
    form.value = { ...defaultFormState, ...role };
    setupPermissions(role.permissions);
    formErrors.value = {};
    isCreateOrEditModal.value = true;
}

function openViewModal(role) {
    modalMode.value = "view";
    selectedRole.value = role;
    setupPermissions(role.permissions);
    isViewModal.value = true;
}

function closeAllModals() {
    isCreateOrEditModal.value = false;
    isViewModal.value = false;
    selectedRole.value = null; // <-- CORREGIDO: Reinicia el rol seleccionado al cerrar.
}

// --- CRUD ---
async function submitForm() {
    loading.value = true;
    formErrors.value = {};

    const payload = {
        name: form.value.name,
        permissions: allPermissions.value
            .filter((p) => p.checked)
            .map((p) => p.id),
    };

    try {
        if (modalMode.value === "edit") {
            await axios.put(
                route("roles.update", selectedRole.value.id),
                payload
            );
            displayToast("success", "Rol actualizado correctamente.");
        } else {
            await axios.post(route("roles.store"), payload);
            displayToast("success", "Rol registrado correctamente.");
        }
        closeAllModals();
        router.reload({ only: ["roles"] });
    } catch (e) {
        if (e.response?.status === 422) {
            formErrors.value = e.response.data.errors;
            displayToast("danger", "Por favor, corrige los errores.");
        } else {
            displayToast(
                "danger",
                e.response?.data?.message || "Ocurrió un error inesperado."
            );
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <AdminLayout title="Roles y Permisos">
        <!-- Toast Notification -->
        <div class="fixed top-4 right-4 z-50">
            <FwbToast v-if="showToast" :type="toastType" closable>{{
                toastMsg
            }}</FwbToast>
        </div>

        <!-- Header & Actions -->
        <div class="flex justify-between my-6 items-center">
            <h2 class="text-2xl font-semibold themed-text-base">
                Roles y Permisos
            </h2>
            <FwbButton
                v-if="canCreateRoles"
                @click="openCreateModal"
                color="purple"
                ><i class="fa-solid fa-plus mr-2"></i>Agregar Rol</FwbButton
            >
        </div>

        <!-- Filters -->
        <form
            @submit.prevent="applyFilters"
            class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 p-4 themed-bg-secondary-light rounded-lg"
        >
            <div class="md:col-span-3">
                <label class="block text-sm font-medium themed-text-muted"
                    >Buscar por Nombre de Rol</label
                >
                <TextInput
                    v-model="filters.search_term"
                    type="text"
                    class="mt-1 block w-full themed-input"
                    placeholder="Escriba un nombre..."
                />
            </div>
            <div class="flex items-end space-x-2">
                <FwbButton color="purple" type="submit">Filtrar</FwbButton>
                <FwbButton color="alternative" @click.prevent="resetFilters"
                    >Limpiar</FwbButton
                >
            </div>
        </form>

        <!-- Roles Table -->
        <FwbTable class="themed-table">
            <FwbTableHead>
                <FwbTableHeadCell class="themed-table-header"
                    >Nombre del Rol</FwbTableHeadCell
                >
                <FwbTableHeadCell class="themed-table-header"
                    >Fecha de Creación</FwbTableHeadCell
                >
                <FwbTableHeadCell class="themed-table-header"
                    >Última Modificación</FwbTableHeadCell
                >
                <FwbTableHeadCell class="themed-table-header"
                    ><span class="sr-only">Acciones</span></FwbTableHeadCell
                >
            </FwbTableHead>
            <FwbTableBody class="themed-table-body">
                <FwbTableRow v-if="!roles.data.length"
                    ><FwbTableCell
                        colspan="4"
                        class="text-center py-4 themed-text-muted"
                        >No se encontraron roles.</FwbTableCell
                    ></FwbTableRow
                >
                <FwbTableRow
                    v-for="role in roles.data"
                    :key="role.id"
                    class="themed-table-row"
                >
                    <FwbTableCell class="themed-text-base">{{
                        role.name
                    }}</FwbTableCell>
                    <FwbTableCell class="themed-text-muted">{{
                        role.created_at
                    }}</FwbTableCell>
                    <FwbTableCell class="themed-text-muted">{{
                        role.updated_at
                    }}</FwbTableCell>
                    <FwbTableCell class="space-x-4 whitespace-nowrap">
                        <button
                            @click="openViewModal(role)"
                            class="themed-action-button-view"
                            title="Ver Permisos"
                        >
                            <i class="fa-solid fa-eye fa-lg"></i>
                        </button>
                        <button
                            v-if="canEditRoles"
                            @click="openEditModal(role)"
                            class="themed-action-button-edit"
                            title="Editar Rol"
                        >
                            <i class="fa-solid fa-pencil fa-lg"></i>
                        </button>
                    </FwbTableCell>
                </FwbTableRow>
            </FwbTableBody>
        </FwbTable>
        <div v-if="roles.data.length" class="flex justify-center my-4">
            <FwbPagination
                v-model="currentPage"
                :total-items="roles.total"
                :per-page="roles.per_page"
                large
            />
        </div>

        <!-- View/Edit/Create Modal -->
        <FwbModal
            size="2xl"
            v-if="isCreateOrEditModal || isViewModal"
            @close="closeAllModals"
        >
            <template #header>
                <h3
                    class="text-xl font-semibold themed-text-base"
                    v-if="modalMode === 'create'"
                >
                    Crear Nuevo Rol
                </h3>
                <h3
                    class="text-xl font-semibold themed-text-base"
                    v-if="modalMode === 'edit'"
                >
                    Editar Rol: {{ selectedRole.name }}
                </h3>
                <h3
                    class="text-xl font-semibold themed-text-base"
                    v-if="modalMode === 'view'"
                >
                    Permisos del Rol: {{ selectedRole.name }}
                </h3>
            </template>
            <template #body>
                <form class="space-y-4 p-2" @submit.prevent="submitForm">
                    <div>
                        <InputLabel value="Nombre del Rol" />
                        <TextInput
                            v-model="form.name"
                            class="mt-1 w-full themed-input"
                            :disabled="
                                modalMode === 'view' || esGerentePropietario
                            "
                        />
                        <InputError :message="formErrors.name?.[0]" />
                    </div>
                    <hr class="themed-border" />
                    <div>
                        <h4 class="font-semibold themed-text-base mb-2">
                            Permisos
                        </h4>
                        <div class="h-80 overflow-y-auto space-y-2 pr-2">
                            <div
                                v-for="permission in allPermissions"
                                :key="permission.id"
                                class="flex items-center justify-between p-2 rounded-md themed-bg-secondary-light"
                            >
                                <span class="themed-text-muted">{{
                                    permission.name
                                }}</span>
                                <FwbToggle
                                    v-model="permission.checked"
                                    :disabled="
                                        modalMode === 'view' ||
                                        esGerentePropietario
                                    "
                                />
                            </div>
                        </div>
                        <InputError :message="formErrors.permissions?.[0]" />
                    </div>
                </form>
            </template>
            <template #footer>
                <div class="flex justify-end w-full">
                    <FwbButton @click="closeAllModals" color="alternative"
                        >Cerrar</FwbButton
                    >
                    <FwbButton
                        v-if="modalMode !== 'view'"
                        @click="submitForm"
                        color="purple"
                        :loading="loading"
                        class="ml-2"
                        >Guardar</FwbButton
                    >
                </div>
            </template>
        </FwbModal>
    </AdminLayout>
</template>

<style scoped>
/* Estilos que usan las variables de tema */
.themed-text-base {
    color: var(--color-text-base);
}
.themed-text-muted {
    color: var(--color-text-muted);
}
.themed-bg-secondary-light {
    background-color: var(--color-background-secondary);
}
.themed-border {
    border-color: var(--color-border);
}

.themed-input {
    background-color: var(--color-background-secondary);
    color: var(--color-text-base);
    border: 1px solid var(--color-border);
}
.themed-input:focus {
    --tw-ring-color: var(--color-primary);
    border-color: var(--color-primary);
}

.themed-table-header {
    background-color: var(--color-background);
    color: var(--color-text-muted);
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
}

.themed-table-body {
    background-color: var(--color-background-secondary);
}

.themed-table-row {
    border-bottom: 1px solid var(--color-border);
}
.themed-table-row:hover {
    background-color: var(--color-background);
}

.themed-action-button-view {
    color: #3b82f6;
}
.themed-action-button-view:hover {
    color: #1d4ed8;
}
.themed-action-button-edit {
    color: #f59e0b;
}
.themed-action-button-edit:hover {
    color: #b45309;
}
.themed-action-button-delete {
    color: #ef4444;
}
.themed-action-button-delete:hover {
    color: #b91c1c;
}

/* Estilos para el modal */
:deep(.fwb-modal-header) {
    background-color: var(--color-background-secondary);
    border-bottom: 1px solid var(--color-border);
    color: var(--color-text-base);
}

:deep(.fwb-modal-body) {
    background-color: var(--color-background-secondary);
    color: var(--color-text-base);
}
:deep(.fwb-modal-body p),
:deep(.fwb-modal-body strong),
:deep(.fwb-modal-body h4),
:deep(.fwb-modal-body label),
:deep(.fwb-modal-body span) {
    color: inherit;
}

:deep(.fwb-modal-footer) {
    background-color: var(--color-background-secondary);
    border-top: 1px solid var(--color-border);
}
</style>
