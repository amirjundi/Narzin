import React, { useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import { useTranslation } from "react-i18next";
import BlockRenderer from "../components/pages/home/blocks/BlockRenderer";
import BlockSkeleton from "../components/pages/home/blocks/BlockSkeleton";
import HomeFallback from "./HomeFallback";
import {
  selectHomeStatus,
  selectPageBlocks,
} from "../Store/slices/HomeSlice";
import { fetchForYou, selectForYouBlocks } from "../Store/slices/ForYouSlice";

const Home = ({ categories, products }) => {
  const dispatch = useDispatch();
  const { i18n } = useTranslation();
  const status = useSelector(selectHomeStatus);
  const pageBlocks = useSelector(selectPageBlocks);
  const forYouBlocks = useSelector(selectForYouBlocks);

  // Load personalized rails for this visitor (empty until they've browsed).
  useEffect(() => {
    dispatch(fetchForYou(i18n.language));
  }, [dispatch, i18n.language]);

  // Show the hero + category circles first, then the personalized "For You"
  // rails, then the rest of the merchandised feed.
  const composedBlocks =
    forYouBlocks.length > 0
      ? [...pageBlocks.slice(0, 2), ...forYouBlocks, ...pageBlocks.slice(2)]
      : pageBlocks;

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
      <BlockRenderer blocks={composedBlocks} />
    </div>
  );
};

export default Home;
