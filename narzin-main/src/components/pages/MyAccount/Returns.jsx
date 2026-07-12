import React, { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { motion } from 'framer-motion';
import { RefreshCcw, AlertCircle } from 'lucide-react';
import { fetchReturns, requestReturn, clearSubmitError } from '../../../Store/slices/ReturnsSlice';
import { fetchOrders } from '../../../Store/slices/MyOrdersSlice';
import ShowToast from '../../ShowToast';

const REASONS = [
  { value: 'damaged', label: 'Item arrived damaged' },
  { value: 'wrong_item', label: 'Wrong item received' },
  { value: 'not_as_described', label: 'Not as described' },
  { value: 'no_longer_needed', label: 'No longer needed' },
  { value: 'other', label: 'Other' },
];

const REASON_LABELS = REASONS.reduce((acc, r) => {
  acc[r.value] = r.label;
  return acc;
}, {});

const ELIGIBLE_ORDER_STATUSES = ['completed', 'processing'];
const ACTIVE_RETURN_STATUSES = ['requested', 'approved', 'refunded'];

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'requested':
      return 'bg-amber-100 text-amber-800';
    case 'approved':
      return 'bg-blue-100 text-blue-800';
    case 'rejected':
      return 'bg-red-100 text-red-800';
    case 'refunded':
      return 'bg-green-100 text-green-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const Returns = () => {
  const dispatch = useDispatch();

  const { returns, status, error, submitting, submitError } = useSelector((state) => state.returns);
  const { orders } = useSelector((state) => state.myOrders);

  const [orderId, setOrderId] = useState('');
  const [reason, setReason] = useState('');
  const [note, setNote] = useState('');

  useEffect(() => {
    dispatch(fetchReturns());
    if (!orders || orders.length === 0) {
      dispatch(fetchOrders());
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dispatch]);

  const eligibleOrders = Array.isArray(orders)
    ? orders.filter(
        (o) =>
          ELIGIBLE_ORDER_STATUSES.includes(o.payment_status) &&
          !returns.some(
            (r) => r.order_id === o.id && ACTIVE_RETURN_STATUSES.includes(r.status)
          )
      )
    : [];

  const resetForm = () => {
    setOrderId('');
    setReason('');
    setNote('');
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!orderId || !reason || submitting) return;

    dispatch(requestReturn({ orderId, reason, note }))
      .unwrap()
      .then(() => {
        ShowToast('Return requested', 'success');
        resetForm();
        // Refetch so the new return comes back with its eager-loaded `order`
        // relation (the POST response doesn't include it).
        dispatch(fetchReturns());
      })
      .catch(() => {});
  };

  return (
    <div className="max-w-[1200px] mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-2xl font-bold">Returns</h2>
      </div>

      {/* My Returns */}
      <div className="mb-10">
        <h3 className="text-lg font-semibold mb-4">My Returns</h3>

        {status === 'failed' && (
          <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {error || "Couldn't load your returns. Please try again."}
          </div>
        )}

        {returns && returns.length > 0 ? (
          <div className="space-y-4">
            {returns.map((r) => (
              <div key={r.id} className="bg-white border rounded-lg p-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                  <div className="flex items-center gap-4">
                    <RefreshCcw className="w-8 h-8 text-[#3084C2]" />
                    <div>
                      <h4 className="font-medium">Order {r.order?.order_number || r.order_id}</h4>
                      <p className="text-sm text-gray-600">
                        {REASON_LABELS[r.reason] || r.reason}
                      </p>
                    </div>
                  </div>
                  <div className="flex items-center gap-4">
                    <span className="text-sm text-gray-600">{formatDate(r.requested_at || r.created_at)}</span>
                    <span
                      className={`px-3 py-1 rounded-full text-sm capitalize ${getStatusBadgeClass(
                        r.status
                      )}`}
                    >
                      {r.status}
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="text-center py-12 bg-gray-50 rounded-lg">
            <RefreshCcw className="w-12 h-12 mx-auto text-gray-400 mb-4" />
            <p className="text-gray-600">You haven't requested any returns yet.</p>
          </div>
        )}
      </div>

      {/* Start a Return */}
      <div className="bg-white border rounded-lg p-6">
        <h3 className="text-xl font-semibold mb-4">Start a Return</h3>
        <form onSubmit={handleSubmit} className="max-w-md space-y-4">
          <div>
            <label className="block text-sm text-gray-600 mb-1" htmlFor="return-order">
              Order
            </label>
            <p className="text-xs text-gray-400 mb-1">Showing your recent eligible orders.</p>
            <select
              id="return-order"
              value={orderId}
              onChange={(e) => {
                setOrderId(e.target.value);
                if (submitError) dispatch(clearSubmitError());
              }}
              className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
            >
              <option value="">Select an order</option>
              {eligibleOrders.map((o) => (
                <option key={o.id} value={o.id}>
                  {o.order_number}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-sm text-gray-600 mb-1" htmlFor="return-reason">
              Reason
            </label>
            <select
              id="return-reason"
              value={reason}
              onChange={(e) => {
                setReason(e.target.value);
                if (submitError) dispatch(clearSubmitError());
              }}
              className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
            >
              <option value="">Select a reason</option>
              {REASONS.map((r) => (
                <option key={r.value} value={r.value}>
                  {r.label}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-sm text-gray-600 mb-1" htmlFor="return-note">
              Note (optional)
            </label>
            <textarea
              id="return-note"
              value={note}
              maxLength={1000}
              onChange={(e) => setNote(e.target.value)}
              rows={4}
              className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
              placeholder="Tell us more about your return (optional)"
            />
          </div>

          <motion.button
            type="submit"
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.98 }}
            disabled={submitting || !orderId || !reason}
            className="bg-[#3084C2] text-white px-6 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {submitting ? 'Submitting...' : 'Submit Return Request'}
          </motion.button>

          {submitError && (
            <div className="flex items-start gap-2 text-sm text-red-600">
              <AlertCircle className="w-4 h-4 mt-0.5 flex-shrink-0" />
              <span>{submitError}</span>
            </div>
          )}
        </form>
      </div>
    </div>
  );
};

export default Returns;
