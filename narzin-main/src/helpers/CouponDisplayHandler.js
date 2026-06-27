// This utility handles the logic for displaying which items have vendor-specific coupon applied

/**
 * Checks if a coupon applies to a specific cart item
 * @param {Object} coupon - The coupon object with vendor_id and discount_type
 * @param {Object} cartItem - The cart item to check
 * @returns {Boolean} - Whether the coupon applies to this item
 */
export const isCouponApplicableToItem = (coupon, cartItem) => {
    if (!coupon) return false;
    
    // If coupon has no vendor_id, it applies to all items
    if (!coupon.vendor_id) return true;
    
    // Check if the item's product belongs to the coupon's vendor
    return cartItem.product?.vendor_id === coupon.vendor_id;
  };
  
  /**
   * Calculates the discount amount for a specific cart item
   * @param {Object} coupon - The coupon object
   * @param {Object} cartItem - The cart item
   * @returns {Number} - The discount amount for this item (0 if not applicable)
   */
  export const calculateItemDiscount = (coupon, cartItem) => {
    if (!isCouponApplicableToItem(coupon, cartItem)) return 0;
    
    const itemPrice = parseFloat(cartItem.price) * cartItem.quantity;
    
    if (coupon.discount_type === 'percentage') {
      return (itemPrice * parseFloat(coupon.discount_amount)) / 100;
    } else if (coupon.discount_type === 'fixed') {
      // For fixed discounts with vendor_id, we need a different approach
      // Typically, fixed discounts are spread proportionally among eligible items
      // But for simplicity, we'll apply the full discount to the first eligible item
      return parseFloat(coupon.discount_amount);
    }
    
    return 0;
  };
  
  /**
   * Calculates the final price for a cart item after any applicable discount
   * @param {Object} coupon - The coupon object
   * @param {Object} cartItem - The cart item
   * @returns {Number} - The final price after discount
   */
  export const calculateItemFinalPrice = (coupon, cartItem) => {
    const itemTotal = parseFloat(cartItem.price) * cartItem.quantity;
    const discount = calculateItemDiscount(coupon, cartItem);
    return itemTotal - discount;
  };
  
  /**
   * Calculates the total discount across all items
   * @param {Object} coupon - The coupon object
   * @param {Array} cartItems - All cart items
   * @returns {Number} - The total discount amount
   */
  export const calculateTotalDiscount = (coupon, cartItems) => {
    if (!coupon || !Array.isArray(cartItems)) return 0;
    
    let totalDiscount = 0;
    
    // For percentage discounts or vendor-specific discounts, calculate per item
    if (coupon.discount_type === 'percentage' || coupon.vendor_id) {
      cartItems.forEach(item => {
        totalDiscount += calculateItemDiscount(coupon, item);
      });
    } else if (coupon.discount_type === 'fixed') {
      // For non-vendor specific fixed discounts, just use the amount
      totalDiscount = parseFloat(coupon.discount_amount);
    }
    
    return totalDiscount;
  };