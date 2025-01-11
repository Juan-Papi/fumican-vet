<script setup>
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({ status: Number });

const title = computed(() => {
    return {
        503: "503: Servicio No Disponible",
        500: "500: Error del Servidor",
        404: "404: Página No Encontrada",
        403: "403: Sin autorización",
    }[props.status];
});

const description = computed(() => {
    return {
        503: "Lo sentimos, estamos haciendo algo de mantenimiento. Por favor, vuelve pronto.",
        500: "Vaya, algo salió mal en nuestros servidores.",
        404: "Lo sentimos, la página que estás buscando no pudo ser encontrada.",
        403: "Lo sentimos, no tienes permiso para acceder a esta página.",
    }[props.status];
});
</script>

<template>
    <Head title="Error" />
    <main class="h-full pb-16 overflow-y-auto">
        <div class="container flex flex-col items-center px-6 mx-auto">
            <svg
                class="w-12 h-12 mt-8 text-purple-200"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path
                    fill-rule="evenodd"
                    d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"
                    clip-rule="evenodd"
                ></path>
            </svg>
            <h1 class="text-6xl font-semibold text-gray-700 dark:text-gray-200">
                {{ title }}
            </h1>
            <p class="text-gray-700 dark:text-gray-300">
                {{ description }} ¿Quieres regresar? Puedes
                <a
                    class="text-purple-600 hover:underline dark:text-purple-300"
                    href="/"
                >
                    ir a la página de inicio
                </a>
                .
            </p>
        </div>
    </main>
</template>

<style>
.text-6xl {
    font-size: 3.75rem;
}
</style>
