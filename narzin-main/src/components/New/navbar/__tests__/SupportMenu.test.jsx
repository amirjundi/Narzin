import { describe, it, expect } from "vitest";
import { screen, fireEvent } from "@testing-library/react";
import { renderWithProviders } from "../../../../test/renderWithProviders";
import settingsReducer from "../../../../Store/slices/SettingsSlice";
import SupportMenu from "../SupportMenu";

describe("SupportMenu", () => {
  it("renders nothing when no whatsapp number is set", () => {
    const { container } = renderWithProviders(<SupportMenu />, {
      reducers: { settings: settingsReducer },
      preloadedState: { settings: { whatsapp_number: null, support_hours: null, status: "succeeded" } },
    });
    expect(container).toBeEmptyDOMElement();
  });

  it("shows the number and a wa.me chat link when set", () => {
    renderWithProviders(<SupportMenu />, {
      reducers: { settings: settingsReducer },
      preloadedState: { settings: { whatsapp_number: "+964 770-123-4567", support_hours: "9-18", status: "succeeded" } },
    });
    fireEvent.click(screen.getByRole("button", { name: /support/i }));
    expect(screen.getByText(/\+964 770-123-4567/)).toBeInTheDocument();
    const link = screen.getByRole("link", { name: /whatsapp/i });
    expect(link).toHaveAttribute("href", "https://wa.me/9647701234567");
  });
});
