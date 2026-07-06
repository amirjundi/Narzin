import { describe, it, expect } from "vitest";
import { buildWhatsappUrl } from "../waLink";

describe("buildWhatsappUrl", () => {
  it("strips non-digits and builds a wa.me link", () => {
    expect(buildWhatsappUrl("+964 770-123-4567")).toBe("https://wa.me/9647701234567");
  });

  it("returns null for empty / nullish input", () => {
    expect(buildWhatsappUrl("")).toBeNull();
    expect(buildWhatsappUrl(null)).toBeNull();
    expect(buildWhatsappUrl(undefined)).toBeNull();
  });

  it("returns null when there are no digits", () => {
    expect(buildWhatsappUrl("abc")).toBeNull();
  });
});
