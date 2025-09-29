<template>
    <Head>
        <title>Edit User</title>
    </Head>
    <div v-if="user.user_type === 'STUDENT'">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#details" aria-selected="true" role="tab" style="cursor: pointer;">User Details</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pref-tab" data-bs-toggle="tab" data-bs-target="#preferences" aria-selected="false" role="tab" style="cursor: pointer;">Edit Preferences</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="detail-tab">
                <h1 class="border-bottom py-3">Edit Details: {{ user.first_name }} {{ user.last_name }}</h1>
                <div class="mx-2">
                    <form @submit.prevent="form.post(route('process_edit_user'))">
                        <input type="hidden" v-model="form.user_id">
                        <!-- First name input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="first_name">First Name</label>
                            <input class="form-control" :class="{ 'is-invalid':form.errors.first_name }" type="text" v-model="form.first_name">
                            <div class="invalid-feedback" v-if="form.errors.first_name">{{ form.errors.first_name }}</div>
                        </div>
            
                        <!-- Last name input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="last_name">Last Name</label>
                            <input class="form-control" :class="{ 'is-invalid':form.errors.last_name }" type="text" v-model="form.last_name">
                            <div class="invalid-feedback" v-if="form.errors.last_name">{{ form.errors.last_name }}</div>
                        </div>
            
                        <!-- Email input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="email">Email address</label>
                            <input class="form-control" :class="{ 'is-invalid':form.errors.email }" type="text" v-model="form.email">
                            <div class="invalid-feedback" v-if="form.errors.email">{{ form.errors.email }}</div>
                        </div>
            
                        <!-- Activation status input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="activation_status">Activation Status</label>
                            <select class="form-select" :class="{ 'is-invalid':form.errors.activation_status }" v-model="form.activation_status">
                                <option value="" disabled selected>-- Select activation status --</option>
                                <option value=0>0</option>
                                <option value=1>1</option>
                            </select>
                            <div class="invalid-feedback" v-if="form.errors.activation_status">{{ form.errors.activation_status }}</div>
                        </div>
            
                        <!-- User type input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="user_type">User Type</label>
                            <select class="form-select" :class="{ 'is-invalid':form.errors.user_type }" v-model="form.user_type">
                                <option value="" disabled selected>-- Select a user type --</option>
                                <option value="ADMIN">Admin</option>
                                <option value="SUPERVISOR">Supervisor</option>
                                <option value="STUDENT">Student</option>
                            </select>
                            <small class="text-muted">Updating User Type will remove groups and preferences attached to user.</small>
                            <div class="invalid-feedback" v-if="form.errors.user_type">{{ form.errors.user_type }}</div>
                        </div>
            
                        <!-- Submit button -->
                        <div>
                            <div class="btn btn-primary btn-block mb-4 disabled" v-if="!form.isDirty">Edit details</div>
                            <button v-if="form.isDirty" class="btn btn-primary btn-block mb-4" type="submit" :disabled="form.processing">Edit details</button>
                            <Link :href="route('manage_user')" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="preferences" role="tabpanel" aria-labelledby="pref-tab">
                <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between pt-3 pb-2 border-bottom gap-sm-3">
                    <h1 class="text-center text-sm-start">Edit Preferences: {{ user.first_name }} {{ user.last_name }}</h1>
                    <button class="btn btn-outline-danger text-nowrap" @click="confirmDelete()">Delete Preferences</button>
                </div>
                <form @submit.prevent="form2.post(route('process_edit_preference'))" class="mb-4 ms-2">
                    <div class="mx-2 mt-4">
                        <div class="table-container">
                            <table class="table table-borderless" style="width:90%; border-collapse: separate;">
                                <thead>
                                    <tr>
                                        <th style="width:10%"></th>
                                        <th v-for="index in preference_size" class="text-center text-nowrap" style="font-weight: 650;">
                                            <div class="invalid-feedback d-block fw-normal" v-if="form2.errors['preference_' + index]">{{ form2.errors['preference_' + index] }}</div>
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
                                                :class="{ 'is-invalid':form2.errors['preference_' + index] }"
                                                type="radio"
                                                :id="'preference_' + index + '_topic_' + topic.id"
                                                :value="topic.id"
                                                :name="'preference_' + index"
                                                v-model="form2['preference_' + index]"
                                                :disabled="topicIsSelected(topic.id)"
                                                style="opacity: 1;"
                                            />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-wrap text-start text-success fw-bold">Selected:</td>
                                        <td v-for="index in preference_size" class="text-center text-wrap text-success fw-bold" style="max-width:100px">
                                            {{ topicName(form2['preference_' + index]) }}
                                        </td>
                                    </tr>
                                    <tr><td></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div>
                        <div class="btn btn-primary btn-block mb-4 disabled" v-if="!form2.isDirty">Edit preferences</div>
                        <button v-if="form2.isDirty" class="btn btn-primary btn-block mb-4" type="submit" :disabled="form2.processing">Edit preferences</button>
                        <Link :href="route('manage_user')" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div v-else-if="user.user_type === 'SUPERVISOR'">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#details"
                    aria-selected="true" role="tab" style="cursor: pointer;">User Details</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pref-tab" data-bs-toggle="tab" data-bs-target="#availability"
                    aria-selected="false" role="tab" style="cursor: pointer;">Edit Availability</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="detail-tab">
                <h1 class="border-bottom py-3">Edit Details: {{ user.first_name }} {{ user.last_name }}</h1>
                <div class="mx-2">
                    <form @submit.prevent="form.post(route('process_edit_user'))">
                        <input type="hidden" v-model="form.user_id">
                        <!-- First name input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="first_name">First Name</label>
                            <input class="form-control" :class="{ 'is-invalid':form.errors.first_name }" type="text" v-model="form.first_name">
                            <div class="invalid-feedback" v-if="form.errors.first_name">{{ form.errors.first_name }}</div>
                        </div>
            
                        <!-- Last name input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="last_name">Last Name</label>
                            <input class="form-control" :class="{ 'is-invalid':form.errors.last_name }" type="text" v-model="form.last_name">
                            <div class="invalid-feedback" v-if="form.errors.last_name">{{ form.errors.last_name }}</div>
                        </div>
            
                        <!-- Email input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="email">Email address</label>
                            <input class="form-control" :class="{ 'is-invalid':form.errors.email }" type="text" v-model="form.email">
                            <div class="invalid-feedback" v-if="form.errors.email">{{ form.errors.email }}</div>
                        </div>
            
                        <!-- Activation status input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="activation_status">Activation Status</label>
                            <select class="form-select" :class="{ 'is-invalid':form.errors.activation_status }" v-model="form.activation_status">
                                <option value="" disabled selected>-- Select activation status --</option>
                                <option value=0>0</option>
                                <option value=1>1</option>
                            </select>
                            <div class="invalid-feedback" v-if="form.errors.activation_status">{{ form.errors.activation_status }}</div>
                        </div>
            
                        <!-- User type input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="user_type">User Type</label>
                            <select class="form-select" :class="{ 'is-invalid':form.errors.user_type }" v-model="form.user_type">
                                <option value="" disabled selected>-- Select a user type --</option>
                                <option value="ADMIN">Admin</option>
                                <option value="SUPERVISOR">Supervisor</option>
                                <option value="STUDENT">Student</option>
                            </select>
                            <small class="text-muted">Updating User Type will remove groups and preferences attached to user.</small>
                            <div class="invalid-feedback" v-if="form.errors.user_type">{{ form.errors.user_type }}</div>
                        </div>
            
                        <!-- Submit button -->
                        <div>
                            <div class="btn btn-primary btn-block mb-4 disabled" v-if="!form.isDirty">Edit details</div>
                            <button v-if="form.isDirty" class="btn btn-primary btn-block mb-4" type="submit" :disabled="form.processing">Edit details</button>
                            <Link :href="route('manage_user')" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="availability" role="tabpanel" aria-labelledby="pref-tab">
                <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between pt-3 pb-2 border-bottom gap-sm-3">
                    <h1 class="text-center text-sm-start">Edit Availability: {{ user.first_name }} {{ user.last_name }}</h1>
                    <button class="btn btn-outline-danger text-nowrap" @click="confirmReset()">Reset Availability</button>
                </div>
                <form @submit.prevent="form2.post(route('process_edit_availability'))">
                    <input type="hidden" v-model="form2.user_id">
                    <div>
                        <div class="mx-2 mt-3">
                            <div class="row mb-4">
                                <div class="col-1"></div>
                                <div class="col text-center">
                                    <div class="invalid-feedback d-block" v-if="form2.errors['availability']">{{form2.errors['availability'] }}</div>
                                    <label class="form-check-label" style="font-weight: 500;">Availability</label>
                                </div>
                            </div>
                            <div v-for="topic in topics" :key="topic.id" class="row mb-4">
                                <div class="col-1">
                                    <label class="clickable text-nowrap"
                                        @click="topicInformation(topic.topic_name, topic.description)">{{topic.topic_name }}
                                    </label>
                                </div>
                                <div class="col form-check d-flex justify-content-center">
                                    <input class="form-check-input border-2"
                                        :class="{ 'is-invalid': form2.errors[topic.id] }"
                                        type="checkbox"
                                        :id="topic.id"
                                        :value="form2[topic.id] === true ? null : true"
                                        :name="topic.id"
                                        v-model="form2[topic.id]"
                                        style="opacity: 1;" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="btn btn-primary btn-block mb-4 disabled" v-if="!form2.isDirty">Edit availability</div>
                        <button v-if="form2.isDirty" class="btn btn-primary btn-block mb-4" type="submit" :disabled="form2.processing">Edit availability</button>
                        <Link :href="route('manage_user')" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div v-else>
        <h1 class="border-bottom pb-3">Edit User: {{ user.first_name }} {{ user.last_name }}</h1>
        <div class="mx-2">
            <form @submit.prevent="form.post(route('process_edit_user'))">
                <input type="hidden" v-model="form.user_id">
                <!-- First name input -->
                <div class="form-outline mb-4">
                    <label class="form-label" for="first_name">First Name</label>
                    <input class="form-control" :class="{ 'is-invalid':form.errors.first_name }" type="text" v-model="form.first_name">
                    <div class="invalid-feedback" v-if="form.errors.first_name">{{ form.errors.first_name }}</div>
                </div>
    
                <!-- Last name input -->
                <div class="form-outline mb-4">
                    <label class="form-label" for="last_name">Last Name</label>
                    <input class="form-control" :class="{ 'is-invalid':form.errors.last_name }" type="text" v-model="form.last_name">
                    <div class="invalid-feedback" v-if="form.errors.last_name">{{ form.errors.last_name }}</div>
                </div>
    
                <!-- Email input -->
                <div class="form-outline mb-4">
                    <label class="form-label" for="email">Email address</label>
                    <input class="form-control" :class="{ 'is-invalid':form.errors.email }" type="text" v-model="form.email">
                    <div class="invalid-feedback" v-if="form.errors.email">{{ form.errors.email }}</div>
                </div>
    
                <!-- Activation status input -->
                <div class="form-outline mb-4">
                    <label class="form-label" for="activation_status">Activation Status</label>
                    <select class="form-select" :class="{ 'is-invalid':form.errors.activation_status }" v-model="form.activation_status">
                        <option value="" disabled selected>-- Select activation status --</option>
                        <option value=0>0</option>
                        <option value=1>1</option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.activation_status">{{ form.errors.activation_status }}</div>
                </div>
    
                <!-- User type input -->
                <div class="form-outline mb-4">
                    <label class="form-label" for="user_type">User Type</label>
                    <select class="form-select" :class="{ 'is-invalid':form.errors.user_type }" v-model="form.user_type">
                        <option value="" disabled selected>-- Select a user type --</option>
                        <option value="ADMIN">Admin</option>
                        <option value="SUPERVISOR">Supervisor</option>
                        <option value="STUDENT">Student</option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.user_type">{{ form.errors.user_type }}</div>
                </div>
    
                <!-- Submit button -->
                <div>
                    <div class="btn btn-primary btn-block mb-4 disabled" v-if="!form.isDirty">Edit details</div>
                    <button v-if="form.isDirty" class="btn btn-primary btn-block mb-4" type="submit" :disabled="form.processing">Edit details</button>
                    <Link :href="route('manage_user')" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
                </div>
            </form>
        </div>
    </div>
    <!-- Topic Modal -->
    <TopicInfoModal :topic_name="topic_name" :topic_description="topic_description"/>
    <!-- Delete Preferences Modal -->
    <DeletePreferencesModal :first_name="user.first_name" :last_name="user.last_name" :user_id="user.id"/>
    <!-- Reset Availability Modal -->
    <ResetAvailabilityModal :first_name="user.first_name" :last_name="user.last_name" :user_id="user.id"/>         
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from "@inertiajs/vue3";
import TopicInfoModal from '../Components/TopicInfoModal.vue';
import DeletePreferencesModal from '../Components/DeletePreferencesModal.vue';
import ResetAvailabilityModal from '../Components/ResetAvailabilityModal.vue';

const props = defineProps({
    topics: Object,
    user: Object,
    preference_size: Number,
});

const form = useForm({
    user_id: props.user.id,
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    activation_status: props.user.activation_status,
    user_type: props.user.user_type,
});

let formData = {
    user_id: props.user.id,
};

if (props.user.user_type === 'STUDENT') {
    for (let i = 1; i <= props.preference_size; i++) {
        formData['preference_' + i] = props.user.student_preferences.find(preference => preference.weight === i)?.topic_id || null;
    }
} else if (props.user.user_type === 'SUPERVISOR') {
    for (const topic of Object.values(props.topics)) {
        formData[topic.id] = props.user.supervisor_preferences.find(preference => preference.topic_id === topic.id)? true : false;
    }
}

const form2 = useForm(formData);

const topicName = (topicId) => {
    const topic = props.topics.find(topic => topic.id === topicId);
    return topic ? topic.topic_name : 'No topic selected';
};

const topicIsSelected = (topicId) => {
    const topicFields = Object.keys(form2).filter(key => key.startsWith('preference_'));
    return topicFields.some(key => form2[key] === topicId);
};

const confirmDelete = () => {
    $('#delete-modal').modal('show');
};

const confirmReset = () => {
    $('#reset-modal').modal('show');
};

// Topic Modal

const topic_name = ref("");
const topic_description = ref("");

const topicInformation = (topicName, topicDescription) => {
    topic_name.value = topicName;
    topic_description.value = topicDescription;
    $('#topic-modal').modal('show');
};

$(document).ready(function() {
$("#topic-modal").on('hidden.bs.modal', function() {
    topic_name.value = "";
    topic_description.value = "";
    });
});

</script>
