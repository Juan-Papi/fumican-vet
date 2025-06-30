<template>
    <footer>
        <div class="container bg-white border-t px-6 mx-auto grid">
            <p class="text-center py-4 text-gray-500 dark:text-gray-400">
                © 2025 Derechos Reservados  Vetfumican - Desarrollado por el grupo 17SA 
            </p>
            <p class="text-center pb-4 text-gray-700 dark:text-gray-300">
                Visitas: {{ visitCount }}
            </p>
        </div>
    </footer>
</template>

<script setup>
// Recibe la prop visitCount desde el layout o la página
const props = defineProps({
    visitCount: {
        type: Number,
        required: true
    }
});
</script>
