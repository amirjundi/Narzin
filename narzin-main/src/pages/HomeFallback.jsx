import React from "react";
import Categories from "../components/pages/home/Categories";
import ProductsSection from "../components/pages/home/ProductsSection";
import { useTranslation } from "react-i18next";

// Rendered when the home feed API fails or returns no blocks: a safe,
// data-driven version of the legacy homepage so customers never see a blank page.
const HomeFallback = ({ categories, products }) => {
  const { t } = useTranslation();
  return (
    <div className="pt-14">
      <Categories data={categories} />
      <ProductsSection data={products} title={t("home.recently_added")} />
    </div>
  );
};

export default HomeFallback;
