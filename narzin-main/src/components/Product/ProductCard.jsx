import React, { useState, useEffect } from "react";
import { Heart, ShoppingCart, Star } from "lucide-react";
import { useTranslation } from "react-i18next";

const ProductCard = ({
  product,
  onProductClick,
  onAddToCart,
  onWishlist,
  language = "german",
}) => {
  const [selectedImage, setSelectedImage] = useState("");
  const [selectedColor, setSelectedColor] = useState("");
  const [isWishlisted, setIsWishlisted] = useState(false);
  const {t , i18n} = useTranslation();

  // Initialize with first image/color when product loads
  useEffect(() => {
    if (product?.images && product.images.length > 0) {
      setSelectedImage(product.images[0].image);
      setSelectedColor(product.images[0].color);
    }
  }, [product?.id]);

  // Handle color selection
  const handleColorChange = (img, color, event) => {
    event.preventDefault();
    event.stopPropagation();
    setSelectedImage(img);
    setSelectedColor(color);
  };

  // Handle card click for navigation
  const handleCardClick = (event) => {
    if (event.target.closest("button")) {
      return;
    }

    if (onProductClick) {
      onProductClick(product);
    } else {
      console.log("Navigate to product:", product?.name);
    }
  };

  // Handle add to cart
  const handleAddToCart = (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (onAddToCart) {
      onAddToCart(product, selectedColor);
    } else {
      console.log("Add to cart:", {
        product: product?.name,
        color: selectedColor,
      });
    }
  };

  // Handle wishlist toggle
  const handleWishlist = (event) => {
    event.preventDefault();
    event.stopPropagation();

    setIsWishlisted(!isWishlisted);

    if (onWishlist) {
      onWishlist(product, !isWishlisted);
    } else {
      console.log("Toggle wishlist:", {
        product: product?.name,
        wishlisted: !isWishlisted,
      });
    }
  };

  // Get display images with fallback
  const displayImages = product?.images || [];

  // Get localized product name
  const productName = i18n.language == "ar" ? product?.name_arabic : product?.name_german;
  const categoryName = i18n.language == "ar" ?  product?.category?.name_arabic : product?.category?.name_german;
  const rating = parseFloat(product?.average_rating) || 0;
  const price = parseFloat(product?.min_price) || 0;
  const originalPrice = parseFloat(product?.original_price || product?.min_price) || price;

  // Get current image
  const currentImage = selectedImage || displayImages[0]?.image;

  return (
    <div className="w-full max-w-xs mx-auto relative h-full">
      {/* Layered background effects - responsive */}
      <div className="absolute inset-0 bg-white border border-blue-200 rounded-lg translate-y-1.5 sm:translate-y-2 scale-95 shadow-sm opacity-60"></div>
      <div className="absolute inset-0 bg-white border border-blue-200 rounded-lg translate-y-0.5 sm:translate-y-1 scale-[0.97] shadow-sm opacity-80"></div>

      {/* Main card */}
      <div
        className="bg-white rounded-lg relative shadow-md overflow-hidden group hover:shadow-xl hover:shadow-blue-200/50 hover:-translate-y-1 sm:hover:-translate-y-2 hover:scale-[1.02] sm:hover:scale-105 transition-all duration-300 cursor-pointer border border-blue-100 h-full flex flex-col"
        onClick={handleCardClick}
      >
        {/* Image container */}
        <div className="w-full h-40 sm:h-48 md:h-52 relative overflow-hidden bg-gray-50 flex-shrink-0">
          {/* Wishlist button */}
          <button
            className={`absolute top-2 sm:top-3 right-2 sm:right-3 z-20 p-1.5 sm:p-2 rounded-full backdrop-blur-sm transition-all duration-200 ${
              isWishlisted
                ? "bg-red-500 text-white shadow-lg scale-110"
                : "bg-white/80 text-gray-600 hover:text-red-500 hover:bg-white shadow-md"
            }`}
            onClick={handleWishlist}
          >
            <Heart
              className={`w-3.5 h-3.5 sm:w-4 sm:h-4 ${isWishlisted ? "fill-current" : ""}`}
            />
          </button>

          {/* Product badge */}
          {product?.badge && (
            <div
              className="absolute top-2 sm:top-3 left-2 sm:left-3 z-20 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-xs font-semibold text-white shadow-md"
              style={{
                backgroundColor:
                  product.badge === "Sale"
                    ? "#ef4444"
                    : product.badge === "Hot"
                    ? "#f97316"
                    : product.badge === "New"
                    ? "#10b981"
                    : "#3084C2",
              }}
            >
              {product.badge}
            </div>
          )}

          {/* Product image */}
          <div className="relative h-full w-full">
            {currentImage ? (
              <img
                src={currentImage}
                alt={productName || "Product"}
                className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                onError={(e) => {
                  e.target.src =
                    "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=300&h=300&fit=crop";
                }}
              />
            ) : (
              <div className="h-full w-full bg-gray-200 flex items-center justify-center">
                <span className="text-gray-400 text-xs sm:text-sm">No Image</span>
              </div>
            )}

            {/* Gradient overlay on hover */}
            <div className="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          </div>
        </div>

        {/* Card content */}
        <div className="p-3 sm:p-4 group-hover:bg-blue-50/50 transition-colors duration-300 flex-grow flex flex-col">
          {/* Title and color swatches */}
          <div className="flex justify-between items-start mb-2 sm:mb-3 gap-2">
            <h3 className="font-semibold text-sm sm:text-base text-gray-800 leading-tight flex-1 group-hover:text-blue-900 transition-colors duration-300 line-clamp-2">
              {productName || "Product Name"}
            </h3>

            {/* Color selection */}
            {displayImages.length > 1 && (
              <div className="flex gap-1 sm:gap-1.5 items-center flex-shrink-0">
                {displayImages.slice(0, 4).map((imageData, index) => (
                  <button
                    key={`${product?.id}-color-${index}`}
                    onClick={(e) =>
                      handleColorChange(imageData.image, imageData.color, e)
                    }
                    className={`relative w-4 h-4 sm:w-5 sm:h-5 rounded-full border-2 transition-all duration-200 hover:scale-110 ${
                      selectedColor === imageData.color
                        ? "border-blue-500 shadow-md ring-1 sm:ring-2 ring-blue-200"
                        : "border-gray-300 hover:border-gray-400"
                    }`}
                    title={`Color variant ${index + 1}`}
                  >
                    <span
                      className="w-2 h-2 sm:w-3 sm:h-3 rounded-full absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"
                      style={{ backgroundColor: imageData.color }}
                    />
                  </button>
                ))}
                {displayImages.length > 4 && (
                  <span className="text-xs text-gray-500 ml-1">+{displayImages.length - 4}</span>
                )}
              </div>
            )}
          </div>

          {/* Category */}
          <div className="mb-2">
            <span className="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
              {categoryName}
            </span>
          </div>

          {/* Rating */}
          {rating > 0 && (
          <div className="flex items-center gap-1 sm:gap-2 mb-2 sm:mb-3">
            <div className="flex items-center">
              {[...Array(5)].map((_, i) => (
                <Star
                  key={i}
                  className={`w-3 h-3 sm:w-3.5 sm:h-3.5 transition-colors duration-300 ${
                    i < Math.floor(rating || 0)
                      ? "text-yellow-400 fill-current"
                      : "text-gray-300"
                  }`}
                />
              ))}
            </div>
            <span className="text-xs text-gray-500 group-hover:text-blue-600 transition-colors duration-300">
              ({product?.reviews_count || 0})
            </span>
          </div>

            )}


          {/* Price and add to cart */}
          <div className="flex justify-between items-center mt-auto gap-2">
            <div className="flex flex-col min-w-0 flex-1">
              <span className="font-bold text-base sm:text-lg text-gray-900 group-hover:text-blue-900 transition-colors duration-300 truncate">
                {product?.min_price
                  ? `${product.min_price}`
                  : "Price on request"}
              </span>
              {product?.original_price && product?.original_price !== product?.min_price && (
                <span className="text-xs sm:text-sm text-gray-500 line-through truncate">
                  ${product.original_price}
                </span>
              )}
            </div>

            <button
              onClick={handleAddToCart}
              className="p-2.5 sm:p-3 text-white rounded-xl shadow-lg transition-all duration-200 hover:scale-110 hover:shadow-xl group-hover:shadow-blue-400/30 flex-shrink-0"
              style={{
                background: "linear-gradient(135deg, #3084C2 0%, #1d4ed8 100%)",
              }}
              onMouseEnter={(e) => {
                e.currentTarget.style.background =
                  "linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%)";
              }}
              onMouseLeave={(e) => {
                e.currentTarget.style.background =
                  "linear-gradient(135deg, #3084C2 0%, #1d4ed8 100%)";
              }}
            >
              <ShoppingCart className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
            </button>
          </div>
        </div>
      </div>

      <style>{`
        .line-clamp-2 {
          overflow: hidden;
          display: -webkit-box;
          -webkit-box-orient: vertical;
          -webkit-line-clamp: 2;
        }
      `}</style>
    </div>
  );
};



export default ProductCard;