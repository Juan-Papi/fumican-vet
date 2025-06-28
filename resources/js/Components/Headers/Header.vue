<script setup>
import {
    FwbAvatar,
    FwbDropdown,
    FwbListGroup,
    FwbListGroupItem,
} from "flowbite-vue";
import { Link, router } from "@inertiajs/vue3";
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';

defineProps({
    toggleSideMenu: Function,
    user: Object,
});

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <header class="z-10 py-4 shadow-md themed-header-bg themed-header-border-b">
        <div
            class="container flex items-center justify-between h-full px-6 mx-auto themed-header-text"
        >
            <!-- Mobile hamburger -->
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

            <!-- Search input -->
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
                        class="w-full pl-8 pr-2 text-sm placeholder-gray-600 border-0 rounded-md focus:placeholder-gray-500 focus:bg-white focus:border-purple-300 focus:outline-none focus:shadow-outline-purple form-input themed-search-input"
                        type="text"
                        placeholder="Search for projects"
                        aria-label="Search"
                    />
                </div>
            </div>

            <!-- Right side menu -->
            <ul class="flex items-center flex-shrink-0 space-x-6">
                <!-- Theme Switcher -->
                <li class="flex">
                    <ThemeSwitcher />
                </li>

                <!-- Profile menu -->
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
