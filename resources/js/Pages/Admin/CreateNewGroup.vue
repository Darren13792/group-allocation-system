<template>
    <Head>
        <title>Create New Group</title>
    </Head>
    <h1 class="border-bottom pb-3">Create New Group</h1>
    <div class="mx-2">
        <form @submit.prevent="form.post(route('process_new_group'))">
            <!-- Group topic input -->
            <div class="form-outline mb-3">
                <label for="group_topic">Group Topic</label>
                <select class="form-select" :class="{ 'is-invalid':form.errors.group_topic }" id="group_topic" v-model="form.group_topic">
                <option value="" disabled selected>-- Select a topic --</option>
                <option v-for="topic in topics" :key="topic.id" :value="topic.id">
                    {{ topic.topic_name }}
                </option>
                </select>
                <div class="invalid-feedback" v-if="form.errors.group_topic">{{ form.errors.group_topic }}</div>
            </div>
            <div class="form-outline mb-4">
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
                    <div class="mx-2 col-6 col-md-7 col-lg-8">
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
                    <div class="col-1">
                        <div class="btn btn-hover px-2 text-danger" @click="removeStudent(index)">
                            <i class="fa-solid fa-trash"></i>
                        </div>
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
                    <div class="mx-2 col-6 col-md-7 col-lg-8">
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
                    <div class="col-1">
                        <div class="btn btn-hover px-2 text-danger" @click="removeSupervisor(index)">
                            <i class="fa-solid fa-trash"></i>
                        </div>
                    </div>
                </div>
                <small class="text-muted">Already assigned supervisors will not be removed from previous group</small>
            </div>
            <!-- Submit button -->
            <div>
                <div class="btn btn-primary btn-block mb-4 disabled" v-if="!form.isDirty">Create group</div>
                <button v-if="form.isDirty" class="btn btn-primary btn-block mb-4" type="submit" :disabled="form.processing">Create group</button>
                <Link :href="route('manage_groups')" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
const props = defineProps({
    topics: Object,
    allStudents: Object,
    studentsNotInGroup: Object,
    allSupervisors: Object,
    supervisorsNotInGroup: Object,
});

const form = useForm({
    group_topic: null,
    students: [{
        "student_id": null, 
        "studentSelectionType": "all" }],
    supervisors: [{
        "supervisor_id": null, 
        "supervisorSelectionType": "all" }]
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
