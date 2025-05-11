// pages/patient/profile.tsx
import React, { useState, useEffect, ChangeEvent, FormEvent, useCallback } from 'react';
import { useRouter } from 'next/router';
import { motion } from 'framer-motion';
import axios, { AxiosError } from 'axios';
import { auth } from '../../firebase/firebase'; // Adjust path
import { onAuthStateChanged, User as FirebaseUser } from 'firebase/auth';
import {
  MdPerson, MdEmail, MdPhone, MdHistory, MdEdit, MdSave, MdCancel,
  MdErrorOutline, MdRefresh, MdAttachFile, MdClose, MdCheckCircle
} from 'react-icons/md';
import Link from 'next/link';
import Image from 'next/image'; // For file previews

// --- Interfaces ---
interface PatientProfileData { // Data that can be edited
  name: string;
  email: string; // Usually not editable directly, fetched for display
  contact: string;
  medicalHistory: string;
  // Add other editable fields: dateOfBirth, address, etc.
}

interface PatientProfile extends PatientProfileData { // Full profile including IDs
  id: number; // MySQL patient.id
  firebase_uid: string;
}

interface FileData {
  file: File;
  preview: string;
}

interface ApiErrorResponse { message: string; error?: string; errors?: Array<{ field?: string; message: string }>; }

const MAX_FILES_PROFILE = 3; // Limit new files per update
const MAX_FILE_SIZE_MB_PROFILE = 5;

export default function PatientProfilePage() {
  const router = useRouter();
  const [firebaseUser, setFirebaseUser] = useState<FirebaseUser | null>(null);
  const [profile, setProfile] = useState<PatientProfile | null>(null); // Original profile data
  const [editableProfile, setEditableProfile] = useState<Partial<PatientProfileData>>({}); // For form fields
  const [newFiles, setNewFiles] = useState<FileData[]>([]); // For new medical records

  const [isEditing, setIsEditing] = useState(false);
  const [loading, setLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Partial<Record<keyof PatientProfileData | 'files', string>>>({});


  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';

  const fetchProfile = useCallback(async () => {
    if (!auth.currentUser) return;
    setLoading(true); setError(null); setSuccessMessage(null);
    try {
      const token = await auth.currentUser.getIdToken();
      const response = await axios.get<PatientProfile>(`${apiUrl}/api/patients/me`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setProfile(response.data);
      setEditableProfile({ // Initialize edit form with fetched data
        name: response.data.name || '',
        contact: response.data.contact || '',
        medicalHistory: response.data.medicalHistory || '',
        // Email is typically not editable by user directly
      });
    } catch (err) { /* ... error handling ... */ }
    finally { setLoading(false); }
  }, [apiUrl]); // Added apiUrl to dependencies

  useEffect(() => {
    const unsubscribe = onAuthStateChanged(auth, (user) => {
      if (user) {
        setFirebaseUser(user);
        fetchProfile();
      } else {
        router.push('/login?redirect=/patient/profile');
      }
    });
    return () => unsubscribe();
  }, [router, fetchProfile]); // Added fetchProfile

  const handleInputChange = (field: keyof PatientProfileData) => (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setEditableProfile(prev => ({ ...prev, [field]: e.target.value }));
    setFieldErrors(prev => ({ ...prev, [field]: undefined, form: undefined }));
    setError(null); setSuccessMessage(null);
  };

  const validateEditForm = (): boolean => {
    const newErrors: Partial<Record<keyof PatientProfileData | 'files', string>> = {};
    let isValid = true;
    if (!editableProfile.name?.trim()) { newErrors.name = 'Name cannot be empty.'; isValid = false; }
    if (editableProfile.contact?.trim() && !/^\+?\d{10,15}$/.test(editableProfile.contact.replace(/\s+/g, ''))) {
      newErrors.contact = 'Invalid contact number format.'; isValid = false;
    }
    // File validations
    if (newFiles.length > MAX_FILES_PROFILE) { newErrors.files = `Max ${MAX_FILES_PROFILE} new files.`; isValid = false;}
    newFiles.forEach(f => {
        if (f.file.size > MAX_FILE_SIZE_MB_PROFILE * 1024 * 1024) {
            newErrors.files = (newErrors.files || '') + `${f.file.name} > ${MAX_FILE_SIZE_MB_PROFILE}MB. `; isValid = false;
        }
    });
    setFieldErrors(newErrors);
    return isValid;
  };


  const handleFileChange = (e: ChangeEvent<HTMLInputElement>) => {
    // ... (similar file handling logic as patient-registration, adapt for newFiles state)
    const selectedFiles = Array.from(e.target.files || []);
    if (newFiles.length + selectedFiles.length > MAX_FILES_PROFILE) {
        setFieldErrors(prev => ({ ...prev, files: `Cannot upload more than ${MAX_FILES_PROFILE} new files.`}));
        return;
    }
    // ... (type/size validation for each file) ...
    const validatedNewFilesData: FileData[] = [];
    // ...
    selectedFiles.forEach(file => {
      // Basic type/size check
      if (!['image/jpeg', 'image/png', 'application/pdf'].includes(file.type) || file.size > MAX_FILE_SIZE_MB_PROFILE * 1024 * 1024) {
        // Handle individual file error display or a general one
        setFieldErrors(prev => ({...prev, files: `Invalid file ${file.name} (type/size).`}));
        return; // Skip this file
      }
      validatedNewFilesData.push({ file, preview: URL.createObjectURL(file) });
    });
    setNewFiles(prev => [...prev, ...validatedNewFilesData]);
    if (validatedNewFilesData.length === selectedFiles.length) { // All files valid
        setFieldErrors(prev => ({ ...prev, files: undefined }));
    }
  };

  const removeNewFile = (indexToRemove: number) => {
    URL.revokeObjectURL(newFiles[indexToRemove].preview);
    setNewFiles(prev => prev.filter((_, index) => index !== indexToRemove));
    // Clear file error if applicable
  };


  const handleSaveChanges = async (e: FormEvent) => {
    e.preventDefault();
    if (!validateEditForm() || !firebaseUser) return;

    setIsSaving(true); setError(null); setSuccessMessage(null); setFieldErrors({});

    const payload = new FormData();
    if (editableProfile.name !== profile?.name) payload.append('name', editableProfile.name || '');
    if (editableProfile.contact !== profile?.contact) payload.append('contact', editableProfile.contact || '');
    if (editableProfile.medicalHistory !== profile?.medicalHistory) payload.append('medicalHistory', editableProfile.medicalHistory || '');

    newFiles.forEach(fileData => {
      payload.append('medicalRecords', fileData.file, fileData.file.name);
    });

    // Check if there's anything to update besides files
    let hasProfileDataChanges = false;
    for (const key in editableProfile) {
        if (editableProfile[key as keyof PatientProfileData] !== profile?.[key as keyof PatientProfileData]) {
            hasProfileDataChanges = true;
            break;
        }
    }

    if (!hasProfileDataChanges && newFiles.length === 0) {
      setSuccessMessage("No changes detected.");
      setIsEditing(false);
      setIsSaving(false);
      return;
    }


    try {
      const token = await firebaseUser.getIdToken();
      // New API endpoint: PATCH /api/patients/me/update
      const response = await axios.patch(`${apiUrl}/api/patients/me/update`, payload, {
        headers: {
          'Content-Type': 'multipart/form-data',
          Authorization: `Bearer ${token}`,
        },
      });
      setSuccessMessage(response.data.message || "Profile updated successfully!");
      setProfile(prev => ({...prev, ...editableProfile, email: prev!.email, id: prev!.id, firebase_uid: prev!.firebase_uid })); // Update local profile optimistically
      setNewFiles([]); // Clear newly added files queue
      setIsEditing(false);
      // Optionally, call fetchProfile() again to get the very latest from DB
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>;
      console.error("Failed to update profile:", error.response?.data || error.message);
      let errorMessage = error.response?.data?.message || "Could not update profile.";
      if (error.response?.data?.errors) {
        const apiFieldErrors : Partial<Record<keyof PatientProfileData | 'files', string>> = {};
        error.response.data.errors.forEach(e => {
            if (e.field) apiFieldErrors[e.field as keyof PatientProfileData] = e.message;
        });
        setFieldErrors(apiFieldErrors);
        if (Object.values(apiFieldErrors).length > 0) errorMessage = "Please correct the errors above.";
      }
      setError(errorMessage);
    } finally {
      setIsSaving(false);
    }
  };

  const cancelEdit = () => {
    if (profile) { // Reset form to original profile data
        setEditableProfile({
            name: profile.name || '',
            contact: profile.contact || '',
            medicalHistory: profile.medicalHistory || '',
        });
    }
    setNewFiles([]); // Clear any staged new files
    setFieldErrors({});
    setError(null);
    setIsEditing(false);
  };

  // Cleanup Object URLs for newFiles
  useEffect(() => {
    const currentNewFiles = newFiles;
    return () => { currentNewFiles.forEach(f => URL.revokeObjectURL(f.preview)); };
  }, [newFiles]);


  if (loading || !firebaseUser) { /* ... Main Loading Spinner ... */ }
  if (!profile && !loading) { /* ... No profile data / Critical error display ... */ }
  if (!profile) return null; // Should be caught by above


  return (
    <div className="min-h-screen bg-gray-100 p-4 sm:p-6 lg:p-8">
      <div className="max-w-2xl mx-auto">
        <header className="mb-8 flex justify-between items-center">
          <h1 className="text-2xl sm:text-3xl font-bold text-blue-700">My Profile</h1>
          {!isEditing && (
            <button onClick={() => { setIsEditing(true); setError(null); setSuccessMessage(null); }}
              className="flex items-center gap-2 px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition-colors">
              <MdEdit /> Edit Profile
            </button>
          )}
        </header>

        {successMessage && (
             <motion.div initial={{opacity:0}} animate={{opacity:1}} className="p-3 mb-4 rounded-md text-sm bg-green-100 border border-green-300 text-green-700 flex items-center gap-2">
                <MdCheckCircle className="text-lg mt-px flex-shrink-0" /> <p>{successMessage}</p>
            </motion.div>
        )}
        {error && (
            <motion.div initial={{opacity:0}} animate={{opacity:1}} className="p-3 mb-4 rounded-md text-sm bg-red-100 border border-red-300 text-red-700 flex items-center gap-2">
                <MdErrorOutline className="text-lg mt-px flex-shrink-0" /> <p className="flex-1 whitespace-pre-line">{error}</p>
                 <button onClick={fetchProfile} className="ml-auto p-1 text-red-500 hover:text-red-700"><MdRefresh size={18}/></button>
            </motion.div>
        )}


        <motion.div layout className="bg-white shadow-xl rounded-lg p-6 sm:p-8">
          <form onSubmit={handleSaveChanges}>
            <div className="space-y-5">
              {/* Name */}
              <div>
                <label htmlFor="name" className="block text-xs font-medium text-gray-500">Full Name</label>
                {isEditing ? (
                  <>
                  <div className="mt-1 relative">
                    <MdPerson className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={18}/>
                    <input type="text" name="name" id="name" value={editableProfile.name || ''}
                           onChange={handleInputChange('name')}
                           className={`w-full p-2.5 pl-10 border rounded-md shadow-sm text-sm ${fieldErrors.name ? 'border-red-500 ring-red-200' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'}`} />
                  </div>
                  {fieldErrors.name && <p className="text-red-500 text-xs mt-1">{fieldErrors.name}</p>}
                  </>
                ) : (
                  <p className="text-gray-800 text-base sm:text-lg font-medium">{profile.name}</p>
                )}
              </div>

              {/* Email (Display Only) */}
              <div>
                <label className="block text-xs font-medium text-gray-500">Email Address</label>
                <div className="mt-1 flex items-center gap-2">
                    <MdEmail className="text-lg text-gray-400"/>
                    <p className="text-gray-700 text-sm sm:text-base">{profile.email}</p>
                </div>
              </div>

              {/* Contact */}
              <div>
                <label htmlFor="contact" className="block text-xs font-medium text-gray-500">Contact Number</label>
                {isEditing ? (
                  <>
                  <div className="mt-1 relative">
                     <MdPhone className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={18}/>
                    <input type="tel" name="contact" id="contact" value={editableProfile.contact || ''}
                           onChange={handleInputChange('contact')} placeholder="Your phone number"
                           className={`w-full p-2.5 pl-10 border rounded-md shadow-sm text-sm ${fieldErrors.contact ? 'border-red-500 ring-red-200' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'}`} />
                  </div>
                  {fieldErrors.contact && <p className="text-red-500 text-xs mt-1">{fieldErrors.contact}</p>}
                  </>
                ) : (
                  <p className="text-gray-800 text-sm sm:text-base">{profile.contact || <span className="text-gray-400 italic">Not provided</span>}</p>
                )}
              </div>

              {/* Medical History */}
              <div>
                <label htmlFor="medicalHistory" className="block text-xs font-medium text-gray-500">Medical History</label>
                {isEditing ? (
                   <>
                  <div className="mt-1 relative">
                     <MdHistory className="absolute left-3 top-3 text-gray-400" size={18}/> {/* Adjusted icon position for textarea */}
                    <textarea name="medicalHistory" id="medicalHistory" value={editableProfile.medicalHistory || ''}
                              onChange={handleInputChange('medicalHistory')} rows={4} placeholder="Allergies, past conditions, etc."
                              className={`w-full p-2.5 pl-10 border rounded-md shadow-sm text-sm resize-none ${fieldErrors.medicalHistory ? 'border-red-500 ring-red-200' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'}`} />
                  </div>
                   {fieldErrors.medicalHistory && <p className="text-red-500 text-xs mt-1">{fieldErrors.medicalHistory}</p>}
                  </>
                ) : (
                  profile.medicalHistory ?
                    <p className="text-gray-700 text-sm sm:text-base whitespace-pre-line bg-gray-50 p-3 rounded-md mt-1">{profile.medicalHistory}</p>
                    : <p className="text-gray-400 italic text-sm sm:text-base mt-1">No medical history provided.</p>
                )}
              </div>

              {/* Existing Medical Records (Display Only - Deletion/Management is more complex) */}
              {/* You would fetch existing medical records via an API and display them here if needed */}
              {/* For now, we only handle adding NEW records during edit mode */}

              {/* File Upload for NEW Medical Records (in Edit Mode) */}
              {isEditing && (
                <div>
                  <label className="block text-xs font-medium text-gray-500 mb-1">Add New Medical Records</label>
                  <label htmlFor="newFileUpload" className="mt-1 flex justify-center px-6 py-4 border-2 border-gray-300 border-dashed rounded-md cursor-pointer hover:border-blue-400 transition-colors">
                    <div className="space-y-1 text-center">
                      <MdAttachFile className="mx-auto h-8 w-8 text-gray-400" />
                      <div className="flex text-xs text-gray-600">
                        <p className="pl-1">Drag & drop or <span className="text-blue-600 font-medium">click to upload</span></p>
                      </div>
                      <p className="text-xs text-gray-500">Max {MAX_FILES_PROFILE} files • PDF, JPG, PNG up to {MAX_FILE_SIZE_MB_PROFILE}MB</p>
                    </div>
                    <input id="newFileUpload" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" onChange={handleFileChange} className="sr-only" />
                  </label>
                  {fieldErrors.files && <p className="text-red-500 text-xs mt-1 ml-1 whitespace-pre-line">{fieldErrors.files}</p>}
                  {newFiles.length > 0 && (
                    <div className="mt-2 space-y-1.5">
                      <h4 className="text-xs font-medium text-gray-600">New files to upload:</h4>
                      {newFiles.map((fileData, index) => (
                        <div key={index} className="flex items-center justify-between p-1.5 bg-gray-100 rounded border border-gray-200 text-xs">
                          {/* ... file preview ... */}
                           <div className="flex items-center gap-1.5 overflow-hidden"> {fileData.file.type.startsWith('image/') ? <Image src={fileData.preview} alt={fileData.file.name} width={24} height={24} className="rounded object-cover flex-shrink-0" /> : <MdAttachFile className="text-gray-500 flex-shrink-0" size={16} />} <span className="text-gray-700 truncate" title={fileData.file.name}>{fileData.file.name}</span> <span className="text-gray-500 flex-shrink-0">({(fileData.file.size / 1024 / 1024).toFixed(2)} MB)</span> </div>
                           <button type="button" onClick={() => removeNewFile(index)} className="p-0.5 text-red-500 hover:text-red-700 rounded-full hover:bg-red-100 transition-colors"> <MdClose size={14} /> </button>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              )}
            </div>

            {isEditing && (
              <div className="mt-8 pt-6 border-t border-gray-200 flex flex-col sm:flex-row justify-end gap-3">
                <button type="button" onClick={cancelEdit} disabled={isSaving}
                  className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 disabled:opacity-50">
                  Cancel
                </button>
                <button type="submit" disabled={isSaving}
                  className="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50 flex items-center justify-center gap-1.5"
                >
                  {isSaving ? (
                    <><div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Saving...</>
                  ) : (
                    <><MdSave /> Save Changes</>
                  )}
                </button>
              </div>
            )}
          </form>
        </motion.div>

         <div className="mt-8 text-center">
            <Link href="/patient/dashboard" className="text-sm text-blue-600 hover:underline">
                ← Back to Dashboard
            </Link>
        </div>
      </div>
    </div>
  );
}