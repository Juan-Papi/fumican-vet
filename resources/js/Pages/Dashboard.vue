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
        xaxis: {
            categories: props.stats.consultations.labels,
        },
        yaxis: {
            title: { text: "Nº de Consultas" },
        },
        title: {
            text: "Consultas Médicas en los Últimos 12 Meses",
            align: "left",
        },
        grid: {
            row: {
                colors: ["#f3f3f3", "transparent"],
                opacity: 0.5,
            },
        },
        tooltip: {
            y: {
                formatter: (val) => `${val} consultas`,
            },
        },
    },
}));

// Gráfico 2: Mascotas por Especie
const speciesChart = computed(() => ({
    series: props.stats.species.data,
    options: {
        chart: {
            type: "donut",
            height: 350,
        },
        labels: props.stats.species.labels,
        title: {
            text: "Distribución de Pacientes por Especie",
            align: "left",
        },
        responsive: [
            {
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200,
                    },
                    legend: {
                        position: "bottom",
                    },
                },
            },
        ],
        legend: {
            position: "right",
            offsetY: 0,
            height: 230,
        },
    },
}));

// Gráfico 3: Nuevos Clientes por Mes
const newCustomersChart = computed(() => ({
    series: [
        {
            name: "Nuevos Clientes",
            data: props.stats.newCustomers.data,
        },
    ],
    options: {
        chart: {
            type: "bar",
            height: 350,
            toolbar: { show: false },
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "55%",
                endingShape: "rounded",
            },
        },
        dataLabels: { enabled: false },
        stroke: {
            show: true,
            width: 2,
            colors: ["transparent"],
        },
        xaxis: {
            categories: props.stats.newCustomers.labels,
        },
        yaxis: {
            title: {
                text: "Nº de Clientes",
            },
        },
        fill: {
            opacity: 1,
        },
        title: {
            text: "Nuevos Clientes en los Últimos 6 Meses",
            align: "left",
        },
        tooltip: {
            y: {
                formatter: (val) => `${val} clientes`,
            },
        },
    },
}));
</script>

<template>
    <AdminLayout title="Dashboard">
        <div class="flex justify-between my-6 items-center">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Dashboard
            </h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Gráfico de Consultas -->
            <div class="bg-white p-4 rounded-lg shadow">
                <VueApexCharts
                    type="area"
                    height="350"
                    :options="consultationsChart.options"
                    :series="consultationsChart.series"
                ></VueApexCharts>
            </div>

            <!-- Gráfico de Especies -->
            <div class="bg-white p-4 rounded-lg shadow">
                <VueApexCharts
                    type="donut"
                    height="350"
                    :options="speciesChart.options"
                    :series="speciesChart.series"
                ></VueApexCharts>
            </div>

            <!-- Gráfico de Nuevos Clientes -->
            <div class="bg-white p-4 rounded-lg shadow lg:col-span-2">
                <VueApexCharts
                    type="bar"
                    height="350"
                    :options="newCustomersChart.options"
                    :series="newCustomersChart.series"
                ></VueApexCharts>
            </div>
        </div>
    </AdminLayout>
</template>
