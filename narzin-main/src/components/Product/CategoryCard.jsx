import React from 'react'
import { useTranslation } from 'react-i18next';

const CategoryCard = ({category}) => {
  const { t, i18n } = useTranslation();
  
  return (
    <div className='group flex flex-col items-center w-full max-w-[160px] mx-auto cursor-pointer'>
      {/* Image container with modern hover effects */}
      <div className='relative w-full aspect-square mb-4 overflow-hidden'>
        {/* Gradient overlay that appears on hover */}
        <div className="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 z-10 rounded-lg"></div>
        
        {/* Main image with enhanced hover effects */}
        <img 
          src={`${category.image}`} 
          alt={i18n.language === 'du' ? category.name_german : category.name_arabic}
          className='w-full h-[200px] object-cover rounded-lg shadow-sm transition-all duration-300 ease-out group-hover:shadow-xl group-hover:shadow-blue-500/20 group-hover:scale-105 group-hover:brightness-110'
        />
        
        {/* Shimmer effect overlay */}
        <div className="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-lg overflow-hidden">
          <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
        </div>
        
        {/* Corner accent that appears on hover */}
        <div className="absolute top-3 right-3 w-2 h-2 bg-blue-500 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-0 group-hover:scale-100"></div>
      </div>
      
      {/* Text with hover effects */}
      <div className="relative">
        <h1 className='text-sm md:text-base font-medium text-center leading-tight px-1 line-clamp-2 transition-all duration-300 group-hover:text-blue-600 group-hover:font-semibold group-hover:transform group-hover:-translate-y-0.5'>
          {i18n.language === 'du' ? category.name_german : category.name_arabic}
        </h1>
        
        {/* Animated underline that appears on hover */}
        <div className="absolute bottom-0 left-1/2 w-0 h-0.5 bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-300 group-hover:w-full group-hover:left-0 rounded-full"></div>
      </div>
      
      {/* Background glow effect */}
      <div className="absolute inset-0 bg-blue-500/5 rounded-lg blur-xl opacity-0 group-hover:opacity-100 transition-all duration-300 -z-10 scale-95 group-hover:scale-100"></div>
    </div>
  )
}

export default CategoryCard