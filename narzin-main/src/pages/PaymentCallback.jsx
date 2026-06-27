import React, { useEffect, useState, useRef } from 'react';
import { useLocation, useNavigate, useSearchParams } from 'react-router-dom';
import { useDispatch, useSelector } from 'react-redux';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  CheckCircle, 
  XCircle, 
  Loader, 
  ShoppingBag, 
  Receipt, 
  AlertCircle,
  Clock,
  RefreshCw,
  Package,
  Truck,
  Copy,
  Phone,
  Mail,
  ArrowRight,
  Wallet
} from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { toast } from 'react-toastify';
import { verifyPayment, clearVerification } from '../Store/slices/CheckoutSlice';
import { clearCart } from '../Store/slices/CardSlice';

const PaymentCallback = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { t, i18n } = useTranslation();
  const [searchParams] = useSearchParams();
  
  // Get orderId from URL (Nass redirects with orderId in URL)
  const orderIdFromUrl = searchParams.get('orderId') || searchParams.get('order_id');
  
  // Redux state
  const { 
    verificationStatus, 
    verificationError, 
    verifiedOrder,
    currentOrder 
  } = useSelector(state => state.checkout);
  
  // Local state
  const [retryCount, setRetryCount] = useState(0);
  const [isRetrying, setIsRetrying] = useState(false);
  const maxRetries = 3;
  const retryInterval = 5000; // 5 seconds
  
  // Ref to prevent double verification
  const verificationAttempted = useRef(false);

  // Clear cart on successful payment
  useEffect(() => {
    if (verificationStatus === 'succeeded') {
      dispatch(clearCart());
    }
  }, [verificationStatus, dispatch]);

  // Verify payment on mount
  useEffect(() => {
    const orderId = orderIdFromUrl || currentOrder?.payment_id;
    
    if (orderId && !verificationAttempted.current) {
      verificationAttempted.current = true;
      dispatch(verifyPayment(orderId));
    } else if (!orderId) {
      // No order ID found, show error
      toast.error(t('thankyou.no_order_id') || 'Order ID not found');
    }
    
    // Cleanup on unmount
    return () => {
      dispatch(clearVerification());
    };
  }, [orderIdFromUrl, currentOrder, dispatch, t]);

  // Auto-retry for pending status
  useEffect(() => {
    let retryTimer;
    
    if (verificationStatus === 'pending' && retryCount < maxRetries) {
      retryTimer = setTimeout(() => {
        setIsRetrying(true);
        const orderId = orderIdFromUrl || currentOrder?.payment_id;
        if (orderId) {
          dispatch(verifyPayment(orderId)).finally(() => {
            setIsRetrying(false);
            setRetryCount(prev => prev + 1);
          });
        }
      }, retryInterval);
    }
    
    return () => {
      if (retryTimer) clearTimeout(retryTimer);
    };
  }, [verificationStatus, retryCount, orderIdFromUrl, currentOrder, dispatch]);

  // Manual retry handler
  const handleRetry = () => {
    const orderId = orderIdFromUrl || currentOrder?.payment_id;
    if (orderId) {
      setIsRetrying(true);
      dispatch(verifyPayment(orderId)).finally(() => {
        setIsRetrying(false);
      });
    }
  };

  // Copy order number
  const handleCopyOrderNumber = () => {
    const orderNumber = verifiedOrder?.order_number;
    if (orderNumber) {
      navigator.clipboard.writeText(orderNumber);
      toast.success(t('thankyou.order_copied') || 'Order number copied!');
    }
  };

  // Format date
  const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString(i18n.language === 'du' ? 'de-DE' : 'ar-IQ', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  // Estimated delivery
  const getEstimatedDelivery = () => {
    if (!verifiedOrder?.created_at) return '';
    const createdDate = new Date(verifiedOrder.created_at);
    const days = verifiedOrder.shipping_type === 'fast' ? 2 : 5;
    createdDate.setDate(createdDate.getDate() + days);
    return createdDate.toLocaleDateString(i18n.language === 'du' ? 'de-DE' : 'ar-IQ', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  // Animation variants
  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: { staggerChildren: 0.1 }
    }
  };

  const itemVariants = {
    hidden: { y: 20, opacity: 0 },
    visible: { y: 0, opacity: 1 }
  };

  // ─────────────────────────────────────────────────────────────────
  // LOADING STATE
  // ─────────────────────────────────────────────────────────────────
  if (verificationStatus === 'loading' || verificationStatus === 'idle') {
    return (
      <div className="mt-24 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-4">
        <motion.div 
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          className="bg-white rounded-2xl shadow-xl p-8 text-center max-w-md w-full"
        >
          <div className="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <Loader className="w-10 h-10 text-[#3084C2] animate-spin" />
          </div>
          <h2 className="text-2xl font-bold text-gray-800 mb-3">
            {t('thankyou.verifying_payment') || 'Verifying Payment'}
          </h2>
          <p className="text-gray-500">
            {t('thankyou.please_wait') || 'Please wait while we confirm your payment...'}
          </p>
          
          {/* Animated dots */}
          <div className="flex justify-center gap-1 mt-4">
            {[0, 1, 2].map((i) => (
              <motion.div
                key={i}
                className="w-2 h-2 bg-[#3084C2] rounded-full"
                animate={{ opacity: [0.3, 1, 0.3] }}
                transition={{ duration: 1, repeat: Infinity, delay: i * 0.2 }}
              />
            ))}
          </div>
        </motion.div>
      </div>
    );
  }

  // ─────────────────────────────────────────────────────────────────
  // PENDING STATE (Nass API temporarily unavailable)
  // ─────────────────────────────────────────────────────────────────
  if (verificationStatus === 'pending') {
    return (
      <div className="mt-24 bg-gradient-to-br from-yellow-50 to-orange-50 flex items-center justify-center p-4">
        <motion.div 
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          className="bg-white rounded-2xl shadow-xl p-8 text-center max-w-md w-full"
        >
          <div className="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6 border-2 border-yellow-200">
            <Clock className="w-10 h-10 text-yellow-600" />
          </div>
          
          <h2 className="text-2xl font-bold text-yellow-700 mb-3">
            {t('thankyou.verification_pending') || 'Verification Pending'}
          </h2>
          
          <p className="text-gray-600 mb-6">
            {t('thankyou.verification_pending_message') || 
              'We\'re having trouble confirming your payment right now. If you completed payment, your order will be confirmed shortly.'}
          </p>
          
          {/* Order info if available */}
          {verifiedOrder && (
            <div className="bg-yellow-50 rounded-lg p-4 mb-6 text-left">
              <p className="text-sm text-gray-600">
                <span className="font-medium">{t('thankyou.order_number') || 'Order'}:</span>{' '}
                {verifiedOrder.order_number}
              </p>
              <p className="text-sm text-gray-600 mt-1">
                <span className="font-medium">{t('thankyou.status') || 'Status'}:</span>{' '}
                <span className="text-yellow-600">
                  {t('thankyou.awaiting_confirmation') || 'Awaiting Confirmation'}
                </span>
              </p>
            </div>
          )}
          
          {/* Retry info */}
          <div className="text-sm text-gray-500 mb-6">
            {isRetrying ? (
              <div className="flex items-center justify-center gap-2">
                <Loader className="w-4 h-4 animate-spin" />
                {t('thankyou.retrying') || 'Retrying...'}
              </div>
            ) : retryCount < maxRetries ? (
              <p>
                {t('thankyou.auto_retry') || 'Auto-retrying in'} {retryInterval / 1000}s...
                ({retryCount + 1}/{maxRetries})
              </p>
            ) : (
              <p>{t('thankyou.max_retries') || 'Maximum retries reached'}</p>
            )}
          </div>
          
          {/* Action buttons */}
          <div className="space-y-3">
            <button
              onClick={handleRetry}
              disabled={isRetrying}
              className="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-3 px-4 rounded-xl font-medium transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <RefreshCw className={`w-5 h-5 ${isRetrying ? 'animate-spin' : ''}`} />
              {t('thankyou.retry_now') || 'Retry Now'}
            </button>
            
            <button
              onClick={() => navigate('/my-account')}
              className="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-4 rounded-xl font-medium transition-all duration-200"
            >
              {t('thankyou.check_orders') || 'Check My Orders'}
            </button>
          </div>
          
          {/* Help text */}
          <p className="text-xs text-gray-400 mt-6">
            {t('thankyou.dont_worry') || 
              'Don\'t worry! If you completed payment, your order will be automatically confirmed within a few minutes.'}
          </p>
        </motion.div>
      </div>
    );
  }

  // ─────────────────────────────────────────────────────────────────
  // FAILED STATE
  // ─────────────────────────────────────────────────────────────────
  if (verificationStatus === 'failed') {
    // Check if it's a refund situation
    const isRefunded = verificationError?.includes('refund') || verificationError?.includes('wallet');
    
    return (
      <div className="min-h-screen bg-gradient-to-br from-red-50 to-pink-50 flex items-center justify-center p-4">
        <motion.div 
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          className="bg-white rounded-2xl shadow-xl p-8 text-center max-w-md w-full"
        >
          <div className={`w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 border-2 ${
            isRefunded ? 'bg-purple-100 border-purple-200' : 'bg-red-100 border-red-200'
          }`}>
            {isRefunded ? (
              <Wallet className="w-10 h-10 text-purple-600" />
            ) : (
              <XCircle className="w-10 h-10 text-red-600" />
            )}
          </div>
          
          <h2 className={`text-2xl font-bold mb-3 ${
            isRefunded ? 'text-purple-700' : 'text-red-700'
          }`}>
            {isRefunded 
              ? (t('thankyou.order_refunded') || 'Order Refunded')
              : (t('thankyou.payment_failed') || 'Payment Failed')
            }
          </h2>
          
          <p className="text-gray-600 mb-6">
            {verificationError || (t('thankyou.payment_error') || 'Something went wrong with your payment.')}
          </p>
          
          {/* Refund info */}
          {isRefunded && verifiedOrder && (
            <div className="bg-purple-50 rounded-lg p-4 mb-6">
              <div className="flex items-center justify-center gap-2 text-purple-700">
                <Wallet className="w-5 h-5" />
                <span className="font-semibold">
                  €{parseFloat(verifiedOrder.final_price || 0).toFixed(2)} {t('thankyou.added_to_wallet') || 'added to your wallet'}
                </span>
              </div>
            </div>
          )}
          
          {/* Action buttons */}
          <div className="space-y-3">
            {!isRefunded && (
              <button
                onClick={() => navigate('/cart')}
                className="w-full bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-xl font-medium transition-all duration-200"
              >
                {t('thankyou.back_to_cart') || 'Back to Cart'}
              </button>
            )}
            
            <button
              onClick={() => navigate('/my-account')}
              className="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-4 rounded-xl font-medium transition-all duration-200 flex items-center justify-center gap-2"
            >
              <Receipt className="w-5 h-5" />
              {t('thankyou.view_orders') || 'View My Orders'}
            </button>
            
            <button
              onClick={() => navigate('/store')}
              className="w-full bg-[#3084C2] hover:bg-[#275a8c] text-white py-3 px-4 rounded-xl font-medium transition-all duration-200 flex items-center justify-center gap-2"
            >
              <ShoppingBag className="w-5 h-5" />
              {t('thankyou.continue_shopping') || 'Continue Shopping'}
            </button>
          </div>
        </motion.div>
      </div>
    );
  }

  // ─────────────────────────────────────────────────────────────────
  // SUCCESS STATE
  // ─────────────────────────────────────────────────────────────────
  return (
    <div className="min-h-screen bg-gradient-to-br from-green-50 to-emerald-50 py-12">
      <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Success Header */}
        <motion.div
          initial={{ scale: 0.8, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          transition={{ type: "spring", duration: 0.5 }}
          className="text-center mb-8"
        >
          <div className="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-6 border-4 border-green-200">
            <motion.div
              initial={{ scale: 0 }}
              animate={{ scale: 1 }}
              transition={{ delay: 0.3, type: "spring" }}
            >
              <CheckCircle className="w-12 h-12 text-green-600" />
            </motion.div>
          </div>
          
          <motion.h1 
            initial={{ y: 20, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ delay: 0.2 }}
            className="text-3xl font-bold text-gray-900 mb-2"
          >
            {t('thankyou.thank_you') || 'Thank You for Your Order!'}
          </motion.h1>
          
          <motion.p 
            initial={{ y: 20, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ delay: 0.3 }}
            className="text-lg text-gray-600"
          >
            {t('thankyou.order_confirmed') || 'Your order has been confirmed and is being processed.'}
          </motion.p>
        </motion.div>

        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="space-y-6"
        >
          {/* Order Summary Card */}
          <motion.div 
            variants={itemVariants}
            className="bg-white rounded-2xl shadow-lg overflow-hidden"
          >
            <div className="bg-gradient-to-r from-[#3084C2] to-[#195e8f] px-6 py-4">
              <div className="flex items-center justify-between text-white">
                <div className="flex items-center gap-3">
                  <Package className="w-6 h-6" />
                  <span className="font-semibold text-lg">
                    {t('thankyou.order_details') || 'Order Details'}
                  </span>
                </div>
                <div className="flex items-center gap-2">
                  <span className="font-mono text-sm bg-white/20 px-3 py-1 rounded-full">
                    {verifiedOrder?.order_number}
                  </span>
                  <button 
                    onClick={handleCopyOrderNumber}
                    className="p-1 hover:bg-white/20 rounded transition-colors"
                    title={t('thankyou.copy') || 'Copy'}
                  >
                    <Copy className="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
            
            <div className="p-6 space-y-4">
              {/* Order Info Grid */}
              <div className="grid grid-cols-2 gap-4">
                <div className="bg-gray-50 rounded-xl p-4">
                  <p className="text-xs text-gray-500 uppercase tracking-wide mb-1">
                    {t('thankyou.order_date') || 'Order Date'}
                  </p>
                  <p className="font-medium text-gray-900">
                    {formatDate(verifiedOrder?.created_at)}
                  </p>
                </div>
                
                <div className="bg-gray-50 rounded-xl p-4">
                  <p className="text-xs text-gray-500 uppercase tracking-wide mb-1">
                    {t('thankyou.payment_status') || 'Payment Status'}
                  </p>
                  <div className="flex items-center gap-2">
                    <span className="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span className="font-medium text-green-600">
                      {t('thankyou.paid') || 'Paid'}
                    </span>
                  </div>
                </div>
                
                <div className="bg-gray-50 rounded-xl p-4">
                  <p className="text-xs text-gray-500 uppercase tracking-wide mb-1">
                    {t('thankyou.shipping_method') || 'Shipping'}
                  </p>
                  <p className="font-medium text-gray-900">
                    {verifiedOrder?.shipping_type === 'fast' 
                      ? (t('thankyou.express') || 'Express (1-2 days)')
                      : (t('thankyou.standard') || 'Standard (3-5 days)')
                    }
                  </p>
                </div>
                
                <div className="bg-gray-50 rounded-xl p-4">
                  <p className="text-xs text-gray-500 uppercase tracking-wide mb-1">
                    {t('thankyou.estimated_delivery') || 'Est. Delivery'}
                  </p>
                  <p className="font-medium text-gray-900">
                    {getEstimatedDelivery()}
                  </p>
                </div>
              </div>
              
              {/* Delivery Address */}
              {verifiedOrder?.address && (
                <div className="border-t border-gray-100 pt-4">
                  <div className="flex items-start gap-3">
                    <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                      <Truck className="w-5 h-5 text-[#3084C2]" />
                    </div>
                    <div>
                      <p className="text-sm text-gray-500 mb-1">
                        {t('thankyou.delivery_to') || 'Delivering to'}
                      </p>
                      <p className="font-medium text-gray-900">
                        {verifiedOrder.address.title || verifiedOrder.address.address}
                      </p>
                      <p className="text-sm text-gray-600">
                        {verifiedOrder.address.address}
                      </p>
                      {verifiedOrder.address.phone_number && (
                        <p className="text-sm text-gray-600">
                          {verifiedOrder.address.phone_number}
                        </p>
                      )}
                    </div>
                  </div>
                </div>
              )}
            </div>
          </motion.div>

          {/* Order Items */}
          {verifiedOrder?.items && verifiedOrder.items.length > 0 && (
            <motion.div 
              variants={itemVariants}
              className="bg-white rounded-2xl shadow-lg overflow-hidden"
            >
              <div className="px-6 py-4 border-b border-gray-100">
                <div className="flex items-center gap-3">
                  <ShoppingBag className="w-5 h-5 text-[#3084C2]" />
                  <span className="font-semibold text-gray-800">
                    {t('thankyou.items') || 'Items'} ({verifiedOrder.items.length})
                  </span>
                </div>
              </div>
              
              <div className="divide-y divide-gray-100">
                {verifiedOrder.items.map((item) => (
                  <div key={item.id} className="p-4 flex items-center gap-4">
                    {/* Product Image */}
                    <div className="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                      {item.product?.images?.[0] ? (
                        <img 
                          src={item.product.images[0].image || item.product.images[0].url} 
                          alt={item.product.name_arabic}
                          className="w-full h-full object-cover"
                        />
                      ) : (
                        <div className="w-full h-full flex items-center justify-center">
                          <Package className="w-6 h-6 text-gray-400" />
                        </div>
                      )}
                    </div>
                    
                    {/* Product Info */}
                    <div className="flex-1 min-w-0">
                      <h3 className="font-medium text-gray-900 truncate">
                        {i18n.language === 'du' 
                          ? item.product?.name_german 
                          : item.product?.name_arabic
                        }
                      </h3>
                      
                      {/* Variant info */}
                      {item.product_variant?.variant_values && (
                        <div className="flex flex-wrap gap-2 mt-1">
                          {item.product_variant.variant_values.map((v) => (
                            <span key={v.id} className="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                              {v.variant_attribute?.type === 'color' ? (
                                <span 
                                  className="inline-block w-3 h-3 rounded-full mr-1" 
                                  style={{ backgroundColor: v.value }}
                                />
                              ) : null}
                              {v.variant_attribute?.type !== 'color' && v.value}
                            </span>
                          ))}
                        </div>
                      )}
                      
                      <div className="flex items-center gap-2 mt-1 text-sm text-gray-600">
                        <span>€{parseFloat(item.unit_price).toFixed(2)}</span>
                        <span>×</span>
                        <span>{item.quantity}</span>
                      </div>
                    </div>
                    
                    {/* Item Total */}
                    <div className="font-semibold text-gray-900">
                      €{parseFloat(item.subtotal).toFixed(2)}
                    </div>
                  </div>
                ))}
              </div>
              
              {/* Price Summary */}
              <div className="bg-gray-50 px-6 py-4 space-y-2">
                <div className="flex justify-between text-sm text-gray-600">
                  <span>{t('thankyou.subtotal') || 'Subtotal'}</span>
                  <span>€{parseFloat(verifiedOrder.total_amount).toFixed(2)}</span>
                </div>
                
                {verifiedOrder.shipping_cost > 0 && (
                  <div className="flex justify-between text-sm text-gray-600">
                    <span>{t('thankyou.shipping') || 'Shipping'}</span>
                    <span>€{parseFloat(verifiedOrder.shipping_cost).toFixed(2)}</span>
                  </div>
                )}
                
                {parseFloat(verifiedOrder.total_amount) > parseFloat(verifiedOrder.price_after_discount) && (
                  <div className="flex justify-between text-sm text-green-600">
                    <span>{t('thankyou.discount') || 'Discount'}</span>
                    <span>-€{(parseFloat(verifiedOrder.total_amount) - parseFloat(verifiedOrder.price_after_discount)).toFixed(2)}</span>
                  </div>
                )}
                
                {verifiedOrder.wallet_usage > 0 && (
                  <div className="flex justify-between text-sm text-green-600">
                    <span>{t('thankyou.wallet_credit') || 'Wallet Credit'}</span>
                    <span>-€{parseFloat(verifiedOrder.wallet_usage).toFixed(2)}</span>
                  </div>
                )}
                
                <div className="border-t border-gray-200 pt-2 mt-2">
                  <div className="flex justify-between font-bold text-lg">
                    <span>{t('thankyou.total') || 'Total'}</span>
                    <span className="text-[#3084C2]">€{parseFloat(verifiedOrder.final_price).toFixed(2)}</span>
                  </div>
                </div>
              </div>
            </motion.div>
          )}

          {/* Action Buttons */}
          <motion.div 
            variants={itemVariants}
            className="grid grid-cols-1 sm:grid-cols-3 gap-4"
          >
            <button
              onClick={() => window.print()}
              className="bg-white border border-gray-200 text-gray-700 py-3 px-4 rounded-xl font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:bg-gray-50 shadow-sm"
            >
              <Receipt className="w-5 h-5" />
              {t('thankyou.print') || 'Print'}
            </button>
            
            <button
              onClick={() => navigate('/my-account')}
              className="bg-[#3084C2] hover:bg-[#275a8c] text-white py-3 px-4 rounded-xl font-medium transition-all duration-200 flex items-center justify-center gap-2 shadow-md"
            >
              <Package className="w-5 h-5" />
              {t('thankyou.view_orders') || 'View Orders'}
            </button>
            
            <button
              onClick={() => navigate('/store')}
              className="bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-xl font-medium transition-all duration-200 flex items-center justify-center gap-2 shadow-md"
            >
              <ArrowRight className="w-5 h-5" />
              {t('thankyou.continue') || 'Continue Shopping'}
            </button>
          </motion.div>

          {/* Support Section */}
          <motion.div 
            variants={itemVariants}
            className="bg-white rounded-2xl shadow-lg p-6 text-center"
          >
            <h3 className="font-semibold text-gray-800 mb-2">
              {t('thankyou.need_help') || 'Need Help?'}
            </h3>
            <p className="text-gray-600 text-sm mb-4">
              {t('thankyou.support_message') || 'Our support team is available 24/7'}
            </p>
            
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <a 
                href="mailto:support@narzin.com" 
                className="flex items-center justify-center gap-2 text-[#3084C2] hover:underline"
              >
                <Mail className="w-5 h-5" />
                <span>support@narzin.com</span>
              </a>
              
              <span className="hidden sm:inline text-gray-300">|</span>
              
              <a 
                href="tel:+9647801234567" 
                className="flex items-center justify-center gap-2 text-[#3084C2] hover:underline"
              >
                <Phone className="w-5 h-5" />
                <span>+964 780 123 4567</span>
              </a>
            </div>
          </motion.div>
        </motion.div>
      </div>
    </div>
  );
};

export default PaymentCallback;