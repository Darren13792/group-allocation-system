<template>
    <Head>
        <title>Topics</title>
    </Head>
    <h1 class="border-bottom text-center text-md-start pb-3">View Topics</h1>
    <div class="mt-4 container">
        <div class="mb-3">
            <button class="bg-transparent p-1 border-0" :class="{ 'active': current === 'card', 'text-primary': current === 'card', 'text-muted': current !== 'card'}" @click="current = 'card'">
                <i class="fa-solid fa-grip"></i>
            </button>
            <button class="bg-transparent p-1 border-0" :class="{ 'active': current === 'table', 'text-primary': current === 'table', 'text-muted': current !== 'table' }" @click="initialiseTable">
                <i class="fa-solid fa-table-list"></i>
            </button>
        </div>
        <div class="row" v-if="current === 'card'">
            <div v-for="topic in topics" class="col-12 col-md-8 col-lg-6 mb-3 ps-0">
                <div class="card d-flex flex-column">
                    <div class="card-header">
                        <h3>{{ topic.topic_name }}</h3>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p v-if="topic.expanded">{{ topic.description }}</p>
                        <p v-else>{{ topic.description.length > 160 ? topic.description.substring(0, 160) + '...' : topic.description }}</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <small class="text-muted">Due date: {{ topic.due_date }}</small>
                            <button v-if="topic.description.length > 160" @click="toggle(topic)" class="btn btn-link text-decoration-none">
                                {{ topic.expanded ? 'Read Less' : 'Read More' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="current === 'table'">
            <DataTable id="topics" :data="topics" :columns="columns" class="table table-striped table-bordered">
            </DataTable>
        </div>
    </div>
</template>



<script setup>
import { ref, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
DataTable.use(DataTablesCore);

const props = defineProps({
    topics: Object,
});

const columns = [
    { title: "Created At", data: "created_at", visible: false },
    { title: "Topic Name", data: "topic_name" },
    { title: "Topic Description", data: "description", className:"text-wrap"},
    { title: "Due Date", data: "due_date" },
];

const current = ref('card');

const initialiseTable = () => {
    current.value = 'table'
    nextTick(() => {
        const topicsTable = document.getElementById('topics');
        if (topicsTable) {
            const parentDiv = topicsTable.closest('.col-12');
            if (parentDiv) {
                parentDiv.classList.add('scrollX');
            }
        }
    });
};

const toggle = (topic) => {
    topic.expanded = !topic.expanded;
};

props.topics.forEach(topic => {
    topic.expanded = false;
});

</script>