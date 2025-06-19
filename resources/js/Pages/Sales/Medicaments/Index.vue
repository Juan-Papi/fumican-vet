<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { ref, watch } from "vue";
import axios from "axios";
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

// Props
const props = defineProps({
    medicaments: Object,
    categories: Array,
    warehouses: Array,
});

// Pagination
const currentPage = ref(props.medicaments.current_page);
watch(currentPage, (page) => {
    router.get(route("medicament.index"), { page }, { preserveState: true });
});

// Toast
const showToast = ref(false);
const toastMsg = ref("");
const toastType = ref("success");

// Modals
const isViewModal = ref(false);
const isCreateModal = ref(false);
const isEditModal = ref(false);
const isDeleteModal = ref(false);
const isAddBatchModal = ref(false);

// State & form
const loading = ref(false);
const isBatchProcessing = ref(false);
const selectedMed = ref(null);
const selectedMedForBatch = ref(null);

const form = ref({
    name: "",
    dosage: "",
    manufacturer: "",
    expiration_date: "",
    controlled_substance: "no",
    category_id: null,
});

const batchForm = ref({
    warehouse_id: null,
    stock: null,
    price: null,
});

// Open modals
function openViewModal(m) {
    selectedMed.value = m;
    isViewModal.value = true;
}
function openCreateModal() {
    selectedMed.value = null;
    Object.assign(form.value, {
        name: "",
        dosage: "",
        manufacturer: "",
        expiration_date: "",
        controlled_substance: "no",
        category_id: null,
    });
    isCreateModal.value = true;
}
function openEditModal(m) {
    selectedMed.value = m;
    Object.assign(form.value, {
        name: m.name,
        dosage: m.dosage,
        manufacturer: m.manufacturer,
        expiration_date: m.expiration_date,
        controlled_substance: m.controlled_substance,
        category_id: m.category.id,
    });
    isEditModal.value = true;
}
function openDeleteModal(m) {
    selectedMed.value = m;
    isDeleteModal.value = true;
}
function openAddBatchModal(m) {
    selectedMedForBatch.value = m;
    Object.assign(batchForm.value, {
        warehouse_id: null,
        stock: null,
        price: null,
    });
    isAddBatchModal.value = true;
}
function closeAddBatchModal() {
    isAddBatchModal.value = false;
    selectedMedForBatch.value = null;
    isBatchProcessing.value = false;
    Object.assign(batchForm.value, {
        warehouse_id: null,
        stock: null,
        price: null,
    });
}

// CRUD via Axios
async function submitCreate() {
    loading.value = true;
    try {
        const { data } = await axios.post(
            route("medicament.store"),
            form.value
        );
        toastType.value = "success";
        toastMsg.value = data.message || "Medicamento creado correctamente";
        isCreateModal.value = false;
        router.reload();
    } catch (e) {
        toastType.value = "danger";
        toastMsg.value =
            e.response?.data?.message ||
            Object.values(e.response?.data?.errors || {})
                .flat()
                .join(" ") ||
            "Error al crear medicamento";
    } finally {
        loading.value = false;
        showToast.value = true;
        setTimeout(() => (showToast.value = false), 3000);
    }
}

async function submitEdit() {
    if (!selectedMed.value) return;
    loading.value = true;
    try {
        const { data } = await axios.put(
            route("medicament.update", selectedMed.value.id),
            form.value
        );
        toastType.value = "success";
        toastMsg.value =
            data.message || "Medicamento actualizado correctamente";
        isEditModal.value = false;
        router.reload();
    } catch (e) {
        toastType.value = "danger";
        toastMsg.value =
            e.response?.data?.message ||
            Object.values(e.response?.data?.errors || {})
                .flat()
                .join(" ") ||
            "Error al actualizar medicamento";
    } finally {
        loading.value = false;
        showToast.value = true;
        setTimeout(() => (showToast.value = false), 3000);
    }
}

async function submitDelete() {
    if (!selectedMed.value) return;
    loading.value = true;
    try {
        const { data } = await axios.delete(
            route("medicament.destroy", selectedMed.value.id)
        );
        toastType.value = "success";
        toastMsg.value = data.message || "Medicamento eliminado correctamente";
        isDeleteModal.value = false;
        router.reload();
    } catch (e) {
        toastType.value = "danger";
        toastMsg.value =
            e.response?.data?.message || "Error al eliminar medicamento";
    } finally {
        loading.value = false;
        showToast.value = true;
        setTimeout(() => (showToast.value = false), 3000);
    }
}

// Batch via Inertia (mantener)
function submitAddBatch() {
    if (isBatchProcessing.value) return;
    if (!batchForm.value.warehouse_id) {
        toastType.value = "danger";
        toastMsg.value = "Debe seleccionar un almacén";
        showToast.value = true;
        return;
    }
    if (!batchForm.value.stock || batchForm.value.stock < 1) {
        toastType.value = "danger";
        toastMsg.value = "Debe ingresar un stock válido";
        showToast.value = true;
        return;
    }
    if (batchForm.value.price === null || batchForm.value.price < 0) {
        toastType.value = "danger";
        toastMsg.value = "Debe ingresar un precio válido";
        showToast.value = true;
        return;
    }

    isBatchProcessing.value = true;
    router.post(
        route("warehouse.medicament.inventory.store", {
            warehouseId: batchForm.value.warehouse_id,
            medicamentId: selectedMedForBatch.value.id,
        }),
        { stock: batchForm.value.stock, price: batchForm.value.price },
        {
            onSuccess: () => {
                closeAddBatchModal();
                toastType.value = "success";
                toastMsg.value = "Lote agregado correctamente";
                showToast.value = true;
            },
            onError: () => {
                toastType.value = "danger";
                toastMsg.value = "Error al agregar lote";
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
            <FwbToast v-if="showToast" :type="toastType">{{
                toastMsg
            }}</FwbToast>
        </div>

        <!-- Header -->
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold">Medicamentos</h2>
            <FwbButton color="purple" @click="openCreateModal">
                <i class="fa-solid fa-plus mr-2"></i> Nuevo Medicamento
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
                    <FwbTableCell class="text-right">
                        <div class="inline-flex space-x-1">
                            <FwbA
                                @click.prevent="openAddBatchModal(m)"
                                class="p-1 rounded hover:bg-gray-100"
                            >
                                <i class="fa-solid fa-warehouse text-black"></i>
                            </FwbA>
                            <FwbA
                                @click.prevent="openViewModal(m)"
                                class="p-1 rounded hover:bg-gray-100"
                            >
                                <i class="fa-solid fa-eye text-black"></i>
                            </FwbA>
                            <FwbA
                                @click.prevent="openEditModal(m)"
                                class="p-1 rounded hover:bg-gray-100"
                            >
                                <i class="fa-solid fa-pencil text-black"></i>
                            </FwbA>
                            <FwbA
                                @click.prevent="openDeleteModal(m)"
                                class="p-1 rounded hover:bg-gray-100"
                            >
                                <i class="fa-solid fa-trash text-black"></i>
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

        <!-- View Modal -->
        <FwbModal v-if="isViewModal" @close="isViewModal = false">
            <template #header>Detalle de Medicamento</template>
            <template #body>
                <div class="space-y-2">
                    <p><strong>Nombre:</strong> {{ selectedMed.name }}</p>
                    <p>
                        <strong>Dosificación:</strong> {{ selectedMed.dosage }}
                    </p>
                    <p>
                        <strong>Fabricante:</strong>
                        {{ selectedMed.manufacturer }}
                    </p>
                    <p>
                        <strong>Expiración:</strong>
                        {{ selectedMed.expiration_date }}
                    </p>
                    <p>
                        <strong>Controlada:</strong>
                        {{
                            selectedMed.controlled_substance === "yes"
                                ? "Sí"
                                : "No"
                        }}
                    </p>
                    <p>
                        <strong>Categoría:</strong>
                        {{ selectedMed.category.name }}
                    </p>
                </div>
            </template>
            <template #footer>
                <FwbButton color="alternative" @click="isViewModal = false"
                    >Cerrar</FwbButton
                >
            </template>
        </FwbModal>

        <!-- Create Modal -->
        <FwbModal v-if="isCreateModal" @close="isCreateModal = false">
            <template #header>Nuevo Medicamento</template>
            <template #body>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm">Nombre</label
                        ><input
                            v-model="form.name"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Dosificación</label
                        ><input
                            v-model="form.dosage"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Fabricante</label
                        ><input
                            v-model="form.manufacturer"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Expiración</label
                        ><input
                            type="date"
                            v-model="form.expiration_date"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Controlada</label
                        ><select
                            v-model="form.controlled_substance"
                            class="w-full p-2 border rounded"
                        >
                            <option value="no">No</option>
                            <option value="yes">Sí</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm">Categoría</label
                        ><select
                            v-model="form.category_id"
                            class="w-full p-2 border rounded"
                        >
                            <option disabled value="">Seleccionar</option>
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
                    :disabled="loading"
                    >Guardar</FwbButton
                >
            </template>
        </FwbModal>

        <!-- Edit Modal -->
        <FwbModal v-if="isEditModal" @close="isEditModal = false">
            <template #header>Editar Medicamento</template>
            <template #body>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm">Nombre</label
                        ><input
                            v-model="form.name"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Dosificación</label
                        ><input
                            v-model="form.dosage"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Fabricante</label
                        ><input
                            v-model="form.manufacturer"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Expiración</label
                        ><input
                            type="date"
                            v-model="form.expiration_date"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Controlada</label
                        ><select
                            v-model="form.controlled_substance"
                            class="w-full p-2 border rounded"
                        >
                            <option value="no">No</option>
                            <option value="yes">Sí</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm">Categoría</label
                        ><select
                            v-model="form.category_id"
                            class="w-full p-2 border rounded"
                        >
                            <option disabled value="">Seleccionar</option>
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
                    :disabled="loading"
                    >Actualizar</FwbButton
                >
            </template>
        </FwbModal>

        <!-- Delete Modal -->
        <FwbModal v-if="isDeleteModal" @close="isDeleteModal = false">
            <template #header>Confirmar eliminación</template>
            <template #body>
                <div class="text-center">
                    <i
                        class="fa-solid fa-exclamation-triangle text-red-500 text-4xl mb-4"
                    ></i>
                    <p class="text-lg">
                        ¿Eliminar <strong>{{ selectedMed.name }}</strong
                        >?
                    </p>
                    <p class="text-sm text-gray-600 mt-2">
                        Esta acción no se puede deshacer.
                    </p>
                </div>
            </template>
            <template #footer>
                <FwbButton color="alternative" @click="isDeleteModal = false"
                    >Cancelar</FwbButton
                >
                <FwbButton color="red" @click="submitDelete" :disabled="loading"
                    >Eliminar</FwbButton
                >
            </template>
        </FwbModal>

        <!-- Add Batch Modal -->
        <FwbModal v-if="isAddBatchModal" @close="closeAddBatchModal">
            <template #header
                >Agregar lote a: {{ selectedMedForBatch.name }}</template
            >
            <template #body>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1"
                            >Almacén</label
                        ><select
                            v-model="batchForm.warehouse_id"
                            class="w-full p-2 border rounded"
                        >
                            <option disabled value="">
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
                        ><input
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
                        ><input
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
                <FwbButton color="alternative" @click="closeAddBatchModal"
                    >Cancelar</FwbButton
                >
                <FwbButton
                    color="green"
                    @click="submitAddBatch"
                    :disabled="isBatchProcessing"
                    >Agregar lote</FwbButton
                >
            </template>
        </FwbModal>
    </AdminLayout>
</template>
