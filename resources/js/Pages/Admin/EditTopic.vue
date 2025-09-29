<template>
    <Head>
        <title>Edit Topic</title>
    </Head>
    <h1 class="border-bottom pb-3">Edit Topic: {{ topic.topic_name }}</h1>
    <div class="mx-2">
        <form @submit.prevent="form.post(route('process_edit_topic'))">
            <input type="hidden" v-model="form.topic_id">
            <!-- Topic name input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="topic_name">Topic Name</label>
                <input class="form-control" :class="{ 'is-invalid':form.errors.topic_name }" type="text" v-model="form.topic_name">
                <div class="invalid-feedback" v-if="form.errors.topic_name">{{ form.errors.topic_name }}</div>
            </div>

            <!-- Description input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" :class="{ 'is-invalid':form.errors.description }" type="text" v-model="form.description"></textarea>
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
                <div class="btn btn-primary btn-block mb-4 disabled" v-if="!form.isDirty">Edit topic</div>
                <button v-if="form.isDirty" class="btn btn-primary btn-block mb-4" type="submit" :disabled="form.processing">Edit topic</button>
                <Link :href="route('manage_topic')" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { Head, Link , useForm } from "@inertiajs/vue3";
import flatpickr from "flatpickr"

const props = defineProps({
    topic: Object,
});

onMounted(() => {
    flatpickr(".flatpickr", {
        enableTime: true,
        time_24hr: true,
        defaultDate: props.topic.due_date,
        minDate: "today",
    })
});

const clearCalendar = () => {
    form.due_date = null;
};

const form = useForm({
    topic_id: props.topic.id,
    topic_name: props.topic.topic_name,
    description: props.topic.description,
    due_date: props.topic.due_date,
});

</script>
