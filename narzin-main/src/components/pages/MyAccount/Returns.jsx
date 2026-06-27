import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  RefreshCcw,
  Search,
  ChevronDown,
  Package,
  ArrowLeft,
  FileText,
  MessageCircle,
  Truck,
  AlertCircle,
  Check
} from 'lucide-react';

const Returns = () => {
  const [activeTab, setActiveTab] = useState('returns');
  const [searchTerm, setSearchTerm] = useState('');
  const [expandedReturn, setExpandedReturn] = useState(null);

  // Mock returns data
  const returns = [
    {
      id: "RET-2024-001",
      orderId: "ORD-2024-3847",
      date: "Feb 20, 2024",
      status: "in-progress",
      reason: "Wrong Size",
      refundAmount: 89.99,
      items: [
        {
          id: 1,
          name: "Modern Comfort Hoodie",
          color: "Slate",
          size: "M",
          price: 89.99,
          image: "/api/placeholder/400/400",
          reason: "Too Small",
          condition: "Unworn"
        }
      ],
      tracking: {
        returnLabel: "1Z999AA1234567890",
        status: "Return Package In Transit",
        events: [
          { date: "Feb 20, 2024", status: "Return Label Created" },
          { date: "Feb 21, 2024", status: "Package Picked Up" }
        ]
      }
    },
    {
      id: "RET-2024-002",
      orderId: "ORD-2024-3846",
      date: "Feb 15, 2024",
      status: "completed",
      reason: "Changed Mind",
      refundAmount: 175.99,
      items: [
        {
          id: 3,
          name: "Premium Cotton T-Shirt",
          color: "White",
          size: "L",
          price: 175.99,
          image: "/api/placeholder/400/400",
          reason: "Style Not as Expected",
          condition: "Unworn"
        }
      ],
      tracking: {
        returnLabel: "1Z999AA1234567891",
        status: "Refund Processed",
        events: [
          { date: "Feb 15, 2024", status: "Return Label Created" },
          { date: "Feb 16, 2024", status: "Package Picked Up" },
          { date: "Feb 18, 2024", status: "Return Received" },
          { date: "Feb 19, 2024", status: "Refund Processed" }
        ]
      }
    }
  ];

  const getStatusColor = (status) => {
    switch (status) {
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'in-progress':
        return 'bg-blue-100 text-blue-800';
      case 'cancelled':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const ReturnCard = ({ returnItem }) => {
    const isExpanded = expandedReturn === returnItem.id;

    return (
      <motion.div
        layout
        className="bg-white border rounded-lg overflow-hidden mb-4"
      >
        {/* Return Header */}
        <div 
          className="p-4 cursor-pointer hover:bg-gray-50"
          onClick={() => setExpandedReturn(isExpanded ? null : returnItem.id)}
        >
          <div className="flex flex-wrap items-center justify-between gap-4">
            <div className="flex items-center gap-4">
              <RefreshCcw className="w-10 h-10 text-[#3084C2]" />
              <div>
                <h3 className="font-medium">{returnItem.id}</h3>
                <p className="text-sm text-gray-600">Order: {returnItem.orderId}</p>
              </div>
            </div>
            <div className="flex items-center gap-4">
              <span className="font-medium">${returnItem.refundAmount}</span>
              <span className={`px-3 py-1 rounded-full text-sm capitalize ${getStatusColor(returnItem.status)}`}>
                {returnItem.status.replace('-', ' ')}
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
              {/* Return Items */}
              <div className="p-4 space-y-4">
                {returnItem.items.map((item) => (
                  <div key={item.id} className="flex gap-4">
                    <img
                      src={item.image}
                      alt={item.name}
                      className="w-20 h-20 object-cover rounded-md"
                    />
                    <div className="flex-1">
                      <h4 className="font-medium">{item.name}</h4>
                      <p className="text-sm text-gray-600">
                        {item.color} • Size {item.size}
                      </p>
                      <div className="mt-2 space-y-1">
                        <p className="text-sm">
                          <span className="text-gray-600">Reason:</span> {item.reason}
                        </p>
                        <p className="text-sm">
                          <span className="text-gray-600">Condition:</span> {item.condition}
                        </p>
                      </div>
                    </div>
                    <div className="text-right">
                      <span className="font-medium">${item.price}</span>
                    </div>
                  </div>
                ))}
              </div>

              {/* Return Tracking */}
              <div className="border-t p-4">
                <h4 className="font-medium mb-4">Return Status</h4>
                <div className="space-y-4">
                  {returnItem.tracking.returnLabel && (
                    <div className="flex items-center gap-2 text-sm">
                      <span className="text-gray-600">Return Label:</span>
                      <span className="font-medium">{returnItem.tracking.returnLabel}</span>
                    </div>
                  )}
                  <div className="space-y-3">
                    {returnItem.tracking.events.map((event, index) => (
                      <div key={index} className="flex items-start gap-3">
                        <div className="relative">
                          <div className={`w-4 h-4 rounded-full ${index === 0 ? 'bg-[#3084C2]' : 'bg-gray-200'}`} />
                          {index !== returnItem.tracking.events.length - 1 && (
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

              {/* Action Buttons */}
              <div className="border-t p-4 bg-gray-50 flex flex-wrap gap-3">
                <motion.button
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.98 }}
                  className="flex items-center gap-2 px-4 py-2 rounded-lg bg-[#3084C2] text-white"
                >
                  <Truck className="w-4 h-4" />
                  Print Return Label
                </motion.button>
                <motion.button
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.98 }}
                  className="flex items-center gap-2 px-4 py-2 rounded-lg border text-gray-600"
                >
                  <MessageCircle className="w-4 h-4" />
                  Contact Support
                </motion.button>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </motion.div>
    );
  };

  const StartReturn = () => (
    <div className="bg-white border rounded-lg p-6">
      <h3 className="text-xl font-semibold mb-4">Start a New Return</h3>
      <div className="max-w-md">
        <p className="text-gray-600 mb-6">
          Enter your order number to begin the return process. You can find this number in your order confirmation email.
        </p>
        <div className="flex gap-3">
          <input
            type="text"
            placeholder="Order number (e.g., ORD-2024-XXXX)"
            className="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
          />
          <motion.button
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.98 }}
            className="bg-[#3084C2] text-white px-6 py-2 rounded-lg"
          >
            Continue
          </motion.button>
        </div>
      </div>
    </div>
  );

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-2xl font-bold">Returns</h2>
      </div>

      {/* Tabs */}
      <div className="flex gap-4 mb-6">
        <motion.button
          whileHover={{ scale: 1.02 }}
          whileTap={{ scale: 0.98 }}
          onClick={() => setActiveTab('returns')}
          className={`px-4 py-2 rounded-lg ${
            activeTab === 'returns'
              ? 'bg-[#3084C2] text-white'
              : 'bg-gray-100 text-gray-600'
          }`}
        >
          Return History
        </motion.button>
        <motion.button
          whileHover={{ scale: 1.02 }}
          whileTap={{ scale: 0.98 }}
          onClick={() => setActiveTab('start')}
          className={`px-4 py-2 rounded-lg ${
            activeTab === 'start'
              ? 'bg-[#3084C2] text-white'
              : 'bg-gray-100 text-gray-600'
          }`}
        >
          Start a Return
        </motion.button>
      </div>

      {activeTab === 'returns' ? (
        <>
          {/* Search */}
          <div className="mb-6">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <input
                type="text"
                placeholder="Search returns..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
              />
            </div>
          </div>

          {/* Returns List */}
          <div className="space-y-4">
            {returns.length > 0 ? (
              returns.map((returnItem) => (
                <ReturnCard key={returnItem.id} returnItem={returnItem} />
              ))
            ) : (
              <div className="text-center py-12 bg-gray-50 rounded-lg">
                <RefreshCcw className="w-12 h-12 mx-auto text-gray-400 mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">
                  No returns found
                </h3>
                <p className="text-gray-600">
                  You haven't made any returns yet
                </p>
              </div>
            )}
          </div>
        </>
      ) : (
        <StartReturn />
      )}
    </div>
  );
};

export default Returns;