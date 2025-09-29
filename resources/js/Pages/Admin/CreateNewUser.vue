<template>
    <Head>
        <title>Create New User</title>
    </Head>
    <!-- CSV Upload -->
    <form @submit.prevent="submitCSV">
        <h1 class="border-bottom pb-3">Import Users via CSV</h1>
        <div class="mx-2">
            <h3>Ensure the following:</h3>
            <ul>
                <li>
                    CSV file must adhere to the specified header order:<br>
                    <span class="text-muted fst-italic">first_name, last_name, email, user_type</span>
                </li>
                <li>All entries are required to be separated by commas</li>
            </ul>
    
            <div class="mb-4">
                <label for="file">Import CSV</label>
                <input class="form-control" :class="{ 'is-invalid':csvform.errors.file }" type="file" v-on:change="handleFileUpload">
                <div class="invalid-feedback" v-if="csvform.errors.file" v-html="csvform.errors.file"></div>
                <small class="text-muted">Warning: updating user type will delete preferences attached to user.</small>
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
    <h1 class="border-top border-bottom py-3">Create New User</h1>
    <div class="mx-2">
        <form @submit.prevent="form.post(route('process_new_user'))">
            <!-- First name input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="first_name">First Name</label>
                <input id="first_name" class="form-control" :class="{ 'is-invalid':form.errors.first_name }" type="text" v-model="form.first_name">
                <div class="invalid-feedback" v-if="form.errors.first_name">{{ form.errors.first_name }}</div>
            </div>

            <!-- Last name input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="last_name">Last Name</label>
                <input id="last_name" class="form-control" :class="{ 'is-invalid':form.errors.last_name }" type="text" v-model="form.last_name">
                <div class="invalid-feedback" v-if="form.errors.last_name">{{ form.errors.last_name }}</div>
            </div>

            <!-- Email input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="email">Email address</label>
                <input id="email" class="form-control" :class="{ 'is-invalid':form.errors.email }" type="text" v-model="form.email">
                <div class="invalid-feedback" v-if="form.errors.email">{{ form.errors.email }}</div>
            </div>

            <!-- User type input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="user_type">User Type</label>
                <select id="user_type" class="form-select" :class="{ 'is-invalid':form.errors.user_type }" v-model="form.user_type">
                    <option value="" disabled selected>-- Select a user type --</option>
                    <option value="ADMIN">Admin</option>
                    <option value="SUPERVISOR">Supervisor</option>
                    <option value="STUDENT">Student</option>
                </select>
                <div class="invalid-feedback" v-if="form.errors.user_type">{{ form.errors.user_type }}</div>
            </div>
            <!-- Submit button -->
            <div>
                <div class="btn btn-primary btn-block mb-4 disabled" v-if="!form.isDirty">Create user</div>
                <button v-if="form.isDirty" class="btn btn-primary btn-block mb-4" type="submit" :disabled="form.processing">Create user</button>
                <Link :href="route('manage_user')" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
    first_name: null,
    last_name: null,
    email: null,
    user_type: null,
});

const csvform = useForm({
    file: null,
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    csvform.file = file;
};

const submitCSV = () => {
    csvform.post(route('process_user_csv'));
};

</script>
