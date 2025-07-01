<script setup>
import {
    FwbAvatar,
    FwbDropdown,
    FwbListGroup,
    FwbListGroupItem,
} from "flowbite-vue";
import { Link, router } from "@inertiajs/vue3";
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import { ref, watch } from "vue";
import axios from "axios";
import { useDebouncedRef } from "@/Utils/debouncedRef.js"; // <-- Importamos nuestro debouncer

defineProps({
    toggleSideMenu: Function,
    user: Object,
});

const logout = () => {
    router.post(route("logout"));
};

// --- Lógica de Búsqueda Global ---
const searchTerm = useDebouncedRef('', 300); // 300ms de espera antes de buscar
const searchResults = ref([]);
const isSearching = ref(false);
const showResultsDropdown = ref(false);

watch(searchTerm, async (newTerm) => {
    if (newTerm.length < 2) {
        searchResults.value = [];
        showResultsDropdown.value = false;
        return;
    }
    isSearching.value = true;
    try {
        const response = await axios.get(route('global.search'), {
            params: { term: newTerm }
        });
        searchResults.value = response.data;
        showResultsDropdown.value = searchResults.value.length > 0;
    } catch (error) {
        console.error('Error durante la búsqueda global:', error);
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
});

const goToResult = (url) => {
  router.get(url, {}, {
    preserveState: false, // Carga la página completa para aplicar los filtros
  });
  searchTerm.value = '';
  searchResults.value = [];
  showResultsDropdown.value = false;
};

// Cierra el dropdown si se hace clic fuera
const closeDropdown = () => {
    setTimeout(() => {
        showResultsDropdown.value = false;
    }, 200); // Pequeño delay para permitir el clic en el resultado
};

</script>

<template>
    <header class="z-30 py-4 shadow-md themed-header-bg themed-header-border-b">
        <div
            class="container flex items-center justify-between h-full px-6 mx-auto themed-header-text"
        >
            <button
                class="p-1 mr-5 -ml-1 rounded-md lg:hidden focus:outline-none focus:shadow-outline-purple"
                @click="toggleSideMenu"
                aria-label="Menu"
            >
                <svg
                    class="w-6 h-6"
                    aria-hidden="true"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                        clip-rule="evenodd"
                    ></path>
                </svg>
            </button>

            <div class="flex justify-center flex-1 lg:mr-32">
                <div
                    class="relative w-full max-w-xl mr-6 focus-within:text-purple-500"
                >
                    <div class="absolute inset-y-0 flex items-center pl-2">
                        <svg
                            class="w-4 h-4"
                            aria-hidden="true"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                    </div>
                    <input
                        v-model="searchTerm"
                        @focus="showResultsDropdown = true"
                        @blur="closeDropdown"
                        class="w-full pl-8 pr-2 text-sm placeholder-gray-600 border-0 rounded-md focus:placeholder-gray-500 focus:bg-white focus:border-purple-300 focus:outline-none focus:shadow-outline-purple form-input themed-search-input"
                        type="text"
                        placeholder="Búsqueda global..."
                        aria-label="Search"
                    />
                    <div v-if="showResultsDropdown && searchTerm.length > 1" class="absolute left-0 right-0 mt-2 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg overflow-hidden">
                        <ul>
                            <li v-if="isSearching" class="px-4 py-2 text-sm text-gray-500">Buscando...</li>
                            <li v-else-if="searchResults.length === 0" class="px-4 py-2 text-sm text-gray-500">No se encontraron resultados.</li>
                            <li
                                v-for="(result, index) in searchResults"
                                :key="index"
                                @click="goToResult(result.url)"
                                class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 p-3 border-b border-gray-200 dark:border-gray-700 last:border-b-0"
                            >
                                <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">{{ result.title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ result.description }}</p>
                                <span class="text-xs font-bold text-purple-600 dark:text-purple-400">{{ result.type }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <ul class="flex items-center flex-shrink-0 space-x-6">
                <li class="flex">
                    <ThemeSwitcher />
                </li>

                <li class="relative flex items-center space-x-4">
                    <span class="hidden md:block text-sm themed-text-base">{{ user.first_name }} {{ user.last_name }}</span>
                    <FwbDropdown text="Menu" align-to-end>
                        <template #trigger>
                            <FwbAvatar
                                :img="user.profile_photo_url"
                                rounded
                                class="cursor-pointer"
                            />
                        </template>
                        <fwb-list-group class="themed-dropdown">
                            <fwb-list-group-item class="themed-dropdown-item">
                                <Link :href="route('profile.show')" class="w-full"> Perfil </Link>
                            </fwb-list-group-item>
                            <fwb-list-group-item class="themed-dropdown-item"> Settings </fwb-list-group-item>
                            <fwb-list-group-item class="themed-dropdown-item">
                                <form class="w-full" @submit.prevent="logout">
                                    <button class="w-full text-left" type="submit">
                                        Cerrar sesión
                                    </button>
                                </form>
                            </fwb-list-group-item>
                        </fwb-list-group>
                    </FwbDropdown>
                </li>
            </ul>
        </div>
    </header>
</template>

<style scoped>
.themed-header-bg {
    background-color: var(--color-background-secondary);
}
.themed-header-border-b {
    border-bottom: 1px solid var(--color-border);
}
.themed-header-text {
    color: var(--color-primary);
}
.themed-text-base {
    color: var(--color-text-base);
}
.themed-search-input {
    background-color: var(--color-background);
    color: var(--color-text-base);
}
.themed-search-input::placeholder {
    color: var(--color-text-muted);
}
.themed-dropdown {
    background-color: var(--color-background-secondary);
    border-color: var(--color-border);
}
.themed-dropdown-item {
    color: var(--color-text-base);
}
.themed-dropdown-item:hover {
    background-color: var(--color-background);
}
</style>
