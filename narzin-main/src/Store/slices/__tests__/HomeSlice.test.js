import { describe, it, expect, vi } from "vitest";
import { configureStore } from "@reduxjs/toolkit";

vi.mock("../../../api/axios", () => ({
  default: { get: vi.fn() },
}));

import api from "../../../api/axios";
import homeReducer, {
  fetchHome,
  selectLayoutBlocks,
  selectPageBlocks,
  selectHomeStatus,
} from "../HomeSlice";

const makeStore = () => configureStore({ reducer: { home: homeReducer } });

const feed = [
  { id: 1, type: "announcement_bar", content: { text: "hi" } },
  { id: 2, type: "hero_slider", content: { slides: [] } },
  { id: 3, type: "popup", content: { title: "app" } },
  { id: 4, type: "product_rail", content: { title: "deals", products: [] } },
];

describe("HomeSlice", () => {
  it("stores blocks on success and splits layout vs page blocks", async () => {
    api.get.mockResolvedValueOnce({ data: { status: true, data: feed } });
    const store = makeStore();

    await store.dispatch(fetchHome("ar"));

    expect(api.get).toHaveBeenCalledWith("/v1/home", {
      params: { platform: "web", locale: "ar" },
    });
    const state = store.getState();
    expect(selectHomeStatus(state)).toBe("succeeded");
    expect(selectLayoutBlocks(state).map((b) => b.id)).toEqual([1, 3]);
    expect(selectPageBlocks(state).map((b) => b.id)).toEqual([2, 4]);
  });

  it("marks failed on error and keeps blocks empty", async () => {
    api.get.mockRejectedValueOnce(new Error("network down"));
    const store = makeStore();

    await store.dispatch(fetchHome("du"));

    const state = store.getState();
    expect(selectHomeStatus(state)).toBe("failed");
    expect(selectPageBlocks(state)).toEqual([]);
  });
});
