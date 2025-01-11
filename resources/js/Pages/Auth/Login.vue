<script setup>
import InputError from "@/Components/InputError.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { FwbButton, FwbCheckbox, FwbInput } from "flowbite-vue";
import { ref } from "vue";

const showPassword = ref(false);
const isLoading = ref(false);

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    isLoading.value = true;
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => {
            isLoading.value = false;
            form.reset("password");
        },
    });
};
</script>

<template>
    <Head title="Iniciar sesión" />

    <div
        class="min-h-screen bg-gradient-to-r from-blue-100 to-green-100 flex items-center justify-center p-6"
    >
        <div
            class="bg-white w-full max-w-md rounded-xl shadow-lg p-8 space-y-6"
        >
            <div class="text-center">
                <img
                    src="https://cdn-icons-png.flaticon.com/128/1326/1326415.png"
                    alt="Veterinaria Amigos Peludos Logo"
                    class="w-24 h-24 mx-auto"
                />
                <h1 class="text-3xl font-bold text-gray-900 mt-4">
                    Veterinaria FUMICAN
                </h1>
                <p class="text-gray-500 mt-2">
                    Inicia sesión para acceder al panel de control
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700 mb-1"
                        >Correo electrónico</label
                    >
                    <FwbInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        placeholder="tu@ejemplo.com"
                        autocomplete="username"
                    >
                        <template #suffix>
                            <span class="text-gray-400">✉️</span>
                        </template>
                    </FwbInput>
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700 mb-1"
                        >Contraseña</label
                    >
                    <FwbInput
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        placeholder="••••••••"
                    >
                        <template #suffix>
                            <button
                                type="button"
                                @click="togglePassword"
                                class="text-gray-400 focus:outline-none"
                            >
                                <span v-if="!showPassword">👁️</span>
                                <span v-else>🙈</span>
                            </button>
                        </template>
                    </FwbInput>
                </div>

                <div class="flex items-center justify-between">
                    <FwbCheckbox v-model="form.remember" label="Recordarme" />

                    <div class="text-sm">
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="font-medium text-blue-600 hover:text-blue-500"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>
                </div>

                <FwbButton type="submit" :disabled="isLoading" :loading="isLoading" class="w-full">
                    Iniciar sesión
                </FwbButton>
            </form>
        </div>
    </div>
</template>
