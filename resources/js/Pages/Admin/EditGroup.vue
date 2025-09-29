<template>
    <Head>
        <title>Edit Group</title>
    </Head>
    <h1 class="border-bottom pb-3">Edit Group: {{ group.id }}</h1>
    <form @submit.prevent="form.post(route('process_edit_group'))">
        <div class="mx-2">
            <div class="border-bottom pb-3">
                <label for="group_topic">Group Topic</label>
                <select class="form-select" :class="{ 'is-invalid':form.errors.group_topic }" id="group_topic" v-model="form.group_topic">
                <option value="" disabled selected>-- Select a topic --</option>
                <option v-for="topic in topics" :key="topic.id" :value="topic.id">
                    {{ topic.topic_name }}
                </option>
                </select>
            </div>
            <div class="border-bottom py-3">
                <h2 class="mb-3">Students:</h2>
                <div v-for="student in group.group_students" :key="student.student_id">
                    <h5>{{ student.student.first_name }} {{ student.student.last_name }}</h5>
                    <label :for="'select-move-' + student.student_id">Move Group</label>
                    <div class="d-flex align-items-start">
                        <div class="me-2 col-11">
                            <select class="form-select mb-3" :class="{ 'is-invalid': form.errors[student.student_id] }" :id="'select-move-' + student.student_id" v-model="form[student.student_id]">
                                <option value="" disabled selected>Move Group</option>
                                <option v-for="group in groups" :key="group.id" :value="group.id">
                                    Group {{ group.id }} ({{ group.topic.topic_name }})
                                </option>
                            </select>
                        </div>
                        <div class="btn btn-hover px-2 text-danger" @click="confirmDelete(student.student)">
                            <i class="fa-solid fa-trash"></i>
                        </div>
                    </div>
                </div>
                <h2 class="mb-3">Supervisors:</h2>
                <div v-for="supervisor in group.group_supervisors" :key="supervisor.supervisor_id">
                    <h5>{{ supervisor.supervisor.first_name }} {{ supervisor.supervisor.last_name }}</h5>
                    <label :for="'select-move-' + supervisor.supervisor_id">Move Group</label>
                    <div class="d-flex align-items-start">
                        <div class="me-2 col-11">
                            <select class="form-select mb-3" :class="{ 'is-invalid': form.errors[supervisor.supervisor_id] }" :id="'select-move-' + supervisor.supervisor_id" v-model="form[supervisor.supervisor_id]">
                                <option value="" disabled selected>Move Group</option>
                                <option v-for="group in groups" :key="group.id" :value="group.id">
                                    Group {{ group.id }} ({{ group.topic.topic_name }})
                                </option>
                            </select>
                        </div>
                        <div class="btn btn-hover px-2 text-danger" @click="confirmDelete(supervisor.supervisor)">
                            <i class="fa-solid fa-trash"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pt-3 form-outline mb-4">
                <div class="d-flex align-items-center mb-2">
                    <label>Add Students</label>
                    <div class="btn border-secondary px-2 py-1 m-1" @click="addStudent">Add</div>
                </div>
                <div class="d-flex align-items-start pb-2" v-for="(student, index) in form.students" :key="index">
                    <div class="col-5 col-md-4 col-lg-3">
                        <select class="form-select" v-model="student.studentSelectionType">
                            <option value="all">-- Select all students --</option>
                            <option value="unassigned">-- Select unassigned students --</option>
                        </select>
                    </div>
                    <div class="ps-2 me-2 col-6 col-md-7 col-lg-8">
                        <select class="form-select" :class="{'is-invalid': form.errors['students.' + index + '.student_id']}" v-model="student.student_id">
                            <option value="" disabled selected>-- Select a student --</option>
                            <option v-if="student.studentSelectionType === 'all'" v-for="student in allStudents" :key="student.id" :value="student.id">
                                {{ student.first_name }} {{ student.last_name }} ({{ student.email.split('@')[0] }})
                            </option>
                            <option v-if="student.studentSelectionType === 'unassigned'" v-for="student in studentsNotInGroup" :key="student.id" :value="student.id">
                                {{ student.first_name }} {{ student.last_name }} ({{ student.email.split('@')[0] }})
                            </option>
                        </select>
                        <div class="invalid-feedback" v-if="form.errors['students.'+index+'.student_id']">{{ form.errors['students.'+index+'.student_id'] }}</div>
                    </div>
                    <div class="btn btn-hover px-2 text-danger" @click="removeStudent(index)">
                        <i class="fa-solid fa-trash"></i>
                    </div>
                </div>
                <small class="text-muted">Warning: Already assigned students will be removed from previous group</small>
            </div>
            <div class="pb-3 form-outline mb-4">
                <div class="d-flex align-items-center mb-2">
                    <label>Add Supervisors</label>
                    <div class="btn border-secondary px-2 py-1 m-1" @click="addSupervisor">Add</div>
                </div>
                <div class="d-flex align-items-start pb-2" v-for="(supervisor, index) in form.supervisors" :key="index">
                    <div class="col-5 col-md-4 col-lg-3">
                        <select class="form-select" v-model="supervisor.supervisorSelectionType">
                            <option value="all">-- Select all supervisors --</option>
                            <option value="unassigned">-- Select unassigned supervisors --</option>
                        </select>
                    </div>
                    <div class="ps-2 me-2 col-6 col-md-7 col-lg-8">
                        <select class="form-select" :class="{'is-invalid': form.errors['supervisors.' + index + '.supervisor_id']}" v-model="supervisor.supervisor_id">
                            <option value="" disabled selected>-- Select a supervisor --</option>
                            <option v-if="supervisor.supervisorSelectionType === 'all'" v-for="supervisor in allSupervisors" :key="supervisor.id" :value="supervisor.id">
                                {{ supervisor.first_name }} {{ supervisor.last_name }} ({{ supervisor.email.split('@')[0] }})
                            </option>
                            <option v-if="supervisor.supervisorSelectionType === 'unassigned'" v-for="supervisor in supervisorsNotInGroup" :key="supervisor.id" :value="supervisor.id">
                                {{ supervisor.first_name }} {{ supervisor.last_name }} ({{ supervisor.email.split('@')[0] }})
                            </option>
                        </select>
                        <div class="invalid-feedback" v-if="form.errors['supervisors.'+index+'.supervisor_id']">{{ form.errors['supervisors.'+index+'.supervisor_id'] }}</div>
                    </div>
                    <div class="btn btn-hover px-2 text-danger" @click="removeSupervisor(index)">
                        <i class="fa-solid fa-trash"></i>
                    </div>
                </div>
                <small class="text-muted">Already assigned supervisors will not be removed from previous group</small>
            </div>
            <!-- Submit button -->
            <div>
                <div class="btn btn-primary btn-block mb-4 disabled" v-if="!form.isDirty">Update group</div>
                <button v-if="form.isDirty" class="btn btn-primary btn-block mb-4" type="submit" :disabled="form.processing">Update group</button>
                <Link :href="route('manage_groups')" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
            </div>
        </div>
        <!-- Remove Group User Modal -->
        <RemoveGroupUserModal :user_name="user_name" :user_id="user_id" :group_id="group.id"/>
    </form>
</template>

<script setup>
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import RemoveGroupUserModal from '../Components/RemoveGroupUserModal.vue';

const props = defineProps({
    topics: Object,
    group: Object,
    groups: Object,
    allStudents: Object,
    studentsNotInGroup: Object,
    allSupervisors: Object,
    supervisorsNotInGroup: Object,
});

let formData = {
    group_id: props.group.id,
    group_topic: props.group.topic_id,
    students: [],
    supervisors: []
};

for (let student of props.group.group_students) {
    formData[student.student_id] = props.group.id;
}

for (let supervisor of props.group.group_supervisors) {
    formData[supervisor.supervisor_id] = props.group.id;
}

const form = useForm(formData);

const user_name = ref("");
const user_id = ref("");

const confirmDelete = (user) => {
    user_name.value = user.first_name + " " + user.last_name
    user_id.value = user.id
    $('#remove-group-user-modal').modal('show');
};

$(document).ready(function() {
    $("#remove-group-user-modal").on('hidden.bs.modal', function() {
        user_name.value = "";
        user_id.value = "";
    });
});

const addStudent = () => {
    form.students.push({
        student_id: null,
        studentSelectionType: "all"
    })
};

const addSupervisor = () => {
    form.supervisors.push({
        supervisor_id: null,
        supervisorSelectionType: "all"
    })
};

const removeStudent = (index) => {
    form.students.splice(index, 1);

    // Maintain errors after removing entry
    const newErrors = {};
    for (const key in form.errors) {
        if (key.startsWith('students.')) {
            const match = key.match(/^students\.(\d+)\.(.*)$/);
            if (match) {
                const errorIndex = parseInt(match[1]);
                if (errorIndex === index) {
                    continue;
                } else if (errorIndex > index) {
                    // Errors after are de-incremented
                    newErrors['students.'+(errorIndex - 1)+'.student_id'] = form.errors[key];
                } else {
                    // Errors before stay the same
                    newErrors[key] = form.errors[key];
                }
            }
        } else {
            // Keep other errors
            newErrors[key] = form.errors[key];
        }
    }
    form.errors = newErrors;
};

const removeSupervisor = (index) => {
    form.supervisors.splice(index, 1);

    // Maintain errors after removing entry
    const newErrors = {};
    for (const key in form.errors) {
        if (key.startsWith('supervisors.')) {
            const match = key.match(/^supervisors\.(\d+)\.(.*)$/);
            if (match) {
                const errorIndex = parseInt(match[1]);
                if (errorIndex === index) {
                    continue;
                } else if (errorIndex > index) {
                    // Errors after are de-incremented
                    newErrors['supervisors.'+(errorIndex - 1)+'.supervisor_id'] = form.errors[key];
                } else {
                    // Errors before stay the same
                    newErrors[key] = form.errors[key];
                }
            }
        } else {
            // Keep other errors
            newErrors[key] = form.errors[key];
        }
    }
    form.errors = newErrors;
};
</script>