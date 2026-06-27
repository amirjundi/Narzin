import React, { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { useTranslation } from "react-i18next";
import { toast } from "react-toastify";
import {
  MapPin,
  CreditCard,
  Truck,
  Shield,
  Package,
  Clock,
  Gift,
  CheckCircle,
  Loader,
  X,
  Check,
  AlertCircle,
  ChevronDown,
  ChevronUp,
  Plus,
  RefreshCw,
  Wallet,
} from "lucide-react";

// Import Redux actions
import { fetchAddress } from "../Store/slices/AddressSlice";
import { fetchCart } from "../Store/slices/CardSlice";
import { validateCoupon, clearCoupon } from "../Store/slices/CouponSlice";
import {
  initiatePayment,
  setShippingOption,
} from "../Store/slices/CheckoutSlice";
import { fetchWallet } from "../Store/slices/WalletSlice";
import { fetchShipping } from "../Store/slices/ShippingSlice";

const CheckoutPage = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { t, i18n } = useTranslation();

  // Component state
  const [isProcessing, setIsProcessing] = useState(false);
  const [couponCode, setCouponCode] = useState("");
  const [selectedAddressId, setSelectedAddressId] = useState(null);
  const [paymentMethod, setPaymentMethod] = useState("card");
  const [useWalletCredit, setUseWalletCredit] = useState(false);

  // Redux state
  const { items: cartItems, status: cartStatus } = useSelector(
    (state) => state.cart
  );
  const { items: addresses, AddressStatus } = useSelector(
    (state) => state.address
  );
  const {
    coupon,
    status: couponStatus,
    error: couponError,
  } = useSelector((state) => state.coupon);
  const { status: orderStatus, error: orderError, currentOrder, selectedShipping } = useSelector(
    (state) => state.checkout
  );
  const { wallet, status: walletStatus } = useSelector((state) => state.wallet);
  const { shippingPrices, shippingStatus } = useSelector(
    (state) => state.shippingPrices
  );

  // Fetch data on component mount
  useEffect(() => {
    dispatch(fetchShipping());
    dispatch(fetchCart());
    dispatch(fetchAddress());
    dispatch(fetchWallet());
  }, [dispatch]);
  // Set default address when addresses are loaded
  useEffect(() => {
    if (addresses && Array.isArray(addresses) && addresses.length > 0) {
      // Try to find default address first
      const defaultAddress = addresses.find((addr) => addr.is_default === 1);
      if (defaultAddress) {
        setSelectedAddressId(defaultAddress.id);
      } else {
        // Otherwise use the first address
        setSelectedAddressId(addresses[0].id);
      }
    }
  }, [addresses]);

  useEffect(() => {
    dispatch(setShippingOption(''));
  }, [selectedAddressId, dispatch]);

  // Calculate order summary
  const calculateSubtotal = () => {
    if (!cartItems || !Array.isArray(cartItems)) return 0;
    return cartItems
      .filter((item) => !item.out_of_stock)
      .reduce((sum, item) => sum + parseFloat(item.price) * item.quantity, 0);
  };

  const subtotal = calculateSubtotal();

  // Calculate total weight
  const totalWeight = React.useMemo(() => {
    if (!cartItems || !Array.isArray(cartItems)) return 0;
    return cartItems
      .filter((item) => !item.out_of_stock)
      .reduce((sum, item) => sum + ((parseFloat(item.product?.weight) || 0) * item.quantity), 0);
  }, [cartItems]);

  // Shipping cost based on selected option
  const shippingCost = React.useMemo(() => {
    if (!shippingPrices || !Array.isArray(shippingPrices) || !selectedAddressId || !selectedShipping) return 0;
    
    const address = addresses?.find(a => a.id === selectedAddressId);
    if (!address) return 0;

    const zone = shippingPrices.find(z => z.id === address.delivery_zone_id);
    if (!zone || !zone.delivery_methods) return 0;

    const method = zone.delivery_methods.find(m => m.id === selectedShipping);
    if (!method) return 0;

    return parseFloat(method.base_price || 0) + (totalWeight * parseFloat(method.price_per_kg || 0));
  }, [selectedShipping, shippingPrices, selectedAddressId, addresses, totalWeight]);

  // Check if coupon applies to a specific item (for vendor-specific coupons)
  const isCouponApplicableToItem = (item) => {
    if (!coupon) return false;

    // If coupon has no vendor_id, it applies to all items
    if (!coupon.vendor_id) return true;

    // Check if the item's product belongs to the coupon's vendor
    return item.product?.vendor_id === coupon.vendor_id;
  };

  // Calculate discount based on coupon type and vendor
  const calculateDiscount = () => {
    if (!coupon || !cartItems || !Array.isArray(cartItems)) return 0;

    // For vendor-specific coupon, only apply to items from that vendor
    if (coupon.vendor_id) {
      let vendorSpecificDiscount = 0;

      if (coupon.discount_type === "percentage") {
        // For percentage discounts, apply to each eligible item
        cartItems.forEach((item) => {
          if (item.product?.vendor_id === coupon.vendor_id) {
            const itemTotal = parseFloat(item.price) * item.quantity;
            vendorSpecificDiscount +=
              (itemTotal * parseFloat(coupon.discount_amount)) / 100;
          }
        });
      } else if (coupon.discount_type === "fixed") {
        // For fixed discounts, apply the whole amount if there's any eligible item
        const hasEligibleItem = cartItems.some(
          (item) => item.product?.vendor_id === coupon.vendor_id
        );
        if (hasEligibleItem) {
          vendorSpecificDiscount = parseFloat(coupon.discount_amount);
        }
      }

      return vendorSpecificDiscount;
    } else {
      // For non-vendor-specific coupons, apply to all items
      if (coupon.discount_type === "percentage") {
        return (subtotal * parseFloat(coupon.discount_amount)) / 100;
      } else if (coupon.discount_type === "fixed") {
        return parseFloat(coupon.discount_amount);
      }
    }

    return 0;
  };

  // Calculate individual item discount (for display purposes)
  const calculateItemDiscount = (item) => {
    if (
      !isCouponApplicableToItem(item) ||
      coupon.discount_type !== "percentage"
    )
      return 0;

    const itemTotal = parseFloat(item.price) * item.quantity;
    return (itemTotal * parseFloat(coupon.discount_amount)) / 100;
  };

  const discount = calculateDiscount();

  // Wallet balance (convert from string to number)
  const walletBalance = wallet ? parseFloat(wallet.balance) : 0;

  // Calculate total
  const total = subtotal + shippingCost - discount;
  const finalTotal =
    useWalletCredit && wallet
      ? Math.max(0, total - Math.min(walletBalance, total))
      : total;

  // Handle coupon validation
  const handleApplyCoupon = () => {
    if (!couponCode.trim()) {
      toast.error(t("checkout.enterCoupon") || "Please enter a coupon code");
      return;
    }

    dispatch(validateCoupon(couponCode.trim()));
  };

  // Handle coupon removal
  const handleRemoveCoupon = () => {
    dispatch(clearCoupon());
    setCouponCode("");
  };

  // Handle place order
  const handlePlaceOrder = () => {
    if (!selectedAddressId) {
      toast.error(
        t("checkout.selectAddress") || "Please select a delivery address"
      );
      return;
    }

    if (
      !cartItems ||
      !Array.isArray(cartItems) ||
      cartItems.filter((item) => !item.out_of_stock).length === 0
    ) {
      toast.error(
        t("checkout.noItemsAvailable") || "No items available for checkout"
      );
      return;
    }

    if (!selectedShipping) {
      toast.error("Please select a delivery method");
      return;
    }

    setIsProcessing(true);

    const checkoutData = {
      address_id: selectedAddressId,
      delivery_method_id: selectedShipping,
      coupon: coupon ? coupon.code : "",
      wallet: useWalletCredit,
    };

    dispatch(initiatePayment(checkoutData))
      .unwrap()
      .then((response) => {
        if (response.data?.payment?.type === "redirect") {
          // Redirect to Nass payment gateway
          const { payment_url, transaction_params } = response.data.payment;

          // Create form to submit to Nass
          const form = document.createElement("form");
          form.method = "POST";
          form.action = payment_url;

          Object.entries(transaction_params).forEach(([key, value]) => {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = key;
            input.value = value;
            form.appendChild(input);
          });

          toast.info(
            t("checkout.redirectingToPayment") || "Redirecting to payment..."
          );

          document.body.appendChild(form);
          form.submit();
        }
      })
      .catch((error) => {
        toast.error(
          error.message ||
            t("checkout.paymentFailed") ||
            "Payment initiation failed"
        );
      })
      .finally(() => {
        setIsProcessing(false);
      });
  };

  // Determine if we can show the page content
  const isLoading =
    (cartStatus === "loading" && (!cartItems || cartItems.length === 0)) ||
    (AddressStatus === "loading" &&
      (!addresses || !Array.isArray(addresses) || addresses.length === 0));

  const hasError = cartStatus === "failed" || AddressStatus === "failed";

  // Loading state
  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="flex flex-col items-center">
          <Loader className="w-12 h-12 text-[#3084C2] animate-spin mb-4" />
          <p className="text-gray-600">
            {t("checkout.loading") || "Loading checkout..."}
          </p>
        </div>
      </div>
    );
  }

  // Error state
  if (hasError) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="flex flex-col items-center text-center max-w-md p-6 bg-white rounded-lg shadow-md">
          <AlertCircle className="w-12 h-12 text-red-500 mb-4" />
          <h2 className="text-xl font-semibold text-gray-900 mb-2">
            {t("checkout.loadError") || "Error loading checkout"}
          </h2>
          <p className="text-gray-600 mb-4">
            {t("checkout.tryAgainLater") || "Please try again later"}
          </p>
          <button
            onClick={() => navigate("/cart")}
            className="bg-[#3084C2] text-white px-6 py-2 rounded-lg"
          >
            {t("checkout.backToCart") || "Back to Cart"}
          </button>
        </div>
      </div>
    );
  }

  // Empty cart state
  if (!cartItems || !Array.isArray(cartItems) || cartItems.length === 0) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="flex flex-col items-center text-center max-w-md p-6 bg-white rounded-lg shadow-md">
          <Package className="w-12 h-12 text-[#3084C2] mb-4" />
          <h2 className="text-xl font-semibold text-gray-900 mb-2">
            {t("checkout.emptyCart") || "Your cart is empty"}
          </h2>
          <p className="text-gray-600 mb-4">
            {t("checkout.addItemsMessage") ||
              "Add items to your cart to continue shopping"}
          </p>
          <button
            onClick={() => navigate("/store")}
            className="bg-[#3084C2] text-white px-6 py-2 rounded-lg"
          >
            {t("checkout.shopNow") || "Shop Now"}
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-8 mt-12">
      <div className="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="text-center mb-8"
        >
          <h1 className="text-3xl font-bold text-gray-900">
            {t("checkout.title") || "Checkout"}
          </h1>
          <p className="text-gray-500 mt-2">
            {t("checkout.subtitle") || "Complete your order in one simple step"}
          </p>
        </motion.div>

        {/* Shopping Benefits */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8"
        >
          {[
            {
              icon: Truck,
              text: t("checkout.freeShipping") || "Fast Delivery",
            },
            {
              icon: Shield,
              text: t("checkout.secureCheckout") || "Secure Checkout",
            },
            { icon: RefreshCw, text: t("checkout.returns") || "Easy Returns" },
            { icon: Clock, text: t("checkout.support") || "24/7 Support" },
          ].map((benefit, index) => (
            <div
              key={index}
              className="bg-white p-4 rounded-lg shadow-sm flex items-center gap-3"
            >
              <benefit.icon className="w-5 h-5 text-[#3084C2]" />
              <span className="text-sm text-gray-600">{benefit.text}</span>
            </div>
          ))}
        </motion.div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
          {/* Main Checkout Content */}
          <div className="lg:col-span-8 space-y-6">
            {/* Delivery Address Section */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="bg-white rounded-lg shadow-md overflow-hidden"
            >
              <div className="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <div className="flex items-center gap-3">
                  <MapPin className="w-5 h-5 text-[#3084C2]" />
                  <h2 className="text-lg font-semibold text-gray-800">
                    {t("checkout.deliveryAddress") || "Delivery Address"}
                  </h2>
                </div>
              </div>

              <div className="p-6">
                {!addresses ||
                !Array.isArray(addresses) ||
                addresses.length === 0 ? (
                  <div className="text-center p-4">
                    <p className="text-gray-500 mb-4">
                      {t("checkout.noAddresses") ||
                        "No addresses found. Please add a delivery address."}
                    </p>
                    <button
                      onClick={() => navigate("/profile/addresses")}
                      className="flex items-center justify-center gap-2 bg-[#3084C2] text-white px-4 py-2 rounded-md mx-auto"
                    >
                      <Plus className="w-4 h-4" />
                      {t("checkout.addNewAddress") || "Add New Address"}
                    </button>
                  </div>
                ) : (
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {Array.isArray(addresses) &&
                      addresses.map((address) => (
                        <motion.div
                          key={address.id}
                          whileHover={{ scale: 1.02 }}
                          className={`p-4 border-2 rounded-lg cursor-pointer ${
                            selectedAddressId === address.id
                              ? "border-[#3084C2] bg-blue-50"
                              : "border-gray-200"
                          }`}
                          onClick={() => setSelectedAddressId(address.id)}
                        >
                          <div className="flex justify-between items-start">
                            <div className="flex-1">
                              {address.title && (
                                <div className="font-medium mb-1">
                                  {address.title}
                                </div>
                              )}
                              <div className="text-gray-700">
                                {address.address}
                              </div>
                              <div className="text-gray-600 text-sm mt-1">
                                {address.phone_number}
                              </div>
                              {address.postal_code && (
                                <div className="text-gray-600 text-sm">
                                  {t("checkout.postalCode") || "Postal Code"}:{" "}
                                  {address.postal_code}
                                </div>
                              )}
                              {address.country && (
                                <div className="text-gray-600 text-sm mt-1">
                                  {address.country.name}
                                </div>
                              )}
                            </div>
                            {selectedAddressId === address.id && (
                              <div className="flex-shrink-0">
                                <Check className="w-5 h-5 text-[#3084C2]" />
                              </div>
                            )}
                          </div>
                          {address.is_default === 1 && (
                            <div className="mt-2">
                              <span className="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">
                                {t("checkout.defaultAddress") || "Default"}
                              </span>
                            </div>
                          )}
                        </motion.div>
                      ))}
                  </div>
                )}
              </div>
            </motion.div>

            {/* Shipping Method Section */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="bg-white rounded-lg shadow-md overflow-hidden"
            >
              <div className="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <div className="flex items-center gap-3">
                  <Truck className="w-5 h-5 text-[#3084C2]" />
                  <h2 className="text-lg font-semibold text-gray-800">
                    {t("checkout.shippingMethod") || "Shipping Method"}
                  </h2>
                </div>
              </div>

              <div className="p-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {(() => {
                    const address = addresses?.find(a => a.id === selectedAddressId);
                    if (!address) return <p className="text-gray-500">Please select an address first.</p>;

                    const zone = shippingPrices?.find(z => z.id === address.delivery_zone_id);
                    if (!zone || !zone.delivery_methods || zone.delivery_methods.length === 0) {
                      return <p className="text-gray-500">No delivery methods available for your address.</p>;
                    }

                    return zone.delivery_methods.map(method => (
                      <motion.div
                        key={method.id}
                        whileHover={{ scale: 1.02 }}
                        className={`p-4 border-2 rounded-lg cursor-pointer ${
                          selectedShipping === method.id
                            ? "border-[#3084C2] bg-blue-50"
                            : "border-gray-200"
                        }`}
                        onClick={() => dispatch(setShippingOption(method.id))}
                      >
                        <div className="flex justify-between items-center">
                          <div>
                            <div className="font-medium">
                              {method.name}
                            </div>
                            <div className="text-sm text-gray-600 mt-1">
                              {method.estimated_days || 'Standard Delivery'}
                            </div>
                          </div>
                          <div className="flex flex-col items-end gap-1">
                            <span className="font-medium">
                              €{(parseFloat(method.base_price) + (totalWeight * parseFloat(method.price_per_kg))).toFixed(2)}
                            </span>
                            {selectedShipping === method.id && (
                              <Check className="w-5 h-5 text-[#3084C2] mt-1" />
                            )}
                          </div>
                        </div>
                      </motion.div>
                    ));
                  })()}
                </div>
              </div>
            </motion.div>

            {/* Payment Options */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="bg-white rounded-lg shadow-md overflow-hidden"
            >
              <div className="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <div className="flex items-center gap-3">
                  <CreditCard className="w-5 h-5 text-[#3084C2]" />
                  <h2 className="text-lg font-semibold text-gray-800">
                    {t("checkout.paymentOptions") || "Payment Options"}
                  </h2>
                </div>
              </div>

              <div className="p-6">
                <div className="space-y-4">
                  {/* Cash on Delivery */}
                  {/* <div 
                    className="border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-[#3084C2] hover:bg-blue-50"
                    onClick={() => setPaymentMethod('cash')}
                  >
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-3">
                        <div className={`w-5 h-5 rounded-full border ${
                          paymentMethod === 'cash' 
                          ? 'border-[#3084C2]' 
                          : 'border-gray-300'
                        } flex items-center justify-center`}>
                          {paymentMethod === 'cash' && (
                            <div className="w-3 h-3 rounded-full bg-[#3084C2]"></div>
                          )}
                        </div>
                        <span className="font-medium">
                          {t('checkout.cashOnDelivery') || 'Cash on Delivery'}
                        </span>
                      </div>
                      <CreditCard className="w-5 h-5 text-gray-400" />
                    </div>
                  </div>
                   */}
                  {/* Credit Card Option */}
                  <div
                    className="border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-[#3084C2] hover:bg-blue-50"
                    onClick={() => setPaymentMethod("card")}
                  >
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-3">
                        <div
                          className={`w-5 h-5 rounded-full border ${
                            paymentMethod === "card"
                              ? "border-[#3084C2]"
                              : "border-gray-300"
                          } flex items-center justify-center`}
                        >
                          {paymentMethod === "card" && (
                            <div className="w-3 h-3 rounded-full bg-[#3084C2]"></div>
                          )}
                        </div>
                        <span className="font-medium">
                          {t("checkout.creditCard") || "Credit Card"}
                        </span>
                      </div>
                      <CreditCard className="w-5 h-5 text-gray-400" />
                    </div>
                  </div>

                  {/* Wallet Option - Only show if wallet exists and has balance */}
                  {wallet && parseFloat(wallet.balance) > 0 && (
                    <div className="border border-gray-200 rounded-lg p-4 mt-6">
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                          <div
                            className={`w-5 h-5 rounded border flex items-center justify-center cursor-pointer ${
                              useWalletCredit
                                ? "bg-[#3084C2] border-[#3084C2]"
                                : "border-gray-300"
                            }`}
                            onClick={() => setUseWalletCredit(!useWalletCredit)}
                          >
                            {useWalletCredit && (
                              <Check className="w-4 h-4 text-white" />
                            )}
                          </div>
                          <div>
                            <span className="font-medium">
                              {t("checkout.useWalletCredit") ||
                                "Use Wallet Credit"}
                            </span>
                            <p className="text-xs text-gray-500 mt-1">
                              {t("checkout.walletAvailableBalance") ||
                                "Available Balance"}
                              : €{parseFloat(wallet.balance).toFixed(2)}
                            </p>
                          </div>
                        </div>
                        <Wallet className="w-5 h-5 text-[#3084C2]" />
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </motion.div>

            {/* Order Items */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="bg-white rounded-lg shadow-md overflow-hidden"
            >
              <div className="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <div className="flex items-center gap-3">
                  <Package className="w-5 h-5 text-[#3084C2]" />
                  <h2 className="text-lg font-semibold text-gray-800">
                    {t("checkout.orderItems") || "Order Items"}
                  </h2>
                  <span className="text-sm ml-auto">
                    {Array.isArray(cartItems)
                      ? cartItems.filter((item) => !item.out_of_stock).length
                      : 0}{" "}
                    {Array.isArray(cartItems) &&
                    cartItems.filter((item) => !item.out_of_stock).length === 1
                      ? t("checkout.item") || "item"
                      : t("checkout.items") || "items"}
                  </span>
                </div>
              </div>

              <div className="divide-y divide-gray-100">
                {Array.isArray(cartItems) &&
                  cartItems.map((item) => {
                    // Determine if coupon applies to this item
                    const couponApplies = isCouponApplicableToItem(item);

                    // Calculate discount for this specific item if applicable
                    const itemDiscount = couponApplies
                      ? calculateItemDiscount(item)
                      : 0;

                    return (
                      <div
                        key={item.id}
                        className={`p-4 flex items-center gap-4 ${
                          item.out_of_stock ? "opacity-50" : ""
                        }`}
                      >
                        {/* Product Image */}
                        <div className="w-16 h-16 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden">
                          {item.product?.images &&
                          item.product.images.length > 0 ? (
                            <img
                              src={item.product.images[0].image}
                              alt={item.product.name_arabic}
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
                          <div className="flex items-center gap-2">
                            <h3 className="font-medium text-gray-800">
                              {i18n.language === "du"
                                ? item.product?.name_german
                                : item.product?.name_arabic}
                            </h3>

                            {/* Coupon indicator badge - only shown for vendor-specific coupons that apply to this item */}
                            {couponApplies && coupon?.vendor_id && (
                              <span className="bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full">
                                {t("checkout.discounted") || "Discounted"}
                              </span>
                            )}
                          </div>

                          {/* Variant Info */}
                          {item.product_variant &&
                            item.product_variant.variant_values && (
                              <div className="flex flex-wrap gap-2 mt-1">
                                {item.product_variant.variant_values.map(
                                  (variant) => (
                                    <div
                                      key={variant.id}
                                      className="text-sm text-gray-500 flex items-center gap-1"
                                    >
                                      <span>
                                        {i18n.language === "du"
                                          ? variant.variant_attribute
                                              .name_german
                                          : variant.variant_attribute
                                              .name_arabic}
                                        :
                                      </span>
                                      {variant.variant_attribute.type ===
                                      "color" ? (
                                        <div
                                          className="w-3 h-3 rounded-full"
                                          style={{
                                            backgroundColor: variant.value,
                                          }}
                                        ></div>
                                      ) : variant.variant_attribute.type ===
                                        "pattern" ? (
                                        <img
                                          src={variant.value}
                                          className="max-w-[40px]"
                                        />
                                      ) : (
                                        <span>{variant.value}</span>
                                      )}
                                    </div>
                                  )
                                )}
                              </div>
                            )}

                          {/* Price and Quantity */}
                          <div className="flex items-center flex-wrap gap-3 mt-1 text-sm">
                            {/* Regular price */}
                            <span
                              className={`font-medium ${
                                couponApplies &&
                                coupon.discount_type === "percentage"
                                  ? "line-through text-gray-400"
                                  : ""
                              }`}
                            >
                              €{parseFloat(item.price).toFixed(2)}
                            </span>

                            {/* Discounted price - only shown for percentage discounts */}
                            {couponApplies &&
                              coupon.discount_type === "percentage" && (
                                <span className="font-medium text-green-600">
                                  €
                                  {(
                                    parseFloat(item.price) *
                                    (1 -
                                      parseFloat(coupon.discount_amount) / 100)
                                  ).toFixed(2)}
                                </span>
                              )}

                            <span className="text-gray-500">×</span>
                            <span>{item.quantity}</span>
                          </div>
                        </div>

                        {/* Item Total with Discount */}
                        <div className="flex-shrink-0">
                          {couponApplies &&
                          coupon.discount_type === "percentage" ? (
                            <div className="text-right">
                              <span className="block line-through text-sm text-gray-400">
                                €
                                {(
                                  parseFloat(item.price) * item.quantity
                                ).toFixed(2)}
                              </span>
                              <span className="block font-medium text-green-600">
                                €
                                {(
                                  parseFloat(item.price) * item.quantity -
                                  itemDiscount
                                ).toFixed(2)}
                              </span>
                            </div>
                          ) : (
                            <span className="font-medium">
                              €
                              {(parseFloat(item.price) * item.quantity).toFixed(
                                2
                              )}
                            </span>
                          )}
                        </div>
                      </div>
                    );
                  })}
              </div>
            </motion.div>
          </div>
          {/* Order Summary Sidebar */}
          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            className="lg:col-span-4 space-y-4"
          >
            {/* Order Summary */}
            <div className="bg-white rounded-lg shadow-md p-6 sticky top-8">
              <h2 className="text-xl font-semibold mb-4">
                {t("checkout.orderSummary") || "Order Summary"}
              </h2>

              {/* Promo Code / Coupon Section */}
              <div className="mb-6">
                <div className="relative">
                  <input
                    type="text"
                    value={couponCode}
                    onChange={(e) => setCouponCode(e.target.value)}
                    placeholder={
                      t("checkout.enterPromoCode") || "Enter promo code"
                    }
                    className="w-full border border-gray-300 rounded-lg px-4 py-2 pr-24 focus:outline-none focus:ring-2 focus:ring-[#3084C2] focus:border-transparent"
                    disabled={couponStatus === "loading" || !!coupon}
                  />
                  {coupon ? (
                    <button
                      onClick={handleRemoveCoupon}
                      className="absolute right-2 top-1/2 transform -translate-y-1/2 text-red-500 px-3 py-1 text-sm font-medium"
                    >
                      <X className="w-4 h-4" />
                    </button>
                  ) : (
                    <button
                      onClick={handleApplyCoupon}
                      disabled={
                        couponStatus === "loading" || !couponCode.trim()
                      }
                      className="absolute right-2 top-1/2 transform -translate-y-1/2 bg-[#3084C2] text-white px-3 py-1 rounded text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {couponStatus === "loading" ? (
                        <Loader className="w-4 h-4 animate-spin" />
                      ) : (
                        t("checkout.apply") || "Apply"
                      )}
                    </button>
                  )}
                </div>

                {/* Coupon Status/Feedback */}
                {couponStatus === "failed" && (
                  <div className="mt-2 text-sm text-red-500 flex items-center gap-1">
                    <AlertCircle className="w-4 h-4" />
                    {couponError ||
                      t("checkout.invalidCoupon") ||
                      "Invalid coupon code"}
                  </div>
                )}

                {coupon && (
                  <div className="mt-2 bg-green-50 border border-green-100 rounded-md p-2 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <CheckCircle className="w-4 h-4 text-green-500" />
                      <div>
                        <span className="text-sm font-medium text-green-700">
                          {coupon.code} {t("checkout.applied") || "applied"}
                        </span>
                        <p className="text-xs text-green-600">
                          {coupon.discount_type === "percentage"
                            ? `${coupon.discount_amount}% ${
                                t("checkout.off") || "off"
                              }`
                            : `€${parseFloat(coupon.discount_amount).toFixed(
                                2
                              )} ${t("checkout.off") || "off"}`}
                          {coupon.vendor_id && (
                            <span>
                              {" "}
                              {t("checkout.vendorSpecific") ||
                                " on selected items"}
                            </span>
                          )}
                        </p>
                      </div>
                    </div>
                    <button
                      onClick={handleRemoveCoupon}
                      className="text-gray-500 hover:text-red-500"
                    >
                      <X className="w-4 h-4" />
                    </button>
                  </div>
                )}
              </div>

              {/* Price Breakdown */}
              <div className="space-y-3 mb-6">
                <div className="flex justify-between text-gray-600">
                  <span>{t("checkout.subtotal") || "Subtotal"}</span>
                  <span>€{subtotal.toFixed(2)}</span>
                </div>

                <div className="flex justify-between text-gray-600">
                  <span>{t("checkout.shipping") || "Shipping"}</span>
                  <span>€{shippingCost.toFixed(2)}</span>
                </div>

                {coupon && discount > 0 && (
                  <div className="flex justify-between text-green-600">
                    <div className="flex items-center gap-1">
                      <Gift className="w-4 h-4" />
                      <span>
                        {t("checkout.discount") || "Discount"}
                        {coupon.vendor_id && (
                          <span className="text-xs ml-1">
                            (
                            {t("checkout.onSelectedItems") ||
                              "on selected items"}
                            )
                          </span>
                        )}
                      </span>
                    </div>
                    <span>-€{discount.toFixed(2)}</span>
                  </div>
                )}

                {useWalletCredit &&
                  wallet &&
                  parseFloat(wallet.balance) > 0 && (
                    <div className="flex justify-between text-green-600">
                      <div className="flex items-center gap-1">
                        <Wallet className="w-4 h-4" />
                        <span>
                          {t("checkout.walletCredit") || "Wallet Credit"}
                        </span>
                      </div>
                      <span>
                        -€
                        {Math.min(parseFloat(wallet.balance), total).toFixed(2)}
                      </span>
                    </div>
                  )}

                <div className="h-px bg-gray-200 my-2" />

                <div className="flex justify-between font-semibold text-lg">
                  <span>{t("checkout.total") || "Total"}</span>
                  <span>€{finalTotal.toFixed(2)}</span>
                </div>
              </div>

              {/* Place Order Button */}
              <motion.button
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.98 }}
                onClick={handlePlaceOrder}
                disabled={
                  !selectedAddressId ||
                  isProcessing ||
                  orderStatus === "loading" ||
                  (Array.isArray(cartItems) &&
                    cartItems.filter((item) => !item.out_of_stock).length === 0)
                }
                className="w-full bg-[#3084C2] text-white py-3 rounded-lg font-medium 
                       flex items-center justify-center gap-2 hover:bg-[#195e8f] 
                       disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isProcessing || orderStatus === "loading" ? (
                  <Loader className="w-5 h-5 animate-spin" />
                ) : (
                  <>
                    {t("checkout.placeOrder") || "Place Order"}
                    <Package className="w-5 h-5" />
                  </>
                )}
              </motion.button>

              {/* Security Message */}
              <div className="mt-4 text-sm text-gray-500 text-center">
                <div className="flex items-center justify-center gap-2 mb-2">
                  <Shield className="w-4 h-4" />
                  <span>{t("checkout.securePayment") || "Secure Payment"}</span>
                </div>

                <p className="text-xs text-gray-400">
                  {t("checkout.termsAgreement") ||
                    "By placing your order, you agree to our Terms of Service and Privacy Policy"}
                </p>
              </div>

              {/* Customer Support Block */}
              <div className="mt-6 pt-6 border-t border-gray-100">
                <h3 className="font-medium mb-2">
                  {t("checkout.needHelp") || "Need Help?"}
                </h3>

                <div className="text-sm text-gray-600">
                  <p className="mb-2 flex items-center gap-1">
                    <Clock className="w-4 h-4 text-[#3084C2]" />
                    <span>
                      {t("checkout.customerSupportHours") ||
                        "Customer support available 24/7"}
                    </span>
                  </p>

                  <button className="text-[#3084C2] font-medium">
                    {t("checkout.contactSupport") || "Contact Support"}
                  </button>
                </div>
              </div>
            </div>
          </motion.div>
        </div>
      </div>
    </div>
  );
};

export default CheckoutPage;
