import React from "react";
import { Link } from "react-router-dom";

const CategoryCircles = ({ content }) => {
  const categories = content?.categories || [];
  if (categories.length === 0) return null;

  return (
    <section className="py-4 bg-white">
      <div className="flex gap-4 overflow-x-auto snap-x px-4 sm:justify-center sm:flex-wrap sm:overflow-visible">
        {categories.map((category) => (
          <Link
            key={category.id}
            to={`/store?category_id=${category.id}`}
            className="flex flex-col items-center gap-1.5 snap-start flex-shrink-0 w-20 group"
          >
            <div className="w-16 h-16 sm:w-20 sm:h-20 rounded-full overflow-hidden bg-narzin-bg ring-1 ring-gray-200 group-hover:ring-2 group-hover:ring-narzin-sand transition-all">
              {category.image ? (
                <img
                  src={category.image}
                  alt={category.name}
                  loading="lazy"
                  className="w-full h-full object-cover"
                />
              ) : (
                <div className="w-full h-full flex items-center justify-center text-narzin-navy/40 text-lg font-semibold">
                  {String(category.name || "?").charAt(0)}
                </div>
              )}
            </div>
            <span className="text-[11px] sm:text-xs text-narzin-navy text-center leading-tight max-w-[72px] truncate">
              {category.name}
            </span>
          </Link>
        ))}
      </div>
    </section>
  );
};

export default CategoryCircles;
