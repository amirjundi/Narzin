import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import InfoStrip from "../InfoStrip";

describe("InfoStrip", () => {
  it("renders every item's text", () => {
    renderWithProviders(
      <InfoStrip
        content={{
          items: [
            { icon: "truck", text: "Free shipping over €49", link: null },
            { icon: "definitely_new_icon", text: "Easy returns", link: null },
          ],
        }}
      />
    );
    expect(screen.getByText("Free shipping over €49")).toBeInTheDocument();
    expect(screen.getByText("Easy returns")).toBeInTheDocument();
  });
});
