import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import BlockRenderer, { registerBlockForTests } from "../BlockRenderer";

const Stub = ({ content }) => <div>stub:{content.text}</div>;

describe("BlockRenderer", () => {
  it("renders known types in order and skips unknown types", () => {
    registerBlockForTests("announcement_bar", Stub);
    const blocks = [
      { id: 1, type: "announcement_bar", content: { text: "one" } },
      { id: 2, type: "from_the_future", content: {} },
      { id: 3, type: "announcement_bar", content: { text: "two" } },
    ];
    renderWithProviders(<BlockRenderer blocks={blocks} />);
    const rendered = screen.getAllByText(/stub:/).map((el) => el.textContent);
    expect(rendered).toEqual(["stub:one", "stub:two"]);
  });

  it("renders nothing for an empty list", () => {
    const { container } = renderWithProviders(<BlockRenderer blocks={[]} />);
    expect(container.textContent).toBe("");
  });
});
