<script setup>
import { computed, ref, watch } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import { useDebouncedRef } from "@/Utils/debouncedRef";
import axios from "axios";

import FormSectionTitle from "@/Components/Forms/FormSectionTitle.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import SearchModal from "@/Components/Modals/SearchModal.vue";
import SearchUser from "@/Components/Icons/Svg/SearchUser.vue";
import {
    FwbButton,
    FwbInput,
    FwbListGroup,
    FwbListGroupItem,
    FwbSpinner,
} from "flowbite-vue";

const props = defineProps({ formAction: String, pet: Object });

const actionTitle = computed(() =>
    props.formAction.includes("edit") ? "Actualizar" : "Agregar"
);

const form = useForm({
    name: props.pet?.name || "",
    customer_id: props.pet?.customer_id || "",
    specie_id: props.pet?.breed.specie_id || "",
    breed_id: props.pet?.breed_id || "",
    color: props.pet?.color || "",
    age: props.pet?.age.toString() || "",
    photo_url: props.pet?.photo_url || "",
});

const areStringsEquals = (specie, value) =>
    specie.name.toLowerCase() === value.toLowerCase();

const submit = async () => {
    try {
        if (
            (!form.breed_id && breed.value) ||
            (!form.specie_id && specie.value)
        ) {
            const response = await axios.post(route("pets.prepareStoreData"), {
                breed: breed.value,
                breed_id: form.breed_id,
                specie: specie.value,
                specie_id: form.specie_id,
            });
            form.breed_id = response.data.breed_id;
            form.specie_id = response.data.specie_id;
        }
        if (props.formAction.includes("edit")) {
            form.put(route("pets.update", props.pet.id));
        } else {
            form.post(route("pets.store"));
        }
    } catch (error) {
        console.error(error);
        alert(error.response.data.message);
    }
};

const isShowModal = ref(false);
const search = useDebouncedRef("", 500);
const isFetchingData = ref(false);
const owner = ref(
    props.formAction.includes("edit") ? props.pet.owner : ""
);
const customersList = ref([]);
const specie = useDebouncedRef(
    props.formAction.includes("edit") ? props.pet.breed.specie.name : "",
    500
);
const speciesList = ref([]);
const breed = useDebouncedRef(
    props.formAction.includes("edit") ? props.pet.breed.name : "",
    500
);
const breedsList = ref([]);
const isFetchingBreeds = ref(false);
const selectedByBreed = ref(false);

const showModal = (show = true) => {
    isShowModal.value = show;
};

const selectOwner = (customer) => {
    owner.value = customer;
    form.customer_id = customer.id;
    showModal(false);
    search.value = "";
};

watch(search, async (value) => {
    customersList.value = [];
    if (value.length < 1) return;

    isFetchingData.value = true;
    try {
        const response = await axios.get(route("customers.search"), {
            params: { search: value },
        });
        customersList.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        isFetchingData.value = false;
    }
});

const selectSpecie = (picked) => {
    form.specie_id = picked.id;
    specie.value = picked.name;
};

watch(specie, async (value) => {
    if (selectedByBreed.value) {
        speciesList.value = [];
        selectedByBreed.value = false;
        return;
    }

    if (
        value &&
        speciesList.value.some((specie) => areStringsEquals(specie, value))
    ) {
        if (!form.specie_id) {
            form.specie_id =
                speciesList.value.find((specie) =>
                    areStringsEquals(specie, value)
                )?.id ?? null;
        }
        speciesList.value = [];
        return;
    }
    speciesList.value = [];
    form.specie_id = null;
    if (value.length < 1) return;

    isFetchingData.value = true;
    try {
        const response = await axios.get(route("species.search"), {
            params: { search: value },
        });
        speciesList.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        isFetchingData.value = false;
    }
});

const selectBreed = (pickedBreed) => {
    form.breed_id = pickedBreed.id;
    breed.value = pickedBreed.name;
    if (pickedBreed.specie_id !== form.specie_id) {
        form.specie_id = pickedBreed.specie_id;
        specie.value = pickedBreed.specie.name;
        selectedByBreed.value = true;
    }
};

watch(breed, async (value) => {
    if (
        value &&
        breedsList.value.some((breed) => areStringsEquals(breed, value))
    ) {
        breedsList.value = [];
        return;
    }
    breedsList.value = [];
    if (value.length < 1) return;

    isFetchingBreeds.value = true;
    try {
        const response = await axios.get(route("breeds.search"), {
            params: {
                search: value,
                specie_id: form.specie_id,
            },
        });
        breedsList.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        isFetchingBreeds.value = false;
    }
});
</script>

<template>
    <AdminLayout :title="actionTitle + ' Consulta Médica'">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                {{ actionTitle }} Mascota
            </h2>
        </div>
        <div>
            <form @submit.prevent="submit">
                <div>
                    <div class="mb-4 flex justify-between">
                        <h3
                            class="text-xl font-semibold text-gray-700 dark:text-gray-200"
                        >
                            Datos del propietario
                        </h3>

                        <FwbButton
                            @click="showModal"
                            type="button"
                            color="purple"
                        >
                            Seleccionar propietario
                        </FwbButton>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-if="!owner"
                            class="md:col-span-2 border rounded py-8 text-center text-gray-500"
                            :class="[
                                form.errors.customer_id
                                    ? 'bg-red-100'
                                    : 'bg-white',
                            ]"
                        >
                            Debe seleccionar al propietario
                        </div>
                        <template v-else>
                            <div>
                                <label for="owner_name" class="mr-2 font-medium"
                                    >Cédula de identidad :</label
                                >
                                <span>{{ owner.ci }}</span>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.gender"
                                />
                            </div>
                            <div>
                                <label for="owner_name" class="mr-2 font-medium"
                                    >Propietario :</label
                                >
                                <span>{{
                                    owner.first_name + " " + owner.last_name
                                }}</span>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.gender"
                                />
                            </div>
                        </template>
                    </div>
                </div>

                <hr class="my-4" />

                <div>
                    <FormSectionTitle title="Datos de la mascota" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="name" value="Nombre" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.name"
                            />
                        </div>

                        <div>
                            <FwbInput
                                v-model="specie"
                                label="Especie"
                                autocomplete="off"
                            >
                                <template v-if="isFetchingData" #suffix>
                                    <FwbSpinner size="6">Search</FwbSpinner>
                                </template>
                            </FwbInput>
                            <div v-if="speciesList.length > 0" class="relative">
                                <FwbListGroup class="absolute w-full border">
                                    <FwbListGroupItem
                                        v-for="specie in speciesList"
                                        :key="specie.id"
                                        @click="selectSpecie(specie)"
                                        hover
                                    >
                                        <div class="w-full items-center flex">
                                            <div class="mx-2 -mt-1 font-normal">
                                                {{ specie.name }}
                                            </div>
                                        </div>
                                    </FwbListGroupItem>
                                </FwbListGroup>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.specie_id"
                            />
                        </div>

                        <div>
                            <FwbInput
                                v-model="breed"
                                label="Raza"
                                autocomplete="off"
                            >
                                <template v-if="isFetchingBreeds" #suffix>
                                    <FwbSpinner size="6">Search</FwbSpinner>
                                </template>
                            </FwbInput>
                            <div v-if="breedsList.length > 0" class="relative">
                                <FwbListGroup class="absolute w-full border">
                                    <FwbListGroupItem
                                        v-for="breed in breedsList"
                                        :key="breed.id"
                                        @click="selectBreed(breed)"
                                        hover
                                    >
                                        <div class="w-full items-center flex">
                                            <div class="mx-2 -mt-1 font-normal">
                                                {{ breed.name }}
                                                <div
                                                    v-if="!form.specie_id"
                                                    class="text-xs truncate w-full normal-case font-normal -mt-1 text-gray-500"
                                                >
                                                    {{
                                                        "Especie: " +
                                                        breed.specie?.name
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </FwbListGroupItem>
                                </FwbListGroup>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.breed_id"
                            />
                        </div>

                        <div>
                            <InputLabel for="color" value="Color" />
                            <TextInput
                                id="color"
                                v-model="form.color"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.color"
                            />
                        </div>

                        <div>
                            <InputLabel for="age" value="Edad" />
                            <TextInput
                                id="age"
                                v-model="form.age"
                                type="number"
                                class="mt-1 block w-full"
                                required
                                autocomplete="off"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.age"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <FwbButton
                        :href="route('medical-consultations.index')"
                        type="button"
                        color="alternative"
                    >
                        Cancelar y volver
                    </FwbButton>

                    <PrimaryButton
                        class="ms-4"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ actionTitle }}
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
            :results="customersList"
            @select="selectOwner"
            title="Propietario de la mascota"
            placeholder="Nombre o carnet de identidad del propietario..."
        >
            <template #prefix>
                <div
                    class="flex relative w-5 h-5 border border-black justify-center items-center m-1 mr-2 mt-1 rounded-full"
                >
                    <SearchUser />
                </div>
            </template>
            <template #result="{ result }">
                <div class="w-full items-center flex">
                    <div class="mx-2 -mt-1">
                        {{ result.first_name + " " + result.last_name }}
                        <small class="text-gray-500">({{ result.id }})</small>
                        <div
                            class="text-xs truncate w-full normal-case font-normal -mt-1 text-gray-500"
                        >
                            {{ "C.I: " + result.ci }}
                        </div>
                    </div>
                </div>
            </template>
        </SearchModal>
    </AdminLayout>
</template>
