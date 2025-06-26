<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PermissionEnum from "@/Utils/Enums/PermissionEnum";
import { usePage, router } from "@inertiajs/vue3";
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
    FwbModal,
    FwbToast,
    FwbRadio,
    FwbTextarea,
} from "flowbite-vue";
import { computed, ref, watch } from "vue";
import axios from "axios";

// --- Components for form & search ---
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import FormSectionTitle from "@/Components/Forms/FormSectionTitle.vue";
import SearchModal from "@/Components/Modals/SearchModal.vue";
import SearchUser from "@/Components/Icons/Svg/SearchUser.vue";
import { useDebouncedRef } from "@/Utils/debouncedRef";

// --- PROPS & PAGINATION ---
const props = defineProps({ medicalConsultations: Object });
const currentPage = ref(props.medicalConsultations.current_page || 1);
watch(currentPage, (newPage) => {
    router.get(route("medical-consultations.index", { page: newPage }), {
        preserveState: true,
    });
});

// --- STATE MANAGEMENT ---
const loading = ref(false);
const showToast = ref(false);
const toastMsg = ref("");
const toastType = ref("success");

// --- Modals ---
const isCreateOrEditModal = ref(false);
const isDeleteModal = ref(false);
const isSearchPetModal = ref(false);
const isViewModal = ref(false); // <-- AÑADIDO: Estado para el modal de vista
const modalMode = ref("create"); // 'create' or 'edit'

const selectedConsultation = ref(null);
const selectedPet = ref(null);

// --- Form ---
const defaultFormState = {
    id: null,
    reason: "",
    pet_id: "",
    dewormed_at: "",
    previous_illnesses: "",
    previous_interventions: "",
    general_condition: "Bueno",
    appetite: [],
    hydratation: [],
    mucosa: [],
    weight: "",
    digestive_system: "",
    genitourinary_system: "",
    respiratory_system: "",
    temperature: "",
    heart_rate: "",
    respiratory_rate: "",
    clinical_observation: "",
    complementary_tests: "",
    presumptive_diagnosis: "",
    confirmatory_diagnosis: "",
    consultation_fee: "",
    treatment: "",
};
const form = ref({ ...defaultFormState });
const formErrors = ref({});

// --- Pet Search ---
const search = useDebouncedRef("", 300);
const isFetchingData = ref(false);
const petsList = ref([]);

// --- HELPERS & PERMISSIONS ---
const isEmptyData = computed(
    () => props.medicalConsultations.data.length === 0
);
const page = usePage();
const canCreateMedCons = true; // Assuming permission
const canEditMedCons = true;
const canDeleteMedCons = true;

// --- MODAL & FORM FUNCTIONS ---

function displayToast(type, message) {
    toastType.value = type;
    toastMsg.value = message;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 3000);
}

function prepareFormData(data) {
    return {
        ...data,
        appetite: data.appetite.join(", "),
        hydratation: data.hydratation.join(", "),
        mucosa: data.mucosa.join(", "),
        veterinarian_id: page.props.auth.user.id,
    };
}

function openCreateModal() {
    modalMode.value = "create";
    form.value = { ...defaultFormState };
    selectedPet.value = null;
    formErrors.value = {};
    isCreateOrEditModal.value = true;
}

function openEditModal(consultation) {
    modalMode.value = "edit";
    selectedConsultation.value = consultation;
    formErrors.value = {};

    // Populate form fields
    form.value = {
        ...defaultFormState,
        ...consultation,
        appetite: consultation.appetite
            ? consultation.appetite.split(", ")
            : [],
        hydratation: consultation.hydratation
            ? consultation.hydratation.split(", ")
            : [],
        mucosa: consultation.mucosa ? consultation.mucosa.split(", ") : [],
    };

    // Populate selected pet details for display
    if (consultation.pet) {
        const pet = consultation.pet;
        selectedPet.value = {
            id: pet.id,
            name: pet.name,
            owner: pet.owner,
            owner_full_name: `${pet.owner.first_name} ${pet.owner.last_name}`,
            specie_and_breed: `${pet.breed.specie.name} - ${pet.breed.name}`,
        };
    } else {
        selectedPet.value = null;
    }

    isCreateOrEditModal.value = true;
}

// <-- AÑADIDO: Función para abrir el modal de vista -->
function openViewModal(consultation) {
    selectedConsultation.value = consultation;
    isViewModal.value = true;
}

function openDeleteModal(consultation) {
    selectedConsultation.value = consultation;
    isDeleteModal.value = true;
}

function closeAllModals() {
    isCreateOrEditModal.value = false;
    isDeleteModal.value = false;
    isSearchPetModal.value = false;
    isViewModal.value = false; // <-- AÑADIDO: Cerrar modal de vista
    selectedConsultation.value = null;
    selectedPet.value = null;
}

// --- Pet Search Functions ---
watch(search, async (value) => {
    if (value.length < 1) {
        petsList.value = [];
        return;
    }
    isFetchingData.value = true;
    try {
        const response = await axios.get(route("pets.search"), {
            params: { search: value },
        });
        petsList.value = response.data;
    } catch (error) {
        console.error("Error searching pets:", error);
    } finally {
        isFetchingData.value = false;
    }
});

function handleSelectPet(pet) {
    selectedPet.value = {
        id: pet.id,
        name: pet.name,
        owner: pet.owner,
        owner_full_name: `${pet.owner?.first_name} ${pet.owner?.last_name}`,
        specie_and_breed: `${pet.breed?.specie?.name} - ${pet.breed?.name}`,
    };
    form.value.pet_id = pet.id;
    formErrors.value.pet_id = null; // Clear any previous error
    isSearchPetModal.value = false;
    search.value = "";
}

// --- CRUD FUNCTIONS ---

async function submitForm() {
    if (modalMode.value === "create") {
        await submitCreate();
    } else {
        await submitEdit();
    }
}

async function submitCreate() {
    loading.value = true;
    formErrors.value = {};
    try {
        const payload = prepareFormData(form.value);
        await axios.post(route("medical-consultations.store"), payload);
        displayToast("success", "Consulta creada correctamente.");
        closeAllModals();
        router.reload({ only: ["medicalConsultations"] });
    } catch (e) {
        if (e.response?.status === 422) {
            formErrors.value = e.response.data.errors;
            displayToast(
                "danger",
                "Por favor, corrige los errores del formulario."
            );
        } else {
            displayToast("danger", "Error al crear la consulta.");
        }
    } finally {
        loading.value = false;
    }
}

async function submitEdit() {
    if (!selectedConsultation.value) return;
    loading.value = true;
    formErrors.value = {};
    try {
        const payload = prepareFormData(form.value);
        await axios.put(
            route(
                "medical-consultations.update",
                selectedConsultation.value.id
            ),
            payload
        );
        displayToast("success", "Consulta actualizada correctamente.");
        closeAllModals();
        router.reload({ only: ["medicalConsultations"] });
    } catch (e) {
        if (e.response?.status === 422) {
            formErrors.value = e.response.data.errors;
            displayToast(
                "danger",
                "Por favor, corrige los errores del formulario."
            );
        } else {
            displayToast("danger", "Error al actualizar la consulta.");
        }
    } finally {
        loading.value = false;
    }
}

async function submitDelete() {
    if (!selectedConsultation.value) return;
    loading.value = true;
    try {
        await axios.delete(
            route(
                "medical-consultations.destroy",
                selectedConsultation.value.id
            )
        );
        displayToast("success", "Consulta eliminada correctamente.");
        closeAllModals();
        router.reload({ only: ["medicalConsultations"] });
    } catch (e) {
        displayToast("danger", "Error al eliminar la consulta.");
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <AdminLayout title="Consultas Médicas">
        <!-- Toast Notification -->
        <div class="fixed top-4 right-4 z-50">
            <FwbToast v-if="showToast" :type="toastType" closable>{{
                toastMsg
            }}</FwbToast>
        </div>

        <!-- Header -->
        <div class="flex justify-between my-6 items-center">
            <h2 class="text-2xl font-semibold">Consultas Médicas</h2>
            <FwbButton
                v-if="canCreateMedCons"
                @click="openCreateModal"
                color="purple"
            >
                Agregar Consulta
            </FwbButton>
        </div>

        <!-- Table -->
        <FwbTable>
            <FwbTableHead>
                <FwbTableHeadCell>ID</FwbTableHeadCell>
                <FwbTableHeadCell>Fecha</FwbTableHeadCell>
                <FwbTableHeadCell>Propietario</FwbTableHeadCell>
                <FwbTableHeadCell>Mascota</FwbTableHeadCell>
                <FwbTableHeadCell>Motivo</FwbTableHeadCell>
                <FwbTableHeadCell>Acciones</FwbTableHeadCell>
            </FwbTableHead>
            <FwbTableBody>
                <FwbTableRow v-if="isEmptyData">
                    <FwbTableCell
                        colspan="6"
                        class="text-center text-gray-500 py-4"
                    >
                        No hay consultas médicas registradas
                    </FwbTableCell>
                </FwbTableRow>
                <FwbTableRow
                    v-for="consultation in medicalConsultations.data"
                    :key="consultation.id"
                >
                    <FwbTableCell>{{ consultation.id }}</FwbTableCell>
                    <FwbTableCell>{{ consultation.created_at }}</FwbTableCell>
                    <FwbTableCell>{{ consultation.pet_owner }}</FwbTableCell>
                    <FwbTableCell>{{ consultation.pet_name }}</FwbTableCell>
                    <FwbTableCell class="max-w-xs truncate">{{
                        consultation.reason
                    }}</FwbTableCell>
                    <FwbTableCell class="space-x-2 whitespace-nowrap">
                        <!-- AÑADIDO: Enlace para ver -->
                        <FwbA
                            href="#"
                            @click.prevent="openViewModal(consultation)"
                            >Ver</FwbA
                        >
                        <FwbA
                            href="#"
                            @click.prevent="openEditModal(consultation)"
                            >Editar</FwbA
                        >
                        <FwbA
                            href="#"
                            @click.prevent="openDeleteModal(consultation)"
                            class="text-red-600 hover:underline"
                            >Eliminar</FwbA
                        >
                    </FwbTableCell>
                </FwbTableRow>
            </FwbTableBody>
        </FwbTable>

        <!-- Pagination -->
        <div v-if="!isEmptyData" class="flex justify-center my-4">
            <FwbPagination
                v-model="currentPage"
                :total-items="medicalConsultations.total"
                :per-page="medicalConsultations.per_page"
                large
            />
        </div>

        <!-- Create/Edit Modal -->
        <FwbModal size="5xl" v-if="isCreateOrEditModal" @close="closeAllModals">
            <template #header>
                <h3 class="text-xl font-semibold">
                    {{ modalMode === "edit" ? "Editar" : "Nueva" }} Consulta
                    Médica
                </h3>
            </template>
            <template #body>
                <form class="space-y-6" @submit.prevent="submitForm">
                    <!-- Pet Selection Section -->
                    <div class="mb-4 flex justify-between items-start">
                        <h3 class="text-xl font-semibold text-gray-700">
                            Datos de la mascota
                        </h3>
                        <FwbButton
                            @click="isSearchPetModal = true"
                            type="button"
                            color="purple"
                        >
                            Seleccionar Mascota
                        </FwbButton>
                    </div>
                    <div>
                        <div
                            v-if="!selectedPet"
                            class="border py-8 text-center text-gray-500 rounded-lg"
                            :class="{
                                'bg-red-50 border-red-300': formErrors.pet_id,
                            }"
                        >
                            Debe seleccionar una mascota
                            <InputError
                                class="mt-2"
                                :message="formErrors.pet_id?.[0]"
                            />
                        </div>
                        <div
                            v-else
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 rounded bg-gray-50 border p-4"
                        >
                            <div>
                                <strong class="font-medium"
                                    >Propietario:</strong
                                >
                                <span>{{ selectedPet.owner_full_name }}</span>
                            </div>
                            <div>
                                <strong class="font-medium">Cédula:</strong>
                                <span>{{ selectedPet.owner?.ci }}</span>
                            </div>
                            <div>
                                <strong class="font-medium">Mascota:</strong>
                                <span>{{ selectedPet.name }}</span>
                            </div>
                            <div>
                                <strong class="font-medium"
                                    >Especie y Raza:</strong
                                >
                                <span>{{ selectedPet.specie_and_breed }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Anamnesis Section -->
                    <FormSectionTitle title="Anamnesis" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <FwbTextarea
                                v-model="form.reason"
                                label="Motivo de la consulta"
                                :rows="3"
                            />
                            <InputError
                                class="mt-2"
                                :message="formErrors.reason?.[0]"
                            />
                        </div>
                        <div>
                            <InputLabel
                                for="dewormed_at"
                                value="Fecha de desparasitación"
                            />
                            <TextInput
                                id="dewormed_at"
                                v-model="form.dewormed_at"
                                type="date"
                                class="mt-1 block w-full"
                            />
                            <InputError
                                class="mt-2"
                                :message="formErrors.dewormed_at?.[0]"
                            />
                        </div>
                        <div>
                            <InputLabel
                                for="previous_illnesses"
                                value="Enfermedades previas"
                            />
                            <TextInput
                                id="previous_illnesses"
                                v-model="form.previous_illnesses"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError
                                class="mt-2"
                                :message="formErrors.previous_illnesses?.[0]"
                            />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel
                                for="previous_interventions"
                                value="Intervenciones previas"
                            />
                            <TextInput
                                id="previous_interventions"
                                v-model="form.previous_interventions"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError
                                class="mt-2"
                                :message="
                                    formErrors.previous_interventions?.[0]
                                "
                            />
                        </div>
                    </div>

                    <!-- Physical Exam Section -->
                    <FormSectionTitle title="Examen Físico" />
                    <div class="space-y-4">
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"
                        >
                            <div>
                                <InputLabel value="Estado General" />
                                <div class="flex gap-4 mt-2">
                                    <FwbRadio
                                        v-model="form.general_condition"
                                        value="Bueno"
                                        label="Bueno"
                                    /><FwbRadio
                                        v-model="form.general_condition"
                                        value="Regular"
                                        label="Regular"
                                    /><FwbRadio
                                        v-model="form.general_condition"
                                        value="Malo"
                                        label="Malo"
                                    />
                                </div>
                                <InputError
                                    :message="formErrors.general_condition?.[0]"
                                />
                            </div>
                            <div>
                                <InputLabel value="Apetito" />
                                <div
                                    class="flex flex-wrap gap-x-4 gap-y-2 mt-2"
                                >
                                    <label class="flex items-center"
                                        ><input
                                            type="checkbox"
                                            v-model="form.appetite"
                                            value="Normal"
                                            class="mr-2 rounded"
                                        />Normal</label
                                    ><label class="flex items-center"
                                        ><input
                                            type="checkbox"
                                            v-model="form.appetite"
                                            value="Disminuido"
                                            class="mr-2 rounded"
                                        />Disminuido</label
                                    ><label class="flex items-center"
                                        ><input
                                            type="checkbox"
                                            v-model="form.appetite"
                                            value="Anorexia"
                                            class="mr-2 rounded"
                                        />Anorexia</label
                                    >
                                </div>
                                <InputError
                                    :message="formErrors.appetite?.[0]"
                                />
                            </div>
                            <div>
                                <InputLabel value="Hidratación" />
                                <div
                                    class="flex flex-wrap gap-x-4 gap-y-2 mt-2"
                                >
                                    <label class="flex items-center"
                                        ><input
                                            type="checkbox"
                                            v-model="form.hydratation"
                                            value="Normal"
                                            class="mr-2 rounded"
                                        />Normal</label
                                    ><label class="flex items-center"
                                        ><input
                                            type="checkbox"
                                            v-model="form.hydratation"
                                            value="Leve"
                                            class="mr-2 rounded"
                                        />Leve</label
                                    ><label class="flex items-center"
                                        ><input
                                            type="checkbox"
                                            v-model="form.hydratation"
                                            value="Moderada"
                                            class="mr-2 rounded"
                                        />Moderada</label
                                    >
                                </div>
                                <InputError
                                    :message="formErrors.hydratation?.[0]"
                                />
                            </div>
                            <div>
                                <InputLabel value="Mucosa" />
                                <div
                                    class="flex flex-wrap gap-x-4 gap-y-2 mt-2"
                                >
                                    <label class="flex items-center"
                                        ><input
                                            type="checkbox"
                                            v-model="form.mucosa"
                                            value="Rosada"
                                            class="mr-2 rounded"
                                        />Rosada</label
                                    ><label class="flex items-center"
                                        ><input
                                            type="checkbox"
                                            v-model="form.mucosa"
                                            value="Pálida"
                                            class="mr-2 rounded"
                                        />Pálida</label
                                    ><label class="flex items-center"
                                        ><input
                                            type="checkbox"
                                            v-model="form.mucosa"
                                            value="Ictérica"
                                            class="mr-2 rounded"
                                        />Ictérica</label
                                    >
                                </div>
                                <InputError :message="formErrors.mucosa?.[0]" />
                            </div>
                        </div>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                        >
                            <div>
                                <InputLabel value="Peso (Kg)" /><TextInput
                                    v-model="form.weight"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 w-full"
                                /><InputError
                                    :message="formErrors.weight?.[0]"
                                />
                            </div>
                            <div>
                                <InputLabel
                                    value="Temperatura (°C)"
                                /><TextInput
                                    v-model="form.temperature"
                                    type="number"
                                    step="0.1"
                                    class="mt-1 w-full"
                                /><InputError
                                    :message="formErrors.temperature?.[0]"
                                />
                            </div>
                            <div>
                                <InputLabel value="Frec. Cardíaca" /><TextInput
                                    v-model="form.heart_rate"
                                    type="number"
                                    class="mt-1 w-full"
                                /><InputError
                                    :message="formErrors.heart_rate?.[0]"
                                />
                            </div>
                            <div>
                                <InputLabel
                                    value="Frec. Respiratoria"
                                /><TextInput
                                    v-model="form.respiratory_rate"
                                    type="number"
                                    class="mt-1 w-full"
                                /><InputError
                                    :message="formErrors.respiratory_rate?.[0]"
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <InputLabel value="Ap. Digestivo" /><TextInput
                                    v-model="form.digestive_system"
                                    class="mt-1 w-full"
                                /><InputError
                                    :message="formErrors.digestive_system?.[0]"
                                />
                            </div>
                            <div>
                                <InputLabel
                                    value="Ap. Genitourinario"
                                /><TextInput
                                    v-model="form.genitourinary_system"
                                    class="mt-1 w-full"
                                /><InputError
                                    :message="
                                        formErrors.genitourinary_system?.[0]
                                    "
                                />
                            </div>
                            <div>
                                <InputLabel
                                    value="Ap. Respiratorio"
                                /><TextInput
                                    v-model="form.respiratory_system"
                                    class="mt-1 w-full"
                                /><InputError
                                    :message="
                                        formErrors.respiratory_system?.[0]
                                    "
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Diagnosis and Treatment -->
                    <FormSectionTitle title="Diagnóstico y Tratamiento" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel
                                value="Diagnóstico Presuntivo"
                            /><TextInput
                                v-model="form.presumptive_diagnosis"
                                class="mt-1 w-full"
                            /><InputError
                                :message="formErrors.presumptive_diagnosis?.[0]"
                            />
                        </div>
                        <div>
                            <InputLabel
                                value="Diagnóstico Confirmativo"
                            /><TextInput
                                v-model="form.confirmatory_diagnosis"
                                class="mt-1 w-full"
                            /><InputError
                                :message="
                                    formErrors.confirmatory_diagnosis?.[0]
                                "
                            />
                        </div>
                        <div class="md:col-span-2">
                            <FwbTextarea
                                v-model="form.treatment"
                                label="Tratamiento y Evolución"
                                :rows="3"
                            /><InputError
                                :message="formErrors.treatment?.[0]"
                            />
                        </div>
                        <div>
                            <InputLabel value="Costo Consulta ($)" /><TextInput
                                v-model="form.consultation_fee"
                                type="number"
                                step="0.01"
                                class="mt-1 w-full"
                            /><InputError
                                :message="formErrors.consultation_fee?.[0]"
                            />
                        </div>
                    </div>
                </form>
            </template>
            <template #footer>
                <div class="flex justify-end">
                    <FwbButton @click="closeAllModals" color="alternative"
                        >Cancelar</FwbButton
                    >
                    <FwbButton
                        @click="submitForm"
                        color="purple"
                        :loading="loading"
                        class="ml-2"
                        >Guardar</FwbButton
                    >
                </div>
            </template>
        </FwbModal>

        <!-- AÑADIDO: Modal de vista -->
        <FwbModal
            size="4xl"
            v-if="isViewModal && selectedConsultation"
            @close="closeAllModals"
        >
            <template #header>
                <h3 class="text-xl font-semibold">
                    Detalles de la Consulta Médica
                </h3>
            </template>
            <template #body>
                <div class="space-y-6">
                    <!-- Pet Details -->
                    <section>
                        <FormSectionTitle title="Datos de la Mascota" />
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 text-sm mt-2 p-4 bg-gray-50 rounded-lg"
                        >
                            <p>
                                <strong class="font-semibold"
                                    >Propietario:</strong
                                >
                                {{ selectedConsultation.pet_owner }}
                            </p>
                            <p>
                                <strong class="font-semibold">Mascota:</strong>
                                {{ selectedConsultation.pet_name }}
                            </p>
                            <p v-if="selectedConsultation.pet?.owner?.ci">
                                <strong class="font-semibold">Cédula:</strong>
                                {{ selectedConsultation.pet.owner.ci }}
                            </p>
                            <p v-if="selectedConsultation.pet?.breed">
                                <strong class="font-semibold">Raza:</strong>
                                {{ selectedConsultation.pet.breed.name }}
                            </p>
                        </div>
                    </section>
                    <!-- Anamnesis Details -->
                    <section>
                        <FormSectionTitle title="Anamnesis" />
                        <div class="space-y-2 text-sm mt-2">
                            <p>
                                <strong class="font-semibold">Motivo:</strong>
                                {{ selectedConsultation.reason || "N/A" }}
                            </p>
                            <p>
                                <strong class="font-semibold"
                                    >Fecha Desparasitación:</strong
                                >
                                {{ selectedConsultation.dewormed_at || "N/A" }}
                            </p>
                            <p>
                                <strong class="font-semibold"
                                    >Enfermedades Previas:</strong
                                >
                                {{
                                    selectedConsultation.previous_illnesses ||
                                    "N/A"
                                }}
                            </p>
                            <p>
                                <strong class="font-semibold"
                                    >Intervenciones Previas:</strong
                                >
                                {{
                                    selectedConsultation.previous_interventions ||
                                    "N/A"
                                }}
                            </p>
                        </div>
                    </section>
                    <!-- Physical Exam Details -->
                    <section>
                        <FormSectionTitle title="Examen Físico" />
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-2 text-sm mt-2"
                        >
                            <p>
                                <strong class="font-semibold"
                                    >Estado General:</strong
                                >
                                {{
                                    selectedConsultation.general_condition ||
                                    "N/A"
                                }}
                            </p>
                            <p>
                                <strong class="font-semibold">Apetito:</strong>
                                {{ selectedConsultation.appetite || "N/A" }}
                            </p>
                            <p>
                                <strong class="font-semibold"
                                    >Hidratación:</strong
                                >
                                {{ selectedConsultation.hydratation || "N/A" }}
                            </p>
                            <p>
                                <strong class="font-semibold">Mucosa:</strong>
                                {{ selectedConsultation.mucosa || "N/A" }}
                            </p>
                            <p>
                                <strong class="font-semibold">Peso:</strong>
                                {{
                                    selectedConsultation.weight
                                        ? `${selectedConsultation.weight} Kg`
                                        : "N/A"
                                }}
                            </p>
                            <p>
                                <strong class="font-semibold"
                                    >Temperatura:</strong
                                >
                                {{
                                    selectedConsultation.temperature
                                        ? `${selectedConsultation.temperature} °C`
                                        : "N/A"
                                }}
                            </p>
                            <p>
                                <strong class="font-semibold"
                                    >Frec. Cardíaca:</strong
                                >
                                {{ selectedConsultation.heart_rate || "N/A" }}
                            </p>
                            <p>
                                <strong class="font-semibold"
                                    >Frec. Respiratoria:</strong
                                >
                                {{
                                    selectedConsultation.respiratory_rate ||
                                    "N/A"
                                }}
                            </p>
                            <p>
                                <strong class="font-semibold"
                                    >Ap. Digestivo:</strong
                                >
                                {{
                                    selectedConsultation.digestive_system ||
                                    "N/A"
                                }}
                            </p>
                            <p>
                                <strong class="font-semibold"
                                    >Ap. Genitourinario:</strong
                                >
                                {{
                                    selectedConsultation.genitourinary_system ||
                                    "N/A"
                                }}
                            </p>
                            <p>
                                <strong class="font-semibold"
                                    >Ap. Respiratorio:</strong
                                >
                                {{
                                    selectedConsultation.respiratory_system ||
                                    "N/A"
                                }}
                            </p>
                        </div>
                    </section>
                    <!-- Diagnosis and Treatment Details -->
                    <section>
                        <FormSectionTitle title="Diagnóstico y Tratamiento" />
                        <div class="space-y-2 text-sm mt-2">
                            <p>
                                <strong class="font-semibold"
                                    >Diagnóstico Presuntivo:</strong
                                >
                                {{
                                    selectedConsultation.presumptive_diagnosis ||
                                    "N/A"
                                }}
                            </p>
                            <p>
                                <strong class="font-semibold"
                                    >Diagnóstico Confirmativo:</strong
                                >
                                {{
                                    selectedConsultation.confirmatory_diagnosis ||
                                    "N/A"
                                }}
                            </p>
                            <div>
                                <strong class="font-semibold block"
                                    >Tratamiento y Evolución:</strong
                                >
                                <p
                                    class="mt-1 whitespace-pre-wrap bg-gray-50 p-2 rounded"
                                >
                                    {{
                                        selectedConsultation.treatment || "N/A"
                                    }}
                                </p>
                            </div>
                            <p>
                                <strong class="font-semibold"
                                    >Costo Consulta:</strong
                                >
                                {{
                                    selectedConsultation.consultation_fee
                                        ? `$${selectedConsultation.consultation_fee}`
                                        : "N/A"
                                }}
                            </p>
                        </div>
                    </section>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end">
                    <FwbButton @click="closeAllModals" color="alternative"
                        >Cerrar</FwbButton
                    >
                </div>
            </template>
        </FwbModal>

        <!-- Pet Search Modal -->
        <SearchModal
            v-if="isSearchPetModal"
            @close="isSearchPetModal = false"
            :search="search"
            @update:search="search = $event"
            :isFetchingData="isFetchingData"
            :results="petsList"
            @select="handleSelectPet"
            title="Seleccionar Mascota"
            placeholder="Buscar por nombre de mascota o propietario..."
        >
            <template #prefix
                ><div class="p-2"><SearchUser /></div
            ></template>
            <template #result="{ result }">
                <div v-if="result" class="w-full items-center flex">
                    <div class="mx-2">
                        <div class="font-semibold">{{ result.name }}</div>
                        <div class="text-xs text-gray-500">
                            Propietario: {{ result.owner?.first_name }}
                            {{ result.owner?.last_name }}
                        </div>
                    </div>
                </div>
            </template>
        </SearchModal>

        <!-- Delete Modal -->
        <FwbModal v-if="isDeleteModal" @close="closeAllModals">
            <template #header>Confirmar Eliminación</template>
            <template #body>
                <p class="text-center text-lg">
                    ¿Estás seguro de que deseas eliminar la consulta para la
                    mascota <strong>{{ selectedConsultation?.pet_name }}</strong
                    >?
                </p>
                <p class="text-sm text-gray-600 mt-2 text-center">
                    Esta acción no se puede deshacer.
                </p>
            </template>
            <template #footer>
                <div class="flex justify-center w-full">
                    <FwbButton @click="closeAllModals" color="alternative"
                        >Cancelar</FwbButton
                    >
                    <FwbButton
                        @click="submitDelete"
                        color="red"
                        :loading="loading"
                        class="ml-2"
                        >Eliminar</FwbButton
                    >
                </div>
            </template>
        </FwbModal>
    </AdminLayout>
</template>
