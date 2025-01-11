<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { FwbButton, FwbInput, FwbRadio } from "flowbite-vue";
import { computed } from "vue";

const props = defineProps({
    formAction: String,
    roles: Array,
    user: Object,
});

const actionTitle = computed(() => {
    return props.formAction.includes("edit") ? "Editar" : "Agregar";
});

const form = useForm({
    first_name: props.user?.first_name || "",
    last_name: props.user?.last_name || "",
    email: props.user?.email || "",
    role_id: props.user?.role?.id || "",
});

const submit = () => {
    if (props.formAction.includes("edit")) {
        form.put(route("users.update", props.user.id));
    } else {
        form.post(route("users.store"));
    }
};
</script>

<template>
    <AdminLayout :title="actionTitle + ' Cliente'">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                {{ actionTitle }} usuario
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
                        <InputLabel for="email" value="Correo electrónico" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                            autocomplete="off"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="mb-6">
                        <label
                            for="role_id"
                            class="block text-sm font-medium text-gray-600"
                        >
                            Role
                        </label>
                        <select
                            v-model="form.role_id"
                            class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">Seleccionar</option>
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                        <InputError
                            class="mt-2"
                            :message="form.errors.role_id"
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
