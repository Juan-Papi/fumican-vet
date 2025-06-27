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

    // Si la contraseña es automática, no la enviamos (el backend la generará)
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
            <h2 class="text-2xl font-semibold">Usuarios</h2>
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
            class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-100 rounded-lg"
        >
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700"
                    >Buscar por Nombre, Apellido o Correo</label
                >
                <TextInput
                    v-model="filters.search_term"
                    type="text"
                    class="mt-1 block w-full"
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
        <FwbTable>
            <FwbTableHead>
                <FwbTableHeadCell>Nombre Completo</FwbTableHeadCell>
                <FwbTableHeadCell>Correo</FwbTableHeadCell>
                <FwbTableHeadCell>Rol</FwbTableHeadCell>
                <FwbTableHeadCell>Actualizado</FwbTableHeadCell>
                <FwbTableHeadCell
                    ><span class="sr-only">Acciones</span></FwbTableHeadCell
                >
            </FwbTableHead>
            <FwbTableBody>
                <FwbTableRow v-if="!users.data.length"
                    ><FwbTableCell
                        colspan="5"
                        class="text-center py-4 text-gray-500"
                        >No se encontraron usuarios.</FwbTableCell
                    ></FwbTableRow
                >
                <FwbTableRow v-for="user in users.data" :key="user.id">
                    <FwbTableCell>{{ user.full_name }}</FwbTableCell>
                    <FwbTableCell>{{ user.email }}</FwbTableCell>
                    <FwbTableCell>{{
                        user.roles[0]?.name || "N/A"
                    }}</FwbTableCell>
                    <FwbTableCell>{{ user.updated_at }}</FwbTableCell>
                    <FwbTableCell class="space-x-4 whitespace-nowrap">
                        <button
                            @click="openViewModal(user)"
                            class="text-blue-600 hover:text-blue-800"
                            title="Ver Detalles"
                        >
                            <i class="fa-solid fa-eye fa-lg"></i>
                        </button>
                        <button
                            @click="openEditModal(user)"
                            class="text-yellow-500 hover:text-yellow-700"
                            title="Editar"
                        >
                            <i class="fa-solid fa-pencil fa-lg"></i>
                        </button>
                        <button
                            @click="openDeleteModal(user)"
                            class="text-red-500 hover:text-red-700"
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
                ><h3 class="text-xl font-semibold">
                    Detalles del Usuario
                </h3></template
            >
            <template #body>
                <div v-if="selectedUser" class="space-y-4 text-sm">
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
            <template #header>Confirmar Eliminación</template>
            <template #body
                ><p class="text-center text-lg">
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
                ><h3 class="text-xl font-semibold">
                    {{ modalMode === "edit" ? "Editar" : "Registrar" }} Usuario
                </h3></template
            >
            <template #body>
                <form class="space-y-4" @submit.prevent="submitForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Nombre(s)" /><TextInput
                                v-model="form.first_name"
                                class="mt-1 w-full"
                            /><InputError
                                :message="formErrors.first_name?.[0]"
                            />
                        </div>
                        <div>
                            <InputLabel value="Apellido(s)" /><TextInput
                                v-model="form.last_name"
                                class="mt-1 w-full"
                            /><InputError
                                :message="formErrors.last_name?.[0]"
                            />
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Correo Electrónico" /><TextInput
                            v-model="form.email"
                            type="email"
                            class="mt-1 w-full"
                        /><InputError :message="formErrors.email?.[0]" />
                    </div>
                    <div>
                        <InputLabel value="Rol" />
                        <select
                            v-model="form.role_id"
                            class="w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
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
                    <hr />
                    <div>
                        <fwb-toggle
                            v-model="autoPassword"
                            label="Generar contraseña automáticamente"
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
                                class="mt-1 w-full pr-10"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 top-6 pr-3 flex items-center text-sm leading-5"
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
                                class="mt-1 w-full"
                            />
                            <InputError
                                :message="formErrors.password_confirmation?.[0]"
                            />
                        </div>
                    </div>
                    <p
                        v-if="modalMode === 'edit' && autoPassword"
                        class="text-xs text-gray-500"
                    >
                        Dejar los campos de contraseña vacíos para no cambiarla.
                    </p>
                </form>
            </template>
            <template #footer>
                <div class="flex justify-end">
                    <FwbButton @click="closeAllModals" color="alternative"
                        >Cancelar</FwbButton
                    ><FwbButton
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
