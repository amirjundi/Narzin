import React, { useEffect, useState } from "react";
import {
  Star,
  Heart,
  Share2,
  ShoppingCart,
  ChevronLeft,
  ChevronRight,
  Truck,
  ShieldCheck,
  RefreshCw,
  Check,
} from "lucide-react";

import ProductsSection from "../components/pages/home/ProductsSection";
import FullDescription from "../components/pages/singleProduct/FullDescription";
import SizeGuide from "../components/pages/singleProduct/SizeGuide";
import Reviews from "../components/pages/singleProduct/Reviews";
import SellerInfo from "../components/pages/singleProduct/SellerInfo";
import { useDispatch, useSelector } from "react-redux";
import { fetchSingleProduct } from "../Store/slices/SingleProductSlice";
import { addToCart, fetchCart } from "../Store/slices/CardSlice";
import { addToWishlist, fetchWishlist } from "../Store/slices/WishlistSlice";
import { Link, useParams } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { toast } from "react-toastify"; // Assuming you use react-toastify for notifications
import api, { getCsrfCookie } from "../api/axios";
import { getSessionId } from "../helpers/session";

const ProductPage = () => {
  const { id } = useParams();
  const dispatch = useDispatch();
  const { t, i18n } = useTranslation();

  // Behavior tracking: record this product view (+ dwell time on leave) so the
  // "For You" personalization has data. Fire-and-forget; never blocks the UI.
  useEffect(() => {
    if (!id) return;
    const sessionId = getSessionId();
    const startedAt = Date.now();

    const track = async (dwell) => {
      const body = {
        product_id: Number(id),
        session_id: sessionId,
        dwell_time_seconds: dwell,
      };
      try {
        await api.post("/v1/telemetry/view", body);
      } catch (e) {
        if (e?.response?.status === 419) {
          try {
            await getCsrfCookie();
            await api.post("/v1/telemetry/view", body);
          } catch {
            /* ignore */
          }
        }
      }
    };

    track(0); // record the view immediately

    return () => {
      const dwell = Math.min(Math.round((Date.now() - startedAt) / 1000), 3600);
      if (dwell > 0) track(dwell);
    };
  }, [id]);

  const {
    items: productData,
    SingleProductStatus,
    SingleProductError,
  } = useSelector((state) => state.SingleProduct);

  const { status: cartStatus } = useSelector((state) => state.cart);
  const { status: wishlistStatus } = useSelector((state) => state.wishlist);

  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);

  // Extract the actual product data from the nested API response
  const singleProduct = productData?.data || productData;

  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [selectedVariant, setSelectedVariant] = useState(null);
  const [selectedSize, setSelectedSize] = useState("");
  const [quantity, setQuantity] = useState(1);
  const [loading, setLoading] = useState(true);
  const [addingToCart, setAddingToCart] = useState(false);
  const [addingToWishlist, setAddingToWishlist] = useState(false);

  // Get currently selected value for an attribute
  const getSelectedAttributeValue = (attributeID) => {
    if (!selectedVariant) return null;

    const attr = selectedVariant.attributes.find(
      (attr) => attr.attribute_id === attributeID
    );
    return attr ? attr.value : null;
  };

  // Group attributes by type
  const getAttributeGroups = () => {
    if (!singleProduct || !singleProduct.variants) return [];

    // Get all unique attribute IDs from all variants
    const allAttributeIDs = new Set();
    singleProduct.variants.forEach((variant) => {
      variant.attributes.forEach((attr) => {
        allAttributeIDs.add(attr.attribute_id);
      });
    });

    // Group attributes by attribute_id
    const attributeGroups = [];
    allAttributeIDs.forEach((attrID) => {
      // Find an example of this attribute to get metadata
      const exampleAttr = singleProduct.variants
        .flatMap((v) => v.attributes)
        .find((a) => a.attribute_id === attrID);

      if (exampleAttr) {
        // Get all unique values for this attribute across variants
        const values = new Set();
        singleProduct.variants.forEach((variant) => {
          const attrs = variant.attributes.filter(
            (a) => a.attribute_id === attrID
          );
          attrs.forEach((a) => values.add(a.value));
        });

        attributeGroups.push({
          id: exampleAttr.attribute_id,
          name:
            i18n.language === "du"
              ? exampleAttr.name_german
              : exampleAttr.name_arabic,
          type: exampleAttr.type,
          type_values: exampleAttr.type_values,
          values: [...values],
        });
      }
    });

    return attributeGroups;
  };

  // Get available values for attribute type based on selected color
  const getAvailableAttributeValues = (attributeID, attributeType) => {
    if (!singleProduct || !singleProduct.variants) return [];

    // If no variant is selected yet, return all possible values
    if (!selectedVariant) {
      const allValues = singleProduct.variants.flatMap((variant) => {
        const attrs = variant.attributes.filter(
          (attr) =>
            attr.attribute_id === attributeID && attr.type === attributeType
        );
        return attrs.map((attr) => attr.value);
      });

      return [...new Set(allValues)];
    }

    // Get the currently selected color
    const selectedColor = selectedVariant.attributes.find(
      (attr) => attr.type === "color"
    )?.value;

    if (!selectedColor) return [];

    // Find all variants with this color
    const variantsWithSameColor = singleProduct.variants.filter((variant) =>
      variant.attributes.some(
        (attr) => attr.type === "color" && attr.value === selectedColor
      )
    );

    // Extract all unique values for the specified attribute from these variants
    const values = variantsWithSameColor.flatMap((variant) => {
      const attrs = variant.attributes.filter(
        (attr) =>
          attr.attribute_id === attributeID && attr.type === attributeType
      );
      return attrs.map((attr) => attr.value);
    });

    // Return unique values
    return [...new Set(values)];
  };

  // Check if a variant with specific attribute values is available
  const getVariantWithAttributes = (attributeSelections) => {
    if (!singleProduct || !singleProduct.variants) return null;

    // Find a variant that matches all the selected attribute values
    return singleProduct.variants.find((variant) => {
      // Check if this variant has all the selected attribute values
      return Object.entries(attributeSelections).every(([attrID, value]) => {
        return variant.attributes.some(
          (attr) =>
            attr.attribute_id.toString() === attrID && attr.value === value
        );
      });
    });
  };
  // Check if a specific attribute value is available given current selections
  const isAttributeValueAvailable = (attributeID, value) => {
    if (!singleProduct || !singleProduct.variants) return false;

    // For color attributes (type 'color'), always allow selection
    const attributeGroup = getAttributeGroups().find(
      (group) => group.id === attributeID
    );
    if (attributeGroup && attributeGroup.type === "color") {
      return true; // Colors are always selectable
    }

    if (!selectedVariant) return false;

    // Create an object with current selections from the selected variant
    const currentSelections = {};
    selectedVariant.attributes.forEach((attr) => {
      // Skip the attribute we're checking availability for
      if (attr.attribute_id !== attributeID) {
        currentSelections[attr.attribute_id] = attr.value;
      }
    });

    // Add the attribute value we're checking
    currentSelections[attributeID] = value;

    // Check if any variant exists with these selections
    const variantExists = singleProduct.variants.some((variant) => {
      return Object.entries(currentSelections).every(([attrID, val]) => {
        return variant.attributes.some(
          (attr) =>
            attr.attribute_id.toString() === attrID && attr.value === val
        );
      });
    });

    // Also check if that variant is in stock
    if (variantExists) {
      const variant = singleProduct.variants.find((variant) => {
        return Object.entries(currentSelections).every(([attrID, val]) => {
          return variant.attributes.some(
            (attr) =>
              attr.attribute_id.toString() === attrID && attr.value === val
          );
        });
      });

      return variant && variant.stock > 0 && !variant.is_out_of_stock;
    }

    return false;
  };

  // Handle attribute selection
  const handleAttributeSelect = (attributeID, value) => {
    if (!singleProduct || !singleProduct.variants) return;

    // Get the attribute type
    const attributeGroup = getAttributeGroups().find(
      (group) => group.id === attributeID
    );
    const isColorAttribute = attributeGroup && attributeGroup.type === "color";

    // For color attributes, we reset other selections and find any variant with this color
    if (isColorAttribute) {
      // Find first available variant with this color (prioritize in-stock variants)
      const inStockVariant = singleProduct.variants.find(
        (v) =>
          v.attributes.some(
            (attr) => attr.attribute_id === attributeID && attr.value === value
          ) &&
          v.stock > 0 &&
          !v.is_out_of_stock
      );

      const anyVariant = singleProduct.variants.find((v) =>
        v.attributes.some(
          (attr) => attr.attribute_id === attributeID && attr.value === value
        )
      );

      const variant = inStockVariant || anyVariant;

      if (variant) {
        setSelectedVariant(variant);

        // Find first image matching this color and set it as current
        const colorMatchingImageIndex = singleProduct.images.findIndex(
          (img) => img.color.toLowerCase() === value.toLowerCase()
        );

        // If a matching image is found, select it
        if (colorMatchingImageIndex !== -1) {
          setCurrentImageIndex(colorMatchingImageIndex);
        }
      }
      return;
    }

    // For other attributes (like size), find a variant with current color and the selected attribute
    if (selectedVariant) {
      const selectedColor = selectedVariant.attributes.find(
        (attr) => attr.type === "color"
      )?.value;

      if (selectedColor) {
        // Find a variant with current color and selected size/attribute
        const variant = singleProduct.variants.find(
          (v) =>
            v.attributes.some(
              (attr) => attr.type === "color" && attr.value === selectedColor
            ) &&
            v.attributes.some(
              (attr) =>
                attr.attribute_id === attributeID && attr.value === value
            )
        );

        if (variant) {
          setSelectedVariant(variant);
        }
      }
    }
  };

  // Handle adding to cart
  const handleAddToCart = async () => {
    if (!selectedVariant || !isAuthenticated) return;

    setAddingToCart(true);

    try {
      await dispatch(
        addToCart({
          product_id: singleProduct.id,
          product_variant_id: selectedVariant.id,
          quantity,
          unit_price: parseFloat(selectedVariant.price),
        })
      ).unwrap();

      toast.success(t("product.addedToCart"));

      // Refresh the cart
      dispatch(fetchCart());
    } catch (error) {
      toast.error(error?.message || t("product.addToCartError"));
    } finally {
      setAddingToCart(false);
    }
  };

  // Handle adding to Wishlist
  const handleAddToWishlist = async () => {
    if (!selectedVariant || !isAuthenticated) return;

    setAddingToWishlist(true);

    try {
      await dispatch(
        addToWishlist({
          product_id: singleProduct.id,
          product_variant_id: selectedVariant.id,
          quantity,
        })
      ).unwrap();

      toast.success(t("product.addedToWishlist"));

      // Refresh the wishlist
      dispatch(fetchWishlist());
    } catch (error) {
      toast.error(error?.message || t("product.addToWishlistError"));
    } finally {
      setAddingToWishlist(false);
    }
  };

  // Navigation functions for image gallery
  const nextImage = () => {
    if (
      !singleProduct ||
      !singleProduct.images ||
      singleProduct.images.length === 0
    )
      return;

    setCurrentImageIndex((prev) =>
      prev === singleProduct.images.length - 1 ? 0 : prev + 1
    );
  };

  const prevImage = () => {
    if (
      !singleProduct ||
      !singleProduct.images ||
      singleProduct.images.length === 0
    )
      return;

    setCurrentImageIndex((prev) =>
      prev === 0 ? singleProduct.images.length - 1 : prev - 1
    );
  };

  const selectImage = (index) => {
    setCurrentImageIndex(index);
  };

  useEffect(() => {
    if (id) {
      dispatch(fetchSingleProduct(id));
      setLoading(true);
    }
  }, [dispatch, id]);

  useEffect(() => {
    if (singleProduct && Object.keys(singleProduct).length > 0) {
      // Set the first variant as selected by default if available
      if (singleProduct.variants && singleProduct.variants.length > 0) {
        setSelectedVariant(singleProduct.variants[0]);

        // Find size attribute using type 'select' for clothing sizes
        const sizeAttribute = singleProduct.variants[0].attributes.find(
          (attr) =>
            attr.type === "select" && attr.type_values?.includes("S,M,L")
        );

        if (sizeAttribute) {
          setSelectedSize(sizeAttribute.value);
        }
      }
      setLoading(false);
    }
  }, [singleProduct]);

  if (loading || !singleProduct || Object.keys(singleProduct).length === 0) {
    return (
      <div className="max-w-[1400px] mx-auto px-4 py-12">
        Loading product details...
      </div>
    );
  }

// ProductPage - Complete Redesigned Return Statement
// Replace your entire return statement with this modernized version:

// ProductPage - Compact & Responsive Redesigned Return Statement

// ProductPage - Compact & Responsive Redesigned Return Statement

return (
  <div className="mt-20 container mx-auto px-3 sm:px-4 lg:px-6 py-6 sm:py-8">
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10">
      
      {/* Image Gallery - Smaller & Compact */}
      <div className="space-y-3 max-w-md mx-auto lg:max-w-full">
        {/* Main Image - Reduced size */}
        <div className="group relative aspect-square max-h-[500px] sm:max-h-[500px] overflow-hidden rounded-2xl shadow-lg bg-gray-50">
          {singleProduct.images && singleProduct.images.length > 0 && (
            <>
              <img
                src={singleProduct.images[currentImageIndex].image}
                alt={i18n.language === "du" ? singleProduct.name_german : singleProduct.name_arabic}
                className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </>
          )}
          
          {/* Navigation */}
          <button onClick={prevImage} className="absolute left-2 sm:left-3 top-1/2 -translate-y-1/2 bg-white/90 backdrop-blur-sm rounded-full p-2 hover:scale-110 transition-all shadow-lg opacity-0 group-hover:opacity-100">
            <ChevronLeft className="w-4 h-4 sm:w-5 sm:h-5" />
          </button>
          <button onClick={nextImage} className="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 bg-white/90 backdrop-blur-sm rounded-full p-2 hover:scale-110 transition-all shadow-lg opacity-0 group-hover:opacity-100">
            <ChevronRight className="w-4 h-4 sm:w-5 sm:h-5" />
          </button>
          
          {/* Counter */}
          <div className="absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/50 backdrop-blur-sm text-white px-3 py-1 rounded-full text-xs font-medium">
            {currentImageIndex + 1}/{singleProduct.images?.length || 0}
          </div>
        </div>

        {/* Thumbnails - Smaller */}
        <div className="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
          {singleProduct.images?.map((image, index) => {
            const isSelected = index === currentImageIndex;
            return (
              <button
                key={index}
                onClick={() => selectImage(index)}
                className={`flex-none w-14 h-14 sm:w-16 sm:h-16 rounded-lg overflow-hidden transition-all duration-300 ${
                  isSelected ? "ring-2 ring-blue-500 scale-105" : "opacity-70 hover:opacity-100"
                }`}
              >
                <img src={image.image} alt={`View ${index + 1}`} className="w-full h-full object-cover" />
              </button>
            );
          })}
        </div>
      </div>

      {/* Product Info - Compact & Responsive */}
      <div className="space-y-5 lg:space-y-6">
        
        {/* Header - Compact */}
        <div className="space-y-3">
          <h1 className="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 leading-tight">
            {i18n.language === "du" ? singleProduct.name_german : singleProduct.name_arabic}
          </h1>
          
          {/* Rating - Inline & Compact */}
          <div className="flex items-center gap-3">
            <div className="flex items-center gap-1 bg-yellow-50 px-3 py-1 rounded-full">
              <div className="flex">
                {[...Array(5)].map((_, i) => (
                  <Star key={i} className={`w-4 h-4 ${i < Math.floor(singleProduct.average_rating) ? "text-yellow-400 fill-yellow-400" : "text-gray-300"}`} />
                ))}
              </div>
              <span className="text-sm font-medium ml-1">{Math.floor(singleProduct.average_rating).toFixed(1) || 0}</span>
            </div>
            <span className="text-sm text-gray-500">({singleProduct.reviews_count || 0})</span>
          </div>
        </div>

        {/* Price - Compact */}
        <div className="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-4 border border-blue-100">
          <div className="flex items-baseline gap-2 flex-wrap">
            <span className="text-3xl sm:text-4xl font-bold text-gray-900">
              €{selectedVariant ? parseFloat(selectedVariant.price).toFixed(2) : "0.00"}
            </span>
            {selectedVariant?.old_price && (
              <span className="text-lg text-gray-400 line-through">€{parseFloat(selectedVariant.old_price).toFixed(2)}</span>
            )}
          </div>
        </div>

        {/* Description - Compact */}
        <p className="text-sm sm:text-base text-gray-600 leading-relaxed line-clamp-3">
          {i18n.language === "du" ? singleProduct.description_german : singleProduct.description_arabic}
        </p>

        {/* Attributes - Compact */}
        {getAttributeGroups().sort((a, b) => a.type === "color" ? -1 : 1).map((attrGroup) => (
            attrGroup.type != "pattern" && (
          <div key={attrGroup.id} className="space-y-2">
            <h3 className="text-sm font-semibold text-gray-900">{attrGroup.name}</h3>

            {attrGroup.type === "color" && (
              <div className="flex gap-2 flex-wrap">
                {attrGroup.values.map((colorValue) => {
                  const isSelected = getSelectedAttributeValue(attrGroup.id) === colorValue;
                  const variantWithThisColor = singleProduct.variants.find(v =>
                    v.attributes.some(attr => attr.type === "color" && attr.value === colorValue)
                  );
                  const patternAttribute = variantWithThisColor?.attributes.find(attr => attr.type === "pattern");

                  if (patternAttribute) {
                    return (
                      <button
                        key={colorValue}
                        onClick={() => handleAttributeSelect(attrGroup.id, colorValue)}
                        className={`w-10 h-10 sm:w-12 sm:h-12 rounded-full overflow-hidden transition-all ${
                          isSelected ? "ring-2 ring-blue-500 scale-110" : "ring-1 ring-gray-200"
                        }`}
                      >
                        <img src={patternAttribute.value} alt="Pattern" className="w-full h-full object-cover" />
                        {isSelected && (
                          <span className="absolute inset-0 flex items-center justify-center bg-black/30">
                            <Check className="w-4 h-4 text-white" />
                          </span>
                        )}
                      </button>
                    );
                  }

                  return (
                    <button
                      key={colorValue}
                      onClick={() => handleAttributeSelect(attrGroup.id, colorValue)}
                      className={`relative w-10 h-10 sm:w-12 sm:h-12 rounded-full transition-all ${
                        isSelected ? "ring-2 ring-blue-500 scale-110" : "ring-1 ring-gray-200"
                      }`}
                    >
                      <span className="absolute inset-1 rounded-full" style={{ backgroundColor: colorValue }} />
                      {isSelected && (
                        <span className="absolute inset-0 flex items-center justify-center">
                          <Check className={`w-4 h-4 ${colorValue === "#ffffff" ? "text-gray-900" : "text-white"}`} />
                        </span>
                      )}
                    </button>
                  );
                })}
              </div>
            )}

            {attrGroup.type === "select" && (
              <div className="flex gap-2 flex-wrap">
                {getAvailableAttributeValues(attrGroup.id, attrGroup.type).map((selectValue) => {
                  const isSelected = getSelectedAttributeValue(attrGroup.id) === selectValue;
                  return (
                    <button
                      key={selectValue}
                      onClick={() => handleAttributeSelect(attrGroup.id, selectValue)}
                      className={`py-2 px-4 text-sm font-medium rounded-lg transition-all ${
                        isSelected ? "bg-gray-900 text-white" : "bg-gray-100 text-gray-900 hover:bg-gray-200"
                      }`}
                    >
                      {selectValue}
                    </button>
                  );
                })}
              </div>
            )}
          </div>
            )
        ))}

        {/* Quantity - Compact */}
        <div className="space-y-2">
          <h3 className="text-sm font-semibold text-gray-900">{t("product.quantity")}</h3>
          <div className="flex items-center gap-3">
            <div className="flex items-center bg-gray-100 rounded-lg">
              <button
                onClick={() => setQuantity(Math.max(1, quantity - 1))}
                className="w-10 h-10 flex items-center justify-center hover:bg-gray-200 rounded-l-lg transition-colors"
              >
                <span className="text-lg font-bold">-</span>
              </button>
              <span className="w-12 text-center font-semibold">{quantity}</span>
              <button
                onClick={() => selectedVariant && quantity < selectedVariant.stock && setQuantity(quantity + 1)}
                disabled={!selectedVariant || quantity >= selectedVariant.stock}
                className="w-10 h-10 flex items-center justify-center hover:bg-gray-200 rounded-r-lg transition-colors disabled:opacity-50"
              >
                <span className="text-lg font-bold">+</span>
              </button>
            </div>
            
            {selectedVariant && (
              <span className={`text-xs sm:text-sm px-3 py-1 rounded-full ${
                selectedVariant.stock > 0 ? "bg-green-50 text-green-700" : "bg-red-50 text-red-700"
              }`}>
                {selectedVariant.stock > 0 ? `${t("product.inStock")} (${selectedVariant.stock})` : t("product.outOfStock")}
              </span>
            )}
          </div>
        </div>

        {/* Actions - Compact & Responsive */}
        <div className="flex gap-2 sm:gap-3 pt-2">
          <button
            onClick={handleAddToCart}
            disabled={!selectedVariant || selectedVariant.stock <= 0 || !isAuthenticated || addingToCart}
            className={`flex-1 py-3 px-4 rounded-xl font-semibold flex items-center justify-center gap-2 transition-all ${
              selectedVariant && selectedVariant.stock > 0 && isAuthenticated && !addingToCart
                ? "bg-[#3794D9] text-white hover:bg-[#3084C2] shadow-lg"
                : "bg-gray-200 text-gray-400 cursor-not-allowed"
            }`}
          >
            {addingToCart ? (
              <svg className="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
            ) : (
              <>
                <ShoppingCart className="w-5 h-5" />
                <span className="hidden sm:inline">{t("product.addToCart")}</span>
                <span className="sm:hidden">Add</span>
              </>
            )}
          </button>
          
          <button onClick={handleAddToWishlist} className="p-3 rounded-xl border-2 border-gray-200 hover:border-red-300 hover:bg-red-50 transition-colors">
            <Heart className="w-5 h-5 text-gray-400 hover:text-red-500" />
          </button>
          
          <button className="p-3 rounded-xl border-2 border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors">
            <Share2 className="w-5 h-5 text-gray-400 hover:text-blue-500" />
          </button>
        </div>

        {!isAuthenticated && (
          <div className="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
            {t("product.loginToAddToCart")} <Link to="/signin" className="underline font-semibold">{t("auth.login")}</Link>
          </div>
        )}
      </div>
    </div>

    {/* Bottom Section - Compact & Responsive */}
    <div className="mt-8 sm:mt-12 space-y-6">
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl p-4 sm:p-6 shadow-md border border-gray-100">
          <SellerInfo vendor={singleProduct.vendor_id} />
        </div>
        <div className="space-y-6">
          <FullDescription data={singleProduct} />
          <SizeGuide sizeChart={singleProduct?.size_chart} />
        </div>
      </div>
      
      <div className="bg-white rounded-xl p-4 sm:p-6 shadow-md border border-gray-100">
        <Reviews productId={singleProduct.id} />
      </div>
    </div>

    {/* Related Products */}
    <div className="mt-8 sm:mt-12">
      <ProductsSection
        productCategory={singleProduct.category}
        data={singleProduct}
        title={t("product.relatedProducts")}
      />
    </div>
  </div>
);
};

export default ProductPage;
