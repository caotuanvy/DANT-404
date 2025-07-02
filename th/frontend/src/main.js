import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import './assets/styles.css';
import 'bootstrap/dist/css/bootstrap.min.css'; // Dòng này import CSS
import 'bootstrap/dist/js/bootstrap.bundle.min.js';


axios.defaults.baseURL = 'http://localhost:8000/api';
axios.defaults.withCredentials = true;
const token = localStorage.getItem('token');
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

const app = createApp(App);
app.use(router);
app.mount('#app');

