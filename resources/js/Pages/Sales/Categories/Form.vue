<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { FwbButton } from "flowbite-vue";
import { computed } from "vue";

const props = defineProps({ formAction: String });

const actionTitle = computed(() => {
    return props.formAction.includes("edit") ? "Editar" : "Agregar";
});

const form = useForm({
    name: "",
});

const submit = () => {
    const routeAction = props.formAction.includes("edit")
        ? route("category.update")
        : route("category.store");

    form.post(routeAction, {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AdminLayout :title="actionTitle + ' Categoría'">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                {{ actionTitle }} categoría
            </h2>
        </div>
        <div>
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <InputLabel for="name" value="Nombre de la categoría" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autofocus
                            autocomplete="off"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.name"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <FwbButton type="button" color="alternative">
                        <Link
                            :href="route('category.index')"
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
