import 'core-js/es/promise';
if (typeof window !== 'undefined' && typeof window.Promise === 'undefined') {
  window.Promise = Promise;
}

import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import './assets/styles.css';
import 'bootstrap/dist/css/bootstrap.min.css'; // Dòng này import CSS
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import { createHead } from '@vueuse/head';


axios.defaults.baseURL = 'http://localhost:8000/api';
axios.defaults.withCredentials = true;
const token = localStorage.getItem('token');
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

const app = createApp(App);
const head = createHead();
app.use(head);
app.use(router);

app.mount('#app');

