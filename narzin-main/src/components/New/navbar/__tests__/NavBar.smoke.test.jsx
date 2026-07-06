import { describe, it, expect } from "vitest";
import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../test/renderWithProviders";
import authReducer from "../../../../Store/slices/Auth/AuthSlice";
import cartReducer from "../../../../Store/slices/CardSlice";
import settingsReducer from "../../../../Store/slices/SettingsSlice";
import homeReducer from "../../../../Store/slices/HomeSlice";
import NavBar from "../../NavBar";

describe("NavBar icon cluster", () => {
  it("renders account, cart and language icons", () => {
    renderWithProviders(<NavBar data={[]} />, {
      reducers: {
        auth: authReducer,
        cart: cartReducer,
        settings: settingsReducer,
        home: homeReducer,
      },
      preloadedState: {
        auth: { isAuthenticated: false },
        cart: { items: [], totalItems: 0 },
        settings: { whatsapp_number: null, support_hours: null, status: "succeeded" },
        home: { blocks: [], status: "succeeded", error: null },
      },
    });
    expect(screen.getByRole("button", { name: /account/i })).toBeInTheDocument();
    expect(screen.getByRole("button", { name: /language/i })).toBeInTheDocument();
    // Cart is a link to /cart
    expect(screen.getAllByRole("link").some((a) => a.getAttribute("href") === "/cart")).toBe(true);
  });
});
