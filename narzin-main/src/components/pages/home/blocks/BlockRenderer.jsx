import React from "react";
import HeroSlider from "./HeroSlider";
import CategoryCircles from "./CategoryCircles";
import InfoStrip from "./InfoStrip";
import ProductRail from "./ProductRail";
import PromoTiles from "./PromoTiles";
import CountdownBanner from "./CountdownBanner";

// Later tasks import their block component here and add it to the registry.
// Types that render at Layout level (announcement_bar, popup) or are not yet
// built stay unregistered — unregistered/unknown types render nothing.
const registry = {
  hero_slider: HeroSlider,
  category_grid: CategoryCircles,
  info_strip: InfoStrip,
  product_rail: ProductRail,
  promo_tiles: PromoTiles,
  countdown_banner: CountdownBanner,
};

// Test hook: lets tests inject a stub without depending on real block components.
export function registerBlockForTests(type, Component) {
  registry[type] = Component;
}

const BlockRenderer = ({ blocks = [] }) => (
  <>
    {blocks.map((block) => {
      const Component = registry[block.type];
      if (!Component) return null;
      return <Component key={block.id} content={block.content} block={block} />;
    })}
  </>
);

export default BlockRenderer;
