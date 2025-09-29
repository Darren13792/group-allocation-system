<template>
    <Head>
        <title>Submit Availability</title>
    </Head>
    <h1 class="border-bottom text-center text-md-start pb-3">Supervisor Availability</h1>
    <div class="mx-3 mt-3">
        <form @submit.prevent="form.post(route('process_supervisor_preference'))">
            <div class="row mb-4">
                <div class="col-1" style="font-weight: 500;">Topic</div>
                    <div class="col text-center">
                        <div class="invalid-feedback d-block" v-if="form.errors['availability']">{{ form.errors['availability'] }}</div>
                        <label class="form-check-label" style="font-weight: 500;">Availability</label>
                    </div>
                </div>
            <div v-for="topic in topics" :key="topic.id" class="row mb-4">
                <div class="col-1">
                    <label class="clickable text-nowrap" @click="topicInformation(topic.topic_name, topic.description)">{{topic.topic_name }}</label>
                </div>
                <div class="col form-check d-flex justify-content-center">
                    <input class="form-check-input border-2" :class="{ 'is-invalid': form.errors[topic.id] }" type="checkbox"
                        :id="topic.id" :value="form[topic.id] === true ? null : true" :name="topic.id" v-model="form[topic.id]"
                        style="opacity: 1;" />
                </div>
            </div>
            <div class="btn btn-primary btn-block mb-4 disabled" v-if="!form.isDirty">Submit</div>
            <button v-if="form.isDirty" class="btn btn-primary btn-block mb-4" type="submit"
              :disabled="form.processing">Submit</button>
        </form>
    </div>
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
});

let availability = {};

for (const topic of Object.values(props.topics)) {
    availability[topic.id] = props.user.supervisor_preferences.find(preference => preference.topic_id === topic.id) ? true : false;
}

const form = useForm(availability);

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