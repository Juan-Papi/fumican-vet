<script setup>
import { ref } from "vue";
import { Head } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import Sidebar from "@/Components/Sidebars/Sidebar.vue";
import MobileSidebar from "@/Components/Sidebars/MobileSidebar.vue";
import Header from "@/Components/Headers/Header.vue";
import Footer from "@/Components/Footers/AdminFooter.vue";

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const openSubMenu = ref(null);
const isNotificationsMenuOpen = ref(false);
const isProfileMenuOpen = ref(false);

const toggleSideMenu = () => {
    showingNavigationDropdown.value = !showingNavigationDropdown.value;
};

const toggleSubMenu = (menuName) => {
    openSubMenu.value = openSubMenu.value === menuName ? null : menuName;
};

const toggleNotificationsMenu = () => {
    isNotificationsMenuOpen.value = !isNotificationsMenuOpen.value;
};

const toggleProfileMenu = () => {
    isProfileMenuOpen.value = !isProfileMenuOpen.value;
};

const closeSideMenu = () => {
    showingNavigationDropdown.value = false;
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div
            class="flex h-screen bg-gray-50 dark:bg-gray-900"
            :class="{ 'overflow-hidden': showingNavigationDropdown }"
        >
            <!-- Sidebar para Desktop -->
            <Sidebar
                :openSubMenu="openSubMenu"
                :toggleSubMenu="toggleSubMenu"
            />

            <!-- Sidebar para Mobile -->
            <MobileSidebar
                :showing-navigation-dropdown="showingNavigationDropdown"
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
                <Footer />
            </div>
        </div>
    </div>
</template>

<style>
.th {
    background-color: #e5e7eb;
}

::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

/* Track */
::-webkit-scrollbar-track {
    background: #dadada;
    border-radius: 5px;
}

/* Handle */
::-webkit-scrollbar-thumb {
    background: #a0a0a0;
    border-radius: 5px;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
    background: #909090;
}
</style>
