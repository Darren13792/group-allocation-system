<template>
    <Head>
        <title>Submit Preferences</title>
    </Head>
    <h1 class="border-bottom text-center text-md-start pb-3">Student Preferences</h1>
    <form @submit.prevent="form.post(route('process_student_preference'))" class="mb-4 ms-2">
        <div class="mx-2 mt-4">
            <div class="table-container">
                <table class="table table-borderless" style="width:90%; border-collapse: separate;">
                    <thead>
                        <tr>
                            <th style="width:10%"></th>
                            <th v-for="index in preference_size" class="text-center text-nowrap" style="font-weight: 650;">
                                <div class="invalid-feedback d-block fw-normal" v-if="form.errors['preference_' + index]">{{ form.errors['preference_' + index] }}</div>
                                Preference {{ index }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="topic in topics">
                            <td class="clickable text-start" @click="topicInformation(topic.topic_name, topic.description)">
                                {{ topic.topic_name }}
                            </td>
                            <td v-for="index in preference_size" class="text-center">
                                <input
                                    class="form-check-input border-2"
                                    :class="{ 'is-invalid':form.errors['preference_' + index] }"
                                    type="radio"
                                    :id="'preference_' + index + '_topic_' + topic.id"
                                    :value="topic.id"
                                    :name="'preference_' + index"
                                    v-model="form['preference_' + index]"
                                    :disabled="topicIsSelected(topic.id)"
                                    style="opacity: 1;"
                                />
                            </td>
                        </tr>
                        <tr>
                            <td class="text-wrap text-start text-success fw-bold">Selected:</td>
                            <td v-for="index in preference_size" class="text-center text-wrap text-success fw-bold" style="max-width:100px">
                                {{ topicName(form['preference_' + index]) }}
                            </td>
                        </tr>
                        <tr><td></td></tr>
                        <tr>
                            <td class="text-start">
                                <div class="btn btn-primary btn-block disabled" v-if="!form.isDirty">Submit</div>
                                <button v-if="form.isDirty" class="btn btn-primary btn-block" type="submit" :disabled="form.processing">Submit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
    <!-- Topic Modal -->
    <TopicInfoModal :topic_name="topic_name" :topic_description="topic_description"/>
</template>



<script setup>
import { ref } from 'vue';
import { Head, useForm } from "@inertiajs/vue3";
import TopicInfoModal from '../Components/TopicInfoModal.vue';
const props = defineProps({
    user: Object,
    topics: Object,
    preference_size: Number,
});

let preferences = {};
for (let i = 1; i <= props.preference_size; i++) {
    const preference = props.user.student_preferences.find(preference => preference.weight === i);
    preferences['preference_' + i] = preference ? preference.topic_id : null;
}

const form = useForm(preferences);

const topicName = (topicId) => {
    const topic = props.topics.find(topic => topic.id === topicId);
    return topic ? topic.topic_name : 'No topic selected';
};

const topicIsSelected = (topicId) => {
    return Object.values(form).includes(topicId);
};

// Topic Modal

const topic_name = ref("");
const topic_description = ref("");

const topicInformation = (topicName, topicDescription) => {
    topic_name.value = topicName;
    topic_description.value = topicDescription;
    $('#topic-modal').modal('show');
};

$(document).ready(function () {
    $("#topic-modal").on('hidden.bs.modal', function () {
        topic_name.value = "";
        topic_description.value = "";
    });
});
</script>