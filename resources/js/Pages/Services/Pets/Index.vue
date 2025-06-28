<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
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
    FwbInput,
    FwbListGroup,
    FwbListGroupItem,
    FwbSpinner,
} from "flowbite-vue";
import { computed, ref, watch } from "vue";
import axios from "axios";
import { useDebouncedRef } from "@/Utils/debouncedRef";

// Components
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import FormSectionTitle from "@/Components/Forms/FormSectionTitle.vue";
import SearchModal from "@/Components/Modals/SearchModal.vue";
import SearchUser from "@/Components/Icons/Svg/SearchUser.vue";

// --- PROPS & FILTERS ---
const props = defineProps({ pets: Object, filters: Object });

const filters = ref({ search_term: props.filters.search_term || "" });
function applyFilters() {
    router.get(route("pets.search"), filters.value, {
        preserveState: true,
        replace: true,
    });
}
function resetFilters() {
    filters.value = { search_term: "" };
    router.get(route("pets.index"));
}

// --- PAGINATION ---
const currentPage = ref(props.pets.current_page || 1);
watch(currentPage, (newPage) => {
    router.get(
        route("pets.search"),
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
const isSearchOwnerModal = ref(false);
const selectedPet = ref(null);

// --- FORM & RELATED DATA ---
const defaultFormState = {
    id: null,
    name: "",
    customer_id: "",
    specie_id: "",
    breed_id: "",
    color: "",
    age: "",
    photo_url: "",
};
const form = ref({ ...defaultFormState });
const formErrors = ref({});
const owner = ref(null);
const specie = useDebouncedRef("", 500);
const speciesList = ref([]);
const breed = useDebouncedRef("", 500);
const breedsList = ref([]);
const isFetchingBreeds = ref(false);
const selectedByBreed = ref(false);

// --- OWNER SEARCH ---
const ownerSearch = useDebouncedRef("", 400);
const isFetchingOwner = ref(false);
const customersList = ref([]);

// --- HELPERS & PERMISSIONS ---
const isEmptyData = computed(() => props.pets.data.length === 0);
const page = usePage();
const canCreatePets = true;
const canEditPets = true;

// --- FUNCTIONS ---
function displayToast(type, message) {
    toastType.value = type;
    toastMsg.value = message;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 3000);
}

function openCreateModal() {
    modalMode.value = "create";
    form.value = { ...defaultFormState };
    owner.value = null;
    specie.value = "";
    breed.value = "";
    formErrors.value = {};
    isCreateOrEditModal.value = true;
}

function openEditModal(pet) {
    modalMode.value = "edit";
    selectedPet.value = pet;
    form.value = { ...defaultFormState, ...pet };
    owner.value = pet.owner;
    specie.value = pet.breed?.specie?.name || "";
    breed.value = pet.breed?.name || "";
    formErrors.value = {};
    isCreateOrEditModal.value = true;
}

function openViewModal(pet) {
    selectedPet.value = pet;
    isViewModal.value = true;
}

function openDeleteModal(pet) {
    selectedPet.value = pet;
    isDeleteModal.value = true;
}

function closeAllModals() {
    isCreateOrEditModal.value = false;
    isViewModal.value = false;
    isDeleteModal.value = false;
    isSearchOwnerModal.value = false;
}

// --- Dynamic Specie/Breed/Owner Logic ---
watch(ownerSearch, async (value) => {
    if (value.length < 2) {
        customersList.value = [];
        return;
    }
    isFetchingOwner.value = true;
    try {
        const response = await axios.get(route("customers.autocomplete"), {
            params: { search: value },
        });
        customersList.value = response.data;
    } catch (error) {
        console.error("Error al buscar propietarios:", error);
        displayToast("danger", "No se pudieron cargar los propietarios.");
        customersList.value = [];
    } finally {
        isFetchingOwner.value = false;
    }
});

function selectOwner(customer) {
    owner.value = customer;
    form.value.customer_id = customer.id;
    isSearchOwnerModal.value = false;
    ownerSearch.value = "";
    customersList.value = [];
}

const areStringsEquals = (item, value) =>
    item.name.toLowerCase() === value.toLowerCase();

watch(specie, async (value) => {
    if (selectedByBreed.value) {
        speciesList.value = [];
        selectedByBreed.value = false;
        return;
    }
    if (value && speciesList.value.some((s) => areStringsEquals(s, value))) {
        if (!form.value.specie_id)
            form.value.specie_id =
                speciesList.value.find((s) => areStringsEquals(s, value))?.id ??
                null;
        speciesList.value = [];
        return;
    }
    speciesList.value = [];
    form.value.specie_id = null;
    if (value.length < 1) return;
    try {
        const response = await axios.get(route("species.search"), {
            params: { search: value },
        });
        speciesList.value = response.data;
    } catch (error) {
        console.error(error);
    }
});

function selectSpecie(picked) {
    form.value.specie_id = picked.id;
    specie.value = picked.name;
    speciesList.value = [];
}

watch(breed, async (value) => {
    if (value && breedsList.value.some((b) => areStringsEquals(b, value))) {
        breedsList.value = [];
        return;
    }
    breedsList.value = [];
    if (value.length < 1) return;
    isFetchingBreeds.value = true;
    try {
        const response = await axios.get(route("breeds.search"), {
            params: { search: value, specie_id: form.value.specie_id },
        });
        breedsList.value = response.data;
    } finally {
        isFetchingBreeds.value = false;
    }
});

function selectBreed(pickedBreed) {
    form.value.breed_id = pickedBreed.id;
    breed.value = pickedBreed.name;
    if (pickedBreed.specie_id !== form.value.specie_id) {
        form.value.specie_id = pickedBreed.specie_id;
        specie.value = pickedBreed.specie.name;
        selectedByBreed.value = true;
    }
    breedsList.value = [];
}

// --- CRUD ---
// CORREGIDO: Se usa axios para capturar la respuesta JSON del backend y mostrar el mensaje dinámico en el toast.
async function submitForm() {
    loading.value = true;
    formErrors.value = {};

    try {
        // La lógica de 'prepare-data' se mantiene igual
        if (
            (!form.value.breed_id && breed.value) ||
            (!form.value.specie_id && specie.value)
        ) {
            const prepResponse = await axios.post(route("pets.prepare-data"), {
                breed: breed.value,
                specie: specie.value,
            });
            form.value.breed_id = prepResponse.data.breed_id;
        }

        let response;
        if (modalMode.value === "edit") {
            response = await axios.put(
                route("pets.update", selectedPet.value.id),
                form.value
            );
        } else {
            response = await axios.post(route("pets.store"), form.value);
        }

        // Usar el mensaje dinámico de la respuesta del backend
        displayToast("success", response.data.message);

        closeAllModals();
        router.reload({ only: ["pets"] });
    } catch (e) {
        if (e.response?.status === 422) {
            formErrors.value = e.response.data.errors;
            // Usar un mensaje de error genérico o el del backend si lo hubiera
            const errorMessage =
                e.response.data.message || "Por favor, corrige los errores.";
            displayToast("danger", errorMessage);
        } else {
            // Mensaje para otros errores (ej. 500, error de red)
            displayToast("danger", "Ocurrió un error inesperado.");
        }
    } finally {
        loading.value = false;
    }
}

// CORREGIDO: Se usa axios para consistencia y para capturar el mensaje del backend.
async function submitDelete() {
    loading.value = true;
    try {
        const response = await axios.delete(
            route("pets.destroy", selectedPet.value.id)
        );

        // Usar el mensaje dinámico de la respuesta del backend
        displayToast("success", response.data.message);

        closeAllModals();
        router.reload({ only: ["pets"] });
    } catch (e) {
        const errorMessage =
            e.response?.data?.message || "Error al eliminar la mascota.";
        displayToast("danger", errorMessage);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <AdminLayout title="Mascotas">
        <div class="fixed top-4 right-4 z-50">
            <FwbToast v-if="showToast" :type="toastType" closable>{{
                toastMsg
            }}</FwbToast>
        </div>

        <div class="flex justify-between my-6 items-center">
            <h2 class="text-2xl font-semibold">Mascotas</h2>
            <FwbButton
                v-if="canCreatePets"
                @click="openCreateModal"
                color="purple"
                ><i class="fa-solid fa-plus mr-2"></i>Agregar Mascota</FwbButton
            >
        </div>

        <form
            @submit.prevent="applyFilters"
            class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-100 rounded-lg"
        >
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700"
                    >Buscar por Nombre o Propietario</label
                >
                <TextInput
                    v-model="filters.search_term"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Ej: Fido, John Doe..."
                />
            </div>
            <div class="flex items-end space-x-2">
                <FwbButton color="purple" type="submit">Filtrar</FwbButton>
                <FwbButton color="alternative" @click.prevent="resetFilters"
                    >Limpiar</FwbButton
                >
            </div>
        </form>

        <FwbTable>
            <FwbTableHead>
                <FwbTableHeadCell>Nombre</FwbTableHeadCell>
                <FwbTableHeadCell>Propietario</FwbTableHeadCell>
                <FwbTableHeadCell>Especie</FwbTableHeadCell>
                <FwbTableHeadCell>Raza</FwbTableHeadCell>
                <FwbTableHeadCell>Actualizado</FwbTableHeadCell>
                <FwbTableHeadCell
                    ><span class="sr-only">Acciones</span></FwbTableHeadCell
                >
            </FwbTableHead>
            <FwbTableBody>
                <FwbTableRow v-if="isEmptyData"
                    ><FwbTableCell
                        colspan="6"
                        class="text-center py-4 text-gray-500"
                        >No se encontraron mascotas.</FwbTableCell
                    ></FwbTableRow
                >
                <FwbTableRow v-for="pet in pets.data" :key="pet.id">
                    <FwbTableCell>{{ pet.name }}</FwbTableCell>
                    <FwbTableCell
                        >{{ pet.owner?.first_name }}
                        {{ pet.owner?.last_name }}</FwbTableCell
                    >
                    <FwbTableCell>{{ pet.breed?.specie?.name }}</FwbTableCell>
                    <FwbTableCell>{{ pet.breed?.name }}</FwbTableCell>
                    <FwbTableCell>{{ pet.updated_at }}</FwbTableCell>
                    <FwbTableCell class="space-x-4 whitespace-nowrap">
                        <button
                            @click="openViewModal(pet)"
                            class="text-blue-600 hover:text-blue-800"
                            title="Ver Detalles"
                        >
                            <i class="fa-solid fa-eye fa-lg"></i>
                        </button>
                        <button
                            @click="openEditModal(pet)"
                            class="text-yellow-500 hover:text-yellow-700"
                            title="Editar"
                        >
                            <i class="fa-solid fa-pencil fa-lg"></i>
                        </button>
                        <button
                            @click="openDeleteModal(pet)"
                            class="text-red-500 hover:text-red-700"
                            title="Eliminar"
                        >
                            <i class="fa-solid fa-trash fa-lg"></i>
                        </button>
                    </FwbTableCell>
                </FwbTableRow>
            </FwbTableBody>
        </FwbTable>
        <div v-if="!isEmptyData" class="flex justify-center my-4">
            <FwbPagination
                v-model="currentPage"
                :total-items="pets.total"
                :per-page="pets.per_page"
                large
            />
        </div>

        <FwbModal size="lg" v-if="isViewModal" @close="closeAllModals">
            <template #header
                ><h3 class="text-xl font-semibold">
                    Detalles de la Mascota
                </h3></template
            >
            <template #body>
                <div v-if="selectedPet" class="space-y-4 text-sm">
                    <p><strong>Nombre:</strong> {{ selectedPet.name }}</p>
                    <p>
                        <strong>Propietario:</strong>
                        {{ selectedPet.owner?.first_name }}
                        {{ selectedPet.owner?.last_name }}
                    </p>
                    <p>
                        <strong>Especie:</strong>
                        {{ selectedPet.breed?.specie?.name }}
                    </p>
                    <p><strong>Raza:</strong> {{ selectedPet.breed?.name }}</p>
                    <p><strong>Color:</strong> {{ selectedPet.color }}</p>
                    <p><strong>Edad:</strong> {{ selectedPet.age }}</p>
                </div>
            </template>
            <template #footer
                ><FwbButton @click="closeAllModals" color="alternative"
                    >Cerrar</FwbButton
                ></template
            >
        </FwbModal>

        <FwbModal v-if="isDeleteModal" @close="closeAllModals">
            <template #header>Confirmar Eliminación</template>
            <template #body
                ><p class="text-center text-lg">
                    ¿Seguro que deseas eliminar a
                    <strong>{{ selectedPet?.name }}</strong
                    >?
                </p></template
            >
            <template #footer>
                <div class="flex justify-center w-full">
                    <FwbButton @click="closeAllModals" color="alternative"
                        >Cancelar</FwbButton
                    ><FwbButton
                        @click="submitDelete"
                        color="red"
                        :loading="loading"
                        class="ml-2"
                        >Eliminar</FwbButton
                    >
                </div>
            </template>
        </FwbModal>

        <FwbModal size="4xl" v-if="isCreateOrEditModal" @close="closeAllModals">
            <template #header
                ><h3 class="text-xl font-semibold">
                    {{ modalMode === "edit" ? "Editar" : "Registrar" }} Mascota
                </h3></template
            >
            <template #body>
                <form class="space-y-6" @submit.prevent="submitForm">
                    <div>
                        <div class="mb-4 flex justify-between items-start">
                            <h3 class="text-lg font-semibold text-gray-700">
                                Datos del Propietario
                            </h3>
                            <FwbButton
                                @click="isSearchOwnerModal = true"
                                type="button"
                                color="purple"
                                size="xs"
                                >Buscar Propietario</FwbButton
                            >
                        </div>
                        <div
                            v-if="!owner"
                            class="border py-6 text-center text-gray-500 rounded-lg"
                            :class="{
                                'bg-red-50 border-red-300':
                                    formErrors.customer_id,
                            }"
                        >
                            Debe seleccionar un propietario
                            <InputError
                                class="mt-1"
                                :message="formErrors.customer_id?.[0]"
                            />
                        </div>
                        <div
                            v-else
                            class="grid grid-cols-2 gap-4 rounded bg-gray-50 border p-3 text-sm"
                        >
                            <p>
                                <strong>Nombre:</strong> {{ owner.first_name }}
                                {{ owner.last_name }}
                            </p>
                            <p><strong>CI:</strong> {{ owner.ci }}</p>
                        </div>
                    </div>
                    <FormSectionTitle title="Datos de la Mascota" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="name" value="Nombre" /><TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 w-full"
                            /><InputError
                                class="mt-1"
                                :message="formErrors.name?.[0]"
                            />
                        </div>
                        <div>
                            <InputLabel for="color" value="Color" /><TextInput
                                id="color"
                                v-model="form.color"
                                type="text"
                                class="mt-1 w-full"
                            /><InputError
                                class="mt-1"
                                :message="formErrors.color?.[0]"
                            />
                        </div>
                        <div class="relative">
                            <FwbInput
                                v-model="specie"
                                label="Especie"
                                autocomplete="off"
                            />
                            <div
                                v-if="speciesList.length > 0"
                                class="absolute w-full z-10"
                            >
                                <FwbListGroup
                                    ><FwbListGroupItem
                                        v-for="s in speciesList"
                                        :key="s.id"
                                        @click="selectSpecie(s)"
                                        hover
                                        ><div class="font-normal">
                                            {{ s.name }}
                                        </div></FwbListGroupItem
                                    ></FwbListGroup
                                >
                            </div>
                            <InputError
                                class="mt-1"
                                :message="formErrors.specie_id?.[0]"
                            />
                        </div>
                        <div class="relative">
                            <FwbInput
                                v-model="breed"
                                label="Raza"
                                autocomplete="off"
                                ><template v-if="isFetchingBreeds" #suffix
                                    ><FwbSpinner size="4" /></template
                            ></FwbInput>
                            <div
                                v-if="breedsList.length > 0"
                                class="absolute w-full z-10"
                            >
                                <FwbListGroup
                                    ><FwbListGroupItem
                                        v-for="b in breedsList"
                                        :key="b.id"
                                        @click="selectBreed(b)"
                                        hover
                                        ><div class="font-normal">
                                            {{ b.name }}
                                        </div></FwbListGroupItem
                                    ></FwbListGroup
                                >
                            </div>
                            <InputError
                                class="mt-1"
                                :message="formErrors.breed_id?.[0]"
                            />
                        </div>
                        <div>
                            <InputLabel for="age" value="Edad" /><TextInput
                                id="age"
                                v-model="form.age"
                                type="text"
                                class="mt-1 w-full"
                            /><InputError
                                class="mt-1"
                                :message="formErrors.age?.[0]"
                            />
                        </div>
                    </div>
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

        <SearchModal
            v-if="isSearchOwnerModal"
            @close="isSearchOwnerModal = false"
            :search="ownerSearch"
            @update:search="ownerSearch = $event"
            :isFetchingData="isFetchingOwner"
            :results="customersList"
            @select="selectOwner"
            title="Buscar Propietario"
            placeholder="Nombre o CI del propietario..."
        >
            <template #prefix
                ><div class="p-2"><SearchUser /></div
            ></template>
            <template #result="{ result }">
                <div class="mx-2">
                    <div class="font-semibold">
                        {{ result.first_name }} {{ result.last_name }}
                    </div>
                    <div class="text-xs text-gray-500">CI: {{ result.ci }}</div>
                </div>
            </template>
        </SearchModal>
    </AdminLayout>
</template>
