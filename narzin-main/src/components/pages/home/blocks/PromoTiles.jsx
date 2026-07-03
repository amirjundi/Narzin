import React from "react";
import { SmartLink } from "./blockLink";

const PromoTiles = ({ content }) => {
  const tiles = content?.tiles || [];
  if (tiles.length === 0) return null;

  const desktopCols =
    tiles.length === 1 ? "sm:grid-cols-1" : tiles.length === 2 ? "sm:grid-cols-2" : "sm:grid-cols-3";
  const mobileCols = tiles.length === 1 ? "grid-cols-1" : "grid-cols-2";

  return (
    <section className={`grid ${mobileCols} ${desktopCols} gap-2 px-4 py-3`}>
      {tiles.map((tile, index) => (
        <SmartLink
          key={index}
          link={tile.link}
          className={`relative rounded-lg overflow-hidden group ${
            tiles.length === 3 && index === 2 ? "col-span-2 sm:col-span-1" : ""
          }`}
        >
          <img
            src={tile.image}
            alt={tile.label || ""}
            loading="lazy"
            className="w-full h-32 sm:h-44 object-cover group-hover:scale-105 transition-transform duration-300"
          />
          {tile.label && (
            <span className="absolute bottom-2 start-2 bg-narzin-navy/80 text-narzin-sand text-xs sm:text-sm px-2 py-1 rounded">
              {tile.label}
            </span>
          )}
        </SmartLink>
      ))}
    </section>
  );
};

export default PromoTiles;
