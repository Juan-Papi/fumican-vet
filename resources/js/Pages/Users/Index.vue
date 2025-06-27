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

// Components
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";

// --- PROPS & FILTERS ---
const props = defineProps({ users: Object, roles: Array, filters: Object });

const filters = ref({ search_term: props.filters?.search_term || "" });
function applyFilters() {
    router.get(route("users.search"), filters.value, {
        preserveState: true,
        replace: true,
    });
}
function resetFilters() {
    filters.value = { search_term: "" };
    router.get(route("users.index"));
}

// --- PAGINATION ---
const currentPage = ref(props.users.current_page || 1);
watch(currentPage, (newPage) => {
    router.get(
        route("users.search"),
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
const isDeleteModal = ref(false);
const selectedUser = ref(null);

// --- FORM ---
const defaultFormState = {
    id: null,
    first_name: "",
    last_name: "",
    email: "",
    role_id: "",
    password: "",
    password_confirmation: "",
};
const form = ref({ ...defaultFormState });
const formErrors = ref({});
const autoPassword = ref(true);
const showPassword = ref(false);

// --- HELPERS & PERMISSIONS ---
const page = usePage();
const canCreateUsers = true;
const canEditUsers = true;

// --- FUNCTIONS ---
function displayToast(type, message) {
    toastType.value = type;
    toastMsg.value = message;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 3000);
}

function openCreateModal() {
    modalMode.value = "create";
    autoPassword.value = true;
    form.value = { ...defaultFormState };
    formErrors.value = {};
    isCreateOrEditModal.value = true;
}

function openEditModal(user) {
    modalMode.value = "edit";
    selectedUser.value = user;
    autoPassword.value = true;
    form.value = {
        ...defaultFormState,
        ...user,
        role_id: user.roles[0]?.id || "",
        password: "",
        password_confirmation: "",
    };
    formErrors.value = {};
    isCreateOrEditModal.value = true;
}

function openViewModal(user) {
    selectedUser.value = user;
    isViewModal.value = true;
}

function openDeleteModal(user) {
    selectedUser.value = user;
    isDeleteModal.value = true;
}

function closeAllModals() {
    isCreateOrEditModal.value = false;
    isViewModal.value = false;
    isDeleteModal.value = false;
}

// --- CRUD ---
async function submitForm() {
    loading.value = true;
    formErrors.value = {};

    const payload = { ...form.value };
    if (autoPassword.value) {
        delete payload.password;
        delete payload.password_confirmation;
    }

    try {
        if (modalMode.value === "edit") {
            await axios.put(
                route("users.update", selectedUser.value.id),
                payload
            );
            displayToast("success", "Usuario actualizado correctamente.");
        } else {
            await axios.post(route("users.store"), payload);
            displayToast("success", "Usuario registrado correctamente.");
        }
        closeAllModals();
        router.reload({ only: ["users"] });
    } catch (e) {
        if (e.response?.status === 422) {
            formErrors.value = e.response.data.errors;
            displayToast(
                "danger",
                "Por favor, corrige los errores del formulario."
            );
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

async function submitDelete() {
    loading.value = true;
    try {
        await axios.delete(route("users.destroy", selectedUser.value.id));
        displayToast("success", "Usuario eliminado correctamente.");
        closeAllModals();
        router.reload({ only: ["users"] });
    } catch (e) {
        displayToast(
            "danger",
            e.response?.data?.message || "Error al eliminar el usuario."
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <AdminLayout title="Usuarios">
        <!-- Toast Notification -->
        <div class="fixed top-4 right-4 z-50">
            <FwbToast v-if="showToast" :type="toastType" closable>{{
                toastMsg
            }}</FwbToast>
        </div>

        <!-- Header & Actions -->
        <div class="flex justify-between my-6 items-center">
            <h2 class="text-2xl font-semibold themed-text-base">Usuarios</h2>
            <FwbButton
                v-if="canCreateUsers"
                @click="openCreateModal"
                color="purple"
                ><i class="fa-solid fa-plus mr-2"></i>Agregar Usuario</FwbButton
            >
        </div>

        <!-- Filters -->
        <form
            @submit.prevent="applyFilters"
            class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 p-4 themed-bg-secondary-light rounded-lg"
        >
            <div class="md:col-span-3">
                <label class="block text-sm font-medium themed-text-muted"
                    >Buscar por Nombre, Apellido o Correo</label
                >
                <TextInput
                    v-model="filters.search_term"
                    type="text"
                    class="mt-1 block w-full themed-input"
                    placeholder="Escriba para buscar..."
                />
            </div>
            <div class="flex items-end space-x-2">
                <FwbButton color="purple" type="submit">Filtrar</FwbButton>
                <FwbButton color="alternative" @click.prevent="resetFilters"
                    >Limpiar</FwbButton
                >
            </div>
        </form>

        <!-- Users Table -->
        <FwbTable class="themed-table">
            <FwbTableHead>
                <FwbTableHeadCell class="themed-table-header"
                    >Nombre Completo</FwbTableHeadCell
                >
                <FwbTableHeadCell class="themed-table-header"
                    >Correo</FwbTableHeadCell
                >
                <FwbTableHeadCell class="themed-table-header"
                    >Rol</FwbTableHeadCell
                >
                <FwbTableHeadCell class="themed-table-header"
                    >Actualizado</FwbTableHeadCell
                >
                <FwbTableHeadCell class="themed-table-header"
                    ><span class="sr-only">Acciones</span></FwbTableHeadCell
                >
            </FwbTableHead>
            <FwbTableBody class="themed-table-body">
                <FwbTableRow v-if="!users.data.length"
                    ><FwbTableCell
                        colspan="5"
                        class="text-center py-4 themed-text-muted"
                        >No se encontraron usuarios.</FwbTableCell
                    ></FwbTableRow
                >
                <FwbTableRow
                    v-for="user in users.data"
                    :key="user.id"
                    class="themed-table-row"
                >
                    <FwbTableCell class="themed-text-base">{{
                        user.full_name
                    }}</FwbTableCell>
                    <FwbTableCell class="themed-text-muted">{{
                        user.email
                    }}</FwbTableCell>
                    <FwbTableCell class="themed-text-base">{{
                        user.roles[0]?.name || "N/A"
                    }}</FwbTableCell>
                    <FwbTableCell class="themed-text-muted">{{
                        user.updated_at
                    }}</FwbTableCell>
                    <FwbTableCell class="space-x-4 whitespace-nowrap">
                        <button
                            @click="openViewModal(user)"
                            class="themed-action-button-view"
                            title="Ver Detalles"
                        >
                            <i class="fa-solid fa-eye fa-lg"></i>
                        </button>
                        <button
                            @click="openEditModal(user)"
                            class="themed-action-button-edit"
                            title="Editar"
                        >
                            <i class="fa-solid fa-pencil fa-lg"></i>
                        </button>
                        <button
                            @click="openDeleteModal(user)"
                            class="themed-action-button-delete"
                            title="Eliminar"
                        >
                            <i class="fa-solid fa-trash fa-lg"></i>
                        </button>
                    </FwbTableCell>
                </FwbTableRow>
            </FwbTableBody>
        </FwbTable>
        <div v-if="users.data.length" class="flex justify-center my-4">
            <FwbPagination
                v-model="currentPage"
                :total-items="users.total"
                :per-page="users.per_page"
                large
            />
        </div>

        <!-- View Modal -->
        <FwbModal size="lg" v-if="isViewModal" @close="closeAllModals">
            <template #header
                ><h3 class="text-xl font-semibold themed-text-base">
                    Detalles del Usuario
                </h3></template
            >
            <template #body>
                <div
                    v-if="selectedUser"
                    class="space-y-4 text-sm themed-text-base p-2"
                >
                    <p><strong>Nombre:</strong> {{ selectedUser.full_name }}</p>
                    <p><strong>Correo:</strong> {{ selectedUser.email }}</p>
                    <p>
                        <strong>Rol:</strong>
                        {{ selectedUser.roles[0]?.name || "N/A" }}
                    </p>
                    <p>
                        <strong>Registrado:</strong>
                        {{ selectedUser.created_at }}
                    </p>
                </div>
            </template>
            <template #footer
                ><div class="flex justify-end w-full">
                    <FwbButton @click="closeAllModals" color="alternative"
                        >Cerrar</FwbButton
                    >
                </div></template
            >
        </FwbModal>

        <!-- Delete Modal -->
        <FwbModal v-if="isDeleteModal" @close="closeAllModals">
            <template #header
                ><h3 class="themed-text-base">
                    Confirmar Eliminación
                </h3></template
            >
            <template #body
                ><p class="text-center text-lg themed-text-base">
                    ¿Seguro que deseas eliminar a
                    <strong>{{ selectedUser?.full_name }}</strong
                    >?
                </p></template
            >
            <template #footer
                ><div class="flex justify-center w-full">
                    <FwbButton @click="closeAllModals" color="alternative"
                        >Cancelar</FwbButton
                    ><FwbButton
                        @click="submitDelete"
                        color="red"
                        :loading="loading"
                        class="ml-2"
                        >Eliminar</FwbButton
                    >
                </div></template
            >
        </FwbModal>

        <!-- Create/Edit Modal -->
        <FwbModal size="2xl" v-if="isCreateOrEditModal" @close="closeAllModals">
            <template #header
                ><h3 class="text-xl font-semibold themed-text-base">
                    {{ modalMode === "edit" ? "Editar" : "Registrar" }} Usuario
                </h3></template
            >
            <template #body>
                <form
                    class="space-y-4 themed-text-base p-2"
                    @submit.prevent="submitForm"
                >
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Nombre(s)" /><TextInput
                                v-model="form.first_name"
                                class="mt-1 w-full themed-input"
                            /><InputError
                                :message="formErrors.first_name?.[0]"
                            />
                        </div>
                        <div>
                            <InputLabel value="Apellido(s)" /><TextInput
                                v-model="form.last_name"
                                class="mt-1 w-full themed-input"
                            /><InputError
                                :message="formErrors.last_name?.[0]"
                            />
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Correo Electrónico" /><TextInput
                            v-model="form.email"
                            type="email"
                            class="mt-1 w-full themed-input"
                        /><InputError :message="formErrors.email?.[0]" />
                    </div>
                    <div>
                        <InputLabel value="Rol" />
                        <select
                            v-model="form.role_id"
                            class="w-full mt-1 rounded-md shadow-sm themed-input"
                        >
                            <option value="" disabled>Seleccione un rol</option>
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                        <InputError :message="formErrors.role_id?.[0]" />
                    </div>
                    <hr class="themed-border" />
                    <div>
                        <FwbToggle
                            v-model="autoPassword"
                            label="Generar/Mantener contraseña automáticamente"
                        />
                    </div>
                    <div
                        v-if="!autoPassword"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                    >
                        <div class="relative">
                            <InputLabel value="Contraseña" />
                            <TextInput
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="mt-1 w-full pr-10 themed-input"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 top-6 pr-3 flex items-center text-sm leading-5 themed-text-muted"
                            >
                                <i
                                    class="fa-solid"
                                    :class="{
                                        'fa-eye': !showPassword,
                                        'fa-eye-slash': showPassword,
                                    }"
                                ></i>
                            </button>
                            <InputError :message="formErrors.password?.[0]" />
                        </div>
                        <div class="relative">
                            <InputLabel value="Confirmar Contraseña" />
                            <TextInput
                                v-model="form.password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                class="mt-1 w-full themed-input"
                            />
                            <InputError
                                :message="formErrors.password_confirmation?.[0]"
                            />
                        </div>
                    </div>
                    <p
                        v-if="modalMode === 'edit' && autoPassword"
                        class="text-xs themed-text-muted"
                    >
                        La contraseña actual se mantendrá sin cambios.
                    </p>
                </form>
            </template>
            <template #footer
                ><div class="flex justify-end">
                    <FwbButton @click="closeAllModals" color="alternative"
                        >Cancelar</FwbButton
                    ><FwbButton
                        @click="submitForm"
                        color="purple"
                        :loading="loading"
                        class="ml-2"
                        >Guardar</FwbButton
                    >
                </div></template
            >
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

/* AÑADIDO: Estilos para el modal */
:deep(.fwb-modal-header) {
    background-color: var(--color-background-secondary);
    border-bottom: 1px solid var(--color-border);
}

:deep(.fwb-modal-body) {
    background-color: var(--color-background-secondary);
}

:deep(.fwb-modal-footer) {
    background-color: var(--color-background-secondary);
    border-top: 1px solid var(--color-border);
}
</style>
