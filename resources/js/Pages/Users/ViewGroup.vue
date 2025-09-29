<template>
    <Head>
        <title>Groups</title>
    </Head>
    <h1 class="border-bottom text-center text-md-start pb-3 mb-4">View Group</h1>
    <div v-if="groups && groups.length" class="mb-5">
        <div v-for="group in groups" :key="group.id" class="card shadow mb-3">
            <div class="card-header">
                <h2>Group ID: {{ group.id }}</h2>
                <h5>Topic: {{ group.topic.topic_name }}</h5>
                <p>{{ group.topic.description }}</p>
                <small class="text-muted">Due Date: {{ group.topic.due_date }}</small>
            </div>
            <div class="card-body">
                <h4>Supervisors</h4>
                <ul class="list-group list-group-flush mb-3">
                    <li v-for="supervisor in group.group_supervisors" :key="supervisor.id" class="list-group-item">
                        {{ supervisor.supervisor.first_name }} {{ supervisor.supervisor.last_name }} - {{ supervisor.supervisor.email }}
                    </li>
                </ul>
                <h4>Students</h4>
                <ul class="list-group list-group-flush">
                    <li v-for="student in group.group_students" :key="student.id" class="list-group-item">
                        {{ student.student.first_name }} {{ student.student.last_name }} - {{ student.student.email }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div v-else class="alert alert-info">You are not currently allocated to any groups. If necessary, please contact admin for support.</div>
</template>
  
<script setup>
import { Head } from '@inertiajs/vue3';
const props = defineProps({
    groups: Object,
});
</script>
  