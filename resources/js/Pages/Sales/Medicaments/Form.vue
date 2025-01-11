<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CustomSelect from "@/Components/Forms/CustomSelect.vue"; // Importa el componente reutilizable
import { Link, useForm } from "@inertiajs/vue3";
import { FwbButton, FwbRadio } from "flowbite-vue";
import { computed } from "vue";

// Props recibidas desde el controlador
const props = defineProps({
    formAction: String, // Acción del formulario (crear o editar)
    categories: Array,  // Listado de categorías enviado desde el controlador
});

// Verificar las categorías recibidas
console.log("Categorías recibidas:", props.categories);

// Computed para el título dinámico
const actionTitle = computed(() => {
    return props.formAction.includes("edit") ? "Editar" : "Agregar";
});

// Formulario de medicamento
const form = useForm({
    name: "",
    dosage: "",
    manufacturer: "",
    expiration_date: "",
    controlled_substance: "",
    category_id: "", // ID de la categoría seleccionada
});

// Método para enviar el formulario
const submit = () => {
    const routeAction = props.formAction.includes("edit")
        ? route("medicament.update", form.id)
        : route("medicament.store");

    form.post(routeAction, {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AdminLayout :title="actionTitle + ' Medicamento'">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                {{ actionTitle }} medicamento
            </h2>
        </div>
        <div>
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nombre del medicamento -->
                    <div>
                        <InputLabel for="name" value="Nombre del medicamento" />
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

                    <!-- Dosificación -->
                    <div>
                        <InputLabel for="dosage" value="Dosificación" />
                        <TextInput
                            id="dosage"
                            v-model="form.dosage"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autocomplete="off"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.dosage"
                        />
                    </div>

                    <!-- Fabricante -->
                    <div>
                        <InputLabel for="manufacturer" value="Fabricante" />
                        <TextInput
                            id="manufacturer"
                            v-model="form.manufacturer"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autocomplete="off"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.manufacturer"
                        />
                    </div>

                    <!-- Fecha de expiración -->
                    <div>
                        <InputLabel
                            for="expiration_date"
                            value="Fecha de expiración"
                        />
                        <TextInput
                            id="expiration_date"
                            v-model="form.expiration_date"
                            type="date"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.expiration_date"
                        />
                    </div>

                    <!-- Sustancia controlada -->
                    <div>
                        <InputLabel
                            for="controlled_substance"
                            value="Sustancia controlada"
                        />
                        <div class="flex pt-1 space-x-4">
                            <FwbRadio
                                v-model="form.controlled_substance"
                                name="controlled_substance"
                                label="Sí"
                                value="yes"
                            />
                            <FwbRadio
                                v-model="form.controlled_substance"
                                name="controlled_substance"
                                label="No"
                                value="no"
                            />
                        </div>
                        <InputError
                            class="mt-2"
                            :message="form.errors.controlled_substance"
                        />
                    </div>

                    <!-- Selección de Categoría -->
                    <div>
                        <CustomSelect
                            v-model="form.category_id"
                            :options="categories"
                            label="Categoría"
                            placeholder="Seleccionar una categoría"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.category_id"
                        />
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex items-center justify-end mt-4">
                    <FwbButton type="button" color="alternative">
                        <Link
                            :href="route('medicament.index')"
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
