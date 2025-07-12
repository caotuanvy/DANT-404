importScripts("https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js");

firebase.initializeApp({
  apiKey: "AIzaSyBcwCHZYnH_ZnXxhHgsL9WRDpdz7CnvAOo",
  authDomain: "datn-70923.firebaseapp.com",
  projectId: "datn-70923",
  storageBucket: "datn-70923.firebasestorage.app",
  messagingSenderId: "588484906208",
  appId: "1:588484906208:web:f035cebd2ff5c2293fd485"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  const notificationTitle = payload.notification?.title || 'Thông báo mới';
  const notificationOptions = {
    body: payload.notification?.body || 'Bạn có một tin nhắn mới.',
    icon: payload.notification?.icon || '/logo.png',
    data: payload.data
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});

self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  const urlToOpen = event.notification?.data?.url || '/';
  event.waitUntil(clients.openWindow(urlToOpen));
});