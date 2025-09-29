<template>
    <Pie/>
</template>

<script setup>
import {
    Chart as ChartJS, Tooltip, Legend, ArcElement, Colors
} from "chart.js";
import { Pie } from "vue-chartjs";

ChartJS.register(
    Tooltip, Legend, ArcElement, Colors
);
</script>