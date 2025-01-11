<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PermissionEnum from "@/Utils/Enums/PermissionEnum";
import { Link, router, usePage } from "@inertiajs/vue3";
import {
    FwbA,
    FwbButton,
    FwbTable,
    FwbTableBody,
    FwbTableCell,
    FwbTableHead,
    FwbTableHeadCell,
    FwbTableRow,
    FwbPagination,
    FwbModal,
} from "flowbite-vue";
import { ref, watch } from "vue";

const props = defineProps({ customers: Object });
const currentPage = ref(props.customers.current_page || 1);

watch(currentPage, (newPage) => {
    router.get(
        route("customers.index"),
        { page: newPage },
        { preserveState: true }
    );
});

// PERMISSIONS
const page = usePage(); // Debe ser importado
const canCreateCustomers = page.props.auth.user_permissions.some(
    (permission) => permission.name === PermissionEnum.CREAR_CLIENTES
);
const canEditCustomers = page.props.auth.user_permissions.some(
    (permission) => permission.name === PermissionEnum.EDITAR_CLIENTES
);

// MODAL
const isShowModal = ref(false);
const selectedCustomer = ref(null);

const showModal = (customer) => {
    selectedCustomer.value = customer;
    isShowModal.value = true;
};
</script>

<template>
    <AdminLayout title="Clientes">
        <div class="flex justify-between my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Clientes
            </h2>
            <FwbButton
                v-if="canCreateCustomers"
                :href="route('customers.create')"
                type="button"
                color="purple"
            >
                Agregar cliente
            </FwbButton>
        </div>
        <div>
            <FwbTable>
                <FwbTableHead class="th">
                    <FwbTableHeadCell>Nombre</FwbTableHeadCell>
                    <FwbTableHeadCell>Apellido</FwbTableHeadCell>
                    <FwbTableHeadCell>Carnet de Identidad</FwbTableHeadCell>
                    <FwbTableHeadCell>Teléfono</FwbTableHeadCell>
                    <FwbTableHeadCell>Última modificación</FwbTableHeadCell>
                    <FwbTableHeadCell v-if="canEditCustomers">
                        <span class="sr-only">Edit</span>
                    </FwbTableHeadCell>
                </FwbTableHead>
                <FwbTableBody>
                    <FwbTableRow
                        v-for="customer in customers.data"
                        :key="customer.id"
                    >
                        <FwbTableCell>{{ customer.first_name }}</FwbTableCell>
                        <FwbTableCell>{{ customer.last_name }}</FwbTableCell>
                        <FwbTableCell>{{ customer.ci }}</FwbTableCell>
                        <FwbTableCell>{{ customer.phone_number }}</FwbTableCell>
                        <FwbTableCell>{{ customer.updated_at }}</FwbTableCell>
                        <FwbTableCell v-if="canEditCustomers">
                            <FwbButton @click="showModal(customer)" color="info">
                                Ver
                            </FwbButton>
                            <FwbA :href="route('customers.edit', customer.id)">
                                Edit
                            </FwbA>
                        </FwbTableCell>
                    </FwbTableRow>
                </FwbTableBody>
            </FwbTable>

            <div class="flex justify-center my-4">
                <FwbPagination
                    v-model="currentPage"
                    :total-items="customers.total"
                    :per-page="customers.per_page"
                    large
                ></FwbPagination>
            </div>
        </div>
    </AdminLayout>

    <FwbModal v-if="isShowModal && selectedCustomer" @close="isShowModal = false">
        <template #header>
            <div class="flex items-center text-lg">
                Cliente
            </div>
        </template>
        <template #body>
            <div class="flex flex-col px-2 gap-2">
                <div class="text-sm">
                    <span class="font-medium mr-2">Nombre: </span>
                    <span class="leading-relaxed text-gray-500 dark:text-gray-400">
                        {{ selectedCustomer.first_name }}
                    </span>
                </div>
                <div class="text-sm">
                    <span class="font-medium mr-2">Apellido: </span>
                    <span class="leading-relaxed text-gray-500 dark:text-gray-400">
                        {{ selectedCustomer.last_name }}
                    </span>
                </div>
                <div class="text-sm">
                    <span class="font-medium mr-2">Carnet de Identidad: </span>
                    <span class="leading-relaxed text-gray-500 dark:text-gray-400">
                        {{ selectedCustomer.ci }}
                    </span>
                </div>
                <div class="text-sm">
                    <span class="font-medium mr-2">Teléfono: </span>
                    <span class="leading-relaxed text-gray-500 dark:text-gray-400">
                        {{ selectedCustomer.phone_number }}
                    </span>
                </div>
                <div class="text-sm">
                    <span class="font-medium mr-2">Última modificación: </span>
                    <span class="leading-relaxed text-gray-500 dark:text-gray-400">
                        {{ selectedCustomer.updated_at }}
                    </span>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-between">
                <FwbButton @click="isShowModal = false" color="alternative">
                    Volver
                </FwbButton>
            </div>
        </template>
    </FwbModal>
</template>
