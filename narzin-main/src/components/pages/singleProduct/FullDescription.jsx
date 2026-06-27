import React from 'react';
import { motion } from 'framer-motion';
import { useTranslation } from 'react-i18next';


const FullDescription = ({data}) => {
  const {t,i18n} = useTranslation();
 return (
    <div className="mt-8 bg-gradient-to-br from-white to-gray-50 rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300">
      <div className="flex items-center gap-3 mb-6">
        <div className="p-2 bg-blue-100 rounded-lg">
          <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <h3 className="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
          {t('product.product_description')}
        </h3>
      </div>
      
      <div className="relative">
        <div className="absolute inset-0 bg-gradient-to-r from-blue-50/50 to-purple-50/50 rounded-xl blur-xl opacity-50"></div>
        <div className="relative bg-white/80 backdrop-blur-sm rounded-xl p-6 border border-gray-100">
          <p className="text-gray-700 leading-relaxed text-lg">
            {i18n.language === 'du' ? data.description_german : data.description_arabic}
          </p>
        </div>
      </div>
    </div>
  );
};
  
  export default FullDescription;