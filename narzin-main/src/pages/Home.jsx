import React from "react";
import { useSelector } from "react-redux";
import BlockRenderer from "../components/pages/home/blocks/BlockRenderer";
import BlockSkeleton from "../components/pages/home/blocks/BlockSkeleton";
import HomeFallback from "./HomeFallback";
import {
  selectHomeStatus,
  selectPageBlocks,
} from "../Store/slices/HomeSlice";

const Home = ({ categories, products }) => {
  const status = useSelector(selectHomeStatus);
  const pageBlocks = useSelector(selectPageBlocks);

  if (status === "loading" || status === "idle") {
    return (
      <div className="pt-14 space-y-4" data-testid="home-skeletons">
        <BlockSkeleton variant="hero" />
        <BlockSkeleton variant="circles" />
        <BlockSkeleton variant="rail" />
        <BlockSkeleton variant="rail" />
      </div>
    );
  }

  if (status === "failed" || pageBlocks.length === 0) {
    return <HomeFallback categories={categories} products={products} />;
  }

  return (
    <div className="pt-14 pb-8 bg-narzin-bg min-h-screen">
      <BlockRenderer blocks={pageBlocks} />
    </div>
  );
};

export default Home;
