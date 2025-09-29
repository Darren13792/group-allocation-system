<template>
    <Head>
        <title>First Time Login</title>
    </Head>
    <form @submit.prevent="form.post(route('process_first_time_login'))">
        <p class="text-muted pt-2">
            Welcome <b>{{ user.first_name }} {{ user.last_name }}!</b><br>
            Please set your new password to gain access to the website.
        </p>
        <!-- Password input -->
        <div class="form-outline mb-4">
            <label class="form-label" for="password">New Password</label>
            <input id="password" class="form-control" :class="{ 'is-invalid':form.errors.password }" type="password" v-model="form.password">
            <div class="invalid-feedback" v-if="form.errors.password">{{ form.errors.password }}</div>
        </div>
        <!-- Confirm Password input -->
        <div class="form-outline mb-4">
            <label class="form-label" for="password_confirmation ">Confirm New Password</label>
            <input id="password_confirmation " class="form-control" :class="{ 'is-invalid':form.errors.password_confirmation  }" type="password" v-model="form.password_confirmation ">
            <div class="invalid-feedback" v-if="form.errors.password_confirmation ">{{ form.errors.password_confirmation  }}</div>
        </div>
        <!-- Submit button -->
        <div>
            <button class="btn btn-primary btn-block mb-4" type="submit" :disabled="form.processing">Submit</button>
            <Link :href="route('logout')" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
        </div>
    </form>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, usePage ,useForm } from "@inertiajs/vue3";

const page = usePage()

const user = computed(() => page.props.auth.user)

const form = useForm({
    user_id: null,
    password: null,
    password_confirmation : null,
});

const user_id = computed(() => user.value.id);
form.user_id = user_id;
</script>