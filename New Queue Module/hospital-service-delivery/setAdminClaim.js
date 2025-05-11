// setAdminClaim.js
const admin = require('firebase-admin');
const serviceAccount = require('./serviceAccountKey.json'); // REPLACE WITH ACTUAL PATH

admin.initializeApp({
  credential: admin.credential.cert(serviceAccount)
});

// UID of the user you want to make an admin
// You can get this from the Firebase Authentication console for your admin user.
const uidToMakeAdmin = 'RElQdHi0tnf0ZWtvQ6CCpu7gHcd2'; // <--- REPLACE THIS
const newRole = 'admin'; // Or 'staff'

admin.auth().setCustomUserClaims(uidToMakeAdmin, { role: newRole })
  .then(() => {
    console.log(`Successfully set custom claim '{ role: "${newRole}" }' for user: ${uidToMakeAdmin}`);
    // To verify, you can fetch the user record again
    return admin.auth().getUser(uidToMakeAdmin);
  })
  .then((userRecord) => {
    console.log('Updated user claims:', userRecord.customClaims);
    process.exit(0);
  })
  .catch((error) => {
    console.error('Error setting custom claims:', error);
    process.exit(1);
  });