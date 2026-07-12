import { describe, it, expect } from "vitest";
import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../test/renderWithProviders";
import returnsReducer from "../../../../Store/slices/ReturnsSlice";
import myOrdersReducer from "../../../../Store/slices/MyOrdersSlice";
import Returns from "../Returns";

describe("Returns", () => {
  it("shows the empty-state when there are no returns", () => {
    renderWithProviders(<Returns />, {
      reducers: { returns: returnsReducer, myOrders: myOrdersReducer },
      preloadedState: {
        returns: { returns: [], status: "succeeded", error: null, submitting: false, submitError: null },
        myOrders: { orders: [], order: null, status: "idle", error: null, pagination: { currentPage: 1, lastPage: 1, total: 0 } },
      },
    });
    expect(screen.getByText(/haven't requested any returns/i)).toBeInTheDocument();
  });
});
