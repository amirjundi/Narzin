import React, { useRef } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";
import RailProductCard from "./RailProductCard";

const ProductRail = ({ content }) => {
  const products = content?.products || [];
  const trackRef = useRef(null);
  if (products.length === 0) return null;

  const scrollByCards = (direction) => {
    const track = trackRef.current;
    if (!track) return;
    // Positive scrollBy 'left' respects RTL automatically in modern browsers
    track.scrollBy({ left: direction * track.clientWidth * 0.8, behavior: "smooth" });
  };

  return (
    <section className="py-4 bg-white mt-2">
      <div className="flex items-center justify-between px-4 mb-2.5">
        {content.title && (
          <h2 className="text-base sm:text-lg font-bold text-narzin-navy">{content.title}</h2>
        )}
        <div className="hidden md:flex gap-1">
          <button
            type="button"
            aria-label="scroll backward"
            onClick={() => scrollByCards(-1)}
            className="p-1.5 rounded-full ring-1 ring-gray-200 text-narzin-navy hover:bg-narzin-bg"
          >
            <ChevronLeft className="w-4 h-4 rtl:rotate-180" />
          </button>
          <button
            type="button"
            aria-label="scroll forward"
            onClick={() => scrollByCards(1)}
            className="p-1.5 rounded-full ring-1 ring-gray-200 text-narzin-navy hover:bg-narzin-bg"
          >
            <ChevronRight className="w-4 h-4 rtl:rotate-180" />
          </button>
        </div>
      </div>
      <div
        ref={trackRef}
        className="flex gap-3 overflow-x-auto snap-x px-4 pb-1"
        style={{ scrollbarWidth: "none" }}
      >
        {products.map((product) => (
          <RailProductCard key={product.id} product={product} />
        ))}
      </div>
    </section>
  );
};

export default ProductRail;
