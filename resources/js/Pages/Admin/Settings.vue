<template>
    <Head>
        <title>Settings</title>
    </Head>
    <h1 class="border-bottom pb-3">&nbsp<i class="fa-solid fa-gear text-secondary"></i> &nbspUpdate Settings</h1>
    <div class="mt-3 mx-2 col-12 col-lg-11">
        <form @submit.prevent="form.post(route('process_settings'))">
            <h3>General Settings:</h3>
            <!-- Status input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="status">Set Website Status</label>
                <select id="status" class="form-select" :class="{ 'is-invalid':form.errors.status }" v-model="form.status">
                    <option value="notstarted">Not Started</option>
                    <option value="started">Started</option>
                    <option value="ended">Ended</option>
                    <option value="visible">Allow allocation visibility</option>
                </select>
                <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
            </div>
            <!-- Submission Deadline input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="deadline">Submission Deadline</label>
                <div class="input-group">
                    <input v-model="form.deadline" placeholder="Select date and time..." id="deadline" class="form-control flatpickr" :class="{ 'is-invalid':form.errors.deadline }" type="date">
                    <div class="btn btn-sm btn-hover text-danger m-0" @click="clearCalendar()">
                        <i class="fa-solid fa-2x fa-xmark"></i>
                    </div>
                </div>
                <div class="invalid-feedback" v-if="form.errors.deadline">{{ form.errors.deadline }}</div>
            </div>
            <!-- Preference Size input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="preference_size">Preference Size</label>
                <input id="preference_size" class="form-control" :class="{ 'is-invalid':!isValidPrefSize }" type="number" v-model="form.preference_size">
                <div class="invalid-feedback" v-if="!isValidPrefSize">Preference Size must be > 0</div>
            </div>
            <h3>Allocation Settings:</h3>
            <!-- Min and Max Group Size input -->
            <div class="form-outline mb-4">
                <div class="row g-3">
                    <div class="col-md-6 pe-2">
                        <label class="form-label" for="min_group_size">Minimum Group Size</label>
                        <input id="min_group_size" class="form-control" :class="{ 'is-invalid':!isValidGroupSize || !isValidMinSize }" type="number" v-model="form.min_group_size">
                        <div class="invalid-feedback" v-if="!isValidMinSize">Minimum Group Size must be > 0.</div>
                        <div class="invalid-feedback" v-if="!isValidGroupSize">Minimum Group Size must be ≤ Maximum Group Size.</div>
                    </div>
                    <div class="col-md-6 ps-2">
                        <label class="form-label" for="max_group_size">Maximium Group Size</label>
                        <input id="max_group_size" class="form-control" :class="{ 'is-invalid':!isValidGroupSize || !isValidMaxSize }" type="number" v-model="form.max_group_size">
                        <div class="invalid-feedback" v-if="!isValidMaxSize">Maximum Group Size must be > 0.</div>
                        <div class="invalid-feedback" v-if="!isValidGroupSize">Maximum Group Size must be ≥ Minimum Group Size.</div>
                    </div>
                </div>
            </div>
            <!-- Max Groups Per Topic input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="max_groups_per_topic">Maximum Groups Per Topic</label>
                <input id="max_groups_per_topic" class="form-control" :class="{ 'is-invalid':!isValidGroupPerTopic }" type="number" v-model="form.max_groups_per_topic">
                <div class="invalid-feedback" v-if="!isValidGroupPerTopic">Maximum Groups Per Topic must be > 0</div>
            </div>
            <!-- Ideal Size input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="ideal_size">Ideal Size</label>
                <input id="ideal_size" class="form-control" :class="{ 'is-invalid':!isValidIdealSize }" type="number" v-model="form.ideal_size">
                <div class="invalid-feedback" v-if="!isValidIdealSize">Ideal Size must be ≥ Minimum Group Size and ≤ Maximum Group Size.</div>
            </div>
            <!-- Submit button -->
            <div>
                <div class="btn btn-primary btn-block mb-4 disabled" v-if="!form.isDirty">Update Settings</div>
                <button v-if="form.isDirty" class="btn btn-primary btn-block mb-4" type="submit" :disabled="form.processing">Update Settings</button>
            </div>
        </form>
    </div>
</template>


<script setup>
import { computed, onMounted } from 'vue';
import { Head, useForm } from "@inertiajs/vue3";
import flatpickr from "flatpickr"

const props = defineProps({
    settings: Object,
});

onMounted(() => {
    flatpickr(".flatpickr", {
        enableTime: true,
        time_24hr: true,
        defaultDate: props.settings.deadline,
        minDate: "today",
    })
});

const form = useForm({
    status: props.settings.status,
    deadline: props.settings.deadline,
    preference_size: props.settings.preference_size,
    min_group_size: props.settings.min_group_size,
    max_group_size: props.settings.max_group_size,
    max_groups_per_topic: props.settings.max_groups_per_topic,
    ideal_size: props.settings.ideal_size,
});

const clearCalendar = () => {
    form.deadline = null;
};

const isValidMinSize = computed(() => {
    return 0 < parseInt(form.min_group_size);
});

const isValidMaxSize = computed(() => {
    return 0 < parseInt(form.max_groups_per_topic);
});

const isValidGroupSize = computed(() => {
    return parseInt(form.min_group_size) <= parseInt(form.max_group_size);
});

const isValidIdealSize = computed(() => {
    if (form.ideal_size === null||form.ideal_size === '') {
        return true;
    }
    return (parseInt(form.min_group_size) <= parseInt(form.ideal_size) && parseInt(form.ideal_size) <= parseInt(form.max_group_size));
});

const isValidGroupPerTopic = computed(() => {
    return 0 < parseInt(form.max_groups_per_topic);
});

const isValidPrefSize = computed(() => {
    return 0 < parseInt(form.preference_size);
});
</script>
