import React, { useState } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { toast } from 'react-toastify';
import { 
  Smartphone, 
  Laptop, 
  Server, 
  AlertCircle,
  X,
  Check,
  ExternalLink,
  Clock,
  Shield
} from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';

// In a real app, you would have actions to revoke devices
// This is a placeholder for the actual implementation
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

const DevicesSection = () => {
  const dispatch = useDispatch();
  const { devices } = useSelector(state => state.profile);
  const [isRevoking, setIsRevoking] = useState(false);
  const [revokeTargetId, setRevokeTargetId] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [deviceToRevoke, setDeviceToRevoke] = useState(null);

  // If there's no devices data yet
  if (!devices) {
    return null;
  }

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
      toast.success('Device session revoked successfully');
    } catch (error) {
      toast.error('Failed to revoke device session');
    } finally {
      setIsRevoking(false);
      setRevokeTargetId(null);
      setShowConfirmModal(false);
      setDeviceToRevoke(null);
    }
  };

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
              <h3 className="text-lg font-medium text-gray-900">Revoke Session</h3>
              <p className="mt-1 text-sm text-gray-500">
                Are you sure you want to revoke this {deviceToRevoke?.type === 'API Token' ? 'API token' : 'session'}? 
                This action cannot be undone.
              </p>
              
              {deviceToRevoke?.is_current && (
                <div className="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-md text-sm text-amber-700">
                  <p className="font-medium">Warning: This is your current session</p>
                  <p>You will be logged out if you revoke this session.</p>
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
              Cancel
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
                  Revoking...
                </>
              ) : (
                'Revoke Session'
              )}
            </button>
          </div>
        </motion.div>
      </div>
    );
  };

  return (
    <div className="mt-12 mb-8">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-2xl font-bold">Active Sessions</h2>
        <div className="text-sm text-gray-500">
          {devices.total_devices} devices
        </div>
      </div>
      
      {/* Current Device */}
      <div className="mb-8">
        <h3 className="text-lg font-medium mb-3">Current Session</h3>
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
                  Current
                </span>
              </div>
              <div className="text-sm text-gray-600">
                <p>Browser: {devices.current_device.browser}</p>
                <p>OS: {devices.current_device.os}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      {/* All Sessions */}
      <div className="mb-8">
        <div className="flex justify-between items-center mb-3">
          <h3 className="text-lg font-medium">All Sessions</h3>
          <div className="text-sm text-gray-500 flex items-center">
            <span className="inline-block w-3 h-3 bg-green-500 rounded-full mr-1"></span>
            Web Sessions: {devices.web_sessions || 0}
            <span className="inline-block w-3 h-3 bg-purple-500 rounded-full ml-4 mr-1"></span>
            API Tokens: {devices.api_tokens || 0}
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
                      {device.device_info?.device_type || 'Unknown Device'}
                    </h4>
                    
                    {device.is_current && (
                      <span className="px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full flex items-center mr-2">
                        <Check className="w-3 h-3 mr-1" />
                        Current
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
                      {device.type}
                    </span>
                    
                    <span className="text-xs text-gray-500 flex items-center">
                      <Clock className="w-3 h-3 mr-1" />
                      {device.type === 'API Token' 
                        ? `Created ${device.created_at}` 
                        : `Last active ${device.last_activity}`}
                    </span>
                  </div>
                  
                  <div className="text-sm text-gray-600">
                    {device.device_info?.browser && <p>Browser: {device.device_info.browser}</p>}
                    {device.device_info?.os && <p>OS: {device.device_info.os}</p>}
                    {device.type === 'API Token' && device.last_used && (
                      <p>Last used: {device.last_used}</p>
                    )}
                  </div>
                </div>
                
                <div className="ml-4">
                  <button
                    onClick={() => handleRevokeClick(device)}
                    disabled={isRevoking && revokeTargetId === device.id}
                    className="text-red-600 hover:text-red-800 disabled:opacity-50 disabled:cursor-not-allowed"
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
  );
};

export default DevicesSection;