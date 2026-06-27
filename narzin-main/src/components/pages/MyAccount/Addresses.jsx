import React, { useEffect, useState, useCallback, memo } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import { motion, AnimatePresence } from 'framer-motion';
import {
  MapPin,
  Plus,
  Edit2,
  Trash2,
  X,
  Home,
  Building2,
  Check,
  Loader,
  AlertCircle
} from 'lucide-react';
import { 
  fetchAddress, 
  createAddress, 
  updateAddress, 
  deleteAddress, 
  setDefaultAddress,
  resetStatus
} from '../../../Store/slices/AddressSlice';
import { fetchShipping } from '../../../Store/slices/ShippingSlice';
import { toast } from 'react-toastify';

// Memoize the modal component
const AddressModal = memo(({ 
  isOpen, 
  onClose, 
  onSubmit, 
  formData, 
  setFormData, 
  editingAddressId, 
  addressType, 
  setAddressType, 
  isSubmitting, 
  t 
}) => {
  // Create local handlers within the modal to prevent parent re-renders
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleAddressTypeChange = (type) => {
    setAddressType(type);
  };

  const handleDefaultCheckboxChange = (e) => {
    setFormData(prev => ({
      ...prev,
      is_default: e.target.checked ? 1 : 0
    }));
  };

  // Only render when open
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <motion.div
        initial={{ opacity: 0, scale: 0.95 }}
        animate={{ opacity: 1, scale: 1 }}
        exit={{ opacity: 0, scale: 0.95 }}
        className="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto"
      >
        <div className="flex items-center justify-between p-6 border-b">
          <h3 className="text-xl font-semibold">
            {editingAddressId ? t('addresses.editAddress') || 'Edit Address' : t('addresses.addNewAddress') || 'Add New Address'}
          </h3>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600">
            <X className="w-6 h-6" />
          </button>
        </div>

        <form onSubmit={onSubmit} className="p-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {/* Address Type */}
            <div className="md:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                {t('addresses.addressType') || 'Address Type'}
              </label>
              <div className="flex gap-4">
                {['home', 'office'].map(type => (
                  <button
                    key={type}
                    type="button"
                    onClick={() => handleAddressTypeChange(type)}
                    className={`flex items-center gap-2 px-4 py-2 rounded-lg border ${
                      addressType === type
                        ? 'border-[#3084C2] bg-blue-50 text-[#3084C2]'
                        : 'border-gray-200 text-gray-600'
                    }`}
                  >
                    {type === 'home' ? <Home className="w-4 h-4" /> : <Building2 className="w-4 h-4" />}
                    {type === 'home' ? 
                      (t('addresses.homeType') || 'Home') : 
                      (t('addresses.officeType') || 'Office')}
                  </button>
                ))}
              </div>
            </div>

            {/* Address Title */}
            <div className="md:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                {t('addresses.addressTitle') || 'Address Title'}
              </label>
              <input
                type="text"
                name="title"
                value={formData.title}
                onChange={handleInputChange}
                placeholder={t('addresses.addressTitlePlaceholder') || "e.g., Home, Office"}
                className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
              />
            </div>

            {/* Street Address */}
            <div className="md:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                {t('addresses.streetAddress') || 'Street Address'}
              </label>
              <input
                type="text"
                name="address"
                value={formData.address}
                onChange={handleInputChange}
                placeholder={t('addresses.streetAddressPlaceholder') || "Street address"}
                className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
                required
              />
            </div>

            {/* Delivery Zone (Country/Region) */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                {t('addresses.deliveryZone') || 'Country / Region'}
              </label>
              <select
                name="delivery_zone_id"
                value={formData.delivery_zone_id}
                onChange={handleInputChange}
                className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
                required
              >
                <option value="">{t('addresses.selectZone') || 'Select Country/Region'}</option>
                {formData.availableZones && formData.availableZones.map(zone => (
                  <option key={zone.id} value={zone.id}>{zone.name}</option>
                ))}
              </select>
            </div>

            {/* City */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                {t('addresses.city') || 'City'}
              </label>
              <input
                type="text"
                name="city"
                value={formData.city}
                onChange={handleInputChange}
                className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
                required
              />
            </div>

            {/* Postal Code */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                {t('addresses.postalCode') || 'Postal Code'}
              </label>
              <input
                type="text"
                name="postal_code"
                value={formData.postal_code}
                onChange={handleInputChange}
                className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
              />
            </div>

            {/* Phone */}
            <div className="md:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                {t('addresses.phoneNumber') || 'Phone Number'}
              </label>
              <input
                type="tel"
                name="phone_number"
                value={formData.phone_number}
                onChange={handleInputChange}
                className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
                required
              />
            </div>
            
            {/* Default Address Checkbox */}
            <div className="md:col-span-2">
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={formData.is_default === 1}
                  onChange={handleDefaultCheckboxChange}
                  className="w-4 h-4 text-[#3084C2] rounded focus:ring-[#3084C2]"
                />
                <span className="text-sm font-medium text-gray-700">
                  {t('addresses.setAsDefault') || 'Set as default address'}
                </span>
              </label>
            </div>
          </div>

          <div className="mt-6 flex justify-end gap-3">
            <motion.button
              whileHover={{ scale: 1.02 }}
              whileTap={{ scale: 0.98 }}
              type="button"
              onClick={onClose}
              className="px-6 py-2 border rounded-lg text-gray-600"
              disabled={isSubmitting}
            >
              {t('addresses.cancel') || 'Cancel'}
            </motion.button>
            <motion.button
              whileHover={{ scale: 1.02 }}
              whileTap={{ scale: 0.98 }}
              type="submit"
              className="px-6 py-2 bg-[#3084C2] text-white rounded-lg flex items-center gap-2"
              disabled={isSubmitting}
            >
              {isSubmitting ? (
                <>
                  <Loader className="w-4 h-4 animate-spin" />
                  {t('addresses.saving') || 'Saving...'}
                </>
              ) : (
                editingAddressId ? 
                  t('addresses.saveChanges') || 'Save Changes' : 
                  t('addresses.addAddress') || 'Add Address'
              )}
            </motion.button>
          </div>
        </form>
      </motion.div>
    </div>
  );
});

// Memoize the address card component
const AddressCard = memo(({ 
  address, 
  onEdit, 
  onDelete, 
  onSetDefault, 
  isDeleting, 
  deleteTargetId, 
  isSettingDefault,
  defaultTargetId,
  t 
}) => (
  <motion.div
    layout
    className="bg-white border rounded-lg p-4"
  >
    <div className="flex items-start justify-between">
      <div className="flex items-center gap-2">
        {address.title?.toLowerCase().includes('office') ? (
          <Building2 className="w-5 h-5 text-[#3084C2]" />
        ) : (
          <Home className="w-5 h-5 text-[#3084C2]" />
        )}
        <h3 className="font-medium">{address.title || t('addresses.address') || 'Address'}</h3>
        {address.is_default === 1 && (
          <span className="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
            {t('addresses.default') || 'Default'}
          </span>
        )}
      </div>
      <div className="flex items-center gap-2">
        <motion.button
          whileHover={{ scale: 1.1 }}
          whileTap={{ scale: 0.9 }}
          onClick={() => onEdit(address)}
          className="p-1 text-gray-400 hover:text-gray-600"
        >
          <Edit2 className="w-4 h-4" />
        </motion.button>
        {address.is_default !== 1 && (
          <motion.button
            whileHover={{ scale: 1.1 }}
            whileTap={{ scale: 0.9 }}
            onClick={() => onDelete(address.id)}
            disabled={isDeleting && deleteTargetId === address.id}
            className="p-1 text-gray-400 hover:text-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {isDeleting && deleteTargetId === address.id ? (
              <Loader className="w-4 h-4 animate-spin text-red-500" />
            ) : (
              <Trash2 className="w-4 h-4" />
            )}
          </motion.button>
        )}
      </div>
    </div>

    <div className="mt-2 space-y-1 text-gray-600">
      <p>{address.address}</p>
      {address.city && <p>{address.city}</p>}
      {address.postal_code && <p>{address.postal_code}</p>}
      {address.delivery_zone && <p>{address.delivery_zone.name}</p>}
      <p className="text-sm">{address.phone_number}</p>
    </div>

    {address.is_default !== 1 && (
      <motion.button
        whileHover={{ scale: 1.02 }}
        whileTap={{ scale: 0.98 }}
        onClick={() => onSetDefault(address.id)}
        disabled={isSettingDefault}
        className="mt-4 text-[#3084C2] text-sm font-medium hover:text-[#195e8f] flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {isSettingDefault && defaultTargetId === address.id ? (
          <>
            <Loader className="w-4 h-4 animate-spin" />
            {t('addresses.settingDefault') || 'Setting as default...'}
          </>
        ) : (
          <>
            <MapPin className="w-4 h-4" />
            {t('addresses.setAsDefault') || 'Set as Default'}
          </>
        )}
      </motion.button>
    )}
  </motion.div>
));

const AddressComponent = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  
  // Redux state
  const { 
    items: addresses, 
    status, 
    error,
    createStatus,
    updateStatus,
    deleteStatus,
    createError,
    updateError,
    deleteError,
    // Add new selectors for the default address status
    defaultStatus,
    defaultError
  } = useSelector(state => state.address);
  const { shippingPrices: zones } = useSelector((state) => state.shippingPrices);

  // Component state
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingAddressId, setEditingAddressId] = useState(null);
  const [formData, setFormData] = useState({
    title: '',
    delivery_zone_id: '',
    address: '',
    phone_number: '',
    postal_code: '',
    city: '',
    is_default: 0,
    availableZones: []
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);
  const [deleteTargetId, setDeleteTargetId] = useState(null);
  const [isSettingDefault, setIsSettingDefault] = useState(false);
  const [defaultTargetId, setDefaultTargetId] = useState(null);
  const [addressType, setAddressType] = useState('home');
  
  // Fetch addresses and zones on component mount
  useEffect(() => {
    dispatch(fetchAddress());
    dispatch(fetchShipping());
  }, [dispatch]);
  
  // Update available zones when they load
  useEffect(() => {
    if (zones && Array.isArray(zones)) {
      setFormData(prev => ({ ...prev, availableZones: zones }));
    }
  }, [zones]);
  
  // Reset form data when modal closes
  useEffect(() => {
    if (!isModalOpen) {
      setFormData({
        title: '',
        delivery_zone_id: '',
        address: '',
        phone_number: '',
        postal_code: '',
        city: '',
        is_default: 0,
        availableZones: zones || []
      });
      setEditingAddressId(null);
      setAddressType('home');
    }
  }, [isModalOpen]);
  
  // Toast notifications for status changes
  useEffect(() => {
    if (createStatus === 'succeeded') {
      toast.success(t('addresses.addSuccess') || 'Address added successfully');
      setIsModalOpen(false);
      setIsSubmitting(false);
      dispatch(resetStatus());
    } else if (createStatus === 'failed') {
      toast.error(createError || t('addresses.addError') || 'Failed to add address');
      setIsSubmitting(false);
      dispatch(resetStatus());
    }
    
    if (updateStatus === 'succeeded') {
      toast.success(t('addresses.updateSuccess') || 'Address updated successfully');
      setIsModalOpen(false);
      setIsSubmitting(false);
      dispatch(resetStatus());
    } else if (updateStatus === 'failed') {
      toast.error(updateError || t('addresses.updateError') || 'Failed to update address');
      setIsSubmitting(false);
      dispatch(resetStatus());
    }
    
    if (deleteStatus === 'succeeded') {
      toast.success(t('addresses.deleteSuccess') || 'Address deleted successfully');
      setIsDeleting(false);
      setDeleteTargetId(null);
      dispatch(resetStatus());
    } else if (deleteStatus === 'failed') {
      toast.error(deleteError || t('addresses.deleteError') || 'Failed to delete address');
      setIsDeleting(false);
      setDeleteTargetId(null);
      dispatch(resetStatus());
    }
    
    // Add new handler for default address status
    if (defaultStatus === 'succeeded') {
      toast.success(t('addresses.defaultSuccess') || 'Default address set successfully');
      setIsSettingDefault(false);
      setDefaultTargetId(null);
      dispatch(resetStatus());
    } else if (defaultStatus === 'failed') {
      toast.error(defaultError || t('addresses.defaultError') || 'Failed to set default address');
      setIsSettingDefault(false);
      setDefaultTargetId(null);
      dispatch(resetStatus());
    }
  }, [
    createStatus, updateStatus, deleteStatus, defaultStatus,
    createError, updateError, deleteError, defaultError,
    dispatch, t
  ]);

  const openModal = useCallback((address = null) => {
    if (address) {
      setFormData({
        title: address.title || '',
        delivery_zone_id: address.delivery_zone_id || '',
        address: address.address || '',
        phone_number: address.phone_number || '',
        postal_code: address.postal_code || '',
        city: address.city || '',
        is_default: address.is_default,
        availableZones: zones || []
      });
      setEditingAddressId(address.id);
      setAddressType(address.title?.toLowerCase().includes('office') ? 'office' : 'home');
    } else {
      setFormData({
        title: '',
        delivery_zone_id: '',
        address: '',
        phone_number: '',
        postal_code: '',
        city: '',
        is_default: 0,
        availableZones: zones || []
      });
      setEditingAddressId(null);
      setAddressType('home');
    }
    setIsModalOpen(true);
  }, []);

  const closeModal = useCallback(() => {
    setIsModalOpen(false);
  }, []);

  const handleSubmit = useCallback((e) => {
    e.preventDefault();
    setIsSubmitting(true);
    
    // Prepare data for submission
    const submissionData = {
      ...formData,
      title: addressType === 'home' ? 
        (formData.title || 'Home') : 
        (formData.title || 'Office')
    };
    
    if (editingAddressId) {
      dispatch(updateAddress({ 
        id: editingAddressId, 
        addressData: submissionData 
      }));
    } else {
      dispatch(createAddress(submissionData));
    }
  }, [dispatch, formData, addressType, editingAddressId]);

  const handleDelete = useCallback((id) => {
    setIsDeleting(true);
    setDeleteTargetId(id);
    dispatch(deleteAddress(id));
  }, [dispatch]);

  const handleSetDefault = useCallback((id) => {
    setIsSettingDefault(true);
    setDefaultTargetId(id);
    dispatch(setDefaultAddress(id));
  }, [dispatch]);

  // Loading state
  if (status === 'loading' && (!addresses || addresses.length === 0)) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="flex flex-col items-center">
          <Loader className="w-12 h-12 text-[#3084C2] animate-spin mb-4" />
          <p className="text-gray-600">{t('addresses.loading') || 'Loading addresses...'}</p>
        </div>
      </div>
    );
  }
  
  // Error state
  if (status === 'failed') {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center max-w-md">
          <AlertCircle className="w-12 h-12 text-red-500 mx-auto mb-4" />
          <h2 className="text-xl font-semibold mb-2">{t('addresses.loadError') || 'Error Loading Addresses'}</h2>
          <p className="text-gray-600 mb-4">{error || t('addresses.loadErrorMessage')}</p>
          <button
            onClick={() => dispatch(fetchAddress())}
            className="px-4 py-2 bg-[#3084C2] text-white rounded-lg"
          >
            {t('addresses.tryAgain') || 'Try Again'}
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-[1200px] mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-2xl font-bold">{t('addresses.myAddresses') || 'My Addresses'}</h2>
        <motion.button
          whileHover={{ scale: 1.02 }}
          whileTap={{ scale: 0.98 }}
          onClick={() => openModal()}
          className="flex items-center gap-2 px-4 py-2 bg-[#3084C2] text-white rounded-lg"
        >
          <Plus className="w-4 h-4" />
          {t('addresses.addNewAddress') || 'Add New Address'}
        </motion.button>
      </div>

      {addresses.length === 0 ? (
        <div className="text-center py-12 bg-gray-50 rounded-lg">
          <MapPin className="w-12 h-12 mx-auto text-gray-400 mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">
            {t('addresses.noAddressesFound') || 'No addresses found'}
          </h3>
          <p className="text-gray-600 mb-4">
            {t('addresses.noAddressesMessage') || "You don't have any saved addresses yet. Add your first address to make checkout easier."}
          </p>
          <motion.button
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.98 }}
            onClick={() => openModal()}
            className="px-4 py-2 bg-[#3084C2] text-white rounded-lg inline-flex items-center gap-2"
          >
            <Plus className="w-4 h-4" />
            {t('addresses.addFirstAddress') || 'Add Your First Address'}
          </motion.button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {addresses.map((address) => (
            <AddressCard 
              key={address.id} 
              address={address} 
              onEdit={openModal}
              onDelete={handleDelete}
              onSetDefault={handleSetDefault}
              isDeleting={isDeleting}
              deleteTargetId={deleteTargetId}
              isSettingDefault={isSettingDefault}
              defaultTargetId={defaultTargetId}
              t={t}
            />
          ))}
        </div>
      )}

      <AnimatePresence>
        {isModalOpen && (
          <AddressModal 
            isOpen={isModalOpen}
            onClose={closeModal}
            onSubmit={handleSubmit}
            formData={formData}
            setFormData={setFormData}
            editingAddressId={editingAddressId}
            addressType={addressType}
            setAddressType={setAddressType}
            isSubmitting={isSubmitting}
            t={t}
          />
        )}
      </AnimatePresence>
    </div>
  );
};

export default AddressComponent;