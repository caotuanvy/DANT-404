import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import './assets/styles.css';
import { createHead } from '@vueuse/head';


axios.defaults.baseURL = 'http://localhost:8000/api';
axios.defaults.withCredentials = true;

const app = createApp(App);
const head = createHead();
app.use(head);
app.use(router);

app.mount('#app');

