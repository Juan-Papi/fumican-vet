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
    FwbToast,
    FwbModal,
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

// Variables y estados modales
const isCreateModal = ref(false);
const isEditModal = ref(false);
const isDeleteModal = ref(false);
const isProcessing = ref(false);
const isDeleting = ref(false);

const form = ref({
    name: "",
    location: "",
    description: "",
});

const selectedWarehouse = ref(null);

const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success");

// Abrir modales
function openCreateModal() {
    form.value = { name: "", location: "", description: "" };
    isCreateModal.value = true;
}

function openEditModal(warehouse) {
    selectedWarehouse.value = warehouse;
    form.value = {
        name: warehouse.name,
        location: warehouse.location,
        description: warehouse.description,
    };
    isEditModal.value = true;
}

function openDeleteModal(warehouse) {
    selectedWarehouse.value = warehouse;
    isDeleteModal.value = true;
}

// Cerrar modales
function closeCreateModal() {
    isCreateModal.value = false;
    isProcessing.value = false;
}

function closeEditModal() {
    isEditModal.value = false;
    isProcessing.value = false;
    selectedWarehouse.value = null;
}

function closeDeleteModal() {
    isDeleteModal.value = false;
    isDeleting.value = false;
    selectedWarehouse.value = null;
}

// Submit Create
function submitCreate() {
    if (isProcessing.value) return;
    isProcessing.value = true;

    router.post(route("warehouse.store"), form.value, {
        onSuccess: () => {
            closeCreateModal();
            toastType.value = "success";
            toastMessage.value = "Almacén creado correctamente";
            showToast.value = true;
        },
        onError: () => {
            toastType.value = "danger";
            toastMessage.value = "Error al crear almacén";
            showToast.value = true;
        },
        onFinish: () => {
            isProcessing.value = false;
            setTimeout(() => (showToast.value = false), 3000);
        },
    });
}

// Submit Edit
function submitEdit() {
    if (isProcessing.value) return;
    isProcessing.value = true;

    router.put(
        route("warehouse.update", selectedWarehouse.value.id),
        form.value,
        {
            onSuccess: () => {
                closeEditModal();
                toastType.value = "success";
                toastMessage.value = "Almacén actualizado correctamente";
                showToast.value = true;
            },
            onError: () => {
                toastType.value = "danger";
                toastMessage.value = "Error al actualizar almacén";
                showToast.value = true;
            },
            onFinish: () => {
                isProcessing.value = false;
                setTimeout(() => (showToast.value = false), 3000);
            },
        }
    );
}

// Submit Delete
function submitDelete() {
    if (isDeleting.value) return;
    isDeleting.value = true;

    router.post(
        route("warehouse.destroy", selectedWarehouse.value.id),
        {},
        {
            onSuccess: () => {
                closeDeleteModal();
                toastType.value = "success";
                toastMessage.value = "Almacén eliminado correctamente";
                showToast.value = true;
            },
            onError: () => {
                isDeleting.value = false;
                toastType.value = "danger";
                toastMessage.value = "Error al eliminar almacén";
                showToast.value = true;
            },
            onFinish: () => {
                isDeleting.value = false;
                setTimeout(() => (showToast.value = false), 3000);
            },
        }
    );
}
</script>

<template>
    <AdminLayout title="Almacenes">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                <i class="fa-solid fa-warehouse mr-1 text-purple-600"></i>
                <i class="fa-solid fa-warehouse mr-1 text-purple-400"></i>
                <i class="fa-solid fa-warehouse text-purple-200"></i>
                Almacenes
            </h2>
            <FwbButton
                type="button"
                color="purple"
                class="flex items-center gap-2"
                @click="openCreateModal"
            >
                <i class="fa-solid fa-plus"></i>
                Agregar Almacén
            </FwbButton>
        </div>
        <div class="fixed top-4 right-4 z-50">
            <FwbToast v-if="showToast" :type="toastType">
                {{ toastMessage }}
            </FwbToast>
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
                                <i
                                    class="fa-solid fa-eye lg:mr-2 text-black hover:text-blue-600"
                                />
                            </FwbA>
                        </FwbTableCell>
                        <FwbTableCell>
                            <FwbA @click.prevent="openEditModal(warehouse)">
                                <i
                                    class="fa-solid fa-pencil lg:mr-2 text-black hover:text-blue-600"
                                />
                            </FwbA>
                        </FwbTableCell>
                        <FwbTableCell>
                            <FwbA @click.prevent="openDeleteModal(warehouse)">
                                <i
                                    class="fa-solid fa-trash lg:mr-2 text-black hover:text-red-600"
                                ></i>
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
        <!-- Modal Crear -->
        <FwbModal v-if="isCreateModal" @close="closeCreateModal">
            <template #header>Nuevo Almacén</template>
            <template #body>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1"
                            >Nombre</label
                        >
                        <input
                            v-model="form.name"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1"
                            >Ubicación</label
                        >
                        <input
                            v-model="form.location"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1"
                            >Descripción</label
                        >
                        <textarea
                            v-model="form.description"
                            class="w-full p-2 border rounded"
                        ></textarea>
                    </div>
                </div>
            </template>
            <template #footer>
                <FwbButton
                    color="alternative"
                    @click="closeCreateModal"
                    :disabled="isProcessing"
                    >Cancelar</FwbButton
                >
                <FwbButton
                    color="purple"
                    @click="submitCreate"
                    :disabled="isProcessing"
                >
                    <i class="fa-solid fa-save mr-2"></i>
                    <span v-if="!isProcessing">Guardar</span>
                    <span v-else>Guardando…</span>
                </FwbButton>
            </template>
        </FwbModal>

        <!-- Modal Editar -->
        <FwbModal v-if="isEditModal" @close="closeEditModal">
            <template #header>Editar Almacén</template>
            <template #body>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1"
                            >Nombre</label
                        >
                        <input
                            v-model="form.name"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1"
                            >Ubicación</label
                        >
                        <input
                            v-model="form.location"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1"
                            >Descripción</label
                        >
                        <textarea
                            v-model="form.description"
                            class="w-full p-2 border rounded"
                        ></textarea>
                    </div>
                </div>
            </template>
            <template #footer>
                <FwbButton
                    color="alternative"
                    @click="closeEditModal"
                    :disabled="isProcessing"
                    >Cancelar</FwbButton
                >
                <FwbButton
                    color="purple"
                    @click="submitEdit"
                    :disabled="isProcessing"
                >
                    <i class="fa-solid fa-arrows-rotate mr-2"></i>
                    <span v-if="!isProcessing">Actualizar</span>
                    <span v-else>Actualizando…</span>
                </FwbButton>
            </template>
        </FwbModal>

        <!-- Modal Eliminar -->
        <FwbModal v-if="isDeleteModal" @close="closeDeleteModal">
            <template #header>Confirmar eliminación</template>
            <template #body>
                <div class="text-center">
                    <i
                        class="fa-solid fa-exclamation-triangle text-red-500 text-4xl mb-4"
                    ></i>
                    <p class="text-lg">
                        ¿Estás seguro de que deseas eliminar
                        <strong>{{ selectedWarehouse?.name }}</strong
                        >?
                    </p>
                    <p class="text-sm text-gray-600 mt-2">
                        Esta acción no se puede deshacer.
                    </p>
                </div>
            </template>
            <template #footer>
                <FwbButton
                    color="alternative"
                    @click="closeDeleteModal"
                    :disabled="isDeleting"
                    >Cancelar</FwbButton
                >
                <FwbButton
                    color="red"
                    @click="submitDelete"
                    :disabled="isDeleting"
                >
                    <i
                        v-if="isDeleting"
                        class="fa-solid fa-spinner fa-spin mr-2"
                    ></i>
                    <i v-else class="fa-solid fa-trash mr-2"></i>
                    <span v-if="!isDeleting">Eliminar</span>
                    <span v-else>Eliminando…</span>
                </FwbButton>
            </template>
        </FwbModal>
    </AdminLayout>
</template>
