<template>
    <Head>
        <title>Dashboard</title>
    </Head>
    <h1 class="border-bottom pb-3 text-center text-md-start">Admin Dashboard</h1>
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
            <div class="d-flex justify-content-center">
                <div class="d-flex card my-3" style="width: 100%;">
                    <div class="card-header">
                        <h2>Process Status:
                            <template v-if="status==='notstarted'">Not Started</template>
                            <template v-if="status==='started'">Started</template>
                            <template v-if="status==='ended'">Ended</template>
                            <template v-if="status==='approved'">Content visible</template>
                        </h2>
                    </div>
                    <div class="card-body">
                        <div v-if="status === 'notstarted'">
                            <ul>
                                <li>Users can sign in/reset password</li>
                                <li>Users can view currently existing topics</li>
                            </ul>
                            <text class="text-muted">Use this time to import necessary users and topics, and update settings.</text>
                        </div>
                        <div v-if="status === 'started'">
                            <ul>
                                <li>Students can submit and edit preferences</li>
                                <li>Supervisors can submit and edit availability</li>
                            </ul>
                            <text class="text-muted">Ensure all the necessary users/topics are imported, and the settings are satisfactory before updating.</text>
                        </div>
                        <div v-if="status === 'ended'">
                            <ul>
                                <li>Students can no longer submit and edit preferences</li>
                                <li>Supervisors can no longer submit and edit availability</li>
                                <li>Users do not have permission to view the allocation</li>
                            </ul>
                            <text class="text-muted">The status will automatically update to 'Ended' after the deadline date, if set, or can be manually updated.</text>
                        </div>
                        <div v-if="status === 'approved'">
                            <ul>
                                <li>Users can view the group allocation</li>
                            </ul>
                            <text class="text-muted">Ensure the allocation is satisfactory before making the allocation visible</text>
                        </div>
                        <button class="btn btn-primary mt-3" @click="updateStatus()">Update status</button>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <div class="col-12 col-md-5 col-lg-3 mb-3">
                    <div class="h-100 card bg-primary d-flex flex-column">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-light">User Count</p>
                                <h1 class="text-white">{{ userCount }}</h1>
                            </div>
                            <i class="fa-regular fa-3x fa-user text-light opacity-50 me-2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-5 col-lg-3 mb-3">
                    <div class="h-100 card bg-warning d-flex flex-column">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-light">Topic Count</p>
                                <h1 class="text-white">{{ topicCount }}</h1>
                            </div>
                            <i class="fa-regular fa-3x fa-clipboard text-light opacity-50 me-2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-5 col-lg-3 mb-3">
                    <div class="h-100 card bg-success d-flex flex-column">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-light">Preferences Submitted</p>
                                <h1 class="text-white">{{ prefCount }}</h1>
                            </div>
                            <i class="fa-solid fa-3x fa-list-check text-light opacity-50 me-2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-5 col-lg-3 mb-3">
                    <div class="h-100 card bg-danger d-flex flex-column">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-light">Unactivated Users</p>
                                <h1 class="text-white">{{ nonActiveCount }}</h1>
                            </div>
                            <i class="fa-solid fa-3x fa-triangle-exclamation text-light opacity-50 me-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn btn-danger btn-lg my-2 me-3" @click="runAllocation()">Run allocation</button>
        </div>
    </div>
    <!-- Status Modal -->
    <UpdateStatusModal :status="status"/>
    <!-- Allocation Modal -->
    <RunAllocationModal :settings="settings"/>
</template>
    
<script setup>
import { Head } from "@inertiajs/vue3";
import RunAllocationModal from '../Components/RunAllocationModal.vue';
import UpdateStatusModal from '../Components/UpdateStatusModal.vue';

const props = defineProps({
    status: String,
    settings: Object,
    userCount: Number,
    topicCount: Number,
    prefCount: Number,
    nonActiveCount: Number,
});

const runAllocation = () => {
    $('#allocation-modal').modal('show');
};
const updateStatus = () => {
    $('#status-modal').modal('show');
};
</script>
