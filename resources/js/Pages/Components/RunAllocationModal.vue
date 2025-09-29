<template>
    <div class="modal fade" id="allocation-modal" tabindex="-1" aria-labelledby="allocation-modal-label" aria-hidden="true">
        <div class="modal-dialog px-3 px-sm-0 modal-m">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title fw-bold" id="allocation-modal-label">Run Allocation Algorithm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="form.post(route('process_allocation'))" id="process-allocation">
                        <div class="mx-3">
                            <div>
                                <!-- Min Size input -->
                                <div class="form-outline mb-4">
                                    <label class="form-label" for="min_group_size">Minimum Group Size</label>
                                    <input class="form-control" :class="{ 'is-invalid':!isValidGroupSize || !isValidMinSize }" type="number" v-model="form.min_group_size">
                                    <div class="invalid-feedback" v-if="!isValidMinSize">Minimum Group Size must be > 0.</div>
                                    <div class="invalid-feedback" v-if="!isValidGroupSize">Minimum Group Size must be ≤ Maximum Group Size.</div>
                                </div>

                                <!-- Max Size input -->
                                <div class="form-outline mb-4">
                                    <label class="form-label" for="max_group_size">Maximum Group Size</label>
                                    <input class="form-control" :class="{ 'is-invalid':!isValidGroupSize || !isValidMaxSize }" type="number" v-model="form.max_group_size"></input>
                                    <div class="invalid-feedback" v-if="!isValidMaxSize">Maximum Group Size must be > 0.</div>
                                    <div class="invalid-feedback" v-if="!isValidGroupSize">Maximum Group Size must be ≥ Minimum Group Size.</div>
                                </div>
                                
                                <!-- Max Groups Per Topic input -->
                                <div class="form-outline mb-4">
                                    <label class="form-label" for="max_groups_per_topic">Maximum Groups Per Topic</label>
                                    <input class="form-control" :class="{ 'is-invalid':!isValidGroupPerTopic }" type="number" v-model="form.max_groups_per_topic"></input>
                                    <div class="invalid-feedback" v-if="!isValidGroupPerTopic">Maximum Groups Per Topic must be > 0</div>
                                </div>

                                <!-- Enable Ideal Size input -->
                                <div class="d-flex justify-content-start mb-2">
                                    <div class="form-check form-switch form-check-reverse">
                                        <label>Set Ideal Group Size?</label>
                                        <input type="checkbox" v-model="useIdealSize" class="form-check-input">
                                    </div>
                                </div>
                                <!-- Ideal Size input -->
                                <div class="form-outline mb-4" v-if="useIdealSize">
                                    <label class="form-label" for="ideal_size">Ideal Group Size</label>
                                    <input class="form-control" :class="{ 'is-invalid':!isValidIdealSize }" type="number" v-model="form.ideal_size"></input>
                                    <div class="invalid-feedback" v-if="!isValidIdealSize">Ideal Size must be ≥ Minimum Group Size and ≤ Maximum Group Size.</div>
                                </div>
                            </div>
                            <small class="text-muted">Warning: Running the algorithm will override existing group allocations</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer text-center px-2 py-1">
                    <button type="button" class="flex-even btn btn-outline-secondary border-0" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <div v-if="!isValid" class="flex-even btn btn-outline-danger fw-bold border-0 disabled">Run Allocation</div>
                    <button v-if="isValid" type="submit" form="process-allocation" class="flex-even btn btn-outline-danger fw-bold border-0" data-bs-dismiss="modal" aria-label="Close" :disabled="form.processing">Run Allocation</button>
                </div>
            </div>
        </div>
    </div>
</template>
    
<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from "@inertiajs/vue3";

const props = defineProps(['settings']);

const useIdealSize = ref(false);

const idealSize = computed(() => {
    return useIdealSize.value ? props.settings.ideal_size : null;
});

const form = useForm({
    min_group_size: props.settings.min_group_size,
    max_group_size: props.settings.max_group_size,
    max_groups_per_topic: props.settings.max_groups_per_topic,
    ideal_size: null,
})

watch(idealSize, (newValue) => {
    form.ideal_size = newValue;
}, { immediate: true });

const isValidMinSize = computed(() => {
    return 0 < parseInt(form.min_group_size);
});

const isValidMaxSize = computed(() => {
    return 0 < parseInt(form.max_group_size);
});

const isValidGroupSize = computed(() => {
    return parseInt(form.min_group_size) <= parseInt(form.max_group_size);
});

const isValidIdealSize = computed(() => {
    if (form.ideal_size === null||form.ideal_size === '') {
        return true;
    }
    return (parseInt(form.min_group_size) <= parseInt(form.ideal_size) && parseInt(form.ideal_size) <= parseInt(form.max_group_size));
});

const isValidGroupPerTopic = computed(() => {
    return 0 < parseInt(form.max_groups_per_topic);
});

const isValid = computed(() => {
    return isValidMinSize.value && isValidMaxSize.value && isValidGroupSize.value && isValidIdealSize.value && isValidGroupPerTopic.value;
});


</script>
