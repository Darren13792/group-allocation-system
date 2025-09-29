<template>
    <div class="modal fade" id="logout-modal" tabindex="-1" aria-labelledby="logout-modal-label" aria-hidden="true">
        <div class="modal-dialog px-3 px-sm-0 modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title fw-bold" id="logout-modal-label">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="form.post(route('logout'))" id="logout">
                        <div class="text-left d-flex gap-3 align-items-center">
                            <i class="fa-solid fa-triangle-exclamation fa-3x px-2 text-warning"></i>
                            <div>Are you sure you want to logout?</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer text-center px-2 py-1">
                    <button type="button" class="btn btn-outline-secondary border-0" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="submit" form="logout" class="flex-even btn btn-outline-danger fw-bold border-0" data-bs-dismiss="modal" aria-label="Close" :disabled="form.processing">Logout</button>
                </div>
            </div>
        </div>
    </div>
</template>
    
<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
})

</script>