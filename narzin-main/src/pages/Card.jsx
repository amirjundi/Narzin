import React, { useEffect, useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Trash2, 
  Plus, 
  Minus, 
  ShoppingBag,
  CreditCard,
  ArrowRight,
  Loader,
  Truck,
  Shield,
  RefreshCw,
  Clock,
  Gift,
  Heart,
  MapPin,
  Package,
  AlertCircle,
  AlertTriangle
} from 'lucide-react';
import emptyCart from '../assets/images/emptyCard.png';
import { Link, useNavigate } from 'react-router-dom';
import { useDispatch, useSelector } from 'react-redux';
import { fetchCart, updateCartItem, removeCartItem, clearCart } from '../Store/slices/CardSlice';
import { useTranslation } from 'react-i18next';
import { toast } from 'react-toastify'; // Assuming you're using react-toastify
import { fetchAddress } from '../Store/slices/AddressSlice';

const Cart = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { t , i18n } = useTranslation();
  
  const { items: cartItems, status, error } = useSelector(state => state.cart);
  const [isLoading, setIsLoading] = useState(false);
  const [promoCode, setPromoCode] = useState('');
  
  
  /***********************Address******************************/
  const [address, setAddress] = useState(null)
  const [defaultAddress, setDefaultAddress] = useState(null)
  const { items: AddressItems, AddressStatus, AddressError } = useSelector(state => state.address);

  /***********************Address******************************/



  // Fetch cart on component mount
  useEffect(() => {
    const fetchData = async () => {
      await dispatch(fetchCart());
      await dispatch(fetchAddress());
      setAddress(AddressItems.data);
      const defaultAddr = AddressItems.data?.find(address => address.is_default === 1) || (AddressItems.data ? AddressItems.data[0] : null);
      setDefaultAddress(defaultAddr);
    };

    fetchData();
  }, [dispatch]);

  const updateQuantity = async (id, change, currentQuantity, isOutOfStock) => {
    // Prevent quantity changes for out of stock items
    if (isOutOfStock) {
      toast.error(t('product.outOfStock'));
      return;
    }
    
    const newQuantity = Math.max(1, currentQuantity + change);
    
    try {
      await dispatch(updateCartItem({ cartItemId: id, quantity: newQuantity })).unwrap();
      // Refresh cart data after update
      dispatch(fetchCart());
      toast.success(t('cart.quantityUpdated'));
    } catch (error) {
      toast.error(error?.message || t('cart.updateError'));
    }
  };

  const removeItem = async (id) => {
    try {
      await dispatch(removeCartItem(id)).unwrap();
      toast.success(t('cart.itemRemoved'));
      // Cart state is updated automatically in the reducer
    } catch (error) {
      toast.error(error?.message || t('cart.removeError'));
    }
  };

  const handleClearCart = async () => {
    try {
      await dispatch(clearCart()).unwrap();
      toast.success(t('cart.cartCleared'));
    } catch (error) {
      toast.error(error?.message || t('cart.clearError'));
    }
  };

  const calculateSubtotal = () => {
    // Only calculate subtotal for in-stock items
    return cartItems
      .filter(item => !item.out_of_stock)
      .reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
  };

  const subtotal = calculateSubtotal();
  const total = subtotal ;

  // Count in-stock items
  const inStockItemsCount = cartItems.filter(item => !item.out_of_stock).length;

  const handleCheckout = () => {
    // Only proceed if there are in-stock items
    if (inStockItemsCount === 0) {
      toast.error(t('cart.noInStockItems'));
      return;
    }
    
    setIsLoading(true);
    setTimeout(() => {
      setIsLoading(false);
      navigate('/checkout');
    }, 2000);
  };

  // Show loading state while fetching cart
  if (status === 'loading' && cartItems.length === 0) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="flex flex-col items-center">
          <Loader className="w-12 h-12 text-[#3084C2] animate-spin mb-4" />
          <p className="text-gray-600">{t('cart.loading')}</p>
        </div>
      </div>
    );
  }

  // Show error state
  if (status === 'failed' && error && error.message == 'Unauthenticated') {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="flex flex-col items-center text-center max-w-md p-6 bg-white rounded-lg shadow-md">
          <AlertCircle className="w-12 h-12 text-red-500 mb-4" />
          <h2 className="text-xl font-semibold text-gray-900 mb-2">{t('cart.pleaseLogin')}</h2>
          <Link 
            to='/signin'
            className="bg-[#3084C2] text-white px-6 py-2 rounded-lg"
          >
            {t('auth.login')}
          </Link>
        </div>
      </div>
    );
  }

    if (status === 'failed' && error && error.message != 'Unauthenticated') {
      console.log(error.message)
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="flex flex-col items-center text-center max-w-md p-6 bg-white rounded-lg shadow-md">
          <AlertCircle className="w-12 h-12 text-red-500 mb-4" />
          <h2 className="text-xl font-semibold text-gray-900 mb-2">{t('cart.loadError')}</h2>
<p className="text-gray-600 mb-4">
  {typeof error === 'string' ? error : error?.message || t('cart.genericError')}
</p>
          <button 
            onClick={() => dispatch(fetchCart())}
            className="bg-[#3084C2] text-white px-6 py-2 rounded-lg"
          >
            {t('cart.tryAgain')}
          </button>
        </div>
      </div>
    );
  }



  // Empty cart state
  if (cartItems.length === 0) {
    return (
      <div className='p-10 flex flex-col items-center justify-center my-28'>
        <img src={emptyCart} alt="emptyCart" className='w-1/6'/>
        <h1 className='mt-5 text-[#626262] text-xl'>
          {t('cart.empty')}
        </h1>
        <h2 className='text-[#626262] text-lg mt-2 bolder'>
          {t('cart.addItemsMessage')}
        </h2>
        <button 
          onClick={() => navigate('/store')}
          className='mt-5 px-10 bg-[#225E8A] text-white py-2 rounded-md'
        >
          {t('cart.shopNow')}
        </button>
      </div>
    );
  }

  // Render cart with items
  return (
    <div className="min-h-screen bg-gray-50 py-8 mt-12">
      <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="text-center mb-8"
        >
          <h1 className="text-4xl font-bold text-gray-900">{t('cart.title')}</h1>
          <p className="text-gray-500 mt-2 text-lg">
            {cartItems.length} {cartItems.length === 1 ? t('cart.item') : t('cart.items')}
          </p>
          {cartItems.some(item => item.out_of_stock) && (
            <div className="mt-2 flex justify-center">
              <div className="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-2 rounded-md flex items-center gap-2 max-w-md">
                <AlertTriangle className="w-5 h-5" />
                <p className="text-sm">{t('cart.outOfStockWarning')}</p>
              </div>
            </div>
          )}
        </motion.div>

        {/* Shopping Benefits */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8"
        >
          {[
            { icon: Truck, text: t('cart.freeShipping') },
            { icon: Shield, text: t('cart.secureCheckout') },
            { icon: RefreshCw, text: t('cart.returns') },
            { icon: Clock, text: t('cart.support') }
          ].map((benefit, index) => (
            <div key={index} className="bg-white p-4 rounded-lg shadow-sm flex items-center gap-3">
              <benefit.icon className="w-6 h-6 text-[#3084C2]" />
              <span className="text-sm text-gray-600">{benefit.text}</span>
            </div>
          ))}
        </motion.div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
          {/* Cart Items */}
          <div className="lg:col-span-8">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-xl font-semibold">{t('cart.cartItems')}</h2>
              {cartItems.length > 0 && (
                <button
                  onClick={handleClearCart}
                  className="text-red-500 hover:text-red-600 flex items-center gap-1 text-sm"
                >
                  <Trash2 className="w-4 h-4" />
                  {t('cart.clearCart')}
                </button>
              )}
            </div>

            <AnimatePresence>
              {cartItems.map((item) => (
                <motion.div
                  key={item.id}
                  layout
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: -20 }}
                  className={`bg-white rounded-lg shadow-md p-6 mb-4 relative ${
                    item.out_of_stock ? 'opacity-70' : ''
                  }`}
                >
                  {/* Out of stock overlay badge */}
                  {item.out_of_stock && (
                    <div className="absolute top-2 right-2 z-10 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                      {t('product.outOfStock')}
                    </div>
                  )}
                  
                  <div className={`flex flex-col sm:flex-row items-start sm:items-center gap-4 ${
                    item.out_of_stock ? 'opacity-70' : ''
                  }`}>
                    <motion.div
                      whileHover={!item.out_of_stock && { scale: 1.05 }}
                      className={`relative group ${item.out_of_stock ? 'grayscale' : ''}`}
                    >
                      {item.product?.images && item.product.images.length > 0 ? (
                        <img
                          src={item.product.images[0].image}
                          alt={item.product.name_arabic}
                          className={`w-24 h-24 object-cover rounded-md ${
                            item.out_of_stock ? 'filter grayscale' : ''
                          }`}
                        />
                      ) : (
                        <div className="w-24 h-24 bg-gray-200 rounded-md flex items-center justify-center">
                          <Package className="w-8 h-8 text-gray-400" />
                        </div>
                      )}
                      {!item.out_of_stock && (
                        <motion.button
                          whileHover={{ scale: 1.1 }}
                          className="absolute top-2 right-2 bg-white rounded-full p-1 shadow-md opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                          <Heart className="w-4 h-4 text-red-500" />
                        </motion.button>
                      )}
                    </motion.div>
                    
                    <div className="flex-1">
                      <h3 className="font-semibold text-lg">
                        {item.product?.name_arabic || t('cart.productName')}
                      </h3>
                      {item.product_variant && (
                        <div className="flex flex-wrap items-center gap-2 text-gray-500 mt-1">
                          {/* Display variant attributes if available */}
                          {item.product_variant?.variant_values && (
                            item.product_variant.variant_values.map((variant) => (
                              <div key={variant.id} className="flex items-center gap-2">
                                <span className="text-sm font-medium">
                                {i18n.language == 'du' ?  variant.variant_attribute.name_german : variant.variant_attribute.name_arabic}
                                :</span>
                                {variant.variant_attribute.type === 'color' ? (
                                  <div
                                    className="w-4 h-4 rounded-full"
                                    style={{ backgroundColor: variant.value }}
                                    title={variant.value}
                                  />
                                ) : (
                                  variant.variant_attribute.type === 'pattern' ? ( 
                                  <img src={variant.value} className='max-w-[40px]'/>
                                  ) : (
                                  <span className="text-sm">{variant.value}</span>
                                  )
                                )} 
                              </div>
                            ))
                          )}
                          {/* Add more variant details as needed */}
                        </div>
                      )}
                      {!item.out_of_stock && (
                        <div className="flex items-center gap-2 text-gray-500 mt-1">
                        </div>
                      )}
                      <p className="font-medium mt-2 text-lg">€{parseFloat(item.price).toFixed(2)}</p>
                    </div>

                    <div className="flex flex-col items-end gap-4">
                      <div className="flex items-center gap-2 bg-gray-50 rounded-lg p-1">
                        <motion.button
                          whileTap={{ scale: 0.95 }}
                          onClick={() => updateQuantity(item.id, -1, item.quantity, item.out_of_stock)}
                          disabled={item.quantity <= 1 || item.out_of_stock}
                          className={`p-1 hover:bg-gray-200 rounded ${
                            item.out_of_stock || item.quantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''
                          }`}
                        >
                          <Minus className="w-4 h-4" />
                        </motion.button>
                        <span className="w-8 text-center">{item.quantity}</span>
                        <motion.button
                          whileTap={{ scale: 0.95 }}
                          onClick={() => updateQuantity(item.id, 1, item.quantity, item.out_of_stock)}
                          disabled={item.out_of_stock || (item.product_variant && item.quantity >= item.product_variant.stock)}
                          className={`p-1 hover:bg-gray-200 rounded ${
                            item.out_of_stock || (item.product_variant && item.quantity >= item.product_variant.stock) 
                              ? 'opacity-50 cursor-not-allowed' : ''
                          }`}
                        >
                          <Plus className="w-4 h-4" />
                        </motion.button>
                      </div>
                      <motion.button
                        whileHover={{ scale: 1.05 }}
                        whileTap={{ scale: 0.95 }}
                        onClick={() => removeItem(item.id)}
                        className="text-red-500 hover:text-red-600 flex items-center gap-1"
                      >
                        <Trash2 className="w-4 h-4" />
                        <span className="text-sm">{t('cart.remove')}</span>
                      </motion.button>
                    </div>
                  </div>
                </motion.div>
              ))}
            </AnimatePresence>
          </div>

          {/* Order Summary */}
          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            className="lg:col-span-4 space-y-4"
          >


            {/* Order Summary */}
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-xl font-semibold mb-4">{t('cart.orderSummary')}</h2>
              
              {cartItems.some(item => item.out_of_stock) && (
                <div className="bg-amber-50 border border-amber-200 text-amber-700 p-3 rounded-md mb-4 text-sm">
                  {t('cart.outOfStockItemsNotIncluded')}
                </div>
              )}
              
              <div className="space-y-3 mb-4">
                <div className="flex justify-between text-gray-600">
                  <span>{t('cart.subtotal')}</span>
                  <span>€{subtotal.toFixed(2)}</span>
                </div>

                <div className="flex justify-between text-gray-600">
                  <span>{t('cart.shipping')}</span>
                    <span className='text-green-500'>{t('cart.calculatedShipping')}</span>
    
                </div>

                <div className="h-px bg-gray-200 my-2" />
                <div className="flex justify-between font-semibold text-lg">
                  <span>{t('cart.total')}</span>
                  <span>€{total.toFixed(2)}</span>
                </div>
              </div>

              <motion.button
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.98 }}
                onClick={handleCheckout}
                disabled={inStockItemsCount === 0 || isLoading}
                className="w-full bg-[#3084C2] text-white py-3 rounded-lg font-medium 
                         flex items-center justify-center gap-2 hover:bg-[#195e8f] 
                         disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isLoading ? (
                  <Loader className="w-5 h-5 animate-spin" />
                ) : (
                  <>
                    <CreditCard className="w-5 h-5" />
                    {t('cart.proceedToCheckout')}
                    <ArrowRight className="w-5 h-5" />
                  </>
                )}
              </motion.button>

              <div className="mt-4 text-sm text-gray-500">
                <div className="flex items-center justify-center gap-2 mb-2">
                  <Shield className="w-4 h-4" />
                  <span>{t('cart.secureCheckoutMessage')}</span>
                </div>
                <div className="flex items-center justify-center gap-2">
                  {/* <Package className="w-4 h-4" /> */}
                  {/* <span>{t('cart.freeShippingMessage')}</span> */}
                </div>
              </div>
            </div>
          </motion.div>
        </div>
      </div>
    </div>
  );
};

export default Cart;