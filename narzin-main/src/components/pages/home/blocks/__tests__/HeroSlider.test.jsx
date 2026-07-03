import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import HeroSlider from "../HeroSlider";

const content = {
  slides: [
    { image: "https://cdn.test/a.jpg", title: "Summer", subtitle: "Sale", link: { type: "category", value: 4 } },
    { image: "https://cdn.test/b.jpg", title: null, subtitle: null, link: null },
  ],
};

describe("HeroSlider", () => {
  it("renders all slides with images and overlay text", () => {
    const { container } = renderWithProviders(<HeroSlider content={content} />);
    const imgs = container.querySelectorAll("img");
    expect(imgs).toHaveLength(2);
    expect(imgs[0]).toHaveAttribute("src", "https://cdn.test/a.jpg");
    expect(screen.getByText("Summer")).toBeInTheDocument();
    expect(screen.getByText("Sale")).toBeInTheDocument();
  });

  it("renders one dot per slide", () => {
    renderWithProviders(<HeroSlider content={content} />);
    expect(screen.getAllByRole("button", { name: /go to slide/i })).toHaveLength(2);
  });

  it("renders nothing with no slides", () => {
    const { container } = renderWithProviders(<HeroSlider content={{ slides: [] }} />);
    expect(container.textContent).toBe("");
  });
});
