<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useTheme } from '@/Composables/useTheme';

const { theme, setTheme } = useTheme();
const isMenuOpen = ref(false);
const dropdownRef = ref(null); // Ref para el contenedor del menú

const themes = [
    { name: 'light', label: 'Claro', icon: 'fa-sun' },
    { name: 'dark', label: 'Oscuro', icon: 'fa-moon' },
    { name: 'female', label: 'Femenino', icon: 'fa-venus' },
    { name: 'elderly', label: 'Accesible', icon: 'fa-low-vision' }
];

const selectTheme = (newTheme) => {
    setTheme(newTheme);
    isMenuOpen.value = false;
};

// Función para cerrar el menú si se hace clic fuera
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isMenuOpen.value = false;
    }
};

// Añadimos el listener cuando el componente se monta
onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

// Limpiamos el listener cuando el componente se destruye para evitar fugas de memoria
onBeforeUnmount(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <button
            @click="isMenuOpen = !isMenuOpen"
            class="relative align-middle rounded-md focus:outline-none focus:shadow-outline-purple"
            aria-label="Account"
            aria-haspopup="true"
        >
            <i class="fa-solid fa-palette text-xl text-gray-500"></i>
        </button>
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <ul
                v-if="isMenuOpen"
                class="absolute right-0 w-48 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:border-gray-700 dark:text-gray-300 dark:bg-gray-700"
            >
                <li v-for="t in themes" :key="t.name">
                    <a
                        class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        href="#"
                        @click.prevent="selectTheme(t.name)"
                    >
                        <i class="fa-solid w-5" :class="t.icon"></i>
                        <span>{{ t.label }}</span>
                    </a>
                </li>
            </ul>
        </transition>
    </div>
</template>
