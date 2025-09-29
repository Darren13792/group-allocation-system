<template>
    <Head>
        <title>Manage Topics</title>
    </Head>
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between pb-2 border-bottom gap-md-3">
        <h1 class="text-center text-md-start text-nowrap">Manage Topics</h1>
        <div class="text-nowrap">
            <Link :href="route('create_new_topic')" class="btn btn-primary btn-sm mb-2" style="cursor: pointer;">Create
                New Topic</Link>
            <button class="btn btn-danger btn-sm ms-2 mb-2" @click="deleteAllTopics()">Delete All Topics</button>
        </div>
    </div>
    <br>
    <div class="mx-2">
        <DataTable id="topics" :data="topics" :columns="columns" class="table table-striped table-bordered">
        </DataTable>
    </div>
    <!-- Delete Topic Modal -->
    <DeleteTopicModal :topic_name="topic_name" :topic_id="topic_id"/>
    <!-- Delete All Topic Modal -->
    <DeleteTopicsModal/>
</template>

<script setup>
import { ref, onMounted, onUnmounted} from 'vue';
import { Head, Link, useForm } from "@inertiajs/vue3";
import DeleteTopicModal from '../Components/DeleteTopicModal.vue';
import DeleteTopicsModal from '../Components/DeleteTopicsModal.vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
DataTable.use(DataTablesCore);

const props = defineProps({
    topics: Object,
});

onMounted(() => {
    const dataTableContainer = document.querySelector('#topics');
    if (dataTableContainer) {
        dataTableContainer.addEventListener('click', handleTopicClick);
    }
    const topicsTable = document.getElementById('topics');
    if (topicsTable) {
        const parentDiv = topicsTable.closest('.col-12');
        if (parentDiv) {
            parentDiv.classList.add('scrollX');
        }
    }
});

onUnmounted(() => {
    const dataTableContainer = document.querySelector('#topics');
    if (dataTableContainer) {
        dataTableContainer.removeEventListener('click', handleTopicClick);
    }
});

function handleTopicClick(event) {
    if (event.target.classList.contains('delete-topic')) {
        topic_name.value = event.target.dataset.topicName;
        topic_id.value = event.target.dataset.topicId;;
        $('#delete-modal').modal('show');
    }
}

const formatActions = (data, type, row) => {
    return `
        <a href="/edit-topic/${row.id}" class="btn btn-hover px-2 text-success" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
        <button class="btn btn-hover px-2 text-danger delete-topic" data-topic-id="${row.id}" data-topic-name="${row.topic_name}"><i class="fa-solid fa-trash" style="pointer-events: none"></i></button>
    `;
};

const columns = [
    { title: "Created At", data: "created_at", visible: false },
    { title: "Topic Name", data: "topic_name" },
    { title: "Topic Description", data: "description", className:"text-wrap"},
    { title: "Due Date", data: "due_date" },
    { sortable: false, render: formatActions, className:"center-actions" }
];

const topic_name = ref("");
const topic_id = ref(null);

$(document).ready(function() {
    $("#delete-modal").on('hidden.bs.modal', function() {
        topic_name.value = "";
        topic_id.value = null;
    });
});

const deleteAllTopics = () => {
    $('#delete-all-modal').modal('show');
};

</script>