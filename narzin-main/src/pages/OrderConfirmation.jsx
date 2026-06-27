import React, { useEffect, useState } from "react";
import { motion } from "framer-motion";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { useSelector, useDispatch } from "react-redux";
import {
  CheckCircle,
  Package,
  Truck,
  Clock,
  ArrowRight,
  ShoppingBag,
  MapPin,
  Printer,
  Mail,
  Copy,
  AlertCircle,
  Loader,
  CreditCard
} from "lucide-react";
import { fetchOrder, setOrderFromCheckout } from "../Store/slices/MyOrdersSlice";
import { toast } from "react-toastify";

const OrderConfirmation = () => {
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();
  const location = useLocation();
  const dispatch = useDispatch();
  
  // Get order data from location state (comes from checkout response)
  const orderDataFromCheckout = location.state?.orderData;
  
  // Get order ID from URL query params or location state
  const queryParams = new URLSearchParams(location.search);
  const orderIdFromQuery = queryParams.get('id') || queryParams.get('order_id');
  const orderIdFromState = location.state?.orderId;
  const orderId = orderIdFromQuery || orderIdFromState || (orderDataFromCheckout ? orderDataFromCheckout.id : null);
  
  // Component state
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);
  
  // Redux state
  const { order, status } = useSelector(state => state.myOrders);
  const checkoutState = useSelector(state => state.checkout);
  
  // Animation variants for staggered animation
  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1
      }
    }
  };
  
  const itemVariants = {
    hidden: { y: 20, opacity: 0 },
    visible: { y: 0, opacity: 1 }
  };

  // If we have order data from checkout, set it in the redux store
  useEffect(() => {
    if (orderDataFromCheckout && !order) {
      dispatch(setOrderFromCheckout(orderDataFromCheckout));
      setIsLoading(false);
    }
  }, [orderDataFromCheckout, dispatch, order]);

  // Fetch order details if needed
  useEffect(() => {
    if (!orderDataFromCheckout && orderId && !order) {
      dispatch(fetchOrder(orderId))
        .unwrap()
        .catch(error => {
          setError(error.message || 'Failed to load order details');
        })
        .finally(() => {
          setIsLoading(false);
        });
    } else if (!orderDataFromCheckout && !orderId) {
      setIsLoading(false);
      setError('Order ID not found');
    }
  }, [dispatch, orderId, orderDataFromCheckout, order]);

  // If we have the order in redux store already, we're not loading
  useEffect(() => {
    if (order) {
      setIsLoading(false);
    }
  }, [order]);

  // Handle "Copy order number" functionality
  const handleCopyOrderNumber = () => {
    if (order && order.order_number) {
      navigator.clipboard.writeText(order.order_number);
      toast.success(t('orderConfirmation.orderNumberCopied') || 'Order number copied to clipboard');
    }
  };

  // Loading state
  if (isLoading || status === 'loading') {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="flex flex-col items-center">
          <Loader className="w-12 h-12 text-[#3084C2] animate-spin mb-4" />
          <p className="text-gray-600">{t('orderConfirmation.loading') || 'Loading order details...'}</p>
        </div>
      </div>
    );
  }

  // Error state
  if (error || status === 'failed' || !order) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="flex flex-col items-center text-center max-w-md p-6 bg-white rounded-lg shadow-md">
          <AlertCircle className="w-12 h-12 text-red-500 mb-4" />
          <h2 className="text-xl font-semibold text-gray-900 mb-2">
            {t('orderConfirmation.orderNotFound') || 'Order Not Found'}
          </h2>
          <p className="text-gray-600 mb-4">
            {error || t('orderConfirmation.orderDetailLoadError') || 'We couldn\'t find your order details.'}
          </p>
          <button 
            onClick={() => navigate('/orders')}
            className="bg-[#3084C2] text-white px-6 py-2 rounded-lg"
          >
            {t('orderConfirmation.viewAllOrders') || 'View All Orders'}
          </button>
        </div>
      </div>
    );
  }

  // Format the date
  const formatDate = (dateString) => {
    if (!dateString) return '';
    
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
  };

  // Estimated delivery date (e.g., 3-5 days for normal shipping, 1-2 days for fast)
  const getEstimatedDelivery = () => {
    if (!order.created_at) return '';
    
    const createdDate = new Date(order.created_at);
    let deliveryDate;
    
    if (order.shipping_type === 'fast') {
      // Fast shipping: 1-2 days
      deliveryDate = new Date(createdDate);
      deliveryDate.setDate(deliveryDate.getDate() + 2);
    } else {
      // Normal shipping: 3-5 days
      deliveryDate = new Date(createdDate);
      deliveryDate.setDate(deliveryDate.getDate() + 5);
    }
    
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return deliveryDate.toLocaleDateString(undefined, options);
  };

  // Helper to parse discount breakdown JSON if present
  const getDiscountBreakdown = () => {
    if (!order.discount_breakdown) return null;
    
    try {
      return typeof order.discount_breakdown === 'string' 
        ? JSON.parse(order.discount_breakdown) 
        : order.discount_breakdown;
    } catch (e) {
      console.error("Error parsing discount breakdown", e);
      return null;
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 py-12">
      <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <motion.div
          initial={{ scale: 0.9, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          className="text-center mb-8"
        >
          <div className="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <CheckCircle className="w-8 h-8 text-green-500" />
          </div>
          <h1 className="text-3xl font-bold text-gray-900 mb-2">
            {t('orderConfirmation.thankYou') || 'Thank You for Your Order!'}
          </h1>
          <p className="text-lg text-gray-600">
            {t('orderConfirmation.orderReceived') || 'Your order has been received and is now being processed.'}
          </p>
        </motion.div>

        {/* Order Details */}
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="bg-white rounded-lg shadow-md overflow-hidden mb-8"
        >
          <div className="bg-gray-50 px-6 py-4 border-b border-gray-100">
            <div className="flex items-center gap-3">
              <Package className="w-5 h-5 text-[#3084C2]" />
              <h2 className="text-lg font-semibold text-gray-800">
                {t('orderConfirmation.orderDetails') || 'Order Details'}
              </h2>
            </div>
          </div>
          
          <div className="p-6 space-y-4">
            <motion.div variants={itemVariants} className="flex justify-between items-center">
              <div className="text-gray-600">
                {t('orderConfirmation.orderNumber') || 'Order Number'}:
              </div>
              <div className="flex items-center gap-2">
                <span className="font-medium">{order.order_number}</span>
                <button 
                  onClick={handleCopyOrderNumber}
                  className="text-[#3084C2] hover:text-[#195e8f]"
                  title={t('orderConfirmation.copyOrderNumber') || 'Copy order number'}
                >
                  <Copy className="w-4 h-4" />
                </button>
              </div>
            </motion.div>
            
            <motion.div variants={itemVariants} className="flex justify-between items-center">
              <div className="text-gray-600">
                {t('orderConfirmation.orderDate') || 'Order Date'}:
              </div>
              <div className="font-medium">
                {formatDate(order.created_at)}
              </div>
            </motion.div>
            
            <motion.div variants={itemVariants} className="flex justify-between items-center">
              <div className="text-gray-600">
                {t('orderConfirmation.paymentMethod') || 'Payment Method'}:
              </div>
              <div className="font-medium">
                {order.payment_status === 'paid' ? 
                  t('orderConfirmation.paid') || 'Paid' : 
                  t('orderConfirmation.cashOnDelivery') || 'Cash on Delivery'}
              </div>
            </motion.div>
            
            <motion.div variants={itemVariants} className="flex justify-between items-center">
              <div className="text-gray-600">
                {t('orderConfirmation.orderStatus') || 'Order Status'}:
              </div>
              <div className={`px-3 py-1 rounded-full text-sm font-medium
                ${order.order_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                  order.order_status === 'shipped' ? 'bg-blue-100 text-blue-800' :
                  order.order_status === 'delivered' ? 'bg-green-100 text-green-800' :
                  order.order_status === 'cancelled' ? 'bg-red-100 text-red-800' :
                  'bg-gray-100 text-gray-800'}`}>
                {order.order_status ? 
                  order.order_status.charAt(0).toUpperCase() + order.order_status.slice(1) : 
                  t('orderConfirmation.processing') || 'Processing'}
              </div>
            </motion.div>
            
            <motion.div variants={itemVariants} className="flex justify-between items-center">
              <div className="text-gray-600">
                {t('orderConfirmation.estimatedDelivery') || 'Estimated Delivery'}:
              </div>
              <div className="font-medium">
                {getEstimatedDelivery()}
              </div>
            </motion.div>
          </div>
        </motion.div>

        {/* Shipping Information */}
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="bg-white rounded-lg shadow-md overflow-hidden mb-8"
        >
          <div className="bg-gray-50 px-6 py-4 border-b border-gray-100">
            <div className="flex items-center gap-3">
              <MapPin className="w-5 h-5 text-[#3084C2]" />
              <h2 className="text-lg font-semibold text-gray-800">
                {t('orderConfirmation.shippingInformation') || 'Shipping Information'}
              </h2>
            </div>
          </div>
          
          <div className="p-6 space-y-4">
            {order.address && (
              <motion.div variants={itemVariants}>
                <h3 className="text-gray-800 font-medium mb-2">
                  {t('orderConfirmation.address') || 'Address'}:
                </h3>
                <p className="text-gray-600">
                  {order.address.address}
                </p>
                {order.address.phone_number && (
                  <p className="text-gray-600 mt-1">
                    {t('orderConfirmation.phoneNumber') || 'Phone'}: {order.address.phone_number}
                  </p>
                )}
                {order.address.postal_code && (
                  <p className="text-gray-600 mt-1">
                    {t('orderConfirmation.postalCode') || 'Postal Code'}: {order.address.postal_code}
                  </p>
                )}
              </motion.div>
            )}
            
            <motion.div variants={itemVariants}>
              <h3 className="text-gray-800 font-medium mb-2">
                {t('orderConfirmation.shippingMethod') || 'Shipping Method'}:
              </h3>
              <p className="flex items-center gap-2 text-gray-600">
                <Truck className="w-4 h-4 text-[#3084C2]" />
                {order.shipping_type === 'fast' ? 
                  t('orderConfirmation.expressShipping') || 'Express Shipping (1-2 Business Days)' : 
                  t('orderConfirmation.standardShipping') || 'Standard Shipping (3-5 Business Days)'}
              </p>
            </motion.div>
          </div>
        </motion.div>

        {/* Order Summary */}
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="bg-white rounded-lg shadow-md overflow-hidden mb-8"
        >
          <div className="bg-gray-50 px-6 py-4 border-b border-gray-100">
            <div className="flex items-center gap-3">
              <ShoppingBag className="w-5 h-5 text-[#3084C2]" />
              <h2 className="text-lg font-semibold text-gray-800">
                {t('orderConfirmation.orderSummary') || 'Order Summary'}
              </h2>
            </div>
          </div>
          
          <div className="p-6 space-y-6">
            {/* Order Items */}
            <motion.div variants={itemVariants} className="space-y-4">
              {order.items && Array.isArray(order.items) && order.items.map((item) => (
                <div key={item.id} className="flex items-center gap-4 py-4 border-b border-gray-100 last:border-0">
                  {/* Product Image */}
                  <div className="w-16 h-16 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden">
                    {item.product?.images && item.product.images.length > 0 ? (
                      <img
                        src={item.product.images[0].url}
                        alt={i18n.language === 'du' ? 
                          item.product.name_german : 
                          item.product.name_arabic}
                        className="w-full h-full object-cover"
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center">
                        <Package className="w-8 h-8 text-gray-400" />
                      </div>
                    )}
                  </div>
                  
                  {/* Product Details */}
                  <div className="flex-1">
                    <h3 className="font-medium text-gray-800">
                      {i18n.language === 'du' ? 
                        item.product?.name_german : 
                        item.product?.name_arabic}
                    </h3>
                    
                    {/* Variant Info if available */}
                    {item.product_variant && item.product_variant.variant_values && (
                      <div className="flex flex-wrap gap-2 mt-1">
                        {item.product_variant.variant_values.map(variant => (
                          <div key={variant.id} className="text-sm text-gray-500 flex items-center gap-1">
                            <span>
                              {i18n.language === 'du' 
                                ? variant.variant_attribute.name_german 
                                : variant.variant_attribute.name_arabic}:
                            </span>
                            {variant.variant_attribute.type === 'color' ? (
                              <div 
                                className="w-3 h-3 rounded-full" 
                                style={{ backgroundColor: variant.value }}
                              ></div>
                            ) : (
                              <span>{variant.value}</span>
                            )}
                          </div>
                        ))}
                      </div>
                    )}
                    
                    {/* Quantity and Price */}
                    <div className="flex items-center gap-3 mt-1 text-sm">
                      <span className="font-medium">€{parseFloat(item.unit_price).toFixed(2)}</span>
                      <span className="text-gray-500">×</span>
                      <span>{item.quantity}</span>
                    </div>
                  </div>
                  
                  {/* Item Total */}
                  <div className="flex-shrink-0 font-medium">
                    €{parseFloat(item.subtotal).toFixed(2)}
                  </div>
                </div>
              ))}
            </motion.div>
            
            {/* Price Breakdown */}
            <motion.div variants={itemVariants} className="space-y-3 pt-4 border-t border-gray-100">
              <div className="flex justify-between text-gray-600">
                <span>{t('orderConfirmation.subtotal') || 'Subtotal'}</span>
                <span>€{parseFloat(order.total_amount || 0).toFixed(2)}</span>
              </div>
              
              <div className="flex justify-between text-gray-600">
                <span>{t('orderConfirmation.shipping') || 'Shipping'}</span>
                <span>
                  {order.shipping_cost ? 
                    `€${parseFloat(order.shipping_cost).toFixed(2)}` : 
                    t('orderConfirmation.free') || 'Free'}
                </span>
              </div>
              
              {parseFloat(order.total_amount) > parseFloat(order.price_after_discount) && (
                <div className="flex justify-between text-green-600">
                  <span>{t('orderConfirmation.discount') || 'Discount'}</span>
                  <span>-€{(parseFloat(order.total_amount) - parseFloat(order.price_after_discount)).toFixed(2)}</span>
                </div>
              )}
              
              {order.wallet_usage && parseFloat(order.wallet_usage) > 0 && (
                <div className="flex justify-between text-green-600">
                  <span>{t('orderConfirmation.walletCredit') || 'Wallet Credit'}</span>
                  <span>-€{parseFloat(order.wallet_usage).toFixed(2)}</span>
                </div>
              )}
              
              <div className="h-px bg-gray-200 my-2" />
              
              <div className="flex justify-between font-bold text-lg text-gray-900">
                <span>{t('orderConfirmation.total') || 'Total'}</span>
                <span>€{parseFloat(order.final_price || 0).toFixed(2)}</span>
              </div>
            </motion.div>
          </div>
        </motion.div>

        {/* Action Buttons */}
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="flex flex-col sm:flex-row gap-4 mt-8"
        >
          <motion.button
            variants={itemVariants}
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.98 }}
            onClick={() => window.print()}
            className="flex-1 bg-gray-100 text-gray-800 py-3 rounded-lg font-medium 
                    flex items-center justify-center gap-2 hover:bg-gray-200"
          >
            <Printer className="w-5 h-5" />
            {t('orderConfirmation.printOrder') || 'Print Order'}
          </motion.button>
          
          <motion.button
            variants={itemVariants}
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.98 }}
            onClick={() => navigate('/orders')}
            className="flex-1 bg-[#3084C2] text-white py-3 rounded-lg font-medium 
                    flex items-center justify-center gap-2 hover:bg-[#195e8f]"
          >
            <ShoppingBag className="w-5 h-5" />
            {t('orderConfirmation.viewAllOrders') || 'View All Orders'}
          </motion.button>
          
          <motion.button
            variants={itemVariants}
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.98 }}
            onClick={() => navigate('/store')}
            className="flex-1 bg-green-600 text-white py-3 rounded-lg font-medium 
                    flex items-center justify-center gap-2 hover:bg-green-700"
          >
            <ArrowRight className="w-5 h-5" />
            {t('orderConfirmation.continueShopping') || 'Continue Shopping'}
          </motion.button>
        </motion.div>

        {/* Support Information */}
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="bg-gray-50 rounded-lg p-6 mt-8 text-center"
        >
          <motion.h3 variants={itemVariants} className="text-lg font-medium text-gray-800 mb-2">
            {t('orderConfirmation.haveQuestions') || 'Have Questions?'}
          </motion.h3>
          
          <motion.p variants={itemVariants} className="text-gray-600 mb-4">
            {t('orderConfirmation.contactSupport') || 'Our customer support team is here to help you.'}
          </motion.p>
          
          <motion.div variants={itemVariants} className="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="mailto:support@example.com" className="flex items-center justify-center gap-2 text-[#3084C2]">
              <Mail className="w-5 h-5" />
              <span>support@example.com</span>
            </a>
            
            <span className="hidden sm:inline text-gray-400">|</span>
            
            <a href="tel:+1234567890" className="flex items-center justify-center gap-2 text-[#3084C2]">
              <Phone className="w-5 h-5" />
              <span>+123 456 7890</span>
            </a>
          </motion.div>
        </motion.div>
      </div>
    </div>
  );
};

// Phone icon component
const Phone = ({ className }) => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    strokeWidth="2"
    strokeLinecap="round"
    strokeLinejoin="round"
    className={className}
  >
    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
  </svg>
);

export default OrderConfirmation;