// firebase/firebase.ts
import { initializeApp, getApps, getApp, FirebaseApp } from 'firebase/app';
import { getAuth, GoogleAuthProvider, Auth } from 'firebase/auth';

console.log("!!!!!!!! USING HARDCODED FIREBASE CONFIG FOR DEBUGGING !!!!!!!");

// firebase/firebase.ts
// ...
const firebaseConfig = {
  apiKey: process.env.NEXT_PUBLIC_FIREBASE_API_KEY,
  authDomain: process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN,
  projectId: process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID,
  storageBucket: process.env.NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: process.env.NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID,
  appId: process.env.NEXT_PUBLIC_FIREBASE_APP_ID,
  // measurementId: process.env.NEXT_PUBLIC_FIREBASE_MEASUREMENT_ID,
};
// ...

let app: FirebaseApp;
let auth: Auth;

if (!getApps().length) {
  try {
    app = initializeApp(firebaseConfig);
  } catch (error) {
    console.error("Firebase initialization error (hardcoded config):", error);
    throw new Error("Failed to initialize Firebase with hardcoded config. Error: " + (error as Error).message);
  }
} else {
  app = getApp();
}

try {
  auth = getAuth(app);
} catch (error) {
  console.error("Firebase getAuth error (hardcoded config):", error);
  throw new Error("Failed to get Firebase Auth instance with hardcoded config. Error: " + (error as Error).message);
}

const googleProvider = new GoogleAuthProvider();
export { app, auth, googleProvider };