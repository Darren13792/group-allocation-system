<template>
    <Head>
        <title>Forgot Password</title>
    </Head>
    <form @submit.prevent="form.post(route('request_password'))">
        <!-- Email input -->
        <div class="form-outline mb-4">
            <label class="form-label" for="email">Email address</label>
            <input id="email" class="form-control" :class="{ 'is-invalid':form.errors.email }" type="text" v-model="form.email">
            <div class="invalid-feedback" v-if="form.errors.email">{{ form.errors.email }}</div>
        </div>
        <!-- Submit button -->
        <div>
            <button class="btn btn-primary btn-block mb-4" type="submit" :disabled="form.processing">Submit</button>
            <Link href="/" class="btn btn-secondary mb-4 mx-1">Cancel</Link>
        </div>
    </form>
</template>

<script setup>
import { Head, Link , useForm } from "@inertiajs/vue3";
const form = useForm({
    email: null,
});
</script>