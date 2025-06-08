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
});

const currentPage = ref(props.medicaments.current_page);
watch(currentPage, (page) => {
    router.get(route("medicament.index"), { page }, { preserveState: true });
});

// estado del toast
const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success");

// estados del modal & formulario
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

// para editar
const selectedMed = ref(null);

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
</script>

<template>
    <AdminLayout title="Medicamentos">
        <!-- Toast -->
        <div class="fixed top-4 right-4 z-50">
            <FwbToast v-if="showToast" :type="toastType">{{
                toastMessage
            }}</FwbToast>
        </div>

        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold">Medicamentos</h2>
            <FwbButton color="purple" @click="openCreateModal">
                <i class="fa-solid fa-plus mr-2"></i> Agregar medicamento
            </FwbButton>
        </div>

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
                            v-if="m.controlled_substance !== 'yes'"
                            type="yellow"
                        >
                            No
                        </FwbBadge>
                        <FwbBadge
                            v-if="m.controlled_substance === 'yes'"
                            type="green"
                        >
                            Sí
                        </FwbBadge>
                    </FwbTableCell>
                    <FwbTableCell>{{ m.category.name }}</FwbTableCell>
                    <FwbTableCell>{{ m.updated_at }}</FwbTableCell>
                    <FwbTableCell>
                        <div class="flex space-x-2">
                            <FwbA
                                href="#"
                                class="p-1 hover:bg-gray-100 rounded"
                            >
                                <i
                                    class="fa-solid fa-boxes-stacked text-black hover:text-green-600"
                                ></i>
                            </FwbA>
                            <FwbA
                                href="#"
                                class="p-1 hover:bg-gray-100 rounded"
                            >
                                <i
                                    class="fa-solid fa-eye text-black hover:text-blue-600"
                                />
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
                                href="#"
                                class="p-1 hover:bg-gray-100 rounded"
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

        <div class="flex justify-center my-4">
            <FwbPagination
                v-model="currentPage"
                :total-items="medicaments.total"
                :per-page="medicaments.per_page"
                large
            />
        </div>

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
    </AdminLayout>
</template>
