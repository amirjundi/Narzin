import { useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import { fetchForYou, selectForYouBlocks } from "../Store/slices/ForYouSlice";
import RailProductCard from "../components/pages/home/blocks/RailProductCard";

const RecentlyViewed = () => {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const blocks = useSelector(selectForYouBlocks);

  useEffect(() => {
    dispatch(fetchForYou(i18n.language));
  }, [dispatch, i18n.language]);

  const rail = blocks.find((b) => b?.content?.key === "recently_viewed");
  const products = rail?.content?.products || [];

  return (
    <div className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pt-24 pb-12">
      <h1 className="text-xl sm:text-2xl font-bold text-narzin-navy mb-6">
        {t("topbar.recently_viewed", "Recently Viewed")}
      </h1>

      {products.length > 0 ? (
        <div className="flex flex-wrap gap-3 sm:gap-4">
          {products.map((product) => (
            <RailProductCard key={product.id} product={product} />
          ))}
        </div>
      ) : (
        <div className="text-center py-16">
          <p className="text-gray-500 mb-4">
            {t("topbar.recently_viewed_empty", "You haven't viewed any products yet.")}
          </p>
          <Link to="/store" className="text-blue-600 font-medium hover:underline">
            {t("topbar.browse_store", "Browse the store")}
          </Link>
        </div>
      )}
    </div>
  );
};

export default RecentlyViewed;
