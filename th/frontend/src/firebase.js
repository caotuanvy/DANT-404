import { initializeApp } from 'firebase/app';
import { getMessaging, getToken } from 'firebase/messaging';

const firebaseConfig = {
  apiKey: "AIzaSyBcwCHZYnH_ZnXxhHgsL9WRDpdz7CnvAOo",
  authDomain: "datn-70923.firebaseapp.com",
  projectId: "datn-70923",
  storageBucket: "datn-70923.firebasestorage.app",
  messagingSenderId: "588484906208",
  appId: "1:588484906208:web:f035cebd2ff5c2293fd485"
};

const firebaseApp = initializeApp(firebaseConfig);
const messaging = getMessaging(firebaseApp);
export { firebaseApp, messaging, getToken };