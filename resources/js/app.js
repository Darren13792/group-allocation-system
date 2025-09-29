import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import DashboardLayout from './Pages/Layouts/DashboardLayout.vue'
import AuthLayout from './Pages/Layouts/AuthLayout.vue'

createInertiaApp({
  title:title => `${title} - Group Allocation System`,
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    let page = pages[`./Pages/${name}.vue`]
    page.default.layout = name.startsWith('Admin/') || name.startsWith('Student/') || name.startsWith('Supervisor/') || name.startsWith('Users/') ? DashboardLayout : AuthLayout
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mixin({ methods: { route}})
      .mount(el)
  },
})