<template>
    <div class="modal fade" id="reset-modal" tabindex="-1" aria-labelledby="reset-modal-label" aria-hidden="true">
        <div class="modal-dialog px-3 px-sm-0 modal-m">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title fw-bold" id="reset-modal-label">Reset '{{ first_name }} {{ last_name }}' Availability</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="form.post(route('reset_availability'))" id="reset-availability">
                        <div class="text-left d-flex gap-3 align-items-center">
                            <i class="fa-solid fa-triangle-exclamation fa-3x px-2 text-warning"></i>
                            <div>Are you sure you want to reset <b>{{ first_name }} {{ last_name }}</b>'s availability?</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer text-center px-2 py-1">
                    <button type="button" class="flex-even btn btn-outline-secondary border-0" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="submit" form="reset-availability" class="flex-even btn btn-outline-danger fw-bold border-0" data-bs-dismiss="modal" aria-label="Close" :disabled="form.processing">Reset availability</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";

const props = defineProps(['first_name', 'last_name', 'user_id']);

const form = useForm({
    user_id: props.user_id,
});

</script>