import { screen, fireEvent } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import homeReducer from "../../../../../Store/slices/HomeSlice";
import AnnouncementBar from "../AnnouncementBar";

const stateWith = (blocks) => ({
  home: { blocks, status: "succeeded", error: null },
});

const bar = {
  id: 11,
  type: "announcement_bar",
  content: { text: "حمل التطبيق", link: null, bg_color: "#141923", text_color: "#C5A880" },
};

describe("AnnouncementBar", () => {
  beforeEach(() => sessionStorage.clear());

  it("renders the announcement text with configured colors", () => {
    renderWithProviders(<AnnouncementBar />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([bar]),
    });
    const strip = screen.getByText("حمل التطبيق").closest("[data-testid='announcement-bar']");
    expect(strip).toHaveStyle({ backgroundColor: "#141923" });
  });

  it("renders nothing when there is no announcement block", () => {
    const { container } = renderWithProviders(<AnnouncementBar />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([]),
    });
    expect(container.textContent).toBe("");
  });

  it("dismisses for the session", () => {
    const first = renderWithProviders(<AnnouncementBar />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([bar]),
    });
    fireEvent.click(screen.getByRole("button", { name: /dismiss/i }));
    expect(screen.queryByText("حمل التطبيق")).toBeNull();
    first.unmount();

    renderWithProviders(<AnnouncementBar />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([bar]),
    });
    expect(screen.queryByText("حمل التطبيق")).toBeNull();
  });

  it("stays dismissed when the bar arrives after the feed resolves late", () => {
    sessionStorage.setItem("home_announcement_dismissed_11", "1");

    const { store } = renderWithProviders(<AnnouncementBar />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([]),
    });

    store.dispatch({ type: "home/fetchHome/fulfilled", payload: [bar] });

    expect(screen.queryByText("حمل التطبيق")).toBeNull();
  });
});
