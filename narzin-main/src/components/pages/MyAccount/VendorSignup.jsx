import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { motion, AnimatePresence } from 'framer-motion';
import { useTranslation } from 'react-i18next';
import {
  CheckCircle,
  AlertCircle,
  Store,
  Upload,
  MapPin,
  Phone,
  Image,
  CheckSquare
} from 'lucide-react';

import { createVendor, resetVendorState } from '../../../Store/slices/VendorSlice';

const VendorSignup = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const { status, error, message } = useSelector((state) => state.vendor);
  
  const [formData, setFormData] = useState({
    store_name_in_arabic: '',
    store_name_in_german: '',
    store_logo: null,
    address: '',
    phone: '',
    store_type: '',
    store_id: null
  });
  
  const [termsAccepted, setTermsAccepted] = useState(false);
  const [formErrors, setFormErrors] = useState({});

  // Cleanup on component unmount
  useEffect(() => {
    return () => {
      dispatch(resetVendorState());
    };
  }, [dispatch]);

  const storeTypes = [
    { id: 'retail', name: t('vendor.storeTypes.retail') },
    { id: 'wholesale', name: t('vendor.storeTypes.wholesale') },
    { id: 'manufacturing', name: t('vendor.storeTypes.manufacturing') },
    { id: 'service', name: t('vendor.storeTypes.service') },
    { id: 'online', name: t('vendor.storeTypes.online') }
  ];

  const handleInputChange = (field, value) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
    
    // Clear field error when user starts typing
    if (formErrors[field]) {
      setFormErrors(prev => ({
        ...prev,
        [field]: null
      }));
    }
  };

  const handleFileUpload = (field, files) => {
    if (files && files[0]) {
      setFormData(prev => ({
        ...prev,
        [field]: files[0]
      }));
      
      // Clear field error when user uploads file
      if (formErrors[field]) {
        setFormErrors(prev => ({
          ...prev,
          [field]: null
        }));
      }
    }
  };

  const validateForm = () => {
    const errors = {};
    const requiredFields = [
      'store_name_in_arabic',
      'store_name_in_german',
      'address',
      'phone',
      'store_type'
    ];
    
    requiredFields.forEach(field => {
      if (!formData[field]) {
        errors[field] = t('vendor.errors.fieldRequired');
      }
    });
    
    if (!formData.store_logo) {
      errors.store_logo = t('vendor.errors.logoRequired');
    }
    
    if (!formData.store_id) {
      errors.store_id = t('vendor.errors.idRequired');
    }
    
    if (!termsAccepted) {
      errors.terms = t('vendor.errors.termsRequired');
    }
    
    setFormErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    
    if (validateForm()) {
      dispatch(createVendor(formData));
    }
  };

  // Show the success/review pending message
  if (status === 'underReview') {
    return (
      <div className="max-w-3xl mx-auto">
        <div className="bg-white rounded-lg p-8 shadow-sm text-center">
          <div className="flex justify-center mb-6">
            <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
              <CheckCircle className="w-8 h-8 text-green-600" />
            </div>
          </div>
          
          <h2 className="text-2xl font-bold mb-4">{t('vendor.reviewStatus.title')}</h2>
          <p className="text-gray-600 mb-6">{t('vendor.reviewStatus.message')}</p>
          
          <div className="p-4 bg-blue-50 rounded-lg border border-blue-100 mb-6">
            <div className="flex gap-3">
              <div className="flex-shrink-0">
                <CheckSquare className="w-5 h-5 text-blue-600" />
              </div>
              <div>
                <h4 className="font-medium text-blue-900">{t('vendor.reviewStatus.nextStepsTitle')}</h4>
                <ul className="mt-2 space-y-2 text-sm text-blue-700">
                  <li>• {t('vendor.reviewStatus.step1')}</li>
                  <li>• {t('vendor.reviewStatus.step2')}</li>
                  <li>• {t('vendor.reviewStatus.step3')}</li>
                </ul>
              </div>
            </div>
          </div>
          
          <button
            onClick={() => dispatch(resetVendorState())}
            className="px-6 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200"
          >
            {t('vendor.backToHome')}
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-3xl mx-auto">
      <div className="mb-6">
        <div className="flex items-center gap-3">
          <Store className="w-6 h-6 text-[#3084C2]" />
          <h2 className="text-2xl font-bold">{t('vendor.title')}</h2>
        </div>
        <p className="text-gray-600 mt-2">
          {t('vendor.subtitle')}
        </p>
      </div>

      {/* Error message at the top if form submission failed */}
      {status === 'failed' && (
        <div className="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
          <div className="flex items-center">
            <AlertCircle className="w-5 h-5 text-red-500 mr-2" />
            <span className="text-red-800">{error}</span>
          </div>
        </div>
      )}

      <form onSubmit={handleSubmit} className="bg-white rounded-lg p-6 shadow-sm">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          {/* Store Names */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('vendor.fields.storeNameArabic')} *
            </label>
            <input
              type="text"
              value={formData.store_name_in_arabic}
              onChange={(e) => handleInputChange('store_name_in_arabic', e.target.value)}
              className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none ${
                formErrors.store_name_in_arabic ? 'border-red-500' : ''
              }`}
              placeholder={t('vendor.placeholders.storeNameArabic')}
              dir="rtl"
            />
            {formErrors.store_name_in_arabic && (
              <p className="mt-1 text-sm text-red-600">{formErrors.store_name_in_arabic}</p>
            )}
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('vendor.fields.storeNameGerman')} *
            </label>
            <input
              type="text"
              value={formData.store_name_in_german}
              onChange={(e) => handleInputChange('store_name_in_german', e.target.value)}
              className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none ${
                formErrors.store_name_in_german ? 'border-red-500' : ''
              }`}
              placeholder={t('vendor.placeholders.storeNameGerman')}
            />
            {formErrors.store_name_in_german && (
              <p className="mt-1 text-sm text-red-600">{formErrors.store_name_in_german}</p>
            )}
          </div>
          
          {/* Store Logo */}
          <div className="md:col-span-2">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('vendor.fields.storeLogo')} *
            </label>
            <div className="relative">
              <input
                type="file"
                onChange={(e) => handleFileUpload('store_logo', e.target.files)}
                className="hidden"
                id="store_logo"
                accept="image/*"
              />
              <label
                htmlFor="store_logo"
                className={`flex items-center justify-center w-full h-24 border-2 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 ${
                  formErrors.store_logo ? 'border-red-500' : 'border-gray-300'
                }`}
              >
                {formData.store_logo ? (
                  <div className="flex items-center gap-2 text-[#3084C2]">
                    <CheckCircle className="w-6 h-6" />
                    <span>{formData.store_logo.name}</span>
                  </div>
                ) : (
                  <div className="flex flex-col items-center">
                    <Image className="w-8 h-8 text-gray-400 mb-2" />
                    <span className="text-sm text-gray-500">{t('vendor.placeholders.uploadLogo')}</span>
                  </div>
                )}
              </label>
              {formErrors.store_logo ? (
                <p className="mt-1 text-sm text-red-600">{formErrors.store_logo}</p>
              ) : (
                <p className="mt-1 text-xs text-gray-500">{t('vendor.hints.logoFormat')}</p>
              )}
            </div>
          </div>
          
          {/* Address */}
          <div className="md:col-span-2">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('vendor.fields.address')} *
            </label>
            <div className="relative">
              <MapPin className="absolute left-3 top-3 text-gray-400 w-5 h-5" />
              <textarea
                value={formData.address}
                onChange={(e) => handleInputChange('address', e.target.value)}
                className={`w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none ${
                  formErrors.address ? 'border-red-500' : ''
                }`}
                rows={2}
                placeholder={t('vendor.placeholders.address')}
              />
              {formErrors.address && (
                <p className="mt-1 text-sm text-red-600">{formErrors.address}</p>
              )}
            </div>
          </div>
          
          {/* Phone */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('vendor.fields.phone')} *
            </label>
            <div className="relative">
              <Phone className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <input
                type="tel"
                value={formData.phone}
                onChange={(e) => handleInputChange('phone', e.target.value)}
                className={`w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none ${
                  formErrors.phone ? 'border-red-500' : ''
                }`}
                placeholder={t('vendor.placeholders.phone')}
              />
              {formErrors.phone && (
                <p className="mt-1 text-sm text-red-600">{formErrors.phone}</p>
              )}
            </div>
          </div>
          
          {/* Store Type */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('vendor.fields.storeType')} *
            </label>
            <select
              value={formData.store_type}
              onChange={(e) => handleInputChange('store_type', e.target.value)}
              className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none ${
                formErrors.store_type ? 'border-red-500' : ''
              }`}
            >
              <option value="">{t('vendor.placeholders.selectStoreType')}</option>
              {storeTypes.map(type => (
                <option key={type.id} value={type.id}>{type.name}</option>
              ))}
            </select>
            {formErrors.store_type && (
              <p className="mt-1 text-sm text-red-600">{formErrors.store_type}</p>
            )}
          </div>
          
          {/* Store ID (Document) */}
          <div className="md:col-span-2">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('vendor.fields.storeId')} *
            </label>
            <div className="relative">
              <input
                type="file"
                onChange={(e) => handleFileUpload('store_id', e.target.files)}
                className="hidden"
                id="store_id"
                accept=".pdf,.doc,.docx,image/*"
              />
              <label
                htmlFor="store_id"
                className={`flex items-center justify-center w-full h-24 border-2 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 ${
                  formErrors.store_id ? 'border-red-500' : 'border-gray-300'
                }`}
              >
                {formData.store_id ? (
                  <div className="flex items-center gap-2 text-[#3084C2]">
                    <CheckCircle className="w-6 h-6" />
                    <span>{formData.store_id.name}</span>
                  </div>
                ) : (
                  <div className="flex flex-col items-center">
                    <Upload className="w-8 h-8 text-gray-400 mb-2" />
                    <span className="text-sm text-gray-500">{t('vendor.placeholders.uploadId')}</span>
                  </div>
                )}
              </label>
              {formErrors.store_id ? (
                <p className="mt-1 text-sm text-red-600">{formErrors.store_id}</p>
              ) : (
                <p className="mt-1 text-xs text-gray-500">{t('vendor.hints.idFormat')}</p>
              )}
            </div>
          </div>
        </div>
        
        {/* Terms and Conditions */}
        <div className={`p-4 border rounded-lg mb-6 ${formErrors.terms ? 'border-red-500 bg-red-50' : ''}`}>
          <div className="flex items-start gap-3">
            <input
              type="checkbox"
              id="terms"
              checked={termsAccepted}
              onChange={() => setTermsAccepted(!termsAccepted)}
              className="mt-1"
            />
            <label htmlFor="terms" className="text-sm text-gray-600">
              {t('vendor.terms')}
            </label>
          </div>
          {formErrors.terms && (
            <p className="mt-2 text-sm text-red-600">{formErrors.terms}</p>
          )}
        </div>
        
        {/* Submit Button */}
        <motion.button
          whileHover={{ scale: 1.02 }}
          whileTap={{ scale: 0.98 }}
          type="submit"
          disabled={status === 'loading'}
          className={`w-full px-6 py-3 bg-[#3084C2] text-white rounded-lg ${
            status === 'loading' ? 'opacity-70 cursor-not-allowed' : ''
          }`}
        >
          {status === 'loading' ? (
            <div className="flex items-center justify-center gap-2">
              <div className="w-5 h-5 border-t-2 border-white rounded-full animate-spin"></div>
              {t('vendor.submitting')}
            </div>
          ) : (
            t('vendor.submit')
          )}
        </motion.button>
      </form>
      
      {/* Required fields note */}
      <p className="text-sm text-gray-500 mt-4">
        * {t('vendor.requiredFields')}
      </p>
    </div>
  );
};

export default VendorSignup;