import React from "react";

const shapes = {
  bar: "h-9 w-full",
  hero: "h-56 sm:h-72 md:h-96 w-full",
  circles: "h-28 w-full",
  rail: "h-64 w-full",
};

const BlockSkeleton = ({ variant = "rail" }) => (
  <div className="px-0">
    <div
      className={`animate-pulse bg-gray-200 rounded-lg ${shapes[variant] || shapes.rail}`}
      data-testid={`skeleton-${variant}`}
    />
  </div>
);

export default BlockSkeleton;
