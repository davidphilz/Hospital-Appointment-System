// pages/patient-registration.tsx
// This page is intended for an ADMIN to register new patients.
// It will create a Firebase user with a default password and link to a MySQL patient record.

import React, { useState, useEffect, ChangeEvent, FormEvent, useCallback } from 'react';
import { motion } from 'framer-motion';
import {
  MdPerson, MdPhone, MdEmail, MdMedicalServices, MdAttachFile,
  MdClose, MdError, MdCheckCircle, MdRefresh
} from 'react-icons/md';
import axios, { AxiosError } from 'axios';
import { useRouter } from 'next/router';
import Image from 'next/image';
import { auth } from '../firebase/firebase'; // For getting admin's ID token

// --- Interfaces ---
interface FileData {
  file: File;
  preview: string;
}

interface PatientFormData {
  name: string;
  contact: string;
  email: string;
  medicalHistory: string;
}

interface ApiErrorResponse {
  message: string;
  error?: string;
  errors?: Array<{ field?: string; message: string }>;
}

// --- Constants ---
const MAX_FILES = 5;
const MAX_FILE_SIZE_MB = 10;

const AdminPatientRegistrationPage = () => {
  const router = useRouter();
  const [formData, setFormData] = useState<PatientFormData>({
    name: '',
    contact: '',
    email: '',
    medicalHistory: '',
  });
  const [files, setFiles] = useState<FileData[]>([]);
  const [errors, setErrors] = useState<Partial<Record<keyof PatientFormData | 'files' | 'form', string>>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submissionStatus, setSubmissionStatus] = useState<'success' | 'error' | null>(null);
  const [submissionMessage, setSubmissionMessage] = useState<string | null>(null);

  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';

  const handleInputChange = useCallback((field: keyof PatientFormData) => (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData(prev => ({ ...prev, [field]: e.target.value }));
    setErrors(prev => ({ ...prev, [field]: undefined, form: undefined })); // Clear field and form error
    setSubmissionStatus(null);
  }, []);

  const validateForm = useCallback((): boolean => {
    const newErrors: Partial<Record<keyof PatientFormData | 'files', string>> = {};
    let isValid = true;

    if (!formData.name.trim()) { newErrors.name = 'Patient full name is required.'; isValid = false; }
    if (!formData.email.trim()) { newErrors.email = 'Patient email address is required.'; isValid = false; }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) { newErrors.email = 'Please enter a valid email address.'; isValid = false;}

    // Contact can be optional for admin registration, default applied by backend, but validate if entered
    if (formData.contact.trim() && !/^\+?\d{10,15}$/.test(formData.contact.replace(/\s+/g, ''))) {
      newErrors.contact = 'Please enter a valid contact number (10-15 digits, optionally starting with +).';
      isValid = false;
    }

    if (files.length > MAX_FILES) {
        newErrors.files = `You can upload a maximum of ${MAX_FILES} files.`;
        isValid = false;
    }
    files.forEach(fileData => {
        if (fileData.file.size > MAX_FILE_SIZE_MB * 1024 * 1024) {
            newErrors.files = (newErrors.files ? newErrors.files + '\n' : '') +
                             `Each file must be ${MAX_FILE_SIZE_MB}MB or less. ${fileData.file.name} is too large.`;
            isValid = false;
        }
    });

    setErrors(newErrors);
    return isValid;
  }, [formData, files]);

  const handleFileChange = useCallback((e: ChangeEvent<HTMLInputElement>) => {
    const selectedFiles = Array.from(e.target.files || []);
    if (files.length + selectedFiles.length > MAX_FILES) {
        setErrors(prev => ({ ...prev, files: `Cannot upload more than ${MAX_FILES} files in total.`}));
        return;
    }

    const newFileErrors: string[] = [];
    const newFilesData: FileData[] = [];

    selectedFiles.forEach(file => {
      if (!['image/jpeg', 'image/png', 'application/pdf'].includes(file.type)) {
        newFileErrors.push(`Invalid file type: ${file.name}. Only JPG, PNG, PDF allowed.`);
        return;
      }
      if (file.size > MAX_FILE_SIZE_MB * 1024 * 1024) {
        newFileErrors.push(`File too large: ${file.name} (max ${MAX_FILE_SIZE_MB}MB).`);
        return;
      }
      newFilesData.push({ file, preview: URL.createObjectURL(file) });
    });

    if (newFileErrors.length > 0) {
        setErrors(prev => ({ ...prev, files: newFileErrors.join('\n') }));
    } else {
        setErrors(prev => ({ ...prev, files: undefined }));
    }
    setFiles(prev => [...prev, ...newFilesData]);
  }, [files]); // Added files to dependency array

  const removeFile = useCallback((indexToRemove: number) => {
    URL.revokeObjectURL(files[indexToRemove].preview);
    setFiles(prev => prev.filter((_, index) => index !== indexToRemove));
    if (errors.files?.includes("maximum") && (files.length -1 <= MAX_FILES) ) {
        setErrors(prev => ({ ...prev, files: undefined }));
    }
  }, [files, errors.files]); // Added files and errors.files

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setSubmissionStatus(null); setSubmissionMessage(null);
    setErrors(prev => ({...prev, form: undefined }));

    if (!validateForm()) return;

    const adminUser = auth.currentUser; // Get current logged-in admin user
    if (!adminUser) {
        setError("Admin authentication error. Please ensure you are logged in as an admin.");
        // router.push('/admin/login'); // Or your admin login path
        return;
    }

    setIsSubmitting(true);
    const payload = new FormData();
    Object.entries(formData).forEach(([key, value]) => {
      payload.append(key, value);
    });
    files.forEach((fileData) => {
      payload.append(`medicalRecords`, fileData.file, fileData.file.name);
    });

    try {
      const token = await adminUser.getIdToken(); // Get current admin's ID token
      const response = await axios.post(`${apiUrl}/api/admin/register-patient`, payload, {
        headers: {
          'Content-Type': 'multipart/form-data',
          'Authorization': `Bearer ${token}` // Admin's token for authorizing this action
        },
      });
      setSubmissionStatus('success');
      setSubmissionMessage(response.data.message || 'Patient registered successfully!');
      // Reset form for next entry
      setFormData({ name: '', contact: '', email: '', medicalHistory: '' });
      files.forEach(file => URL.revokeObjectURL(file.preview));
      setFiles([]);
      setErrors({}); // Clear validation errors

    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>;
      console.error('Admin patient registration failed:', error.response?.data || error.message, error.response?.status);
      setSubmissionStatus('error');
      let errorMessage = 'Failed to register patient. Please try again.';
      if (error.response?.data) {
        const apiErrorData = error.response.data;
        errorMessage = apiErrorData.message || errorMessage;
        if (apiErrorData.errors && apiErrorData.errors.length > 0) {
            const fieldErrorsUpdate: Partial<Record<keyof PatientFormData | 'files' | 'form', string>> = {};
            apiErrorData.errors.forEach(e_1 => {
                if (e_1.field && (Object.keys(formData).includes(e_1.field) || e_1.field === 'files')) {
                    fieldErrorsUpdate[e_1.field as keyof PatientFormData | 'files'] = e_1.message;
                } else {
                    fieldErrorsUpdate.form = (fieldErrorsUpdate.form ? fieldErrorsUpdate.form + '\n' : '') + e_1.message;
                }
            });
            setErrors(prev_1 => ({ ...prev_1, ...fieldErrorsUpdate}));
            if (fieldErrorsUpdate.form) errorMessage = fieldErrorsUpdate.form;
        } else if(apiErrorData.error) {
            errorMessage += `\nDetails: ${apiErrorData.error}`;
        }
      }
      setSubmissionMessage(errorMessage);
      if (!Object.values(errors).some(val => val)) { // If no specific field errors, set general form error
         setErrors(prev => ({ ...prev, form: errorMessage }));
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  // Cleanup Object URLs on component unmount
  useEffect(() => {
    const currentFiles = files; // Capture files in a variable accessible by the cleanup function's closure
    return () => {
      // console.log("Cleaning up object URLs for files:", currentFiles.length);
      currentFiles.forEach(fileData => {
        if (fileData.preview) {
          URL.revokeObjectURL(fileData.preview);
        }
      });
    };
  }, [files]); // This effect depends on the files array

  return (
    <div className="min-h-screen flex flex-col md:flex-row">
      {/* Left panel with illustration */}
      <div
        className="hidden md:flex w-1/2 bg-blue-700 items-center justify-center relative"
        style={{
          backgroundImage: 'url(/freepik__upload__74489.png)', // Ensure this image is in public folder
          backgroundSize: 'cover',
          backgroundPosition: 'center center', // Changed to center center
        }}
      >
        <div className="flex flex-col justify-center text-white p-10 absolute inset-0 bg-blue-800/50 backdrop-blur-sm text-center"> {/* Adjusted overlay */}
          <motion.h2
            initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.2 }}
            className="text-4xl lg:text-5xl font-bold mb-4"
          >
            Admin Portal
          </motion.h2>
          <motion.p
            initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.4 }}
            className="text-lg lg:text-xl mb-6"
          >
            Efficiently register new patients into the system.
          </motion.p>
        </div>
      </div>

      {/* Right panel with form */}
      <motion.div
        initial={{ opacity: 0, x: 20 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ duration: 0.5 }}
        className="flex flex-col justify-center w-full md:w-1/2 p-6 sm:p-8 bg-gray-50 overflow-y-auto"
      >
        <div className="max-w-md w-full mx-auto">
          <h1 className="text-2xl sm:text-3xl font-bold text-gray-800 mb-6 text-center md:text-left">
            Register New Patient 
          </h1>

          {/* Submission Status & Error Messages */}
          {submissionStatus && submissionMessage && (
            <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1}}
              className={`p-3 mb-4 rounded-md text-sm flex items-start gap-2
                ${submissionStatus === 'success' ? 'bg-green-100 border border-green-300 text-green-700' : 'bg-red-100 border border-red-300 text-red-700'} `}
            >
              {submissionStatus === 'success' ? <MdCheckCircle className="text-lg mt-px flex-shrink-0" /> : <MdError className="text-lg mt-px flex-shrink-0" />}
              <p className="flex-1 whitespace-pre-line">{submissionMessage}</p>
            </motion.div>
          )}
          {errors.form && !submissionStatus && (
              <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1}}
                  className="p-3 mb-4 rounded-md text-sm bg-red-100 border border-red-300 text-red-700 flex items-start gap-2"
              >
                  <MdError className="text-lg mt-px flex-shrink-0" />
                  <p className="flex-1 whitespace-pre-line">{errors.form}</p>
              </motion.div>
          )}

          <form onSubmit={handleSubmit} className="space-y-5">
            {/* Full Name Field */}
            <div className="relative">
              <label htmlFor="name" className="block text-xs font-medium text-gray-600 mb-1">Patient's Full Name <span className="text-red-500">*</span></label>
              <div className="relative">
                <MdPerson className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" size={18} />
                <input id="name" type="text" name="name" value={formData.name} onChange={handleInputChange('name')} placeholder="e.g., John Doe" required
                  className={`w-full p-3 pl-10 border rounded-lg focus:outline-none focus:ring-2 shadow-sm text-sm ${errors.name ? 'border-red-500 ring-red-200' : 'border-gray-300 focus:ring-blue-500 focus:border-transparent'}`} />
              </div>
              {errors.name && <p className="text-red-500 text-xs mt-1 ml-1">{errors.name}</p>}
            </div>

            {/* Email Address Field */}
            <div className="relative">
              <label htmlFor="email" className="block text-xs font-medium text-gray-600 mb-1">Patient's Email Address <span className="text-red-500">*</span></label>
              <div className="relative">
                <MdEmail className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" size={18} />
                <input id="email" type="email" name="email" value={formData.email} onChange={handleInputChange('email')} placeholder="patient@example.com" required
                  className={`w-full p-3 pl-10 border rounded-lg focus:outline-none focus:ring-2 shadow-sm text-sm ${errors.email ? 'border-red-500 ring-red-200' : 'border-gray-300 focus:ring-blue-500 focus:border-transparent'}`} />
              </div>
              {errors.email && <p className="text-red-500 text-xs mt-1 ml-1">{errors.email}</p>}
            </div>

            {/* Contact Number Field */}
            <div className="relative">
              <label htmlFor="contact" className="block text-xs font-medium text-gray-600 mb-1">Patient's Contact (Optional)</label>
              <div className="relative">
                <MdPhone className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" size={18} />
                <input id="contact" type="tel" name="contact" value={formData.contact} onChange={handleInputChange('contact')} placeholder="e.g., 08012345678"
                  className={`w-full p-3 pl-10 border rounded-lg focus:outline-none focus:ring-2 shadow-sm text-sm ${errors.contact ? 'border-red-500 ring-red-200' : 'border-gray-300 focus:ring-blue-500 focus:border-transparent'}`} />
              </div>
              {errors.contact && <p className="text-red-500 text-xs mt-1 ml-1">{errors.contact}</p>}
            </div>

            {/* Medical History Field */}
            <div className="relative">
              <label htmlFor="medicalHistory" className="block text-xs font-medium text-gray-600 mb-1">Medical History (Optional)</label>
              <div className="relative">
                <MdMedicalServices className="absolute left-3 top-3.5 text-gray-400 pointer-events-none" size={18} />
                <textarea id="medicalHistory" name="medicalHistory" value={formData.medicalHistory} onChange={handleInputChange('medicalHistory')} placeholder="List any allergies, past surgeries, chronic conditions, etc." rows={3}
                  className={`w-full p-3 pl-10 border rounded-lg focus:outline-none focus:ring-2 resize-none shadow-sm text-sm ${errors.medicalHistory ? 'border-red-500 ring-red-200' : 'border-gray-300 focus:ring-blue-500 focus:border-transparent'}`} />
              </div>
            </div>

            {/* File Upload Field */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                <MdAttachFile className="mr-1.5 text-blue-600" size={18} /> Medical Records (Optional)
              </label>
              <label htmlFor="fileUpload" className="mt-1 flex justify-center px-6 py-4 border-2 border-gray-300 border-dashed rounded-md cursor-pointer hover:border-blue-400 transition-colors">
                <div className="space-y-1 text-center">
                  <MdAttachFile className="mx-auto h-8 w-8 text-gray-400" />
                  <div className="flex text-xs text-gray-600">
                    <p className="pl-1">Drag & drop files or <span className="text-blue-600 font-medium">click to upload</span></p>
                  </div>
                  <p className="text-xs text-gray-500">Max {MAX_FILES} files • PDF, JPG, PNG up to {MAX_FILE_SIZE_MB}MB</p>
                </div>
                <input id="fileUpload" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" onChange={handleFileChange} className="sr-only" />
              </label>
              {errors.files && <p className="text-red-500 text-xs mt-1 ml-1 whitespace-pre-line">{errors.files}</p>}
              {files.length > 0 && (
                <div className="mt-2 space-y-1.5">
                  {files.map((fileData, index) => (
                    <div key={index} className="flex items-center justify-between p-1.5 bg-gray-100 rounded border border-gray-200 text-xs">
                      <div className="flex items-center gap-1.5 overflow-hidden">
                        {fileData.file.type.startsWith('image/') ? (
                          <Image src={fileData.preview} alt={fileData.file.name} width={24} height={24} className="rounded object-cover flex-shrink-0" />
                        ) : (
                          <MdAttachFile className="text-gray-500 flex-shrink-0" size={16} />
                        )}
                        <span className="text-gray-700 truncate" title={fileData.file.name}>{fileData.file.name}</span>
                        <span className="text-gray-500 flex-shrink-0">({(fileData.file.size / 1024 / 1024).toFixed(2)} MB)</span>
                      </div>
                      <button type="button" onClick={() => removeFile(index)} className="p-0.5 text-red-500 hover:text-red-700 rounded-full hover:bg-red-100 transition-colors">
                        <MdClose size={14} />
                      </button>
                    </div>
                  ))}
                </div>
              )}
            </div>

            <button
              type="submit"
              disabled={isSubmitting}
              className="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold flex items-center justify-center relative shadow-md"
            >
              {isSubmitting ? (
                <>
                  <span className="invisible">Register Patient</span>
                  <div className="absolute inset-0 flex items-center justify-center">
                    <div className="animate-spin rounded-full h-5 w-5 border-4 border-white border-t-transparent"></div>
                  </div>
                </>
              ) : (
                'Register Patient (Admin)'
              )}
            </button>
          </form>
        </div>
      </motion.div>
    </div>
  );
};

export default AdminPatientRegistrationPage;