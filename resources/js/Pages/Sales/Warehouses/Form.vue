<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { FwbButton } from "flowbite-vue";
import { computed } from "vue";

// Props recibidas desde el controlador
const props = defineProps({ formAction: String });

// Computed para definir el título dinámico del formulario
const actionTitle = computed(() => {
    return props.formAction.includes("edit") ? "Editar" : "Agregar";
});

// Definición del formulario para almacén
const form = useForm({
    name: "",
    location: "",
    description: "",
});

// Método para manejar el envío del formulario
const submit = () => {
    const routeAction = props.formAction.includes("edit")
        ? route("warehouse.update", form.id) // Para edición
        : route("warehouse.store");         // Para creación

    form.post(routeAction, {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AdminLayout :title="actionTitle + ' Almacén'">
        <!-- Título -->
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                {{ actionTitle }} almacén
            </h2>
        </div>

        <!-- Formulario -->
        <div>
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-4">
                    <!-- Nombre -->
                    <div>
                        <InputLabel for="name" value="Nombre del almacén" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autofocus
                            autocomplete="off"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <!-- Ubicación -->
                    <div>
                        <InputLabel for="location" value="Ubicación" />
                        <TextInput
                            id="location"
                            v-model="form.location"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autocomplete="off"
                        />
                        <InputError class="mt-2" :message="form.errors.location" />
                    </div>

                    <!-- Descripción -->
                    <div>
                        <InputLabel for="description" value="Descripción" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            rows="3"
                        ></textarea>
                        <InputError
                            class="mt-2"
                            :message="form.errors.description"
                        />
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex items-center justify-end mt-4">
                    <FwbButton type="button" color="alternative">
                        <Link
                            :href="route('warehouse.index')"
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
                        {{ actionTitle }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
