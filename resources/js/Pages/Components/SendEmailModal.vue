<template>
    <div class="modal fade" id="send-allocation-modal" tabindex="-1" aria-labelledby="send-allocation-modal-label" aria-hidden="true">
        <div class="modal-dialog px-3 px-sm-0 modal-m">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title fw-bold" id="send-allocation-modal-label">Dispatch Allocation Emails</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="form.post(route('send_allocation'))" id="process-send-allocation">
                        <div class="text-left d-flex gap-3 align-items-center">
                            <i class="fa-solid fa-triangle-exclamation fa-3x px-2 text-warning"></i>
                            <div><b>Warning:</b><br> All assigned users will be sent group specific details. Are you sure you want to continue?</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer text-center px-2 py-1">
                    <button type="button" class="flex-even btn btn-outline-secondary border-0" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="submit" form="process-send-allocation" class="flex-even btn btn-outline-primary fw-bold border-0" data-bs-dismiss="modal" aria-label="Close" :disabled="form.processing">Dispatch emails</button>
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
