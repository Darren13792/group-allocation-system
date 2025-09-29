<template>
    <Head>
        <title>Create New Topic</title>
    </Head>
    <!-- CSV Upload -->
    <form @submit.prevent="submitCSV">
        <h1 class="border-bottom pb-3">Import Topics via CSV</h1>
        <div class="mx-2">
            <h3>Ensure the following:</h3>
            <ul>
                <li>
                    CSV file must adhere to the specified header order:<br>
                    <span class="text-muted fst-italic">topic_name, description, due_date</span>
                </li>
                <li>Date should be formated as follows:
                    <span class="text-muted fst-italic"> YYYY-MM-DD HH:MM:SS</span>
                </li>
                <li>All entries are required to be separated by commas</li>
            </ul>
            <div class="mb-4">
                <label for="file">Import CSV</label>
                <input class="form-control" :class="{ 'is-invalid':csvform.errors.file }" type="file" v-on:change="handleFileUpload">
                <div class="invalid-feedback" v-if="csvform.errors.file" v-html="csvform.errors.file"></div>
            </div>
            <div class="mb-4">
                <div class="btn btn-primary btn-block disabled" v-if="!csvform.isDirty">Upload File</div>
                <input v-if="csvform.isDirty && !csvform.processing" type="submit" value="Upload File" class="btn btn-primary">
                <div v-if="csvform.processing" class="btn btn-primary btn-block disabled">
                    <span class="spinner-border spinner-border-sm"></span>
                    <span> Processing...</span>
                </div>
            </div>
        </div>
    </form>
    <h1 class="border-top border-bottom py-3">Create New Topic</h1>
    <div class="mx-2">
        <form @submit.prevent="form.post(route('process_new_topic'))">
            <!-- Topic name input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="topic_name">Topic Name</label>
                <input id="topic_name" class="form-control" :class="{ 'is-invalid':form.errors.topic_name }" type="text" v-model="form.topic_name">
                <div class="invalid-feedback" v-if="form.errors.topic_name">{{ form.errors.topic_name }}</div>
            </div>

            <!-- Description input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" class="form-control" :class="{ 'is-invalid':form.errors.description }" type="text" v-model="form.description"></textarea>
                <div class="invalid-feedback" v-if="form.errors.description">{{ form.errors.description }}</div>
            </div>
            <!-- Due date input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="due_date">Due date</label>
                <div class="input-group">
                    <input v-model="form.due_date" placeholder="Select date and time..." id="due_date" class="form-control flatpickr" :class="{ 'is-invalid':form.errors.due_date }" type="date">
                    <div class="btn btn-sm btn-hover text-danger m-0" @click="clearCalendar()">
                        <i class="fa-solid fa-2x fa-xmark"></i>
                    </div>
                    <div class="invalid-feedback" v-if="form.errors.due_date">{{ form.errors.due_date }}</div>
                </div>
            </div>
            <!-- Submit button -->
            <div>
                <div class="btn btn-primary btn-block mb-4 disabled" v-if="!form.isDirty">Create topic</div>
                <button v-if="form.isDirty" class="btn btn-primary btn-block mb-4" type="submit" :disabled="form.processing">Create topic</button>
                <Link :href="route('manage_topic')" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { Head, Link, useForm } from "@inertiajs/vue3";
import flatpickr from "flatpickr"

onMounted(() => {
    flatpickr(".flatpickr", {
        enableTime: true,
        time_24hr: true,
        minDate: "today",
    })
});

const form = useForm({
    topic_name: null,
    description: null,
    due_date: null,
});

const csvform = useForm({
    file: null,
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    csvform.file = file;
};

const submitCSV = () => {
    csvform.post(route('process_topic_csv'));
};

const clearCalendar = () => {
    form.due_date = null;
};

</script>
