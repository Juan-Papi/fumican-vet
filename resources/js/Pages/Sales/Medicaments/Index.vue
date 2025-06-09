<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import {
    FwbTable,
    FwbTableHead,
    FwbTableHeadCell,
    FwbTableBody,
    FwbTableRow,
    FwbTableCell,
    FwbButton,
    FwbA,
    FwbPagination,
    FwbModal,
    FwbToast,
    FwbBadge,
} from "flowbite-vue";

const props = defineProps({
    medicaments: Object,
    categories: Array,
    warehouses: Array,
});

const currentPage = ref(props.medicaments.current_page);
watch(currentPage, (page) => {
    router.get(route("medicament.index"), { page }, { preserveState: true });
});

// Toast
const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success");

// Crear / Editar modales
const isCreateModal = ref(false);
const isEditModal = ref(false);
const isProcessing = ref(false);
const form = ref({
    name: "",
    dosage: "",
    manufacturer: "",
    expiration_date: "",
    controlled_substance: "no",
    category_id: null,
});
const selectedMed = ref(null);

// Ver / Eliminar modales
const isShowViewModal = ref(false);
const isShowDeleteModal = ref(false);
const isDeleting = ref(false);

function closeDeleteModal() {
    isShowDeleteModal.value = false;
    selectedMed.value = null;
    isDeleting.value = false;
}

function openCreateModal() {
    selectedMed.value = null;
    form.value = {
        name: "",
        dosage: "",
        manufacturer: "",
        expiration_date: "",
        controlled_substance: "no",
        category_id: null,
    };
    isCreateModal.value = true;
}

function openEditModal(med) {
    selectedMed.value = med;
    form.value = {
        name: med.name,
        dosage: med.dosage,
        manufacturer: med.manufacturer,
        expiration_date: med.expiration_date,
        controlled_substance: med.controlled_substance,
        category_id: med.category.id,
    };
    isEditModal.value = true;
}

function openViewModal(m) {
    selectedMed.value = m;
    isShowViewModal.value = true;
}

function openDeleteModal(m) {
    selectedMed.value = m;
    isShowDeleteModal.value = true;
}

function submitCreate() {
    if (isProcessing.value) return;
    isProcessing.value = true;
    router.post(route("medicament.store"), form.value, {
        onSuccess: () => {
            isCreateModal.value = false;
            toastType.value = "success";
            toastMessage.value = "Medicamento creado correctamente";
            showToast.value = true;
        },
        onError: () => {
            toastType.value = "danger";
            toastMessage.value = "Error al crear medicamento";
            showToast.value = true;
        },
        onFinish: () => {
            isProcessing.value = false;
            setTimeout(() => (showToast.value = false), 3000);
        },
    });
}

function submitEdit() {
    if (isProcessing.value) return;
    isProcessing.value = true;
    router.put(route("medicament.update", selectedMed.value.id), form.value, {
        onSuccess: () => {
            isEditModal.value = false;
            toastType.value = "success";
            toastMessage.value = "Medicamento actualizado";
            showToast.value = true;
        },
        onError: () => {
            toastType.value = "danger";
            toastMessage.value = "Error al actualizar";
            showToast.value = true;
        },
        onFinish: () => {
            isProcessing.value = false;
            setTimeout(() => (showToast.value = false), 3000);
        },
    });
}

function submitDelete() {
    if (isDeleting.value) return;
    isDeleting.value = true;

    router.post(
        route("medicament.destroy", selectedMed.value.id),
        {}, // sin payload
        {
            onSuccess: () => {
                closeDeleteModal(); // Cierra modal y resetea estado
                toastType.value = "success";
                toastMessage.value = "Medicamento eliminado correctamente";
                showToast.value = true;
            },
            onError: () => {
                isDeleting.value = false;
                toastType.value = "danger";
                toastMessage.value = "Error eliminando medicamento";
                showToast.value = true;
            },
            onFinish: () => {
                // Aseguramos que el spinner se desactive después de todo
                isDeleting.value = false;
                setTimeout(() => (showToast.value = false), 3000);
            },
        }
    );
}

// Modal para agregar nuevo lote de medicamento
const isAddBatchModal = ref(false);
const batchForm = ref({
    warehouse_id: null,
    stock: null,
    price: null,
});
const selectedMedForBatch = ref(null);
const isBatchProcessing = ref(false);

function openAddBatchModal(med) {
    selectedMedForBatch.value = med;
    batchForm.value = {
        warehouse_id: null,
        stock: null,
        price: null,
    };
    isAddBatchModal.value = true;
}

function closeAddBatchModal() {
    isAddBatchModal.value = false;
    selectedMedForBatch.value = null;
    isBatchProcessing.value = false;
    batchForm.value = {
        warehouse_id: null,
        stock: null,
        price: null,
    };
}

function submitAddBatch() {
    if (isBatchProcessing.value) return;
    if (!batchForm.value.warehouse_id) {
        toastType.value = "danger";
        toastMessage.value = "Debe seleccionar un almacén";
        showToast.value = true;
        return;
    }
    if (!batchForm.value.stock || batchForm.value.stock < 1) {
        toastType.value = "danger";
        toastMessage.value = "Debe ingresar un stock válido";
        showToast.value = true;
        return;
    }
    if (batchForm.value.price === null || batchForm.value.price < 0) {
        toastType.value = "danger";
        toastMessage.value = "Debe ingresar un precio válido";
        showToast.value = true;
        return;
    }

    isBatchProcessing.value = true;

    router.post(
        route("warehouse.medicament.inventory.store", {
            warehouseId: batchForm.value.warehouse_id,
            medicamentId: selectedMedForBatch.value.id,
        }),
        {
            stock: batchForm.value.stock,
            price: batchForm.value.price,
        },
        {
            onSuccess: () => {
                closeAddBatchModal();
                toastType.value = "success";
                toastMessage.value = "Lote agregado correctamente";
                showToast.value = true;
            },
            onError: () => {
                toastType.value = "danger";
                toastMessage.value = "Error al agregar lote";
                showToast.value = true;
            },
            onFinish: () => {
                isBatchProcessing.value = false;
                setTimeout(() => (showToast.value = false), 3000);
            },
        }
    );
}
</script>

<template>
    <AdminLayout title="Medicamentos">
        <!-- Toast -->
        <div class="fixed top-4 right-4 z-50">
            <FwbToast v-if="showToast" :type="toastType">
                {{ toastMessage }}
            </FwbToast>
        </div>

        <!-- Cabecera + Botón “Nuevo” -->
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold">Medicamentos</h2>
            <FwbButton color="purple" @click="openCreateModal">
                <i class="fa-solid fa-plus mr-2"></i>
                Agregar medicamento
            </FwbButton>
        </div>

        <!-- Tabla -->
        <FwbTable>
            <FwbTableHead>
                <FwbTableHeadCell>Nombre</FwbTableHeadCell>
                <FwbTableHeadCell>Dosificación</FwbTableHeadCell>
                <FwbTableHeadCell>Fabricante</FwbTableHeadCell>
                <FwbTableHeadCell>Expiración</FwbTableHeadCell>
                <FwbTableHeadCell>Controlada</FwbTableHeadCell>
                <FwbTableHeadCell>Categoría</FwbTableHeadCell>
                <FwbTableHeadCell>Modificado</FwbTableHeadCell>
                <FwbTableHeadCell
                    ><span class="sr-only">Acciones</span></FwbTableHeadCell
                >
            </FwbTableHead>
            <FwbTableBody>
                <FwbTableRow v-for="m in medicaments.data" :key="m.id">
                    <FwbTableCell>{{ m.name }}</FwbTableCell>
                    <FwbTableCell>{{ m.dosage }}</FwbTableCell>
                    <FwbTableCell>{{ m.manufacturer }}</FwbTableCell>
                    <FwbTableCell>{{ m.expiration_date }}</FwbTableCell>
                    <FwbTableCell>
                        <FwbBadge
                            :type="
                                m.controlled_substance === 'yes'
                                    ? 'green'
                                    : 'yellow'
                            "
                        >
                            {{ m.controlled_substance === "yes" ? "Sí" : "No" }}
                        </FwbBadge>
                    </FwbTableCell>
                    <FwbTableCell>{{ m.category.name }}</FwbTableCell>
                    <FwbTableCell>{{ m.updated_at }}</FwbTableCell>
                    <FwbTableCell>
                        <div class="flex space-x-2">
                            <FwbA
                                @click.prevent="openAddBatchModal(m)"
                                class="p-1 rounded hover:bg-gray-100"
                            >
                                <i
                                    class="fa-solid fa-warehouse text-black hover:text-green-600"
                                ></i>
                            </FwbA>
                            <FwbA
                                @click.prevent="openViewModal(m)"
                                class="p-1 rounded hover:bg-gray-100"
                            >
                                <i
                                    class="fa-solid fa-eye text-black hover:text-purple-600"
                                ></i>
                            </FwbA>
                            <FwbA
                                @click.prevent="openEditModal(m)"
                                class="p-1 rounded hover:bg-gray-100"
                            >
                                <i
                                    class="fa-solid fa-pencil text-black hover:text-blue-600"
                                ></i>
                            </FwbA>
                            <FwbA
                                @click.prevent="openDeleteModal(m)"
                                class="p-1 rounded hover:bg-gray-100"
                            >
                                <i
                                    class="fa-solid fa-trash text-black hover:text-red-600"
                                ></i>
                            </FwbA>
                        </div>
                    </FwbTableCell>
                </FwbTableRow>
            </FwbTableBody>
        </FwbTable>

        <!-- Paginación -->
        <div class="flex justify-center my-4">
            <FwbPagination
                v-model="currentPage"
                :total-items="medicaments.total"
                :per-page="medicaments.per_page"
                large
            />
        </div>

        <!-- Modal “Ver” -->
        <FwbModal v-if="isShowViewModal" @close="isShowViewModal = false">
            <template #header>Detalle de Medicamento</template>
            <template #body>
                <p><strong>Nombre:</strong> {{ selectedMed.name }}</p>
                <p><strong>Dosificación:</strong> {{ selectedMed.dosage }}</p>
                <p>
                    <strong>Fabricante:</strong> {{ selectedMed.manufacturer }}
                </p>
                <p>
                    <strong>Expiración:</strong>
                    {{ selectedMed.expiration_date }}
                </p>
                <p>
                    <strong>Controlada:</strong>
                    {{
                        selectedMed.controlled_substance === "yes" ? "Sí" : "No"
                    }}
                </p>
                <p>
                    <strong>Categoría:</strong> {{ selectedMed.category.name }}
                </p>
            </template>
            <template #footer>
                <FwbButton color="alternative" @click="isShowViewModal = false">
                    Cerrar
                </FwbButton>
            </template>
        </FwbModal>

        <!-- Modal “Eliminar” -->
        <FwbModal v-if="isShowDeleteModal" @close="closeDeleteModal">
            <template #header>Confirmar eliminación</template>
            <template #body>
                <div class="text-center">
                    <i
                        class="fa-solid fa-exclamation-triangle text-red-500 text-4xl mb-4"
                    ></i>
                    <p class="text-lg">
                        ¿Estás seguro de que deseas eliminar
                        <strong>{{ selectedMed?.name }}</strong
                        >?
                    </p>
                    <p class="text-sm text-gray-600 mt-2">
                        Esta acción no se puede deshacer.
                    </p>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <FwbButton
                        color="alternative"
                        @click="closeDeleteModal"
                        :disabled="isDeleting"
                    >
                        Cancelar
                    </FwbButton>
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
                </div>
            </template>
        </FwbModal>

        <!-- Modal Crear -->
        <FwbModal v-if="isCreateModal" @close="isCreateModal = false">
            <template #header>Nuevo Medicamento</template>
            <template #body>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm">Nombre</label>
                        <input
                            v-model="form.name"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Dosificación</label>
                        <input
                            v-model="form.dosage"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Fabricante</label>
                        <input
                            v-model="form.manufacturer"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Expiración</label>
                        <input
                            v-model="form.expiration_date"
                            type="date"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Controlada</label>
                        <select
                            v-model="form.controlled_substance"
                            class="w-full p-2 border rounded"
                        >
                            <option value="no">No</option>
                            <option value="yes">Sí</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm">Categoría</label>
                        <select
                            v-model="form.category_id"
                            class="w-full p-2 border rounded"
                        >
                            <option value="" disabled>Seleccionar</option>
                            <option
                                v-for="c in categories"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </template>
            <template #footer>
                <FwbButton color="alternative" @click="isCreateModal = false"
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
        <FwbModal v-if="isEditModal" @close="isEditModal = false">
            <template #header>Editar Medicamento</template>
            <template #body>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm">Nombre</label>
                        <input
                            v-model="form.name"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Dosificación</label>
                        <input
                            v-model="form.dosage"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Fabricante</label>
                        <input
                            v-model="form.manufacturer"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Expiración</label>
                        <input
                            v-model="form.expiration_date"
                            type="date"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Controlada</label>
                        <select
                            v-model="form.controlled_substance"
                            class="w-full p-2 border rounded"
                        >
                            <option value="no">No</option>
                            <option value="yes">Sí</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm">Categoría</label>
                        <select
                            v-model="form.category_id"
                            class="w-full p-2 border rounded"
                        >
                            <option value="" disabled>Seleccionar</option>
                            <option
                                v-for="c in categories"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </template>
            <template #footer>
                <FwbButton color="alternative" @click="isEditModal = false"
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

        <!-- Modal Agregar Lote -->
        <FwbModal v-if="isAddBatchModal" @close="closeAddBatchModal">
            <template #header>
                Agregar lote a medicamento:&nbsp;
                <span class="font-bold">{{ selectedMedForBatch?.name }}</span>
            </template>
            <template #body>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1"
                            >Almacén</label
                        >
                        <select
                            v-model="batchForm.warehouse_id"
                            class="w-full p-2 border rounded"
                        >
                            <option value="" disabled>
                                Seleccionar almacén
                            </option>
                            <option
                                v-for="w in warehouses"
                                :key="w.id"
                                :value="w.id"
                            >
                                {{ w.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1"
                            >Stock</label
                        >
                        <input
                            type="number"
                            v-model.number="batchForm.stock"
                            min="1"
                            class="w-full p-2 border rounded"
                            placeholder="Cantidad de unidades"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1"
                            >Precio</label
                        >
                        <input
                            type="number"
                            v-model.number="batchForm.price"
                            min="0"
                            step="0.01"
                            class="w-full p-2 border rounded"
                            placeholder="Precio por unidad"
                        />
                    </div>
                </div>
            </template>
            <template #footer>
                <FwbButton
                    color="alternative"
                    @click="closeAddBatchModal"
                    :disabled="isBatchProcessing"
                    >Cancelar</FwbButton
                >
                <FwbButton
                    color="green"
                    @click="submitAddBatch"
                    :disabled="isBatchProcessing"
                >
                    <i
                        v-if="isBatchProcessing"
                        class="fa-solid fa-spinner fa-spin mr-2"
                    ></i>
                    <i v-else class="fa-solid fa-plus mr-2"></i>
                    <span v-if="!isBatchProcessing">Agregar lote</span>
                    <span v-else>Agregando…</span>
                </FwbButton>
            </template>
        </FwbModal>
    </AdminLayout>
</template>
