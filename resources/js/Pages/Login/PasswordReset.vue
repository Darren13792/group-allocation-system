<template>
    <Head>
        <title>Reset Password</title>
    </Head>
    <form @submit.prevent="form.post(route('reset_password'))">
        <!-- Email input -->
        <div class="form-outline mb-4">
            <label class="form-label" for="email">Email address</label>
            <input id="email" class="form-control" :class="{ 'is-invalid':form.errors.email }" type="text" v-model="form.email" readonly>
            <div class="invalid-feedback" v-if="form.errors.email">{{ form.errors.email }}</div>
        </div>
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
            <Link href="/" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
        </div>
    </form>
</template>


<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";

const { props } = usePage();

const form = useForm({
    email: props.route.query.email || null,
    token: props.route.params.token || null,
    password: null,
    password_confirmation: null,
});

</script>
