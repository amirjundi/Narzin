import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import { toast } from 'react-toastify';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Camera, 
  Save, 
  Mail, 
  Lock,
  Bell,
  Smartphone,
  Laptop,
  Server,
  AlertCircle,
  X,
  Check,
  ExternalLink,
  Clock,
  Shield,
  Loader
} from 'lucide-react';
import { fetchProfile, updateProfile, resetStatus } from '../../../Store/slices/ProfileSlice';

// In a real app, you would have an action to revoke devices
const revokeDevice = (id) => {
  return async (dispatch) => {
    try {
      // API call would go here
      return { success: true };
    } catch (error) {
      throw error;
    }
  };
};

const MyAccount = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  
  // Redux state
  const { 
    user, 
    devices,
    status, 
    error,
    updateStatus,
    updateError,
    emailVerificationNeeded
  } = useSelector(state => state.profile);

  // Local state for form data
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    current_password: '',
    password: '',
    password_confirmation: ''
  });

  // State for input touched status
  const [touched, setTouched] = useState({
    current_password: false,
    password: false,
    password_confirmation: false
  });

  // State for loading and device management
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isRevoking, setIsRevoking] = useState(false);
  const [revokeTargetId, setRevokeTargetId] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [deviceToRevoke, setDeviceToRevoke] = useState(null);

  // Notification preferences (we'll assume these come from another API endpoint in real app)
  const [notifications, setNotifications] = useState({
    push: true
  });

  // Fetch profile data on component mount
  useEffect(() => {
    dispatch(fetchProfile());
  }, [dispatch]);

  // Update local form data when user data is fetched
  useEffect(() => {
    if (user) {
      setFormData(prev => ({
        ...prev,
        name: user.name || '',
        email: user.email || ''
      }));
    }
  }, [user]);

  // Handle notifications for update status
  useEffect(() => {
    if (updateStatus === 'succeeded') {
      toast.success(t('myaccount.profileUpdatedSuccess'));
      
      // Show additional notification if email verification is needed
      if (emailVerificationNeeded) {
        toast.info(t('myaccount.emailVerificationNeeded'));
      }
      
      // Reset password fields
      setFormData(prev => ({
        ...prev,
        current_password: '',
        password: '',
        password_confirmation: ''
      }));
      
      // Reset touched state
      setTouched({
        current_password: false,
        password: false,
        password_confirmation: false
      });
      
      setIsSubmitting(false);
      dispatch(resetStatus());
    } else if (updateStatus === 'failed') {
      toast.error(updateError || t('myaccount.updateProfileError'));
      setIsSubmitting(false);
      dispatch(resetStatus());
    }
  }, [updateStatus, updateError, emailVerificationNeeded, dispatch, t]);

  // Input change handler
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  // Input blur handler to track touched state
  const handleBlur = (e) => {
    const { name } = e.target;
    if (Object.keys(touched).includes(name)) {
      setTouched(prev => ({
        ...prev,
        [name]: true
      }));
    }
  };

  // Toggle notification settings
  const toggleNotification = (key) => {
    setNotifications(prev => ({
      ...prev,
      [key]: !prev[key]
    }));
  };

  // Form validation logic
  const validateForm = () => {
    // Password validation
    if (formData.password && formData.password.length < 8) {
      toast.error(t('myaccount.passwordLengthError'));
      return false;
    }
    
    // Check if password confirmation matches
    if (formData.password && formData.password !== formData.password_confirmation) {
      toast.error(t('myaccount.passwordMatchError'));
      return false;
    }
    
    // Check if current password is provided when changing password
    if (formData.password && !formData.current_password) {
      toast.error(t('myaccount.currentPasswordRequired'));
      return false;
    }
    
    // Check if password confirmation is provided when changing password
    if (formData.password && !formData.password_confirmation) {
      toast.error(t('myaccount.confirmPasswordRequired'));
      return false;
    }
    
    return true;
  };

  // Handle form submission
  const handleSubmit = (e) => {
    e.preventDefault();
    
    // Validate form
    if (!validateForm()) {
      return;
    }
    
    setIsSubmitting(true);
    
    // Prepare data for submission - only include changed fields
    const submissionData = {};
    
    if (formData.name !== user.name) {
      submissionData.name = formData.name;
    }
    
    if (formData.email !== user.email) {
      submissionData.email = formData.email;
    }
    
    // Only include password fields if a new password is being set
    if (formData.password) {
      submissionData.current_password = formData.current_password;
      submissionData.password = formData.password;
      submissionData.password_confirmation = formData.password_confirmation;
    }
    
    // Only submit if there are changes
    if (Object.keys(submissionData).length > 0) {
      dispatch(updateProfile(submissionData));
    } else {
      toast.info(t('myaccount.noChanges'));
      setIsSubmitting(false);
    }
  };

  // Get initials for profile avatar
  const getInitials = () => {
    if (!user || !user.name) return '';
    
    const names = user.name.split(' ');
    if (names.length >= 2) {
      return `${names[0][0]}${names[1][0]}`.toUpperCase();
    }
    
    return names[0][0].toUpperCase();
  };
  
  // Device related functions
  const getDeviceIcon = (deviceInfo) => {
    const deviceType = deviceInfo?.device_type?.toLowerCase();
    
    if (deviceType?.includes('mobile') || deviceType?.includes('phone')) {
      return <Smartphone className="w-6 h-6 text-gray-600" />;
    } else if (deviceType?.includes('desktop') || deviceType?.includes('laptop')) {
      return <Laptop className="w-6 h-6 text-gray-600" />;
    } else {
      return <Server className="w-6 h-6 text-gray-600" />;
    }
  };

  const handleRevokeClick = (device) => {
    setDeviceToRevoke(device);
    setShowConfirmModal(true);
  };

  const handleConfirmRevoke = async () => {
    if (!deviceToRevoke) return;
    
    const deviceId = deviceToRevoke.id;
    setIsRevoking(true);
    setRevokeTargetId(deviceId);
    
    try {
      await dispatch(revokeDevice(deviceId));
      toast.success(t('myaccount.deviceRevokedSuccess'));
    } catch (error) {
      toast.error(t('myaccount.deviceRevokeError'));
    } finally {
      setIsRevoking(false);
      setRevokeTargetId(null);
      setShowConfirmModal(false);
      setDeviceToRevoke(null);
    }
  };

  // Confirmation Modal for device revocation
  const ConfirmationModal = () => {
    if (!showConfirmModal) return null;
    
    return (
      <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <motion.div
          initial={{ opacity: 0, scale: 0.95 }}
          animate={{ opacity: 1, scale: 1 }}
          exit={{ opacity: 0, scale: 0.95 }}
          className="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6"
        >
          <div className="flex items-start mb-4">
            <div className="flex-shrink-0 mr-3">
              <AlertCircle className="w-6 h-6 text-amber-500" />
            </div>
            <div>
              <h3 className="text-lg font-medium text-gray-900">{t('myaccount.revokeSession')}</h3>
              <p className="mt-1 text-sm text-gray-500">
                {deviceToRevoke?.type === 'API Token' 
                  ? t('myaccount.confirmApiTokenRevoke') 
                  : t('myaccount.confirmSessionRevoke')}
              </p>
              
              {deviceToRevoke?.is_current && (
                <div className="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-md text-sm text-amber-700">
                  <p className="font-medium">{t('myaccount.currentSessionWarning')}</p>
                  <p>{t('myaccount.currentSessionLogoutWarning')}</p>
                </div>
              )}
            </div>
          </div>
          
          <div className="flex justify-end gap-3 mt-5">
            <button
              type="button"
              onClick={() => {
                setShowConfirmModal(false);
                setDeviceToRevoke(null);
              }}
              className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              {t('myaccount.cancel')}
            </button>
            <button
              type="button"
              onClick={handleConfirmRevoke}
              disabled={isRevoking}
              className="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              {isRevoking ? (
                <>
                  <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {t('myaccount.revoking')}
                </>
              ) : (
                t('myaccount.revokeSession')
              )}
            </button>
          </div>
        </motion.div>
      </div>
    );
  };

  // Loading state component
  const LoadingState = () => (
    <div className="min-h-screen flex items-center justify-center">
      <div className="flex flex-col items-center">
        <Loader className="w-12 h-12 text-[#3084C2] animate-spin mb-4" />
        <p className="text-gray-600">{t('myaccount.loadingProfile')}</p>
      </div>
    </div>
  );
  
  // Error state component
  const ErrorState = () => (
    <div className="min-h-screen flex items-center justify-center">
      <div className="text-center max-w-md">
        <AlertCircle className="w-12 h-12 text-red-500 mx-auto mb-4" />
        <h2 className="text-xl font-semibold mb-2">{t('myaccount.errorLoadingProfile')}</h2>
        <p className="text-gray-600 mb-4">{error || t('myaccount.failedLoadProfile')}</p>
        <button
          onClick={() => dispatch(fetchProfile())}
          className="px-4 py-2 bg-[#3084C2] text-white rounded-lg"
        >
          {t('myaccount.tryAgain')}
        </button>
      </div>
    </div>
  );

  // Check if loading
  if (status === 'loading' && !user) {
    return <LoadingState />;
  }
  
  // Check if error
  if (status === 'failed') {
    return <ErrorState />;
  }

  return (
    <div className="max-w-3xl mx-auto pb-12">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-2xl font-bold">{t('myaccount.title')}</h2>
      </div>

      <div className="space-y-8">
        {/* Profile Picture */}
        <div className="flex items-center gap-4">
          <div className="relative">
            <div className="w-24 h-24 rounded-full bg-[#3084C2] flex items-center justify-center text-white text-2xl font-medium">
              {getInitials()}
            </div>
            <button className="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-md">
              <Camera className="w-4 h-4 text-gray-600" />
            </button>
          </div>
          <div>
            <h3 className="font-medium text-lg">{t('myaccount.profilePicture')}</h3>
            <p className="text-sm text-gray-600">
              {t('myaccount.uploadNewPicture')}
            </p>
          </div>
        </div>

        {/* Personal Information Form */}
        <form onSubmit={handleSubmit} className="space-y-8">
          {/* Basic Information */}
          <section>
            <h3 className="font-medium text-lg mb-4">{t('myaccount.basicInformation')}</h3>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                {t('myaccount.fullName')}
              </label>
              <input
                type="text"
                name="name"
                value={formData.name}
                onChange={handleChange}
                className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
              />
            </div>
          </section>

          {/* Contact Information */}
          <section>
            <h3 className="font-medium text-lg mb-4">{t('myaccount.contactInformation')}</h3>
            <div className="space-y-4">
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Mail className="h-5 w-5 text-gray-400" />
                </div>
                <input
                  type="email"
                  name="email"
                  value={formData.email}
                  onChange={handleChange}
                  className="w-full pl-10 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
                />
                {formData.email !== user?.email && (
                  <p className="mt-1 text-sm text-amber-600">
                    {t('myaccount.emailVerificationWarning')}
                  </p>
                )}
              </div>
            </div>
          </section>

          {/* Notification Preferences */}
          <section>
            <h3 className="font-medium text-lg mb-4">{t('myaccount.notificationPreferences')}</h3>
            <div className="space-y-4">
              {[
                { key: 'push', label: t('myaccount.pushNotifications'), icon: Bell },
              ].map(({ key, label, icon: Icon }) => (
                <div
                  key={key}
                  className="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                >
                  <div className="flex items-center gap-3">
                    <Icon className="w-5 h-5 text-gray-600" />
                    <span>{label}</span>
                  </div>
                  <button
                    type="button"
                    onClick={() => toggleNotification(key)}
                    className="relative"
                  >
                    <div
                      className={`w-12 h-6 transition-colors duration-200 rounded-full ${
                        notifications[key] ? 'bg-[#3084C2]' : 'bg-gray-300'
                      }`}
                    >
                      <div
                        className={`absolute top-0.5 left-0.5 w-5 h-5 transition-transform duration-200 transform bg-white rounded-full ${
                          notifications[key] ? 'translate-x-6' : 'translate-x-0'
                        }`}
                      />
                    </div>
                  </button>
                </div>
              ))}
            </div>
          </section>

          {/* Password Change */}
          <section>
            <h3 className="font-medium text-lg mb-4">{t('myaccount.changePassword')}</h3>
            <div className="space-y-4">
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Lock className="h-5 w-5 text-gray-400" />
                </div>
                <input
                  type="password"
                  name="current_password"
                  placeholder={t('myaccount.currentPassword')}
                  value={formData.current_password}
                  onChange={handleChange}
                  onBlur={handleBlur}
                  className={`w-full pl-10 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none ${
                    touched.current_password && formData.password && !formData.current_password 
                      ? 'border-red-500' 
                      : ''
                  }`}
                />
                {touched.current_password && formData.password && !formData.current_password && (
                  <p className="mt-1 text-sm text-red-600">{t('myaccount.currentPasswordRequiredHint')}</p>
                )}
              </div>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Lock className="h-5 w-5 text-gray-400" />
                </div>
                <input
                  type="password"
                  name="password"
                  placeholder={t('myaccount.newPassword')}
                  value={formData.password}
                  onChange={handleChange}
                  onBlur={handleBlur}
                  className="w-full pl-10 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
                />
              </div>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Lock className="h-5 w-5 text-gray-400" />
                </div>
                <input
                  type="password"
                  name="password_confirmation"
                  placeholder={t('myaccount.confirmNewPassword')}
                  value={formData.password_confirmation}
                  onChange={handleChange}
                  onBlur={handleBlur}
                  className={`w-full pl-10 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none ${
                    touched.password_confirmation && formData.password && formData.password !== formData.password_confirmation 
                      ? 'border-red-500' 
                      : ''
                  }`}
                />
                {touched.password_confirmation && formData.password && formData.password !== formData.password_confirmation && (
                  <p className="mt-1 text-sm text-red-600">{t('myaccount.passwordsDoNotMatch')}</p>
                )}
              </div>
            </div>
          </section>

          {/* Save Button */}
          <div className="flex justify-end">
            <button
              type="submit"
              disabled={isSubmitting || updateStatus === 'loading'}
              className="bg-[#3084C2] text-white px-6 py-2 rounded-lg flex items-center gap-2 hover:bg-[#2473b1] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isSubmitting || updateStatus === 'loading' ? (
                <>
                  <Loader className="w-4 h-4 animate-spin" />
                  {t('myaccount.saving')}
                </>
              ) : (
                <>
                  <Save className="w-4 h-4" />
                  {t('myaccount.saveChanges')}
                </>
              )}
            </button>
          </div>
        </form>
      </div>

      {/* Devices Section */}
      {devices && (
        <div className="mt-12 mb-8">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-2xl font-bold">{t('myaccount.activeSessions')}</h2>
            <div className="text-sm text-gray-500">
              {t('myaccount.deviceCount', { count: devices.total_devices })}
            </div>
          </div>
          
          {/* Current Device */}
          <div className="mb-8">
            <h3 className="text-lg font-medium mb-3">{t('myaccount.currentSession')}</h3>
            <div className="bg-blue-50 border border-blue-100 rounded-lg p-4">
              <div className="flex items-start">
                <div className="flex-shrink-0 mr-3">
                  {getDeviceIcon(devices.current_device)}
                </div>
                <div className="flex-1">
                  <div className="flex items-center mb-1">
                    <h4 className="font-medium">{devices.current_device.device_type}</h4>
                    <span className="ml-2 px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full flex items-center">
                      <Check className="w-3 h-3 mr-1" />
                      {t('myaccount.current')}
                    </span>
                  </div>
                  <div className="text-sm text-gray-600">
                    <p>{t('myaccount.browser')}: {devices.current_device.browser}</p>
                    <p>{t('myaccount.os')}: {devices.current_device.os}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          {/* All Sessions */}
          <div className="mb-8">
            <div className="flex justify-between items-center mb-3">
              <h3 className="text-lg font-medium">{t('myaccount.allSessions')}</h3>
              <div className="text-sm text-gray-500 flex items-center">
                <span className="inline-block w-3 h-3 bg-green-500 rounded-full mr-1"></span>
                {t('myaccount.webSessions')}: {devices.web_sessions || 0}
                <span className="inline-block w-3 h-3 bg-purple-500 rounded-full ml-4 mr-1"></span>
                {t('myaccount.apiTokens')}: {devices.api_tokens || 0}
              </div>
            </div>
            
            <div className="space-y-3">
              {devices.all_devices.map((device) => (
                <div 
                  key={device.id} 
                  className={`border rounded-lg p-4 ${device.is_current ? 'bg-blue-50 border-blue-100' : 'bg-white border-gray-200'}`}
                >
                  <div className="flex items-start">
                    <div className="flex-shrink-0 mr-3">
                      {getDeviceIcon(device.device_info)}
                    </div>
                    <div className="flex-1">
                      <div className="flex flex-wrap items-center mb-1">
                        <h4 className="font-medium mr-2">
                          {device.device_info?.device_type || t('myaccount.unknownDevice')}
                        </h4>
                        
                        {device.is_current && (
                          <span className="px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full flex items-center mr-2">
                            <Check className="w-3 h-3 mr-1" />
                            {t('myaccount.current')}
                          </span>
                        )}
                        
                        <span className={`px-2 py-0.5 text-xs rounded-full flex items-center mr-2 ${
                          device.type === 'API Token' 
                            ? 'bg-purple-100 text-purple-800' 
                            : 'bg-green-100 text-green-800'
                        }`}>
                          {device.type === 'API Token' ? (
                            <Shield className="w-3 h-3 mr-1" />
                          ) : (
                            <ExternalLink className="w-3 h-3 mr-1" />
                          )}
                          {device.type === 'API Token' ? t('myaccount.apiToken') : t('myaccount.webSession')}
                        </span>
                        
                        <span className="text-xs text-gray-500 flex items-center">
                          <Clock className="w-3 h-3 mr-1" />
                          {device.type === 'API Token' 
                            ? t('myaccount.created', { time: device.created_at }) 
                            : t('myaccount.lastActive', { time: device.last_activity })}
                        </span>
                      </div>
                      
                      <div className="text-sm text-gray-600">
                        {device.device_info?.browser && <p>{t('myaccount.browser')}: {device.device_info.browser}</p>}
                        {device.device_info?.os && <p>{t('myaccount.os')}: {device.device_info.os}</p>}
                        {device.type === 'API Token' && device.last_used && (
                          <p>{t('myaccount.lastUsed')}: {device.last_used}</p>
                        )}
                      </div>
                    </div>
                    
                    <div className="ml-4">
                      <button
                        onClick={() => handleRevokeClick(device)}
                        disabled={isRevoking && revokeTargetId === device.id}
                        className="text-red-600 hover:text-red-800 disabled:opacity-50 disabled:cursor-not-allowed"
                        aria-label={t('myaccount.revokeSession')}
                      >
                        {isRevoking && revokeTargetId === device.id ? (
                          <svg className="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                          </svg>
                        ) : (
                          <X className="w-5 h-5" />
                        )}
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
          
          <AnimatePresence>
            {showConfirmModal && <ConfirmationModal />}
          </AnimatePresence>
        </div>
      )}
    </div>
  );
};

export default MyAccount;