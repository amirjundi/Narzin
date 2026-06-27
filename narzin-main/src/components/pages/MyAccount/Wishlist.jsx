import React, { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { motion, AnimatePresence } from 'framer-motion';
import { useTranslation } from 'react-i18next';
import {
  Heart,
  Search,
  ShoppingCart,
  Share2,
  Trash2,
  Bell,
  Check,
  AlertCircle,
  ChevronDown
} from 'lucide-react';

import { fetchWishlist, removeWishlistItem } from '../../../Store/slices/WishlistSlice';
import { useNavigate } from 'react-router-dom';


const Wishlist = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const { items, status } = useSelector((state) => state.wishlist);
const navigate = useNavigate();  
  const [searchTerm, setSearchTerm] = useState('');
  const [notification, setNotification] = useState(null);
  const [expandedItem, setExpandedItem] = useState(null);

  useEffect(() => {
    dispatch(fetchWishlist());
  }, [dispatch]);

  const toggleNotification = (productId) => {
    // This would typically update a backend notification preference
    // For now we'll just show the notification UI
    setNotification({
      message: t('wishlist.notificationSet'),
      type: 'success'
    });

    setTimeout(() => setNotification(null), 3000);
  };

  const removeFromWishlist = (wishlistItemId) => {
    dispatch(removeWishlistItem(wishlistItemId))
      .then(() => {
        setNotification({
          message: t('wishlist.itemRemoved'),
          type: 'success'
        });
        setTimeout(() => setNotification(null), 3000);
      })
      .catch(() => {
        setNotification({
          message: t('wishlist.errorRemoving'),
          type: 'error'
        });
        setTimeout(() => setNotification(null), 3000);
      });
  };

  const viewProduct = (productId) => {

   navigate('/product/'+productId);
  };

  const shareItem = (item) => {
    // This would typically implement sharing functionality
    // For demonstration, we'll just show a notification
    setNotification({
      message: t('wishlist.shareLinkCopied'),
      type: 'success'
    });
    setTimeout(() => setNotification(null), 3000);
  };

  const getFormattedDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
  };

  const filteredItems = items.filter(item => 
    item.product.name_german.toLowerCase().includes(searchTerm.toLowerCase()) ||
    item.product.name_arabic.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const Notification = ({ message, type }) => (
    <motion.div
      initial={{ opacity: 0, y: 50 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: 50 }}
      className="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg p-4 flex items-center gap-2"
    >
      {type === 'success' ? (
        <Check className="w-5 h-5 text-green-500" />
      ) : (
        <AlertCircle className="w-5 h-5 text-red-500" />
      )}
      <span className="text-gray-800">{message}</span>
    </motion.div>
  );

  const WishlistItem = ({ item }) => {
    const isExpanded = expandedItem === item.id;
    const product = item.product;
    const primaryImage = product.images && product.images.length > 0 ? product.images[0].url : "/api/placeholder/400/400";
    
    // For demonstration, we'll consider the product is in stock if it has images
    const inStock = product.images && product.images.length > 0;

    return (
      <motion.div
        layout
        className="bg-white border rounded-lg overflow-hidden"
      >
        <div className="p-4">
          <div className="flex gap-4">
            <motion.div
              whileHover={{ scale: 1.05 }}
              className="relative w-24 h-24"
            >
              <img
                src={primaryImage}
                alt={product.name_german}
                className="w-full h-full object-cover rounded-md"
              />
              {!inStock && (
                <div className="absolute inset-0 bg-black bg-opacity-50 rounded-md flex items-center justify-center text-white text-sm">
                  {t('wishlist.outOfStock')}
                </div>
              )}
            </motion.div>
            
            <div className="flex-1">
              <div className="flex justify-between">
                <div>
                  <h3 className="font-medium">{product.name_german}</h3>
                  <p className="text-sm text-gray-600 mb-1">{t('wishlist.category')}: {product.category_id}</p>
                  <div className="flex items-center gap-2">
                    {/* Since we don't have price in the API response, I'm showing the ID for now */}
                    <span className="font-medium">ID: {product.id}</span>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <motion.button
                    whileHover={{ scale: 1.1 }}
                    whileTap={{ scale: 0.9 }}
                    onClick={() => shareItem(item)}
                    className="p-2 text-gray-400 hover:text-gray-600"
                  >
                    <Share2 className="w-5 h-5" />
                  </motion.button>
                  <motion.button
                    whileHover={{ scale: 1.1 }}
                    whileTap={{ scale: 0.9 }}
                    onClick={() => removeFromWishlist(item.id)}
                    className="p-2 text-gray-400 hover:text-red-500"
                  >
                    <Trash2 className="w-5 h-5" />
                  </motion.button>
                </div>
              </div>

              <div className="flex items-center gap-4 mt-2">
                {inStock ? (
                  <motion.button
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                    onClick={() => viewProduct(product.id)}
                    className="flex items-center gap-2 px-4 py-2 bg-[#3084C2] text-white rounded-lg"
                  >
                    <ShoppingCart className="w-4 h-4" />
                    {t('wishlist.viewProduct')}
                  </motion.button>
                ) : (
                  <motion.button
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                    onClick={() => toggleNotification(product.id)}
                    className="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-800 rounded-lg"
                  >
                    <Bell className="w-4 h-4" />
                    {t('wishlist.notifyWhenAvailable')}
                  </motion.button>
                )}
                <button
                  onClick={() => setExpandedItem(isExpanded ? null : item.id)}
                  className="text-gray-500 hover:text-gray-700"
                >
                  <ChevronDown className={`w-5 h-5 transition-transform ${
                    isExpanded ? 'rotate-180' : ''
                  }`} />
                </button>
              </div>
            </div>
          </div>

          <AnimatePresence>
            {isExpanded && (
              <motion.div
                initial={{ height: 0, opacity: 0 }}
                animate={{ height: 'auto', opacity: 1 }}
                exit={{ height: 0, opacity: 0 }}
                className="mt-4 pt-4 border-t"
              >
                <div className="grid grid-cols-2 gap-4 text-sm">
                  {product.images && product.images.length > 0 && (
                    <div>
                      <span className="text-gray-600">{t('wishlist.availableColors')}:</span>
                      <div className="flex mt-2 space-x-2">
                        {product.images.map((image, index) => (
                          <div 
                            key={image.id} 
                            className="w-6 h-6 rounded-full border border-gray-300"
                            style={{ backgroundColor: image.color ? image.color.replace('0xFF', '#') : '#CCCCCC' }}
                            title={`Color ${index + 1}`}
                          />
                        ))}
                      </div>
                    </div>
                  )}
                  <div>
                    <span className="text-gray-600">{t('wishlist.addedOn')}:</span>
                    <span className="ml-2">
                      {getFormattedDate(item.created_at)}
                    </span>
                  </div>
                  <div>
                    <span className="text-gray-600">{t('wishlist.status')}:</span>
                    <span className={`ml-2 ${
                      inStock ? 'text-green-600' : 'text-red-600'
                    }`}>
                      {inStock ? t('wishlist.inStock') : t('wishlist.outOfStock')}
                    </span>
                  </div>
                  <div>
                    <span className="text-gray-600">{t('wishlist.productId')}:</span>
                    <span className="ml-2">{product.id}</span>
                  </div>
                </div>
                
                <div className="mt-4">
                  <h4 className="font-medium mb-2">{t('wishlist.description')}</h4>
                  <p className="text-sm text-gray-700">
                    {product.description_german || t('wishlist.noDescription')}
                  </p>
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </div>
      </motion.div>
    );
  };

  // Show loading state
  if (status === 'loading' && items.length === 0) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#3084C2]"></div>
      </div>
    );
  }

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-2xl font-bold">{t('wishlist.title')}</h2>
        <div className="text-gray-600">
          {items.length} {items.length === 1 ? t('wishlist.item') : t('wishlist.items')}
        </div>
      </div>

      {/* Search */}
      <div className="mb-6">
        <div className="relative">
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
          <input
            type="text"
            placeholder={t('wishlist.searchPlaceholder')}
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
          />
        </div>
      </div>

      {/* Wishlist Items */}
      <div className="space-y-4">
        {filteredItems.length > 0 ? (
          filteredItems.map((item) => (
            <WishlistItem key={item.id} item={item} />
          ))
        ) : (
          <div className="text-center py-12 bg-gray-50 rounded-lg">
            <Heart className="w-12 h-12 mx-auto text-gray-400 mb-4" />
            <h3 className="text-lg font-medium text-gray-900 mb-2">
              {t('wishlist.emptyWishlist')}
            </h3>
            <p className="text-gray-600">
              {searchTerm
                ? t('wishlist.noMatchingItems')
                : t('wishlist.emptyWishlistMessage')}
            </p>
          </div>
        )}
      </div>

      {/* Notification */}
      <AnimatePresence>
        {notification && (
          <Notification
            message={notification.message}
            type={notification.type}
          />
        )}
      </AnimatePresence>
    </div>
  );
};

export default Wishlist;