import { screen, fireEvent, act } from "@testing-library/react";
import { vi } from "vitest";
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

  it("syncs the active dot when the track is scrolled manually", () => {
    vi.useFakeTimers();
    const threeSlideContent = {
      slides: [
        ...content.slides,
        { image: "https://cdn.test/c.jpg", title: null, subtitle: null, link: null },
      ],
    };
    const { container } = renderWithProviders(<HeroSlider content={threeSlideContent} />);
    const track = container.querySelector("div.flex.overflow-x-auto");

    Object.defineProperty(track, "clientWidth", { value: 320, configurable: true });
    Object.defineProperty(track, "scrollLeft", { value: 640, configurable: true });

    fireEvent.scroll(track);
    act(() => {
      vi.advanceTimersByTime(200);
    });

    const dots = screen.getAllByRole("button", { name: /go to slide/i });
    expect(dots[2].className).toContain("w-5");
    expect(dots[0].className).not.toContain("w-5");

    vi.useRealTimers();
  });
});
