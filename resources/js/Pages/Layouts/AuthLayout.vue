<template>
    <div class="d-flex align-items-center min-vh-100">
        <div class="w-100" style="max-width: 500px; margin: auto;">
            <div class="card shadow-lg bg-body border-0">
                <div class="m-5">
                    <div>
                        <div v-if="$page.props.flash.success && showFlash" class="alert alert-success" role="alert">
                            {{ $page.props.flash.success }}
                            <button type="button" class="btn-close" @click="showFlash=false"></button>
                        </div>
                        <div v-if="$page.props.flash.warning && showFlash" class="alert alert-warning" role="alert">
                            {{ $page.props.flash.warning }}
                            <button type="button" class="btn-close" @click="showFlash=false"></button>
                        </div>
                        <div v-if="$page.props.flash.danger && showFlash" class="alert alert-danger" role="alert">
                            {{ $page.props.flash.danger }}
                            <button type="button" class="btn-close" @click="showFlash=false"></button>
                        </div>
                    </div>
                    <div class="mb-3 text-center">
                        <div>
                            <Link href="/">
                                <img class="mx-4" :src="'/assets/leicester-university-logo.png'" width=50 alt="University Logo">
                            </Link>
                        </div>
                        <div v-if="$page.url === '/'">
                            <h1>Welcome Back!</h1>
                            <h4 class="text-secondary fw-normal">Sign in to your account</h4>
                        </div>
                        <div v-if="$page.url === '/forgot-password'">
                            <h1>Forgot Password</h1>
                            <p class="text-muted pt-2 text-start">
                                Please enter your email address. We'll send you a link to reset your password.
                            </p>
                        </div>
                        <div v-if="$page.url === '/request-password-success'">
                            <h1>Forgot Password</h1>
                            <p class="text-muted pt-2 text-start">
                                Success! A password recovery link has been sent to your email.
                            </p>
                        </div>
                        <div v-if="$page.url.startsWith('/reset-password')">
                            <h1>Reset Password</h1>
                        </div>
                        <div v-if="$page.url.startsWith('/first-time-login')">
                            <h1>First Time Login</h1>
                        </div>
                    </div>
                    <slot/>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { usePage, Link } from "@inertiajs/vue3";

const showFlash = ref(true);
const page = usePage()

watch(() => page.props.flash, (value) => {
    showFlash.value = !!value;
}, { immediate: true, deep: true });

const html = document.querySelector('html');
const currentTheme = localStorage.getItem('theme') || 'light';
html.setAttribute('data-bs-theme', currentTheme);

</script>
