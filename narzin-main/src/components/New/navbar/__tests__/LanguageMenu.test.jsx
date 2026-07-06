import { describe, it, expect } from "vitest";
import { screen, fireEvent } from "@testing-library/react";
import { renderWithProviders } from "../../../../test/renderWithProviders";
import LanguageMenu from "../LanguageMenu";

describe("LanguageMenu", () => {
  it("opens and shows language options and the currency read-out", () => {
    renderWithProviders(<LanguageMenu />, { language: "du" });
    fireEvent.click(screen.getByRole("button", { name: /language/i }));
    expect(screen.getByText(/Deutsch/)).toBeInTheDocument();
    expect(screen.getByText(/العربية/)).toBeInTheDocument();
    expect(screen.getByText(/EUR/)).toBeInTheDocument();
  });
});
