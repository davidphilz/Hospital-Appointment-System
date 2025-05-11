// lib/firebaseAdmin.ts
import * as admin from 'firebase-admin'; // This import will now resolve

if (!admin.apps.length) {
  try {
    // Ensure these environment variables are correctly set in your .env.local
    // and are NOT prefixed with NEXT_PUBLIC_
    const privateKey = (process.env.FIREBASE_PRIVATE_KEY_ADMIN || '').replace(/\\n/g, '\n');

    if (!process.env.FIREBASE_PROJECT_ID_ADMIN || !process.env.FIREBASE_CLIENT_EMAIL_ADMIN || !privateKey) {
        console.error("FATAL: Missing Firebase Admin SDK credentials in environment variables (FIREBASE_PROJECT_ID_ADMIN, FIREBASE_CLIENT_EMAIL_ADMIN, FIREBASE_PRIVATE_KEY_ADMIN).");
        // In a production app, you might throw an error or handle this more gracefully.
        // The initializeApp call below will likely fail if these are missing.
    }

    admin.initializeApp({
      credential: admin.credential.cert({
        projectId: process.env.FIREBASE_PROJECT_ID_ADMIN,
        clientEmail: process.env.FIREBASE_CLIENT_EMAIL_ADMIN,
        privateKey: privateKey,
      }),
      // databaseURL: `https://${process.env.FIREBASE_PROJECT_ID_ADMIN}.firebaseio.com` // If using Realtime DB
    });
    console.log('Firebase Admin SDK Initialized successfully.');
  } catch (error: any) {
    console.error('Firebase Admin SDK initialization error:', error.message);
    // This could be due to malformed credentials or other issues.
  }
}

export default admin;