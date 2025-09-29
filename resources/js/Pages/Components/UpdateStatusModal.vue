<template>
    <div class="modal fade" id="status-modal" tabindex="-1" aria-labelledby="status-modal-label" aria-hidden="true">
        <div class="modal-dialog px-3 px-sm-0 modal-m">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title fw-bold" id="status-modal-label">Update Website Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="statusform.post(route('update_status'))" id="update-status">
                        <div class="mx-3">
                            <!-- Status input -->
                            <div class="form-outline mb-4">
                                <label class="form-label" for="status">Set Website Status</label>
                                <select id="status" class="form-select" :class="{ 'is-invalid':statusform.errors.status }" v-model="statusform.status">
                                    <option value="notstarted">Not Started</option>
                                    <option value="started">Started</option>
                                    <option value="ended">Ended</option>
                                    <option value="approved">Allow allocation visibility</option>
                                </select>
                            </div>
                            <div v-if="statusform.status === 'notstarted'">
                                <ul>
                                    <li>Users can sign in/reset password</li>
                                    <li>Users can view currently existing topics</li>
                                </ul>
                                <text class="text-muted">Use this time to import necessary users and topics, and update settings.</text>
                            </div>
                            <div v-if="statusform.status === 'started'">
                                <ul>
                                    <li>Students can submit and edit preferences</li>
                                    <li>Supervisors can submit and edit availability</li>
                                </ul>
                                <text class="text-muted">Ensure all the necessary users/topics are imported, and the settings are satisfactory before updating.</text>
                            </div>
                            <div v-if="statusform.status === 'ended'">
                                <ul>
                                    <li>Students can no longer submit and edit preferences</li>
                                    <li>Supervisors can no longer submit and edit availability</li>
                                    <li>Users do not have permission to view the allocation</li>
                                </ul>
                                <text class="text-muted">The status will automatically update to 'Ended' after the deadline date, if set, or can be manually updated.</text>
                            </div>
                            <div v-if="statusform.status === 'approved'">
                                <ul>
                                    <li>Users can view the group allocation</li>
                                </ul>
                                <text class="text-muted">Ensure the allocation is satisfactory before making the allocation visible</text>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer text-center px-2 py-1">
                    <button type="button" class="flex-even btn btn-outline-secondary border-0" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <div class="flex-even btn btn-outline-primary fw-bold border-0 disabled" v-if="!statusform.isDirty">Update status</div>
                    <button v-if="statusform.isDirty" type="submit" form="update-status" class="flex-even btn btn-outline-primary fw-bold border-0" data-bs-dismiss="modal" aria-label="Close" :disabled="statusform.processing">Update status</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";

const props = defineProps(['status']);

const statusform = useForm({
    status: props.status,
})
</script>