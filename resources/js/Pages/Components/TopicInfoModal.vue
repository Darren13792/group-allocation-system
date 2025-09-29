<template>
    <div class="modal fade" id="topic-modal" tabindex="-1" aria-labelledby="topic-modal-label" aria-hidden="true">
        <div class="modal-dialog px-3 px-sm-0 modal-m">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title fw-bold" id="topic-modal-label">{{ topic_name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-left d-flex gap-3 align-items-center">
                        <div>{{ topic_description }}</div>
                    </div>
                </div>
                <div class="modal-footer text-center px-2 py-1">
                    <button type="button" class="flex-even btn btn-outline-secondary border-0" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>

const props = defineProps(['topic_name', 'topic_description']);

</script>