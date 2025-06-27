<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed } from "vue";
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    stats: Object,
});

// Gráfico 1: Consultas por Mes
const consultationsChart = computed(() => ({
    series: [
        {
            name: "Consultas",
            data: props.stats.consultations.data,
        },
    ],
    options: {
        chart: {
            type: "area",
            height: 350,
            zoom: { enabled: false },
            toolbar: { show: false },
        },
        dataLabels: { enabled: false },
        stroke: { curve: "smooth" },
        xaxis: { categories: props.stats.consultations.labels },
        yaxis: { title: { text: "Nº de Consultas" } },
        title: {
            text: "Rendimiento de Consultas (Últimos 12 Meses)",
            align: "left",
        },
        grid: { row: { colors: ["#f3f3f3", "transparent"], opacity: 0.5 } },
        tooltip: { y: { formatter: (val) => `${val} consultas` } },
    },
}));

// Gráfico 2: Mascotas por Especie
const speciesChart = computed(() => ({
    series: props.stats.species.data,
    options: {
        chart: { type: "donut", height: 350 },
        labels: props.stats.species.labels,
        title: { text: "Distribución de Pacientes por Especie", align: "left" },
        responsive: [
            {
                breakpoint: 480,
                options: {
                    chart: { width: 200 },
                    legend: { position: "bottom" },
                },
            },
        ],
        legend: { position: "right", offsetY: 0, height: 230 },
    },
}));

// Gráfico 3: Nuevos Clientes
const newCustomersChart = computed(() => ({
    series: [
        {
            name: "Nuevos Clientes",
            data: props.stats.newCustomers.data,
        },
    ],
    options: {
        chart: { type: "bar", height: 350, toolbar: { show: false } },
        plotOptions: {
            bar: { borderRadius: 4, horizontal: false, columnWidth: "50%" },
        },
        dataLabels: { enabled: false },
        xaxis: { categories: props.stats.newCustomers.labels },
        yaxis: { title: { text: "Nº de Clientes" } },
        title: {
            text: "Adquisición de Clientes (Últimos 6 Meses)",
            align: "left",
        },
        tooltip: { y: { formatter: (val) => `${val} clientes` } },
    },
}));

// Gráfico 4: Ventas vs Compras
const salesVsPurchasesChart = computed(() => ({
    series: [
        { name: "Ventas Totales", data: props.stats.salesVsPurchases.sales },
        {
            name: "Compras Totales",
            data: props.stats.salesVsPurchases.purchases,
        },
    ],
    options: {
        chart: { type: "line", height: 350, toolbar: { show: false } },
        stroke: { width: [4, 4], curve: "smooth" },
        title: { text: "Análisis Financiero - Ventas vs Compras (Últimos 6 Meses)", align: "left" },
        xaxis: { categories: props.stats.salesVsPurchases.labels },
        yaxis: [
            {
                seriesName: "Ventas Totales",
                title: { text: "Monto en Bs." },
                axisTicks: { show: true },
                axisBorder: { show: true },
            },
            { seriesName: "Compras Totales", show: false },
        ],
        tooltip: { y: { formatter: (val) => `Bs. ${val.toFixed(2)}` } },
    },
}));

// Gráfico 5: Medicamentos Más Vendidos
const topMedicamentsChart = computed(() => ({
    series: [
        {
            name: "Cantidad Vendida",
            data: props.stats.topMedicaments.data,
        },
    ],
    options: {
        chart: { type: "bar", height: 350, toolbar: { show: false } },
        plotOptions: { bar: { borderRadius: 4, horizontal: true } },
        dataLabels: { enabled: false },
        xaxis: { categories: props.stats.topMedicaments.labels },
        title: { text: "Top 5 Medicamentos Más Vendidos", align: "left" },
        tooltip: {
            x: { formatter: (val) => val },
            y: { formatter: (val) => `${val} unidades` },
        },
    },
}));

// Gráfico 6: Valor de Inventario por Almacén
const inventoryValueChart = computed(() => ({
    series: props.stats.inventoryValue.data,
    options: {
        chart: { type: "pie", height: 350 },
        labels: props.stats.inventoryValue.labels,
        title: { text: "Valor del Inventario por Almacén", align: "left" },
        responsive: [
            {
                breakpoint: 480,
                options: {
                    chart: { width: 200 },
                    legend: { position: "bottom" },
                },
            },
        ],
        legend: { position: "bottom" },
        tooltip: { y: { formatter: (val) => `Bs. ${val.toFixed(2)}` } },
    },
}));
</script>

<template>
    <AdminLayout title="Dashboard">
        <div class="flex justify-between my-6 items-center">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Dashboard Analítico
            </h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <VueApexCharts
                    type="bar"
                    height="350"
                    :options="topMedicamentsChart.options"
                    :series="topMedicamentsChart.series"
                />
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <VueApexCharts
                    type="donut"
                    height="350"
                    :options="speciesChart.options"
                    :series="speciesChart.series"
                />
            </div>

            <div class="bg-white p-4 rounded-lg shadow lg:col-span-2">
                <VueApexCharts
                    type="line"
                    height="350"
                    :options="salesVsPurchasesChart.options"
                    :series="salesVsPurchasesChart.series"
                />
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <VueApexCharts
                    type="area"
                    height="350"
                    :options="consultationsChart.options"
                    :series="consultationsChart.series"
                />
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <VueApexCharts
                    type="bar"
                    height="350"
                    :options="newCustomersChart.options"
                    :series="newCustomersChart.series"
                />
            </div>

            <!-- CORREGIDO: Se re-añade el gráfico de valor de inventario -->
            <div class="bg-white p-4 rounded-lg shadow">
                <VueApexCharts
                    type="pie"
                    height="350"
                    :options="inventoryValueChart.options"
                    :series="inventoryValueChart.series"
                />
            </div>
        </div>
    </AdminLayout>
</template>
