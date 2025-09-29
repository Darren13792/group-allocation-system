<template>
    <Head>
        <title>Dashboard</title>
    </Head>
    <h1 v-if="user.user_type === 'STUDENT'" class="border-bottom text-center text-md-start pb-3">Student Dashboard</h1>
    <h1 v-if="user.user_type === 'SUPERVISOR'" class="border-bottom text-center text-md-start pb-3">Supervisor Dashboard</h1>
    <div v-if="status === 'ended'" class="alert alert-info">The deadline for submissions has passed. Please wait while we process the groups.</div>
    <div class="mx-2">
        <div class="container my-4">
            <div class="d-flex justify-content-center">
                <ul class="d-flex m-0 p-0 progressbar" style="width: 90%">
                    <li class='fw-bold text-primary active' style="color:gray" step="1">Not Started</li>
                    <li :class="{'fw-bold text-primary active': status === 'started' || status === 'ended' || status ==='approved'}" style="color:gray" step="2">Started</li>
                    <li :class="{'fw-bold text-primary active': status==='ended' || status ==='approved'}" style="color:gray" step="3">Ended</li>
                    <li :class="{'fw-bold text-primary active': status==='approved'}" style="color:gray" step="4">Finished</li>
                </ul>
            </div>
            <div class="card shadow-lg mt-4 pt-2 text-center">
                <h1>Deadline: {{ deadline ? formatDate(deadline) : 'Not Currently Set' }}</h1>
                <ul v-if="status === 'notstarted'" class="pt-2 text-start">
                    <li>Please wait until you are granted permission to submit your preferences.</li>
                    <li>You may view all the topics available.</li>
                </ul>
                <ul v-if="status === 'started'" class="pt-2 text-start">
                    <li>Please submit your preferences before the deadline.</li>
                    <li>You are able to edit your preferences if needed.</li>
                </ul>
                <ul v-if="status === 'ended'" class="pt-2 text-start">
                    <li>You are no longer able to submit your preferences. If necessary, please contact admin for support.</li>
                    <li>Please wait while we process the groups</li>
                </ul>
                <ul v-if="status === 'approved'" class="pt-2 text-start">
                    <li>Your group allocation is now visible on the 'View Group' tab.</li>
                </ul>
            </div>
        </div>
    </div>
</template>
    
<script setup>
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { formatDate } from '../../../../public/js/dateformat.js';

const props = defineProps({
    status: String,
    deadline: String,
});

const page = usePage()

const user = computed(() => page.props.auth.user)
</script>
