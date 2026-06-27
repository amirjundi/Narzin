import React, { useEffect, useState } from "react";
import ProductCard from "../../Product/ProductCard";
import PrimaryLink from "../../utils/PrimaryLink";
import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import { fetchProductsStore } from "../../../Store/slices/StoreSlice";
import { useDispatch, useSelector } from "react-redux";

const ProductsSection = ({
  title,
  data,
  productCategory,
  route = "store",
}) => {
  const { t, i18n } = useTranslation();
  const [products, setProducts] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const isRTL = i18n.language === "ar";
  const dispatch = useDispatch();
  const {
    items: store,
    StoreStatus,
    StoreError,
  } = useSelector((state) => state.store);

  // If productCategory is passed, run the normal script (fetch from store)
  useEffect(() => {
    if (productCategory) {
      // Call API with the current filters
      const queryString = `?category_id=${productCategory?.id}`;
      dispatch(fetchProductsStore(queryString));
    }
  }, [dispatch, productCategory]);

  // If data is passed and productCategory is NOT passed, use data directly
  useEffect(() => {
    if (data && !productCategory) {
      setProducts(data.data || data);
      setIsLoading(false);
    }
  }, [data, productCategory]);

  // Handle store data when productCategory is passed
  useEffect(() => {
    if (productCategory && store?.data) {
      if (store.data.products?.data) {
        const filteredProducts = store.data.products.data.filter(
          (product) => product.id !== data?.id
        );
        setProducts(filteredProducts);
        console.log(
          "Products set from store with products.data (filtered)",
          filteredProducts
        );
      }
      setIsLoading(false);
    }
  }, [store, productCategory, data]);

  // Loading skeleton
  const LoadingSkeleton = () => (
    <div className="animate-pulse">
      <div className="aspect-square bg-gray-200 rounded-lg mb-4"></div>
      <div className="space-y-2">
        <div className="h-4 bg-gray-200 rounded w-3/4"></div>
        <div className="h-4 bg-gray-200 rounded w-1/2"></div>
        <div className="h-6 bg-gray-200 rounded w-1/4"></div>
      </div>
    </div>
  );

  return (
    products && products.length > 0 ? (
    <section className={`py-12 bg-gray-50 ${isRTL ? "rtl" : "ltr"}`}>
      <div className=" container mx-auto px-4">
        {/* Section Header */}
        <div className={`flex items-center justify-between mb-8 px-2`}>
          <div className="space-y-2">
            <h2 className="text-lg  font-bold text-gray-900 leading-tight">
              {title}
            </h2>
            <div className="h-1 w-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full"></div>
          </div>
          <PrimaryLink text={t("home.view_all")} route={route} />
        </div>

        {/* Products Grid */}
        <div className="relative">
          {isLoading ? (
            // Loading State
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3 md:gap-4 lg:gap-6">
              {[...Array(12)].map((_, index) => (
                <LoadingSkeleton key={`skeleton-${index}`} />
              ))}
            </div>
          ) : products && products.length > 0 ? (
            // Products Grid
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-6 gap-3 md:gap-4 lg:gap-6">
              {products.map((product, index) => (
                <div
                  key={`product-${product.id}-${index}`}
                  className="transform transition-all duration-300 hover:scale-[1.02]"
                  style={{
                    animationDelay: `${index * 50}ms`,
                    animation: "fadeInUp 0.6s ease-out forwards",
                  }}
                >
                  <Link to={`/product/${product.id}`} className="block h-full">
                    <ProductCard product={product} />
                  </Link>
                </div>
              ))}
            </div>
          ) : (
            // Empty State
            <div className="text-center py-16">
              <div className="w-24 h-24 mx-auto mb-6 opacity-50">
                <svg
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                  className="w-full h-full text-gray-400"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={1}
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                  />
                </svg>
              </div>
              <h3 className="text-xl font-semibold text-gray-700 mb-2">
                {t("product.no_products")}
              </h3>
              <p className="text-gray-500">
                {t("product.no_products_description")}
              </p>
            </div>
          )}
        </div>
      </div>

      {/* CSS Animations */}
      <style>{`
        @keyframes fadeInUp {
          from {
            opacity: 0;
            transform: translateY(30px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }

        .line-clamp-2 {
          display: -webkit-box;
          -webkit-line-clamp: 2;
          -webkit-box-orient: vertical;
          overflow: hidden;
        }

        /* RTL Styles */
        .rtl {
          direction: rtl;
        }

        .rtl .grid {
          direction: ltr; /* Keep grid LTR for proper layout */
        }

        .rtl .grid > * {
          direction: rtl; /* Apply RTL to grid items */
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
          .grid {
            gap: 0.75rem;
          }
        }
      `}</style>
    </section>
    ) : null
  );
};

export default ProductsSection;