<template>
    <header>
        <nav class="navbar navbar-expand fixed-top border-bottom bg-body">
            <div class="my-0 py-0 container-fluid px-2">
                <div class="d-none d-md-flex navbar-nav col-2">
                    <div class="d-flex align-items-center">
                        <Link href="/">
                            <img class="mx-3" :src="'/assets/leicester-university-logo.png'" width=30 alt="University Logo">
                        </Link>
                        <button @click="toggleSidebar" type="button" :class="['rounded-pill p-1 btn', isLightMode ? 'btn-light' : 'btn-dark']">
                            <svg height="25" width="25" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                <path fill="#0d6efd" d="M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="d-md-none navbar-nav col-2">
                    <button @click="toggleMobileSidebar" type="button" :class="['rounded-pill p-1 btn', isLightMode ? 'btn-light' : 'btn-dark']">
                        <svg height="25" width="25" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path fill="#0d6efd" d="M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z"/>
                        </svg>
                    </button>
                </div>
                <div class="navbar-nav">
                    <a class="text-decoration-none text-black" href="/">
                        <div class="text-body text-center font-weight-bold my-0 mx-2 h3">Group Allocation System</div>
                    </a>
                </div>
                <div class="navbar-nav col-2 justify-content-end">
                    <div class="nav-item">
                        <button @click="toggleDarkMode" id="dark-mode-toggle" type="button" :class="['rounded-pill p-2 btn', isLightMode ? 'btn-light' : 'btn-dark']">
                            <div class="d-flex" style="height: 20px; min-height: 20px; width: 20px; min-width: 20px;">
                                <svg v-if="isLightMode" id="sunIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path fill="black" d="M361.5 1.2c5 2.1 8.6 6.6 9.6 11.9L391 121l107.9 19.8c5.3 1 9.8 4.6 11.9 9.6s1.5 10.7-1.6 15.2L446.9 256l62.3 90.3c3.1 4.5 3.7 10.2 1.6 15.2s-6.6 8.6-11.9 9.6L391 391 371.1 498.9c-1 5.3-4.6 9.8-9.6 11.9s-10.7 1.5-15.2-1.6L256 446.9l-90.3 62.3c-4.5 3.1-10.2 3.7-15.2 1.6s-8.6-6.6-9.6-11.9L121 391 13.1 371.1c-5.3-1-9.8-4.6-11.9-9.6s-1.5-10.7 1.6-15.2L65.1 256 2.8 165.7c-3.1-4.5-3.7-10.2-1.6-15.2s6.6-8.6 11.9-9.6L121 121 140.9 13.1c1-5.3 4.6-9.8 9.6-11.9s10.7-1.5 15.2 1.6L256 65.1 346.3 2.8c4.5-3.1 10.2-3.7 15.2-1.6zM160 256a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zm224 0a128 128 0 1 0 -256 0 128 128 0 1 0 256 0z"/>
                                </svg>
                                <svg v-else id="moonIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                                    <path fill="white" d="M223.5 32C100 32 0 132.3 0 256S100 480 223.5 480c60.6 0 115.5-24.2 155.8-63.4c5-4.9 6.3-12.5 3.1-18.7s-10.1-9.7-17-8.5c-9.8 1.7-19.8 2.6-30.1 2.6c-96.9 0-175.5-78.8-175.5-176c0-65.8 36-123.1 89.3-153.3c6.1-3.5 9.2-10.5 7.7-17.3s-7.3-11.9-14.3-12.5c-6.3-.5-12.6-.8-19-.8z"/>
                                </svg>
                            </div>
                        </button>
                    </div>
                    <div class="nav-item">
                        <button @click="confirmLogout" type="button" :class="['rounded-pill p-2 btn', isLightMode ? 'btn-light' : 'btn-dark']">
                            <div class="d-flex" style="height: 20px; min-height: 20px; width: 20px; min-width: 20px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path :fill="isLightMode ? 'black' : 'white'" d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"/>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
        <nav class="sidebar border-end bg-body shadow" :class="{'d-md-none': !isSidebarVisible}" :style="{'padding-top': headerHeight}">
            <div class="position-sticky">
                <div class="d-none d-md-block mx-3 mt-3 pb-0">
                    <h5 v-if="user.user_type === 'ADMIN'" class="pb-2 mb-0 border-bottom" style="display: inline-block">Admin Pages</h5>
                    <h5 v-if="user.user_type === 'STUDENT'" class="pb-2 mb-0 border-bottom" style="display: inline-block">Student Pages</h5>
                    <h5 v-if="user.user_type === 'SUPERVISOR'" class="pb-2 mb-0 border-bottom" style="display: inline-block">Supervisor Pages</h5>
                </div>
                <div :class="{'d-none': !isMobileNavbarVisible, 'd-block': isMobileNavbarVisible, 'd-md-block': true}" style="user-select: none">
                    <div v-if="user.user_type === 'STUDENT'" class="list-group list-group-flush sidebar-pages">
                        <Link :href="route('user_dashboard')" class="list-group-item border-0" :class="{ 'active rounded-2 shadow' : $page.url === '/user-dashboard'}">
                            <text><i class="fa-solid fa-house fa-fw me-3"></i>Student Dashboard</text></Link>
                        <Link v-if="status === 'approved'" :href="route('view_group')" class="list-group-item border-0" :class="{ 'active rounded-2 shadow' : $page.url === '/view-group'}">
                            <text><i class="fa-solid fa-users fa-fw me-3"></i>View Group</text></Link>
                        <Link :href="route('view_topics')" class="list-group-item border-0" :class="{ 'active rounded-2 shadow' : $page.url === '/view-topics'}">
                            <text><i class="fa-solid fa-clipboard fa-fw me-3"></i>View Topics</text></Link>
                        <Link v-if="status === 'started'" :href="route('student_preferences')" class="list-group-item border-0" :class="{ 'active rounded-2 shadow' : $page.url === '/student-preference'}">
                            <text><i class="fa-solid fa-list-check fa-fw me-3"></i>Submit Preferences</text></Link>
                        <a @click="confirmLogout" class="hover list-group-item border-0">
                            <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i>Logout</a>
                    </div> 
                    <div v-if="user.user_type === 'SUPERVISOR'" class="list-group list-group-flush sidebar-pages text-nowrap">
                        <Link :href="route('user_dashboard')" class="list-group-item border-0" :class="{ 'active rounded-2 shadow' : $page.url === '/user-dashboard'}">
                            <text><i class="fa-solid fa-house fa-fw me-3"></i>Supervisor Dashboard</text></Link>
                        <Link v-if="status === 'approved'" :href="route('view_group')" class="list-group-item border-0" :class="{ 'active rounded-2 shadow' : $page.url === '/view-group'}">
                            <text><i class="fa-solid fa-users fa-fw me-3"></i>View Groups</text></Link>
                        <Link :href="route('view_topics')" class="list-group-item border-0" :class="{ 'active rounded-2 shadow' : $page.url === '/view-topics'}">
                            <text><i class="fa-solid fa-clipboard fa-fw me-3"></i>View Topics</text></Link>
                        <Link v-if="status === 'started'" :href="route('supervisor_preferences')" class="list-group-item border-0" :class="{ 'active rounded-2 shadow' : $page.url === '/supervisor-availability'}">
                            <text><i class="fa-solid fa-list-check fa-fw me-3"></i>Submit Availability</text></Link>
                        <a @click="confirmLogout" class="hover list-group-item border-0">
                            <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i>Logout</a>
                    </div>
                    <div v-if="user.user_type === 'ADMIN'" class="list-group list-group-flush sidebar-pages" id="sidebar-links">
                        <Link :href="route('admin_dashboard')" class="list-group-item border-0" :class="{ 'active rounded-2 shadow' : $page.url === '/admin-dashboard'}">
                            <text><i class="fa-solid fa-house fa-fw me-3"></i>Admin Dashboard</text></Link>
                        <div class="list-group-item border-0 d-flex justify-content-between align-items-center"
                            :class="{ 'active rounded-2 shadow': $page.url === '/manage-user' || $page.url === '/create-new-user' || $page.url.startsWith('/edit-user')}">
                            <Link :href="route('manage_user')" class="stretch-link"></Link>
                            <div><i class="fa-solid fa-user fa-fw me-3"></i>Manage Users</div>
                            <i class="fa-solid fa-chevron-up" :class="{'closed': currentLevel !== 'users'}"
                                data-bs-toggle="collapse" data-bs-target="#user-links" aria-expanded="false"
                                style="z-index: 1;cursor: pointer;padding: 10px;margin: -10px;" @click="toggleLink('users')">
                            </i>
                        </div>
                        <div class="collapse" id="user-links" data-bs-parent="#sidebar-links">
                            <Link :href="route('create_new_user')" class="list-group-item border-0" :style="{'font-weight': $page.url === '/create-new-user' ? '600': ''}">
                                Create New User</Link>
                        </div>
                        <div class="list-group-item border-0 d-flex justify-content-between align-items-center"
                            :class="{ 'active rounded-2 shadow': $page.url === '/manage-topic' || $page.url === '/create-new-topic' || $page.url.startsWith('/edit-topic')}">
                            <Link :href="route('manage_topic')" class="stretch-link"></Link>
                            <div><i class="fa-solid fa-clipboard fa-fw me-3"></i>Manage Topics</div>
                            <i class="fa-solid fa-chevron-up" :class="{'closed': currentLevel !== 'topics'}"
                                data-bs-toggle="collapse" data-bs-target="#topic-links" aria-expanded="false"
                                style="z-index: 1;cursor: pointer;padding: 10px;margin: -10px;" @click="toggleLink('topics')">
                            </i>
                        </div>
                        <div class="collapse" id="topic-links" data-bs-parent="#sidebar-links">
                            <Link :href="route('create_new_topic')" class="list-group-item border-0" :style="{'font-weight': $page.url === '/create-new-topic' ? '600': ''}">
                                Create New Topic</Link>
                        </div>
                        <div class="list-group-item border-0 d-flex justify-content-between align-items-center"
                            :class="{ 'active rounded-2 shadow': $page.url === '/manage-groups' || $page.url === '/create-new-group' || $page.url.startsWith('/edit-group')}">
                            <Link :href="route('manage_groups')" class="stretch-link"></Link>
                            <div><i class="fa-solid fa-layer-group fa-fw me-3"></i>Manage Groups</div>
                            <i class="fa-solid fa-chevron-up" :class="{ 'closed': currentLevel !== 'groups'}"
                                data-bs-toggle="collapse" data-bs-target="#group-links" aria-expanded="false"
                                style="z-index: 1;cursor: pointer;padding: 10px;margin: -10px;" @click="toggleLink('groups')">
                            </i>
                        </div>
                        <div class="collapse" id="group-links" data-bs-parent="#sidebar-links">
                            <Link :href="route('create_new_group')" class="list-group-item border-0" :style="{'font-weight': $page.url === '/create-new-group' ? '600': ''}">
                                Create New Group</Link>
                        </div>
                        <Link :href="route('settings')" class="list-group-item border-0" :class="{ 'active rounded-2 shadow' : $page.url === '/settings'}">
                            <text><i class="fa-solid fa-gear fa-fw me-3"></i>Settings</text></Link>
                        <a @click="confirmLogout" class="hover list-group-item border-0">
                            <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i>Logout</a>
                    </div>
                </div>
            </div>
            <div class="px-3 py-3 border-top bg-body-tertiary d-none d-md-block" style="position:absolute;bottom:0;width:100%">
                <p class="mb-0">User Type: {{user.user_type}}<br>Logged in as <b>{{user.first_name}} {{user.last_name}}</b></p>
            </div>
        </nav>
    </header>
    <main id="content" :style="{'padding-left': !isSidebarVisible ? '0px': '', 'margin-top': isMobile ? navbarHeight : headerHeight}">
        <div class="container-fluid pt-3 px-4">
            <div v-if="$page.props.flash.success && showFlash" class="alert alert-success" role="alert">
                {{ $page.props.flash.success }}
                <button type="button" class="btn-close" @click="showFlash=false"></button>
            </div>
            <div v-if="$page.props.flash.warning && showFlash" class="alert alert-warning" role="alert">
                {{ $page.props.flash.warning }}
                <button type="button" class="btn-close" @click="showFlash=false"></button>
            </div>
            <div v-if="$page.props.flash.danger && showFlash" class="alert alert-danger" role="alert">
                {{ $page.props.flash.danger }}
                <button type="button" class="btn-close" @click="showFlash=false"></button>
            </div>
            <slot/>
            <!-- Logout Modal -->
            <LogoutModal/>
        </div>
    </main>
</template>

<script setup>
import { computed , ref, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { usePage , Link } from '@inertiajs/vue3'
import LogoutModal from '../Components/LogoutModal.vue';

const props = defineProps({
    status: String,
});

onMounted(() => {
    updateWindowWidth();
    window.addEventListener('resize', updateWindowWidth);
});

onUnmounted(() => {
    window.removeEventListener('resize', updateWindowWidth);
});

const showFlash = ref(true);
const page = usePage()

watch(() => page.props.flash, (value) => {
    showFlash.value = !!value;
}, { immediate: true, deep: true });

const user = computed(() => page.props.auth.user)

const html = document.querySelector('html');
const currentTheme = localStorage.getItem('theme') || 'light';
html.setAttribute('data-bs-theme', currentTheme);

const currentLevel = ref(null);

function toggleLink(level) {
    currentLevel.value = currentLevel.value === level ? null : level;
}

const isLightMode = ref(currentTheme === 'light');

watch(isLightMode, (change) => {
    const fpLink = document.getElementById('calendar-style');
    fpLink.href = `https://npmcdn.com/flatpickr/dist/themes/${change ? 'light' : 'dark'}.css`;
}, { immediate: true });

function toggleDarkMode() {
    const html = document.getElementsByTagName('html')[0];
    const currentTheme = html.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    html.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    isLightMode.value = !isLightMode.value;
}

const isSidebarVisible = ref(true);
const isMobileNavbarVisible = ref(false);
const windowWidth = ref(window.innerWidth);
const navbarHeight = ref('0px');
const headerHeight = ref('54px');

function updateWindowWidth() {
    windowWidth.value = window.innerWidth;
    const headerElement = document.querySelector('nav.navbar');
    if (headerElement) {
        headerHeight.value = `${headerElement.offsetHeight}px`;
    }
}

const isMobile = computed(() => {
    return windowWidth.value < 768;
});

function toggleSidebar() {
    isSidebarVisible.value = !isSidebarVisible.value;
}

function toggleMobileSidebar() {
    isMobileNavbarVisible.value = !isMobileNavbarVisible.value;
    nextTick(() => {
        if (isMobileNavbarVisible.value) {
            const navbarElement = document.querySelector('.position-sticky');
            navbarHeight.value = `${navbarElement.offsetHeight}px`;
        } else {
            navbarHeight.value = '0px';
        }
    });
}

function confirmLogout() {
    $('#logout-modal').modal('show');
}

</script>