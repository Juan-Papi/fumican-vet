<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { FwbButton, FwbInput, FwbRadio } from "flowbite-vue";
import { computed } from "vue";

const props = defineProps({ formAction: String, customer: Object });

const actionTitle = computed(() => {
    return props.formAction.includes("edit") ? "Editar" : "Agregar";
});

const form = useForm({
    first_name: props.customer?.first_name || "",
    last_name: props.customer?.last_name || "",
    ci: props.customer?.ci || "",
    phone_number: props.customer?.phone_number || "",
    gender: props.customer?.gender || "",
    birthdate: props.customer?.birthdate || "",
});

const submit = () => {
    const routeName = props.formAction.includes("edit") ? "customers.update" : "customers.store";
    const method = props.formAction.includes("edit") ? "put" : "post";
    form[method](route(routeName, props.customer?.id), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <AdminLayout :title="actionTitle + ' Cliente'">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                {{ actionTitle }} cliente
            </h2>
        </div>
        <div>
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="first_name" value="Nombre" />
                        <TextInput
                            id="first_name"
                            v-model="form.first_name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autofocus
                            autocomplete="off"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.first_name"
                        />
                    </div>

                    <div>
                        <InputLabel for="last_name" value="Apellido" />
                        <TextInput
                            id="last_name"
                            v-model="form.last_name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autocomplete="off"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.last_name"
                        />
                    </div>

                    <div>
                        <InputLabel for="ci" value="Carnet de Identidad" />
                        <TextInput
                            id="ci"
                            v-model="form.ci"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autocomplete="off"
                        />
                        <InputError class="mt-2" :message="form.errors.ci" />
                    </div>

                    <div>
                        <InputLabel
                            for="phone_number"
                            value="Número de teléfono"
                        />
                        <TextInput
                            id="phone_number"
                            v-model="form.phone_number"
                            type="number"
                            class="mt-1 block w-full"
                            required
                            autocomplete="off"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.phone_number"
                        />
                    </div>

                    <div>
                        <InputLabel for="gender" value="Género" />
                        <div class="flex pt-1">
                            <FwbRadio
                                v-model="form.gender"
                                name="radio"
                                label="Masculino"
                                value="0"
                            />
                            <FwbRadio
                                v-model="form.gender"
                                name="radio"
                                label="Femenino"
                                value="1"
                            />
                            <FwbRadio
                                v-model="form.gender"
                                name="radio"
                                label="Otro"
                                value="2"
                            />
                        </div>
                        <InputError
                            class="mt-2"
                            :message="form.errors.gender"
                        />
                    </div>

                    <div>
                        <InputLabel
                            class="mb-2"
                            for="birthdate"
                            value="Fecha de nacimiento"
                        />
                        <FwbInput v-model="form.birthdate" type="date" />
                        <InputError
                            class="mt-2"
                            :message="form.errors.birthdate"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <FwbButton type="button" color="alternative">
                        <Link
                            :href="route('customers.index')"
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
    </AdminLayout>
</template>
