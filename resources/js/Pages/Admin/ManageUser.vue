<template>
    <Head>
        <title>Manage Users</title>
    </Head>
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between border-bottom gap-md-3">
        <div class="nav nav-tabs border-0">
            <div class="nav-item">
                <a class="nav-link active" id="student-tab" data-bs-toggle="tab" data-bs-target="#student_table"
                    aria-selected="true" role="tab" style="cursor: pointer;">Students</a>
            </div>
            <div class="nav-item">
                <a class="nav-link" id="supervisor-tab" data-bs-toggle="tab" data-bs-target="#supervisor_table"
                    aria-selected="false" role="tab" style="cursor: pointer;">Supervisors</a>
            </div>
            <div class="nav-item">
                <a class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin_table" aria-selected="false"
                    role="tab" style="cursor: pointer;">Admins</a>
            </div>
        </div>
        <div class="text-nowrap pt-2 pt-md-0">
            <Link :href="route('create_new_user')" class="btn btn-primary btn-sm mb-2" style="cursor: pointer;">Create
            New User</Link>
            <button class="btn btn-danger btn-sm ms-2 mb-2" @click="deleteAllUsers()">Delete All Users</button>
        </div>
    </div>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="student_table" role="tabpanel" aria-labelledby="student-tab">
            <h1 class="border-bottom py-3 text-center text-md-start">Manage Users: Students</h1>
            <div class="mx-2 pt-2" v-if="activeTab === 'student'">
                <DataTable id="students" :data="students" :columns="studentColumns" class="users-data-table table table-striped table-bordered">
                </DataTable>
            </div>
        </div>
        <div class="tab-pane fade" id="supervisor_table" role="tabpanel" aria-labelledby="supervisor-tab">
            <h1 class="border-bottom py-3 text-center text-md-start">Manage Users: Supervisors</h1>
            <div class="mx-2 pt-2" v-if="activeTab === 'supervisor'">
                <DataTable id="supervisors" :data="supervisors" :columns="supervisorColumns" class="users-data-table table table-striped table-bordered">
                </DataTable>
            </div>
        </div>
        <div class="tab-pane fade" id="admin_table" role="tabpanel" aria-labelledby="admin-tab">
            <h1 class="border-bottom py-3 text-center text-md-start">Manage Users: Admins</h1>
            <div class="container pt-2" style="width: 90%;" v-if="activeTab === 'admin'">
                <DataTable id="admins" :data="admins" :columns="adminColumns" class="users-data-table table table-striped table-bordered">
                </DataTable>
            </div>
        </div>
    </div>
    <!-- Delete User Modal -->
    <DeleteUserModal :user_fullname="user_fullname" :user_id="user_id"/>
    <!-- Delete All Users Modal -->
    <DeleteUsersModal :user_fullname="user_fullname" :user_id="user_id"/>
    <!-- Topic Modal -->
    <TopicInfoModal :topic_name="topic_name" :topic_description="topic_description"/>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, useForm } from "@inertiajs/vue3";
import DeleteUserModal from '../Components/DeleteUserModal.vue';
import DeleteUsersModal from '../Components/DeleteUsersModal.vue';
import TopicInfoModal from '../Components/TopicInfoModal.vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
DataTable.use(DataTablesCore);

const props = defineProps({
    students: Object,
    supervisors: Object,
    admins: Object,
    preference_size: Number,
});

const activeTab = ref('student');

onMounted(() => {
    $('#student-tab').on('shown.bs.tab', () => {
        activeTab.value = 'student';
        addListeners('students');
    });
    $('#supervisor-tab').on('shown.bs.tab', () => {
        activeTab.value = 'supervisor';
        addListeners('supervisors');
    });
    $('#admin-tab').on('shown.bs.tab', () => {
        activeTab.value = 'admin';
        addListeners('admins');
    });
    addListeners('students');
});

onUnmounted(() => {
    $('.tab-content').off();
});

function addListeners(tableId) {
    nextTick(() => {
        const usersTable = document.getElementById(tableId);
        if (usersTable) {
            const parentDiv = usersTable.closest('.col-12');
            if (parentDiv) {
                parentDiv.classList.add('scrollX');
            }
        }
        $('.tab-content').off('click', '.topic-info').on('click', '.topic-info', function(event) {
            const target = event.currentTarget;
            topic_name.value = target.dataset.topicName
            topic_description.value = target.dataset.topicDescription
            $('#topic-modal').modal('show');
        });
        $('.tab-content').off('click', '.delete-user').on('click', '.delete-user', function(event) {
            const target = event.currentTarget;
            user_id.value = target.dataset.userId;
            user_fullname.value = target.dataset.firstName + " " + target.dataset.lastName;
            $('#delete-modal').modal('show');
        });
    });
}

const generatePrefColumns = () => {
    return Array.from({ length: props.preference_size }, (_, i) => ({
        title: `Preference ${i + 1}`,
        render: (data, type, row) => {
            const preference = row.student_preferences.find(preference => preference.weight === i + 1);
            return preference && preference.topic ?
            `<span class="clickable topic-info"
            data-topic-name="${preference.topic.topic_name}"
            data-topic-description="${preference.topic.description}"
            >${preference.topic.topic_name}</span>`
            : '-';
        }
    }));
};

const formatAvailability = (data, type, row) => {
    if (data && data.length > 0) {
        return data.map(preference =>
        `<span class="clickable topic-info"
        data-topic-name="${preference.topic.topic_name}"
        data-topic-description="${preference.topic.description}"
        >${preference.topic.topic_name}</span>`
        ).join(", ");
    } else {
        return "-";
    };
};

const formatActions = (data, type, row) => {
    return `
        <a href="/edit-user/${row.id}" class="btn btn-hover px-2 text-success" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
        <button class="btn btn-hover px-2 text-danger delete-user" data-user-id="${row.id}" data-first-name="${row.first_name}" data-last-name="${row.last_name}"><i class="fa-solid fa-trash" style="pointer-events: none"></i></button>
    `;
};

const studentColumns = [
    { title: "ID", data: "id" },
    { title: "First Name", data: "first_name" },
    { title: "Last Name", data: "last_name" },
    { title: "Email", data: "email" },
    { title: "Activation Status", data: "activation_status", className:"text-wrap" },
    ...generatePrefColumns(),
    { sortable: false, render: formatActions, className:"center-actions" }
];

const supervisorColumns = [
    { title: "ID", data: "id" },
    { title: "First Name", data: "first_name" },
    { title: "Last Name", data: "last_name" },
    { title: "Email", data: "email" },
    { title: "Activation Status", data: "activation_status" },
    { title: "Availability", data: "supervisor_preferences", render: formatAvailability },
    { sortable: false, render: formatActions, className:"center-actions" }
];

const adminColumns = [
    { title: "ID", data: "id" },
    { title: "First Name", data: "first_name" },
    { title: "Last Name", data: "last_name" },
    { title: "Email", data: "email" },
    { title: "Activation Status", data: "activation_status" },
    { sortable: false, render: formatActions, className:"center-actions" }
];

const user_id = ref(null);
const user_fullname = ref("");
const topic_name = ref("");
const topic_description = ref("");

$(document).ready(function() {
    $("#delete-modal").on('hidden.bs.modal', function() {
        user_id.value = null;
        user_fullname.value = "";
    });
    $("#topic-modal").on('hidden.bs.modal', function() {
        topic_name.value = "";
        topic_description = "";
    });
});

const deleteAllUsers = () => {
    $('#delete-all-modal').modal('show');
};

</script>
