import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import { linkTarget, SmartLink } from "../blockLink";

describe("linkTarget", () => {
  it("maps product, category, url and null", () => {
    expect(linkTarget({ type: "product", value: 7 })).toEqual({
      kind: "internal",
      to: "/product/7",
    });
    expect(linkTarget({ type: "category", value: 3 })).toEqual({
      kind: "internal",
      to: "/store?category_id=3",
    });
    expect(linkTarget({ type: "url", value: "https://x.test/a" })).toEqual({
      kind: "external",
      href: "https://x.test/a",
    });
    expect(linkTarget(null)).toBeNull();
    expect(linkTarget({ type: "weird", value: 1 })).toBeNull();
  });

  it("allows http(s) and rejects script schemes", () => {
    expect(linkTarget({ type: "url", value: "http://x.test/a" })).toEqual({
      kind: "external",
      href: "http://x.test/a",
    });
    expect(linkTarget({ type: "url", value: "javascript:alert(1)" })).toBeNull();
  });
});

describe("SmartLink", () => {
  it("renders router link for internal targets", () => {
    renderWithProviders(
      <SmartLink link={{ type: "product", value: 9 }}>go</SmartLink>
    );
    expect(screen.getByRole("link", { name: "go" })).toHaveAttribute(
      "href",
      "/product/9"
    );
  });

  it("renders external anchor with safe rel", () => {
    renderWithProviders(
      <SmartLink link={{ type: "url", value: "https://x.test" }}>out</SmartLink>
    );
    const a = screen.getByRole("link", { name: "out" });
    expect(a).toHaveAttribute("target", "_blank");
    expect(a).toHaveAttribute("rel", expect.stringContaining("noopener"));
  });

  it("renders a plain wrapper when there is no link", () => {
    renderWithProviders(<SmartLink link={null}>flat</SmartLink>);
    expect(screen.queryByRole("link")).toBeNull();
    expect(screen.getByText("flat")).toBeInTheDocument();
  });
});
