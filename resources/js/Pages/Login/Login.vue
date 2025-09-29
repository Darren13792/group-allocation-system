<template>
    <Head>
        <title>Login</title>
    </Head>
    <form @submit.prevent="form.post(route('process_login'))">
        <!-- Email input -->
        <div class="form-outline mb-4">
            <label class="form-label" for="email">Email address</label>
            <input id="email" class="form-control" :class="{ 'is-invalid':form.errors.email }" type="text" v-model="form.email">
            <div class="invalid-feedback" v-if="form.errors.email">{{ form.errors.email }}</div>
        </div>
        <!-- Password input -->
        <div class="form-outline mb-4">
            <label class="form-label" for="password">Password</label>
            <input id="password" class="form-control" :class="{ 'is-invalid':form.errors.password }" type="password" v-model="form.password">
            <div class="invalid-feedback" v-if="form.errors.password">{{ form.errors.password }}</div>
        </div>
        <!-- Forgot Password button -->
        <div class="row mb-4">
            <div>
                <Link :href="route('forgot_password')">Forgot Password?</Link>
            </div>
        </div>
        <!-- Submit button -->
        <button class="btn btn-primary btn-block mb-4" type="submit" :disabled="form.processing">Log in</button>
    </form>
</template>

<script setup>
import { Link , useForm, Head } from "@inertiajs/vue3";

const form = useForm({
    email: null,
    password: null,
});

</script>
