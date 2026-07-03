import React from "react";
import { Link } from "react-router-dom";
import { useTranslation } from "react-i18next";

const formatIqd = (value) =>
  value == null ? null : `${Number(value).toLocaleString("en-US")} IQD`;

const RailProductCard = ({ product }) => {
  const { i18n } = useTranslation();
  const name =
    i18n.language === "ar"
      ? product.name_arabic || product.name_german
      : product.name_german || product.name_arabic;

  return (
    <Link
      to={`/product/${product.id}`}
      className="block w-[41%] sm:w-44 md:w-48 flex-shrink-0 snap-start group"
    >
      <div className="aspect-[3/4] rounded-lg overflow-hidden bg-narzin-bg ring-1 ring-gray-100">
        {product.image ? (
          <img
            src={product.image}
            alt={name || ""}
            loading="lazy"
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center text-gray-300 text-xs">
            —
          </div>
        )}
      </div>
      <p className="mt-1.5 text-xs sm:text-sm text-narzin-navy truncate">{name}</p>
      <p className="text-sm sm:text-base font-semibold text-narzin-navy">
        {product.min_price != null ? `€${Number(product.min_price).toFixed(2)}` : ""}
      </p>
      {product.min_price_iqd != null && (
        <p className="text-[10px] sm:text-xs text-gray-500">{formatIqd(product.min_price_iqd)}</p>
      )}
    </Link>
  );
};

export default RailProductCard;
