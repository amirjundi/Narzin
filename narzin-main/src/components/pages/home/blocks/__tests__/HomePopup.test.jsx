import { screen, fireEvent, act } from "@testing-library/react";
import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import homeReducer from "../../../../../Store/slices/HomeSlice";
import HomePopup from "../HomePopup";

const popupBlock = {
  id: 31,
  type: "popup",
  content: {
    image: null,
    title: "Get the app",
    text: "Shop faster",
    button_label: "Download",
    link: { type: "url", value: "https://apps.test/narzin" },
    frequency: { mode: "once_per_session", days: 0 },
    delay_seconds: 2,
  },
};

const stateWith = (blocks) => ({
  home: { blocks, status: "succeeded", error: null },
});

describe("HomePopup", () => {
  beforeEach(() => {
    vi.useFakeTimers();
    sessionStorage.clear();
    localStorage.clear();
  });
  afterEach(() => vi.useRealTimers());

  it("appears after the configured delay and can be closed", () => {
    renderWithProviders(<HomePopup />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([popupBlock]),
    });
    expect(screen.queryByText("Get the app")).toBeNull();

    act(() => vi.advanceTimersByTime(2000));
    expect(screen.getByText("Get the app")).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "Download" })).toHaveAttribute(
      "href",
      "https://apps.test/narzin"
    );

    fireEvent.click(screen.getByRole("button", { name: /close/i }));
    expect(screen.queryByText("Get the app")).toBeNull();
  });

  it("does not show again in the same session", () => {
    const first = renderWithProviders(<HomePopup />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([popupBlock]),
    });
    act(() => vi.advanceTimersByTime(2000));
    first.unmount();

    renderWithProviders(<HomePopup />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([popupBlock]),
    });
    act(() => vi.advanceTimersByTime(5000));
    expect(screen.queryByText("Get the app")).toBeNull();
  });

  it("renders nothing when there is no popup block", () => {
    const { container } = renderWithProviders(<HomePopup />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([]),
    });
    act(() => vi.advanceTimersByTime(5000));
    expect(container.textContent).toBe("");
  });
});
