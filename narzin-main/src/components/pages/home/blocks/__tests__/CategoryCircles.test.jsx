import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import CategoryCircles from "../CategoryCircles";

describe("CategoryCircles", () => {
  it("renders a linked circle per category", () => {
    renderWithProviders(
      <CategoryCircles
        content={{
          categories: [
            { id: 1, name: "Kleider", image: "https://cdn.test/c1.jpg" },
            { id: 2, name: "Schuhe", image: null },
          ],
        }}
      />
    );
    expect(screen.getByRole("link", { name: /Kleider/ })).toHaveAttribute(
      "href",
      "/store?category_id=1"
    );
    expect(screen.getByText("Schuhe")).toBeInTheDocument();
  });

  it("renders nothing when empty", () => {
    const { container } = renderWithProviders(
      <CategoryCircles content={{ categories: [] }} />
    );
    expect(container.textContent).toBe("");
  });
});
