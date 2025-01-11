<script setup>
import { ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

defineProps({
    showingNavigationDropdown: Boolean,
});

const menus = ref([
    {
        name: "Dashboard",
        icon: "fa-solid fa-house",
        link: "/dashboard",
    },
    {
        name: "Usuarios",
        icon: "fa-solid fa-users",
        submenus: [
            { name: "Personal", link: "/users/users" },
            { name: "Roles", link: "/users/roles" },
            { name: "Permisos", link: "/users/permissions" },
        ],
    },
    {
        name: "Servicios",
        icon: "fa-solid fa-briefcase",
        submenus: [
            { name: "Clientes", link: "/services/customers" },
            {
                name: "Consultas médicas",
                link: "/services/medical-consultations",
            },
            { name: "Mascotas", link: "/services/pets" },
        ],
    },
    {
        name: "Ventas",
        icon: "fa-solid fa-money-bill",
        submenus: [
            { name: "Nota de Venta", link: "/sales/sales-note" },
            { name: "Nota de Compra", link: "/sales/purchases" },
            { name: "Almacen", link: "/sales/warehouses" },
            { name: "Medicamentos", link: "/sales/medicaments" },
            { name: "Categorias", link: "/sales/categories" },
            { name: "Proveedores", link: "/sales/suppliers" },
        ],
    },
]);

const openSubMenu = ref(null);

const toggleSubMenu = (menuName) => {
    openSubMenu.value = openSubMenu.value === menuName ? null : menuName;
};
</script>

<template>
    <transition
        enter-active-class="transition ease-in-out duration-150"
        enter-from-class="opacity-0 transform -translate-x-20"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in-out duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0 transform -translate-x-20"
    >
        <aside
            v-if="showingNavigationDropdown"
            class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-white dark:bg-gray-800 lg:hidden"
        >
            <div class="py-4 text-gray-500 dark:text-gray-400">
                <a
                    class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200"
                    href="#"
                >
                    Veterinaria Fumican
                </a>
                <ul class="mt-6">
                    <li
                        v-for="menu in menus"
                        :key="menu.name"
                        class="relative px-6 py-3"
                    >
                        <span
                            v-if="menu.name === 'Dashboard'"
                            class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                            aria-hidden="true"
                        ></span>
                        <Link
                            v-if="!menu.submenus"
                            :href="menu.link"
                            class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                            :class="{ 'text-gray-800': $page.url === menu.link }"
                        >
                            <i :class="menu.icon" />
                            <span class="ml-4">{{ menu.name }}</span>
                        </Link>
                        <div v-else>
                            <button
                                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                @click="toggleSubMenu(menu.name)"
                                aria-haspopup="true"
                            >
                                <span class="inline-flex items-center">
                                    <i :class="menu.icon" />
                                    <span class="ml-4">{{ menu.name }}</span>
                                </span>
                                <svg
                                    class="w-4 h-4"
                                    aria-hidden="true"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                            </button>
                            <transition
                                enter-active-class="transition-all ease-in-out duration-300"
                                enter-from-class="opacity-0 max-h-0"
                                enter-to-class="opacity-100 max-h-xl"
                                leave-from-class="opacity-100 max-h-xl"
                                leave-to-class="opacity-0 max-h-0"
                            >
                                <ul
                                    v-if="openSubMenu === menu.name"
                                    class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50 dark:text-gray-400 dark:bg-gray-900"
                                    aria-label="submenu"
                                >
                                    <li
                                        v-for="submenu in menu.submenus"
                                        :key="submenu.name"
                                        class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                    >
                                        <Link :href="submenu.link" class="w-full">
                                            {{ submenu.name }}
                                        </Link>
                                    </li>
                                </ul>
                            </transition>
                        </div>
                    </li>
                </ul>
            </div>
        </aside>
    </transition>
</template>
