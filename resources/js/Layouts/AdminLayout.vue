<script setup>
import { ref, onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import Sidebar from "@/Components/Sidebars/Sidebar.vue";
import MobileSidebar from "@/Components/Sidebars/MobileSidebar.vue";
import Header from "@/Components/Headers/Header.vue";
import Footer from "@/Components/Footers/AdminFooter.vue";
import { useTheme } from '@/Composables/useTheme';

// Inicializa el sistema de temas
const { initTheme } = useTheme();
onMounted(() => {
    initTheme();
});

// --- Component Props ---
defineProps({
    title: String
});

// --- Layout State Management ---
const showingNavigationDropdown = ref(false);
const openSubMenu = ref(null);

const toggleSideMenu = () => {
    showingNavigationDropdown.value = !showingNavigationDropdown.value;
};

const toggleSubMenu = (menuName) => {
    openSubMenu.value = openSubMenu.value === menuName ? null : menuName;
};

const closeSideMenu = () => {
    showingNavigationDropdown.value = false;
};
</script>

<template>
    <div>
        <Head :title="title" />
        <Banner />
        <div class="flex h-screen page-background" :class="{ 'overflow-hidden': showingNavigationDropdown }">
            <!-- Sidebar para Desktop -->
            <Sidebar
                :openSubMenu="openSubMenu"
                :toggleSubMenu="toggleSubMenu"
            />

            <!-- Sidebar para Mobile -->
            <MobileSidebar
                :showing-navigation-dropdown="showingNavigationDropdown"
                @close-side-menu="closeSideMenu"
            />

            <div class="flex flex-col flex-1 w-full">
                <Header
                    :user="$page.props.auth.user"
                    :toggleSideMenu="toggleSideMenu"
                />
                <main class="h-full overflow-y-auto">
                    <div class="container px-6 mx-auto grid">
                        <slot />
                    </div>
                </main>
                <Footer :visit-count="$page.props.visitCount ?? 0" />
            </div>
        </div>
    </div>
</template>

<style>
/* La importación de themes.css fue movida a resources/js/app.js */

/* Aplica las variables CSS a los elementos base */
body {
    font-size: var(--font-size-base);
}

.page-background {
    background-color: var(--color-background);
    color: var(--color-text-base);
}

/* Estilos de la tabla y scrollbar */
.th {
    background-color: #e5e7eb;
}

::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #dadada;
    border-radius: 5px;
}

::-webkit-scrollbar-thumb {
    background: #a0a0a0;
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: #909090;
}
</style>
