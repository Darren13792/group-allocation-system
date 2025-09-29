<template>
    <div class="modal fade" id="delete-modal" tabindex="-1" aria-labelledby="delete-modal-label" aria-hidden="true">
        <div class="modal-dialog px-3 px-sm-0 modal-m">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title fw-bold" id="delete-modal-label">Delete '{{ topic_name }}'</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="form.post(route('delete_topic'))" id="delete-topic">
                        <input type="hidden" v-model="form.topic_id">
                        <div class="text-left d-flex gap-3 align-items-center">
                            <i class="fa-solid fa-triangle-exclamation fa-3x px-2 text-warning"></i>
                            <div>Are you sure you want to delete this topic? Groups/Preferences containing this topic will become blank.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer text-center px-2 py-1">
                    <button type="button" class="flex-even btn btn-outline-secondary border-0" data-bs-dismiss="modal"
                        aria-label="Close">Cancel</button>
                    <button type="submit" form="delete-topic" class="flex-even btn btn-outline-danger fw-bold border-0"
                        data-bs-dismiss="modal" aria-label="Close" :disabled="form.processing">Delete Topic</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps(['topic_name', 'topic_id']);
const topicId = computed(() => props.topic_id);

const form = useForm({
    topic_id: topicId,
});

</script>