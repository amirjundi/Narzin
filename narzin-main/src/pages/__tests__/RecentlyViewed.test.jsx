import { describe, it, expect } from "vitest";
import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../test/renderWithProviders";
import RecentlyViewed from "../RecentlyViewed";

// Identity reducer: RecentlyViewed dispatches fetchForYou on mount, which would
// (with the real reducer + no test server) reject and wipe `blocks`. An identity
// reducer keeps the preloaded state authoritative so the render is deterministic.
const forYouIdentity = (state = { blocks: [], status: "idle" }) => state;

describe("RecentlyViewed", () => {
  it("renders products from the recently_viewed rail", () => {
    renderWithProviders(<RecentlyViewed />, {
      route: "/recently-viewed",
      reducers: { forYou: forYouIdentity },
      preloadedState: {
        forYou: {
          status: "succeeded",
          blocks: [
            {
              id: 2000,
              type: "product_rail",
              content: {
                key: "recently_viewed",
                title: "Recently Viewed",
                products: [
                  { id: 7, name_german: "Testschuh", name_arabic: "حذاء", min_price: 42, image: null },
                ],
              },
            },
          ],
        },
      },
    });
    expect(screen.getByText("Testschuh")).toBeInTheDocument();
  });

  it("shows an empty state when there is no recently_viewed rail", () => {
    renderWithProviders(<RecentlyViewed />, {
      route: "/recently-viewed",
      reducers: { forYou: forYouIdentity },
      preloadedState: { forYou: { status: "succeeded", blocks: [] } },
    });
    expect(screen.getByText(/haven't viewed|no recently viewed|nothing/i)).toBeInTheDocument();
  });
});
