<template>
    <div class="modal fade" id="delete-all-modal" tabindex="-1" aria-labelledby="delete-all-modal-label" aria-hidden="true">
        <div class="modal-dialog px-3 px-sm-0 modal-m">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title fw-bold" id="delete-all-modal-label">Delete All Users</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="form.post(route('delete_all_users'))" id="delete-all-users">
                        <div class="text-left d-flex gap-3 align-items-center">
                            <i class="fa-solid fa-triangle-exclamation fa-3x px-2 text-warning"></i>
                            <div class="form-outline mb-4 flex-grow-1">
                                <label class="form-label" for="status">Delete option</label>
                                <select id="status" class="form-select" v-model.number="form.option">
                                    <option value=1>Students and Supervisors</option>
                                    <option value=2>Only Students</option>
                                    <option value=3>Only Supervisors</option>
                                </select>
                            </div>
                        </div>
                        <div class="ms-2">Are you sure you want to delete these users? This cannot be reversed.</div>
                    </form>
                </div>
                <div class="modal-footer text-center px-2 py-1">
                    <button type="button" class="flex-even btn btn-outline-secondary border-0" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="submit" form="delete-all-users" class="flex-even btn btn-outline-danger fw-bold border-0" data-bs-dismiss="modal" aria-label="Close" :disabled="form.processing">Delete Users</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    option: 1
})
</script>