<script setup>
import FormSectionTitle from "@/Components/Forms/FormSectionTitle.vue";
import SearchUser from "@/Components/Icons/Svg/SearchUser.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SearchModal from "@/Components/Modals/SearchModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useDebouncedRef } from "@/Utils/debouncedRef";
import PermissionEnum from "@/Utils/Enums/PermissionEnum";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { FwbButton, FwbRadio, FwbTextarea } from "flowbite-vue";
import { computed, onMounted, ref, watch } from "vue";

const props = defineProps({ formAction: String, medicalConsultation: Object });
const page = usePage();

const actionTitle = computed(() => {
    return props.formAction.includes("edit") ? "Actualizar" : "Agregar";
});

const form = useForm({
    reason: props.medicalConsultation?.reason || "",
    pet_id: props.medicalConsultation?.pet_id || "",
    dewormed_at: props.medicalConsultation?.dewormed_at || "",
    previous_illnesses: props.medicalConsultation?.previous_illnesses || "",
    previous_interventions:
        props.medicalConsultation?.previous_interventions || "",
    general_condition: props.medicalConsultation?.general_condition || "0",
    appetite: props.medicalConsultation?.appetite?.split(", ") || [],
    hydratation: props.medicalConsultation?.hydratation?.split(", ") || [],
    weight: props.medicalConsultation?.weight || "",
    mucosa: props.medicalConsultation?.mucosa?.split(", ") || [],
    digestive_system: props.medicalConsultation?.digestive_system || "",
    genitourinary_system: props.medicalConsultation?.genitourinary_system || "",
    respiratory_system: props.medicalConsultation?.respiratory_system || "",
    temperature: props.medicalConsultation?.temperature || "",
    heart_rate: props.medicalConsultation?.heart_rate || "",
    respiratory_rate: props.medicalConsultation?.respiratory_rate || "",
    clinical_observation: props.medicalConsultation?.clinical_observation || "",
    complementary_tests: props.medicalConsultation?.complementary_tests || "",
    presumptive_diagnosis:
        props.medicalConsultation?.presumptive_diagnosis || "",
    confirmatory_diagnosis:
        props.medicalConsultation?.confirmatory_diagnosis || "",
    consultation_fee: props.medicalConsultation?.consultation_fee || "",
    treatment: props.medicalConsultation?.treatment || "",
    veterinarian_id: page.props.auth.user.id,
});

const submit = () => {
    form.appetite = formatCheckboxValueToString(form.appetite);
    form.hydratation = formatCheckboxValueToString(form.hydratation);
    form.mucosa = formatCheckboxValueToString(form.mucosa);

    if (props.formAction.includes("edit")) {
        form.put(
            route("medical-consultations.update", {
                id: props.medicalConsultation.id,
            })
        );
        return;
    }

    form.post(route(routeName));
};

const selectedPet = ref(null);

const search = useDebouncedRef("", 300);
const isFetchingData = ref(false);
const petsList = ref([]);
const isShowModal = ref(false);

const showModal = (value) => {
    isShowModal.value = value;
};

const selectPet = (pet) => {
    selectedPet.value = pet;
    form.pet_id = pet.id;
    showModal(false);
    search.value = "";
};

watch(search, async (value) => {
    petsList.value = [];
    if (value.length < 1) {
        return;
    }

    isFetchingData.value = true;

    try {
        const response = await axios.get(route("pets.search"), {
            params: { search: value },
        });
        petsList.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        isFetchingData.value = false;
    }
});

const formatCheckboxValueToString = (value) => {
    return value.join(", ");
};
onMounted(() => {
    if (props.medicalConsultation) {
        const pet = props.medicalConsultation.pet;
        selectedPet.value = {
            id: pet.id,
            name: pet.name,
            owner: pet.owner,
            owner_full_name: `${pet.owner.first_name} ${pet.owner.last_name}`,
            specie_and_breed: `${pet.breed.specie.name} - ${pet.breed.name}`,
        };
    }
});
</script>

<template>
    <AdminLayout :title="actionTitle + ' Consulta Médica'">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                {{ actionTitle }} Consulta médica
            </h2>
        </div>

        <hr class="my-4" />

        <div>
            <form @submit.prevent="submit">
                <div>
                    <div class="mb-4 flex justify-between">
                        <h3
                            class="text-xl font-semibold text-gray-700 dark:text-gray-200"
                        >
                            Datos de la mascota
                        </h3>

                        <FwbButton
                            @click="showModal"
                            type="button"
                            color="purple"
                        >
                            Seleccionar mascota
                        </FwbButton>
                    </div>
                    <div>
                        <div
                            v-if="!selectedPet"
                            class="md:col-span-2 border py-8 text-center text-gray-500"
                            :class="[
                                form.errors.pet_id ? 'bg-red-100' : 'bg-white',
                            ]"
                        >
                            Debe seleccionar una mascota
                        </div>
                        <div
                            v-else
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 rounded bg-white border p-4"
                        >
                            <div>
                                <label
                                    for="owner_name"
                                    class="mr-2 font-medium"
                                >
                                    Propietario :
                                </label>
                                <span>
                                    {{ selectedPet.owner_full_name }}
                                </span>
                            </div>
                            <div>
                                <label for="owner_ci" class="mr-2 font-medium">
                                    Cédula de identidad :
                                </label>
                                <span>{{ selectedPet.owner?.ci }}</span>
                            </div>
                            <div>
                                <label
                                    for="owner_name"
                                    class="mr-2 font-medium"
                                >
                                    Mascota :
                                </label>
                                <span>
                                    {{ selectedPet.name }}
                                </span>
                            </div>
                            <div>
                                <label for="owner_ci" class="mr-2 font-medium">
                                    Especie y raza :
                                </label>
                                <span>{{ selectedPet.specie_and_breed }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4" />

                <div>
                    <FormSectionTitle title="Anamnesis" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <FwbTextarea
                                v-model="form.reason"
                                :rows="3"
                                custom
                                label="Motivo de la consulta"
                                placeholder=""
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.reason"
                            />
                        </div>

                        <div>
                            <InputLabel
                                for="dewormed_at"
                                value="Fecha de desparacitación"
                            />
                            <TextInput
                                id="dewormed_at"
                                v-model="form.dewormed_at"
                                type="date"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.dewormed_at"
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
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.previous_illnesses"
                            />
                        </div>

                        <div>
                            <InputLabel
                                for="previous_interventions"
                                value="Intervenciones previas"
                            />
                            <TextInput
                                id="previous_interventions"
                                v-model="form.previous_interventions"
                                type="text"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.previous_illnesses"
                            />
                        </div>
                    </div>
                </div>

                <!-- Separación por sección -->

                <hr class="mt-8 mb-4" />

                <div>
                    <FormSectionTitle title="Examen físico" />
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6"
                    >
                        <div>
                            <InputLabel
                                for="general_condition"
                                value="Estado general"
                            />
                            <div class="flex pt-1">
                                <FwbRadio
                                    v-model="form.general_condition"
                                    name="radio"
                                    label="Bueno"
                                    value="0"
                                />
                                <FwbRadio
                                    v-model="form.general_condition"
                                    name="radio"
                                    label="Regular"
                                    value="1"
                                />
                                <FwbRadio
                                    v-model="form.general_condition"
                                    name="radio"
                                    label="Malo"
                                    value="2"
                                />
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.general_condition"
                            />
                        </div>

                        <div>
                            <InputLabel for="appetite" value="Apetito" />
                            <div
                                class="flex flex-wrap gap-y-4 justify-between items-center pt-1 text-sm mt-2"
                            >
                                <div>
                                    <input
                                        type="checkbox"
                                        id="apetito-normal"
                                        value="Normal"
                                        v-model="form.appetite"
                                        class="mr-2 rounded"
                                    />
                                    <label for="apetito-normal">Normal</label>
                                </div>

                                <div>
                                    <input
                                        type="checkbox"
                                        id="apetito-disminuido"
                                        value="Disminuido"
                                        v-model="form.appetite"
                                        class="mr-2 rounded"
                                    />
                                    <label for="apetito-disminuido"
                                        >Disminuido</label
                                    >
                                </div>

                                <div>
                                    <input
                                        type="checkbox"
                                        id="apetito-anorexia"
                                        value="Anorexia"
                                        v-model="form.appetite"
                                        class="mr-2 rounded"
                                    />
                                    <label for="apetito-anorexia"
                                        >Anoréxico</label
                                    >
                                </div>

                                <div>
                                    <input
                                        type="checkbox"
                                        id="apetito-polifagia"
                                        value="Polifagia"
                                        v-model="form.appetite"
                                        class="mr-2 rounded"
                                    />
                                    <label for="apetito-anorexia"
                                        >Polifagia</label
                                    >
                                </div>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.appetite"
                            />
                        </div>

                        <div>
                            <InputLabel for="hidratation" value="Hidratación" />
                            <div
                                class="flex flex-wrap gap-y-4 justify-between items-center pt-1 text-sm mt-2"
                            >
                                <div>
                                    <input
                                        type="checkbox"
                                        id="hidratacion-normal"
                                        value="Normal"
                                        v-model="form.hydratation"
                                        class="mr-2 rounded"
                                    />
                                    <label for="hidratacion-normal"
                                        >Normal</label
                                    >
                                </div>

                                <div>
                                    <input
                                        type="checkbox"
                                        id="hidratacion-leve"
                                        value="Leve"
                                        v-model="form.hydratation"
                                        class="mr-2 rounded"
                                    />
                                    <label for="hidratacion-leve">Leve</label>
                                </div>

                                <div>
                                    <input
                                        type="checkbox"
                                        id="hidratacion-moderada"
                                        value="Moderada"
                                        v-model="form.hydratation"
                                        class="mr-2 rounded"
                                    />
                                    <label for="hidratacion-moderada"
                                        >Moderada</label
                                    >
                                </div>

                                <div>
                                    <input
                                        type="checkbox"
                                        id="hidratacion-grave"
                                        value="Grave"
                                        v-model="form.hydratation"
                                        class="mr-2 rounded"
                                    />
                                    <label for="hidratacion-grave">Grave</label>
                                </div>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.hydratation"
                            />
                        </div>

                        <div>
                            <InputLabel for="mucosa" value="Mucosa" />
                            <div
                                class="flex flex-wrap gap-y-4 justify-between items-center pt-1 text-sm mt-2"
                            >
                                <div>
                                    <input
                                        type="checkbox"
                                        id="mucosa-rosada"
                                        value="Rosada"
                                        v-model="form.mucosa"
                                        class="mr-2 rounded"
                                    />
                                    <label for="mucosa-rosada">Rosada</label>
                                </div>

                                <div>
                                    <input
                                        type="checkbox"
                                        id="mucosa-palida"
                                        value="Palida"
                                        v-model="form.mucosa"
                                        class="mr-2 rounded"
                                    />
                                    <label for="mucosa-palida">Pálida</label>
                                </div>

                                <div>
                                    <input
                                        type="checkbox"
                                        id="mucosa-congestionada"
                                        value="Congestionada"
                                        v-model="form.mucosa"
                                        class="mr-2 rounded"
                                    />
                                    <label for="mucosa-congestionada"
                                        >Congestionada</label
                                    >
                                </div>

                                <div>
                                    <input
                                        type="checkbox"
                                        id="mucosa-iorotica"
                                        value="Iorotica"
                                        v-model="form.mucosa"
                                        class="mr-2 rounded"
                                    />
                                    <label for="mucosa-ioretica"
                                        >Iorética</label
                                    >
                                </div>

                                <div>
                                    <input
                                        type="checkbox"
                                        id="mucosa-cianotica"
                                        value="Cianotica"
                                        v-model="form.mucosa"
                                        class="mr-2 rounded"
                                    />
                                    <label for="mucosa-cianotica"
                                        >Cianotica</label
                                    >
                                </div>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.mucosa"
                            />
                        </div>

                        <div>
                            <InputLabel
                                for="digestive_system"
                                value="Ap. Digestivo"
                            />
                            <TextInput
                                id="digestive_system"
                                v-model="form.digestive_system"
                                type="text"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.digestive_system"
                            />
                        </div>

                        <div>
                            <InputLabel
                                for="genitourinary_system"
                                value="Ap. Genitourinario"
                            />
                            <TextInput
                                id="genitourinary_system"
                                v-model="form.genitourinary_system"
                                type="text"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.genitourinary_system"
                            />
                        </div>

                        <div>
                            <InputLabel
                                for="respiratory_system"
                                value="Ap. Respiratorio"
                            />
                            <TextInput
                                id="respiratory_system"
                                v-model="form.respiratory_system"
                                type="text"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.respiratory_system"
                            />
                        </div>

                        <div>
                            <InputLabel for="weight" value="Peso (Kg.)" />
                            <TextInput
                                id="weight"
                                v-model="form.weight"
                                type="number"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.weight"
                            />
                        </div>

                        <div>
                            <InputLabel for="temperature" value="Temperatura" />
                            <TextInput
                                id="temperature"
                                v-model="form.temperature"
                                type="number"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.temperature"
                            />
                        </div>

                        <div>
                            <InputLabel
                                for="heart_rate"
                                value="Frecuencia cardíaca"
                            />
                            <TextInput
                                id="heart_rate"
                                v-model="form.heart_rate"
                                type="number"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.heart_rate"
                            />
                        </div>

                        <div>
                            <InputLabel
                                for="respiratory_rate"
                                value="Frecuencia respiratoria"
                            />
                            <TextInput
                                id="respiratory_rate"
                                v-model="form.respiratory_rate"
                                type="number"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.respiratory_rate"
                            />
                        </div>

                        <div></div>

                        <div>
                            <FwbTextarea
                                v-model="form.clinical_observation"
                                :rows="3"
                                custom
                                label="Observación clínica"
                                placeholder=""
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.clinical_observation"
                            />
                        </div>

                        <div>
                            <FwbTextarea
                                v-model="form.complementary_tests"
                                :rows="3"
                                custom
                                label="Pruebas complementarias"
                                placeholder=""
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.complementary_tests"
                            />
                        </div>

                        <div>
                            <InputLabel
                                for="presumptive_diagnosis"
                                value="Diagnóstico presuntivo"
                            />
                            <TextInput
                                id="presumptive_diagnosis"
                                v-model="form.presumptive_diagnosis"
                                type="text"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.presumptive_diagnosis"
                            />
                        </div>

                        <div>
                            <InputLabel
                                for="confirmatory_diagnosis"
                                value="Diagnóstico Confirmativo"
                            />
                            <TextInput
                                id="confirmatory_diagnosis"
                                v-model="form.confirmatory_diagnosis"
                                type="text"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.confirmatory_diagnosis"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <FwbTextarea
                                v-model="form.treatment"
                                :rows="3"
                                custom
                                label="Tratamiento y evolución"
                                placeholder=""
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.treatment"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <FwbButton type="button" color="alternative">
                        <Link
                            :href="route('medical-consultations.index')"
                            class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Cancelar y volver
                        </Link>
                    </FwbButton>

                    <PrimaryButton
                        class="ms-4"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Guardar
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <SearchModal
            v-if="isShowModal"
            @close="showModal(false)"
            :search="search"
            @update:search="search = $event"
            :isFetchingData="isFetchingData"
            :results="petsList"
            @select="selectPet"
            title="Seleccionar mascota"
            placeholder="Nombre o ID de la mascota..."
        >
            <template #prefix>
                <div
                    class="flex relative w-5 h-5 border border-black justify-center items-center m-1 mr-2 mt-1 rounded-full"
                >
                    <SearchUser />
                </div>
            </template>
            <template #result="{ result }">
                <div v-if="result" class="w-full items-center flex">
                    <div class="mx-2 -mt-1">
                        {{ result.name }}
                        <!-- <small class="text-gray-500">({{ result.id }})</small> -->
                        <div
                            class="text-xs truncate w-full normal-case font-normal -mt-1 text-gray-500"
                        >
                            {{
                                `Propietario: ${result.owner?.first_name} ${result.owner?.last_name}`
                            }}
                        </div>
                    </div>
                </div>
            </template>
        </SearchModal>
    </AdminLayout>
</template>
