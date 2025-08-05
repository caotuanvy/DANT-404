// src/main.js
import 'core-js/es/promise';
if (typeof window !== 'undefined' && typeof window.Promise === 'undefined') {
  window.Promise = Promise;
}

import { createApp, reactive } from 'vue'; // Thêm reactive ở đây
import App from './App.vue';
import router from './router';
import axios from 'axios';
import './assets/styles.css';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import './assets/mobile.css';
import { createHead } from '@vueuse/head';

axios.defaults.baseURL = 'http://localhost:8000/api';
axios.defaults.withCredentials = true;
const token = localStorage.getItem('token');
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Khởi tạo trạng thái ứng dụng để theo dõi SDKs
export const appState = reactive({
  isFacebookSdkLoaded: false,
  isGoogleSdkLoaded: false,
});

const app = createApp(App);
const head = createHead();
app.use(head);
app.use(router);
app.mount('#app');

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/firebase-messaging-sw.js')
      .then((registration) => {
        console.log(' Service Worker đã được đăng ký:', registration);
      })
      .catch((error) => {
        console.error(' Đăng ký Service Worker thất bại:', error);
      });
  });
}

// Khởi tạo Facebook SDK
function loadFacebookSdk() {
    return new Promise((resolve) => {
        window.fbAsyncInit = function () {
            FB.init({
                appId: '731596522938443',
                cookie: true,
                xfbml: true,
                version: 'v19.0',
            });
            FB.AppEvents.logPageView();
            console.log('Facebook SDK đã khởi tạo thành công!');
            appState.isFacebookSdkLoaded = true;
            resolve();
        };

        const sdk = document.createElement('script');
        sdk.src = 'https://connect.facebook.net/en_US/sdk.js';
        sdk.async = true;
        sdk.defer = true;
        document.body.appendChild(sdk);
    });
}

loadFacebookSdk();
