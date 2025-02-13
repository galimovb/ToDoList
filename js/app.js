import { createApp } from 'vue';
import LoginPage from './pages/LoginPage/LoginPage.vue';
import HomePage from './pages/HomePage/HomePage.vue';
import RegistrPage from "./pages/registr-page/RegistrPage.vue";
import 'element-plus/theme-chalk/dark/css-vars.css';
import ElementPlus from 'element-plus';
import '../assets/styles/app.css';
import 'element-plus/dist/index.css';

import * as ElementPlusIconsVue from '@element-plus/icons-vue';
import PlannedTasksPage from "./pages/PlannedTasksPage/PlannedTasksPage.vue";
import AdminPage from "./pages/AdminPage/AdminPage.vue";


const app = createApp({});

app.component('login-page', LoginPage);
app.component('home-page', HomePage);
app.component('registr-page', RegistrPage)
app.component('planned-tasks-page', PlannedTasksPage)
app.component('admin-page', AdminPage)

for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
    app.component(key, component);
}

app.use(ElementPlus);

app.mount('#app');
