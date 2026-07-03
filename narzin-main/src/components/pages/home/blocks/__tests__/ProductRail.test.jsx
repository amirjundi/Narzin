import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import ProductRail from "../ProductRail";

const content = {
  title: "Super Deals",
  rule: "manual",
  products: [
    {
      id: 21,
      name_arabic: "فستان",
      name_german: "Kleid",
      image: "https://cdn.test/p21.jpg",
      min_price: 49.99,
      min_price_iqd: 72500,
      min_price_variant_id: 210,
    },
    {
      id: 22,
      name_arabic: "حذاء",
      name_german: null,
      image: null,
      min_price: 20,
      min_price_iqd: 29000,
      min_price_variant_id: 220,
    },
  ],
};

describe("ProductRail", () => {
  it("renders title, product cards, dual prices and product links", () => {
    renderWithProviders(<ProductRail content={content} />);
    expect(screen.getByText("Super Deals")).toBeInTheDocument();
    expect(screen.getByText("Kleid")).toBeInTheDocument();
    expect(screen.getByText("€49.99")).toBeInTheDocument();
    expect(screen.getByText("72,500 IQD")).toBeInTheDocument();
    expect(screen.getAllByRole("link")[0]).toHaveAttribute("href", "/product/21");
  });

  it("uses the arabic name and falls back when german is missing", () => {
    renderWithProviders(<ProductRail content={content} />, { language: "ar" });
    expect(screen.getByText("فستان")).toBeInTheDocument();
    expect(screen.getByText("حذاء")).toBeInTheDocument();
  });

  it("renders nothing without products", () => {
    const { container } = renderWithProviders(
      <ProductRail content={{ title: "x", products: [] }} />
    );
    expect(container.textContent).toBe("");
  });
});
