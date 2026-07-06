import { describe, it, expect } from "vitest";
import { getInitialTab } from "../MyAccountLayout";

describe("getInitialTab", () => {
  it("returns the requested tab when valid", () => {
    expect(getInitialTab("orders")).toBe("orders");
    expect(getInitialTab("wallet")).toBe("wallet");
  });

  it("falls back to my-account for unknown or missing tabs", () => {
    expect(getInitialTab("bogus")).toBe("my-account");
    expect(getInitialTab(null)).toBe("my-account");
  });
});
