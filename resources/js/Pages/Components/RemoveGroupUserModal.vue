<template>
    <div class="modal fade" id="remove-group-user-modal" tabindex="-1" aria-labelledby="remove-group-user-modal-label" aria-hidden="true">
        <div class="modal-dialog px-3 px-sm-0 modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title fw-bold" id="remove-group-user-modal-label">Remove '{{ form.user_name }}'</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="form.post(route('remove_group_user'))" id="remove-group-user">
                        <div class="text-left d-flex gap-3 align-items-center">
                            <i class="fa-solid fa-triangle-exclamation fa-3x px-2 text-warning"></i>
                            <div>Are you sure you want to remove <b>{{ form.user_name }}</b> from group {{ form.group_id }}?</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer text-center px-2 py-1">
                    <button type="button" class="flex-even btn btn-outline-secondary border-0" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="submit" form="remove-group-user" class="flex-even btn btn-outline-danger fw-bold border-0" data-bs-dismiss="modal" aria-label="Close" :disabled="form.processing">Remove</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from "@inertiajs/vue3";

const props = defineProps(['user_name', 'user_id', 'group_id']);

const userName = computed(() => props.user_name);
const userId = computed(() => props.user_id);
const groupId = computed(() => props.group_id);

const form = useForm({
    user_name: userName,
    user_id: userId,
    group_id: groupId
});

</script>