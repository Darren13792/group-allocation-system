<template>
    <Head>
        <title>Manage Groups</title>
    </Head>
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between pb-2 border-bottom gap-md-3">
        <h1 class="text-center text-md-start text-nowrap">Manage Groups</h1>
        <div class="text-nowrap">
            <Link :href="route('create_new_group')" class="btn btn-primary btn-sm mb-2" style="cursor: pointer;">Create New Group</Link>
            <button class="btn btn-danger btn-sm ms-2 mb-2" @click="deleteAllGroups()">Delete All Groups</button>
        </div>
    </div>
    <br>
    <div class="mx-2">
        <DataTable id="groups" :data="groups" :columns="columns" :options="options" class="table table-striped table-bordered">
        </DataTable>
        <div class="d-flex flex-column flex-md-row align-items-center my-2">
            <div class="text-nowrap">
                <button class="btn btn-success" @click="sendAllocation()">Send allocation emails</button>
                <button class="btn btn-danger ms-2" @click="runAllocation()">Run allocation</button>
            </div>
        </div>
        <div class="row justify-content-center mt-3">
            <div class="col-12 col-md-6 mb-3 px-2">
                <div class="h-100">
                    <div class="card">
                        <div class="card-header">
                            <h3>Student Summary:</h3>
                        </div>
                        <div class="card-body">
                            <h6 class="d-inline" v-for="i in preference_size"><b>Choice {{ i }}:</b> {{ studentCounts.counts[i] }}&nbsp&nbsp</h6>
                            <h6 class="mb-0"><b>Unfavoured choice:</b> {{ studentCounts.none }}</h6>
                            <div v-if="studentsNotInGroup" class="mb-3">
                                <h6 class="d-inline fw-bold">Unassigned students: </h6>
                                <h6 class="d-inline clickable" v-for="(student, index) in studentsNotInGroup" :key="student" @click="userInformation(student)">
                                    {{ student.first_name }} {{ student.last_name }}
                                    <span v-if="index < studentsNotInGroup.length - 1">, </span>
                                </h6>
                            </div>
                            <div v-if="Object.values(studentCounts.counts).some(count => count > 0) || studentCounts.none > 0">
                                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#studentChart" aria-expanded="false" aria-controls="studentChart">
                                    Show chart
                                </button>
                                <div class="collapse" id="studentChart">
                                    <CountChart :data="studentCountFormat(studentCounts)"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 mb-3 px-2">
                <div class="h-100">
                    <div class="card">
                        <div class="card-header">
                            <h3>Supervisor Summary:</h3>
                        </div>
                        <div class="card-body">
                            <h6 class="d-inline"><b>Favoured choice:</b> {{ supervisorCounts.counts }}&nbsp&nbsp</h6>
                            <h6 class="mb-0"><b>Unfavoured choice:</b> {{ supervisorCounts.none }}</h6>
                            <div v-if="studentsNotInGroup" class="mb-3">
                                <h6 class="d-inline fw-bold">Unassigned supervisors: </h6>
                                <h6 class="d-inline clickable" v-for="(supervisor, index) in supervisorsNotInGroup"
                                    :key="supervisor" @click="userInformation(supervisor)">
                                    {{ supervisor.first_name }} {{ supervisor.last_name }}
                                    <span v-if="index < supervisorsNotInGroup.length - 1">, </span>
                                </h6>
                            </div>
                            <div v-if="supervisorCounts.counts > 0 || supervisorCounts.none > 0">
                                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#supervisorChart" aria-expanded="false" aria-controls="supervisorChart">
                                    Show chart
                                </button>
                                <div class="collapse" id="supervisorChart">
                                    <CountChart :data="supervisorCountFormat(supervisorCounts)"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- User Modal -->
    <div class="modal fade" id="user-modal" tabindex="-1" aria-labelledby="user-modal-label" aria-hidden="true">
        <div class="modal-dialog px-3 px-sm-0 modal-m">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title fw-bold" id="user-modal-label">{{ user_name }}</h5>
                    <form @submit.prevent="removeForm.post(route('remove_group_user'))" id="remove-group-user">
                        <input type="hidden" v-model="removeForm.user_id">
                    </form>
                    <button class="btn btn-hover px-2 text-danger" type="submit" form="remove-group-user"
                        data-bs-dismiss="modal" aria-label="Close" :disabled="removeForm.processing">
                        <i class="fa-solid fa-2x fa-xmark"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="form.post(route('move_group'))" id="select-move">
                        <div class="text-left d-flex gap-3 align-items-start justify-content-between">
                            <div>
                                <p>Email: {{ user_email }}</p>
                                <p v-if="form.user_type === 'SUPERVISOR'">Group: {{ form.old_group_id }}</p>
                                <div v-if="form.user_type === 'STUDENT'" v-for="(preference, index) in student_preferences" :key="index">
                                    <p>Preference {{ index + 1 }}: {{ preference }}</p>
                                </div>
                                <div class="break-words" v-if="form.user_type === 'SUPERVISOR'">
                                    <p>Availability: {{ supervisor_availability }}</p>
                                </div>
                            </div>
                            <div class="me-4">
                                <label class="small text-muted" :for="'select-move-' + form.user_id">Move Group</label>
                                <select class="form-select mb-3" :id="'select-move-' + form.user_id" v-model="form.group_id">
                                    <option value="" disabled selected>Move Group</option>
                                    <option v-for="group in groups" :key="group.id" :value="group.id">
                                        Group {{ group.id }} ({{ group.topic ? group.topic.topic_name : 'No Topic Data' }})
                                    </option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer text-center px-2 py-1">
                    <button type="button" class="flex-even btn btn-outline-secondary border-0" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="submit" form="select-move" class="flex-even btn btn-outline-primary fw-bold border-0" data-bs-dismiss="modal" aria-label="Close" :disabled="form.processing">Move Group</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Topic Modal -->
    <TopicInfoModal :topic_name="topic_name" :topic_description="topic_description"/>
    <!-- Allocation Modal -->
    <RunAllocationModal :settings="settings"/>
    <!-- Send Email Modal -->
    <SendEmailModal/>
    <!-- Delete Group Modal -->
    <DeleteGroupModal :group_id="group_id"/>
    <!-- Delete All Groups Modal -->
    <DeleteGroupsModal/>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm } from "@inertiajs/vue3";
import RunAllocationModal from '../Components/RunAllocationModal.vue';
import SendEmailModal from '../Components/SendEmailModal.vue';
import DeleteGroupModal from '../Components/DeleteGroupModal.vue';
import DeleteGroupsModal from '../Components/DeleteGroupsModal.vue';
import TopicInfoModal from '../Components/TopicInfoModal.vue';
import CountChart from "../Components/CountChart.vue"
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
import jszip from 'jszip';
import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
pdfMake.vfs = pdfFonts.pdfMake.vfs;
import 'datatables.net-buttons-bs5';
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
DataTable.use(DataTablesCore);
DataTablesCore.Buttons.jszip(jszip);
DataTablesCore.Buttons.pdfMake(pdfMake);

const props = defineProps({
    settings: Object,
    studentCounts: Object,
    supervisorCounts: Object,
    groups: Object,
    studentsNotInGroup: Object,
    supervisorsNotInGroup: Object,
    preference_size: Number,
});

const studentCountFormat = (studentCounts) => {
    const labels = Object.keys(studentCounts.counts).map(key => `Choice ${key}`);
    labels.push('Unfavoured choice');
    const data = Object.values(studentCounts.counts);
    data.push(studentCounts.none);
    const chartData = {
        labels: labels,
        datasets: [
            {
                label: "Student Counts",
                data: data,
            },
        ],
    };
    return chartData;
};

const supervisorCountFormat = (supervisorCounts) => {
    const labels = ['Favoured Choice', 'Unfavoured Choice'];
    const data = Object.values(supervisorCounts);
    const chartData = {
        labels: labels,
        datasets: [
            {
                label: "Supervisor Counts",
                data: data,
            },
        ],
    };
    return chartData;
};

onMounted(() => {
    const dataTableContainer = document.querySelector('#groups');
    if (dataTableContainer) {
        dataTableContainer.addEventListener('click', handleStudentClick);
    }
    const groupsTable = document.getElementById('groups');
    if (groupsTable) {
        const parentDiv = groupsTable.closest('.col-12');
        if (parentDiv) {
            parentDiv.classList.add('scrollX');
        }
    }
});

onUnmounted(() => {
    const dataTableContainer = document.querySelector('#groups');
    if (dataTableContainer) {
        dataTableContainer.removeEventListener('click', handleStudentClick);
    }
});

function handleStudentClick(event) {
    if (event.target.classList.contains('user-info')) {
        user_email.value = event.target.dataset.email
        user_name.value = event.target.dataset.firstName + " " + event.target.dataset.lastName
        form.user_name = user_name
        form.user_id = parseInt(event.target.dataset.id)
        form.user_type = event.target.dataset.userType
        form.group_id = parseInt(event.target.dataset.groupId)
        form.old_group_id = parseInt(event.target.dataset.groupId)
        removeForm.group_id = parseInt(event.target.dataset.groupId)
        removeForm.user_id = parseInt(event.target.dataset.id)
        removeForm.user_name = user_name

        if (form.user_type === 'STUDENT') {
            for (let i = 1; i <= props.preference_size; i++) {
                student_preferences.value[i - 1] = props.groups.flatMap(group => group.group_students).find(group_student => group_student.student_id === form.user_id).student.student_preferences.find(preference => preference.weight === i)?.topic.topic_name || null;
            }
        }
        if (form.user_type === 'SUPERVISOR') {
            const supervisor_preferences = props.groups.flatMap(group => group.group_supervisors).find(group_supervisor => group_supervisor.supervisor_id === form.user_id).supervisor.supervisor_preferences.map(preference => preference.topic.topic_name);
            supervisor_availability.value = supervisor_preferences.join(', ');
        }
        $('#user-modal').modal('show');
    }
    if (event.target.classList.contains('topic-info')) {
        topic_name.value = event.target.dataset.topicName
        topic_description.value = event.target.dataset.topicDescription
        $('#topic-modal').modal('show');
    }
    if (event.target.classList.contains('delete-group')) {
        group_id.value = event.target.dataset.groupId;
        $('#delete-modal').modal('show');
    }
}

const formatStudents = (data) => {
    return data.map((student) => {
        const email = student.student.email.split('@')[0];
        return `<span class="clickable user-info" 
        data-id="${student.student.id}"
        data-first-name="${student.student.first_name}"
        data-last-name="${student.student.last_name}"
        data-email="${student.student.email}"
        data-user-type="${student.student.user_type}"
        data-group-id="${student.group_id}"
        >${email}</span>`;
    }).join(', ');
};

const formatSupervisors = (data) => {
    return data.map((supervisor) => {
        const email = supervisor.supervisor.email.split('@')[0];
        return `<span class="clickable user-info" 
        data-id="${supervisor.supervisor.id}"
        data-first-name="${supervisor.supervisor.first_name}"
        data-last-name="${supervisor.supervisor.last_name}"
        data-email="${supervisor.supervisor.email}"
        data-user-type="${supervisor.supervisor.user_type}"
        data-group-id="${supervisor.group_id}"
        >${email}</span>`;
    }).join(', ');
};

const formatActions = (data, type, row) => {
    return `
        <a href="/edit-group/${row.id}" class="btn btn-hover px-2 text-success" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
        <button class="btn btn-hover px-2 text-danger delete-group" data-group-id="${row.id}"><i class="fa-solid fa-trash" style="pointer-events: none"></i></button>
    `;
};

const columns = [
    { title: "Group ID", data: "id" },
    { title: "Topic Name", data: "topic", render:data => {
        if (data) {
            return `<span class="clickable topic-info" data-topic-name="${data.topic_name}" data-topic-description="${data.description}">${data.topic_name}</span>`
        } else {
            return `<span class="text-muted">No Topic</span>`;
        }
    }},
    { title: "Students", data: "group_students", render: formatStudents },
    { title: "Supervisors", data: "group_supervisors", render: formatSupervisors },
    { title: "Size", data: "group_students.length" },
    { title: "Actions", sortable: false, render: formatActions, className:"center-actions" }
];

const options = {
    layout: {
        top2Start: 'buttons',
        top2End: null,
        topStart: 'pageLength',
        topEnd: 'search',
        bottomStart: 'info',
        bottomEnd: 'paging',
        bottom2Start: null,
        bottom2End: null
    },
    buttons: {
        dom: {
            button: {
                tag: 'button',
                className: 'btn btn-outline-primary btn-sm rounded mx-1 mb-2'
            }
        },
        buttons: [{
            extend: 'copy',
            exportOptions: {
                columns: 'th:not(:last-child)'
            }
        }, {
            extend: 'csv',
            title: 'CO7210 Group Allocation',
            exportOptions: {
                columns: 'th:not(:last-child)'
            }
        }, {
            extend: 'excel',
            title: 'CO7210 Group Allocation',
            exportOptions: {
                columns: 'th:not(:last-child)'
            }
        }, {
            extend: 'pdf',
            title: 'CO7210 Group Allocation',
            exportOptions: {
                columns: 'th:not(:last-child)'
            }
        }, {
            extend: 'print',
            title: 'CO7210 Group Allocation',
            exportOptions: {
                columns: 'th:not(:last-child)'
            }
        }, {
            extend: 'colvis',
            title: 'CO7210 Group Allocation',
        }]
    }

}

const user_name = ref("");
const user_email = ref("");
const student_preferences = ref(Array(props.preference_size).fill(null));
const supervisor_availability = ref("");

const userInformation = (user) => {
    user_name.value = user.first_name + " " + user.last_name
    user_email.value = user.email
    form.user_name = user_name
    form.user_id = user.id
    form.user_type = user.user_type
    removeForm.user_id = user.id
    removeForm.user_name = user_name

    if (form.user_type === 'STUDENT') {
        form.group_id = props.groups.flatMap(group => group.group_students).find(group_student => group_student.student_id === user.id)?.group_id || null;
        form.old_group_id = form.group_id
        removeForm.group_id = form.group_id
        for (let i = 1; i <= props.preference_size; i++) {
            student_preferences.value[i - 1] = user.student_preferences.find(preference => preference.weight === i)?.topic.topic_name || null;
        }
    }
    else if (form.user_type === 'SUPERVISOR') {
        form.group_id = props.groups.flatMap(group => group.group_supervisors).find(group_supervisor => group_supervisor.supervisor_id === user.id)?.group_id || 'None';
        form.old_group_id = form.group_id
        removeForm.group_id = form.group_id
        const supervisor_preferences = user.supervisor_preferences.map(preference => preference.topic.topic_name);
        supervisor_availability.value = supervisor_preferences.join(', ');
    }
    $('#user-modal').modal('show');
};

$(document).ready(function () {
    $("#user-modal").on('hidden.bs.modal', function () {
        user_name.value = "";
        user_email.value = "";
        student_preferences.value.fill(null);
        form.user_name = null;
        form.user_id = null;
        form.group_id = null;
        form.user_type = null;
        form.old_group_id = null;
        removeForm.group_id = null;
        removeForm.user_id = null;
        removeForm.user_name = null;
        supervisor_availability.value = "";
    });
    $("#delete-modal").on('hidden.bs.modal', function () {
        group_id.value = null;
    });
    $("#topic-modal").on('hidden.bs.modal', function () {
        topic_name.value = "";
        topic_description.value = "";
    });
});

const topic_name = ref("");
const topic_description = ref("");
const group_id = ref(null);

const form = useForm({
    user_name: null,
    user_id: null,
    group_id: null,
    old_group_id: null,
    user_type: null,
});

const removeForm = useForm({
    user_name: null,
    user_id: null,
    group_id: null,
})

const deleteAllGroups = () => {
    $('#delete-all-modal').modal('show');
};

const runAllocation = () => {
    $('#allocation-modal').modal('show');
};

const sendAllocation = () => {
    $('#send-allocation-modal').modal('show');
};
</script>

<style>
@import 'datatables.net-buttons-bs5';
</style>