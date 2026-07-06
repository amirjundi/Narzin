import { describe, it, expect } from "vitest";
import { screen, fireEvent } from "@testing-library/react";
import { renderWithProviders } from "../../../../test/renderWithProviders";
import authReducer from "../../../../Store/slices/Auth/AuthSlice";
import AccountMenu from "../AccountMenu";

describe("AccountMenu", () => {
  it("shows sign in / register when logged out", () => {
    renderWithProviders(<AccountMenu />, {
      reducers: { auth: authReducer },
      preloadedState: { auth: { isAuthenticated: false } },
    });
    fireEvent.click(screen.getByRole("button", { name: /account/i }));
    expect(screen.getByRole("link", { name: /sign in/i })).toBeInTheDocument();
    expect(screen.getByRole("link", { name: /register/i })).toBeInTheDocument();
  });

  it("shows orders and recently viewed when logged in", () => {
    renderWithProviders(<AccountMenu />, {
      reducers: { auth: authReducer },
      preloadedState: { auth: { isAuthenticated: true } },
    });
    fireEvent.click(screen.getByRole("button", { name: /account/i }));
    const orders = screen.getByRole("link", { name: /orders/i });
    expect(orders).toHaveAttribute("href", "/my-account?tab=orders");
    expect(screen.getByRole("link", { name: /recently viewed/i })).toHaveAttribute("href", "/recently-viewed");
  });
});
