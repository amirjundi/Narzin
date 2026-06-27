import React, { useEffect, useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { useNavigate } from 'react-router-dom';
import { useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import {
  Search,
  Package,
  ChevronDown,
  ChevronRight,
  Clock,
  Truck,
  FileText,
  RefreshCw,
  AlertCircle,
  Loader,
  ArrowLeft,
  ArrowRight,
  XCircle
} from 'lucide-react';
import { fetchOrders } from '../../../Store/slices/MyOrdersSlice';
import { toast } from 'react-toastify';

const MyOrders = () => {
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  
  // Component state
  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus, setFilterStatus] = useState('all');
  const [expandedOrder, setExpandedOrder] = useState(null);
  const [currentPage, setCurrentPage] = useState(1);
  
  // Redux state
  const { orders, status, error, pagination } = useSelector(state => state.myOrders);
  
  // Fetch orders on component mount
  useEffect(() => {
    dispatch(fetchOrders());
  }, [dispatch]);
  
  // Status colors
  const getStatusColor = (status) => {
    switch (status) {
      case 'delivered':
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'processing':
      case 'pending':
      case 'shipped':
        return 'bg-blue-100 text-blue-800';
      case 'cancelled':
        return 'bg-red-100 text-red-800';
      case 'returned':
        return 'bg-amber-100 text-amber-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };
  
  // Format date
  const formatDate = (dateString) => {
    if (!dateString) return '';
    
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
  };
  
  // Filter orders based on search term and status filter
  const filteredOrders = Array.isArray(orders) ? orders.filter(order => {
    const matchesSearch = order.order_number?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         order.items.some(item => {
                           const productName = i18n.language === 'du' ? 
                             item.product?.name_german : 
                             item.product?.name_arabic;
                           return productName?.toLowerCase().includes(searchTerm.toLowerCase());
                         });
    
    const matchesStatus = filterStatus === 'all' || order.order_status === filterStatus;
    return matchesSearch && matchesStatus;
  }) : [];
  
  // Order Card Component
  const OrderCard = ({ order }) => {
    const isExpanded = expandedOrder === order.id;
    
    // Get tracking events from order status history if available
    const getTrackingEvents = () => {
      // If there's a dedicated tracking history, use that
      if (order.tracking_history && Array.isArray(order.tracking_history)) {
        return order.tracking_history;
      }
      
      // Otherwise, generate simple events based on order status and dates
      const events = [];
      
      if (order.order_status === 'delivered' || order.order_status === 'completed') {
        events.push({ date: formatDate(order.updated_at), status: 'Delivered' });
      }
      
      if (order.order_status === 'shipped' || events.length > 0) {
        events.push({ date: formatDate(order.updated_at), status: 'Shipped' });
      }
      
      if (order.order_status === 'processing' || events.length > 0) {
        events.push({ date: formatDate(order.created_at), status: 'Processing' });
      }
      
      // Always include order placed
      events.push({ date: formatDate(order.created_at), status: 'Order Placed' });
      
      // Special cases for canceled/returned
      if (order.order_status === 'cancelled') {
        events.unshift({ date: formatDate(order.updated_at), status: 'Cancelled' });
      }
      
      if (order.order_status === 'returned') {
        events.unshift({ date: formatDate(order.updated_at), status: 'Returned' });
      }
      
      // Sort events newest first
      return events.filter((event, index, self) => 
        index === self.findIndex((e) => e.status === event.status)
      );
    };
    
    return (
      <motion.div
        layout
        className="bg-white border rounded-lg overflow-hidden mb-4"
      >
        {/* Order Header */}
        <div 
          className="p-4 cursor-pointer hover:bg-gray-50"
          onClick={() => setExpandedOrder(isExpanded ? null : order.id)}
        >
          <div className="flex flex-wrap items-center justify-between gap-4">
            <div className="flex items-center gap-4">
              <Package className="w-10 h-10 text-[#3084C2]" />
              <div>
                <h3 className="font-medium">{order.order_number}</h3>
                <p className="text-sm text-gray-600">{formatDate(order.created_at)}</p>
              </div>
            </div>
            <div className="flex items-center gap-4">
              <span className="font-medium">€{parseFloat(order.final_price).toFixed(2)}</span>
              <span className={`px-3 py-1 rounded-full text-sm capitalize ${getStatusColor(order.order_status)}`}>
                {order.order_status}
              </span>
              <ChevronDown 
                className={`w-5 h-5 transition-transform ${isExpanded ? 'rotate-180' : ''}`} 
              />
            </div>
          </div>
        </div>

        {/* Expanded Content */}
        <AnimatePresence>
          {isExpanded && (
            <motion.div
              initial={{ height: 0, opacity: 0 }}
              animate={{ height: 'auto', opacity: 1 }}
              exit={{ height: 0, opacity: 0 }}
              className="border-t"
            >
              {/* Order Items */}
              <div className="p-4 space-y-4">
                {order.items.map((item) => (
                  <div key={item.id} className="flex gap-4">
                    {item.product?.images && item.product.images.length > 0 ? (
                      <img
                        src={item.product.images[0].url}
                        alt={i18n.language === 'du' ? 
                          item.product.name_german : 
                          item.product.name_arabic}
                        className="w-20 h-20 object-cover rounded-md"
                      />
                    ) : (
                      <div className="w-20 h-20 bg-gray-200 rounded-md flex items-center justify-center">
                        <Package className="w-8 h-8 text-gray-400" />
                      </div>
                    )}
                    
                    <div className="flex-1">
                      <h4 className="font-medium">
                        {i18n.language === 'du' ? 
                          item.product?.name_german : 
                          item.product?.name_arabic}
                      </h4>
                      
                      {/* Variant Info */}
                      {item.product_variant && item.product_variant.variant_values && (
                        <p className="text-sm text-gray-600">
                          {item.product_variant.variant_values.map((variant, idx) => (
                            <span key={idx}>
                              {idx > 0 && " • "}
                              {variant.variant_attribute.type === 'color' ? (
                                <span className="inline-flex items-center gap-1">
                                  {i18n.language === 'du' ? 
                                    variant.variant_attribute.name_german : 
                                    variant.variant_attribute.name_arabic}: 
                                  <span 
                                    className="inline-block w-3 h-3 rounded-full" 
                                    style={{ backgroundColor: variant.value }}
                                  />
                                </span>
                              ) : (
                                <span>
                                  {i18n.language === 'du' ? 
                                    variant.variant_attribute.name_german : 
                                    variant.variant_attribute.name_arabic}: {variant.value}
                                </span>
                              )}
                            </span>
                          ))}
                        </p>
                      )}
                      
                      <div className="flex justify-between mt-2">
                        <span className="text-gray-600">{t('orders.quantity')}: {item.quantity}</span>
                        <span className="font-medium">€{parseFloat(item.unit_price).toFixed(2)}</span>
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              {/* Tracking Info */}
              <div className="border-t p-4">
                <h4 className="font-medium mb-4">{t('orders.trackingInformation')}</h4>
                <div className="space-y-4">
                  {order.tracking_number && (
                    <div className="flex items-center gap-2 text-sm">
                      <span className="text-gray-600">{t('orders.trackingNumber')}:</span>
                      <span className="font-medium">{order.tracking_number}</span>
                      {order.tracking_carrier && (
                        <span className="text-[#3084C2]">({order.tracking_carrier})</span>
                      )}
                    </div>
                  )}
                  <div className="space-y-3">
                    {getTrackingEvents().map((event, index) => (
                      <div key={index} className="flex items-start gap-3">
                        <div className="relative">
                          <div className={`w-4 h-4 rounded-full ${index === 0 ? 'bg-[#3084C2]' : 'bg-gray-200'}`} />
                          {index !== getTrackingEvents().length - 1 && (
                            <div className="absolute top-4 left-2 w-px h-12 bg-gray-200" />
                          )}
                        </div>
                        <div>
                          <p className="font-medium">{event.status}</p>
                          <p className="text-sm text-gray-600">{event.date}</p>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>

              {/* Order Summary */}
              <div className="border-t p-4">
                <h4 className="font-medium mb-4">{t('orders.orderSummary')}</h4>
                <div className="space-y-2">
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">{t('orders.subtotal')}:</span>
                    <span>€{parseFloat(order.total_amount).toFixed(2)}</span>
                  </div>
                  
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">{t('orders.shipping')}:</span>
                    <span>€{parseFloat(order.shipping_cost || 0).toFixed(2)}</span>
                  </div>
                  
                  {parseFloat(order.total_amount) > parseFloat(order.price_after_discount) && (
                    <div className="flex justify-between text-sm text-green-600">
                      <span>{t('orders.discount')}:</span>
                      <span>-€{(parseFloat(order.total_amount) - parseFloat(order.price_after_discount)).toFixed(2)}</span>
                    </div>
                  )}
                  
                  {order.wallet_usage && parseFloat(order.wallet_usage) > 0 && (
                    <div className="flex justify-between text-sm text-green-600">
                      <span>{t('orders.walletCredit')}:</span>
                      <span>-€{parseFloat(order.wallet_usage).toFixed(2)}</span>
                    </div>
                  )}
                  
                  <div className="border-t pt-2 mt-2">
                    <div className="flex justify-between font-medium">
                      <span>{t('orders.total')}:</span>
                      <span>€{parseFloat(order.final_price).toFixed(2)}</span>
                    </div>
                  </div>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="border-t p-4 bg-gray-50 flex flex-wrap gap-3">

                
                {(order.order_status === 'delivered' || order.order_status === 'completed') && (
                  <motion.button
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                    onClick={() => handleReturnOrder(order.id)}
                    className="flex items-center gap-2 px-4 py-2 rounded-lg border border-[#3084C2] text-[#3084C2]"
                  >
                    <RefreshCw className="w-4 h-4" />
                    {t('orders.returnItems')}
                  </motion.button>
                )}
                
                {(order.order_status === 'pending' || order.order_status === 'processing') && (
                  <motion.button
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                    onClick={() => handleCancelOrder(order.id)}
                    className="flex items-center gap-2 px-4 py-2 rounded-lg border border-red-500 text-red-500"
                  >
                    <XCircle className="w-4 h-4" />
                    {t('orders.cancelOrder')}
                  </motion.button>
                )}
                
                <motion.button
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.98 }}
                  className="flex items-center gap-2 px-4 py-2 rounded-lg border text-gray-600"
                >
                  <AlertCircle className="w-4 h-4" />
                  {t('orders.needHelp')}
                </motion.button>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </motion.div>
    );
  };
  
  // Handle order cancellation
  const handleCancelOrder = (orderId) => {
    // Placeholder for cancel order API call
    toast.info(t('orders.cancelOrderConfirm'));
  };
  
  // Handle order returns
  const handleReturnOrder = (orderId) => {
    // Placeholder for return order API call
    toast.info(t('orders.returnOrderConfirm'));
  };

  // Loading state
  if (status === 'loading' && (!orders || orders.length === 0)) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="flex flex-col items-center">
          <Loader className="w-12 h-12 text-[#3084C2] animate-spin mb-4" />
          <p className="text-gray-600">{t('orders.loading') || 'Loading your orders...'}</p>
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
          <h2 className="text-xl font-semibold mb-2">{t('orders.loadError') || 'Error Loading Orders'}</h2>
          <p className="text-gray-600 mb-4">{error || t('orders.loadErrorMessage')}</p>
          <button
            onClick={() => dispatch(fetchOrders())}
            className="px-4 py-2 bg-[#3084C2] text-white rounded-lg"
          >
            {t('orders.tryAgain') || 'Try Again'}
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-[1200px] mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-2xl font-bold">{t('orders.myOrders') || 'My Orders'}</h2>
      </div>

      {/* Filters */}
      <div className="flex flex-wrap gap-4 mb-6">
        <div className="flex-1">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
            <input
              type="text"
              placeholder={t('orders.searchOrders') || 'Search orders...'}
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
            />
          </div>
        </div>
        <select
          value={filterStatus}
          onChange={(e) => setFilterStatus(e.target.value)}
          className="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
        >
          <option value="all">{t('orders.allOrders') || 'All Orders'}</option>
          <option value="pending">{t('orders.pending') || 'Pending'}</option>
          <option value="processing">{t('orders.processing') || 'Processing'}</option>
          <option value="shipped">{t('orders.shipped') || 'Shipped'}</option>
          <option value="delivered">{t('orders.delivered') || 'Delivered'}</option>
          <option value="completed">{t('orders.completed') || 'Completed'}</option>
          <option value="cancelled">{t('orders.cancelled') || 'Cancelled'}</option>
          <option value="returned">{t('orders.returned') || 'Returned'}</option>
        </select>
      </div>

      {/* Orders List */}
      <div className="space-y-4">
        {filteredOrders.length > 0 ? (
          filteredOrders.map((order) => (
            <OrderCard key={order.id} order={order} />
          ))
        ) : (
          <div className="text-center py-12 bg-gray-50 rounded-lg">
            <Package className="w-12 h-12 mx-auto text-gray-400 mb-4" />
            <h3 className="text-lg font-medium text-gray-900 mb-2">
              {t('orders.noOrdersFound') || 'No orders found'}
            </h3>
            <p className="text-gray-600">
              {searchTerm
                ? t('orders.noMatchingOrders') || "No orders match your search criteria"
                : t('orders.noOrdersYet') || "You haven't placed any orders yet"}
            </p>
            {!searchTerm && (
              <button
                onClick={() => navigate('/store')}
                className="mt-4 px-4 py-2 bg-[#3084C2] text-white rounded-lg inline-flex items-center gap-2"
              >
                {t('orders.startShopping') || 'Start Shopping'} <ArrowRight className="w-4 h-4" />
              </button>
            )}
          </div>
        )}
      </div>
      
      {/* Pagination */}
      {pagination && pagination.lastPage > 1 && (
        <div className="flex justify-center items-center gap-2 mt-8">
          <button
            onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
            disabled={currentPage === 1}
            className="p-2 border rounded-md disabled:opacity-50"
          >
            <ArrowLeft className="w-4 h-4" />
          </button>
          
          <div className="flex items-center gap-1">
            {[...Array(pagination.lastPage)].map((_, index) => (
              <button
                key={index}
                onClick={() => setCurrentPage(index + 1)}
                className={`w-8 h-8 rounded-md ${
                  currentPage === index + 1 
                    ? 'bg-[#3084C2] text-white' 
                    : 'border hover:bg-gray-50'
                }`}
              >
                {index + 1}
              </button>
            ))}
          </div>
          
          <button
            onClick={() => setCurrentPage(prev => Math.min(prev + 1, pagination.lastPage))}
            disabled={currentPage === pagination.lastPage}
            className="p-2 border rounded-md disabled:opacity-50"
          >
            <ArrowRight className="w-4 h-4" />
          </button>
        </div>
      )}
    </div>
  );
};

export default MyOrders;