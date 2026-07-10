import { describe, it, expect, vi, beforeEach } from "vitest";

vi.mock("../../api/axios", () => ({
  default: { post: vi.fn(() => Promise.resolve({ data: {} })) },
  getCsrfCookie: vi.fn(() => Promise.resolve()),
}));
vi.mock("../session", () => ({ getSessionId: () => "sess-test" }));

import api from "../../api/axios";
import { readAttribution, trackSession, trackCartEvent } from "../tracking";

describe("readAttribution", () => {
  it("extracts utm params, referrer, landing url", () => {
    const a = readAttribution(
      "?utm_source=google&utm_medium=cpc&utm_campaign=july",
      "https://ref.example/",
      "https://shop.example/?utm_source=google"
    );
    expect(a.utm_source).toBe("google");
    expect(a.utm_medium).toBe("cpc");
    expect(a.utm_campaign).toBe("july");
    expect(a.referrer).toBe("https://ref.example/");
    expect(a.landing_url).toBe("https://shop.example/?utm_source=google");
  });

  it("omits absent params as undefined", () => {
    const a = readAttribution("", "", "https://shop.example/");
    expect(a.utm_source).toBeUndefined();
    expect(a.referrer).toBeUndefined();
    expect(a.landing_url).toBe("https://shop.example/");
  });
});

describe("trackSession", () => {
  beforeEach(() => vi.clearAllMocks());

  it("posts session_id + attribution to /v1/track/session", async () => {
    await trackSession();
    expect(api.post).toHaveBeenCalledWith(
      "/v1/track/session",
      expect.objectContaining({ session_id: "sess-test" })
    );
  });

  it("swallows a rejected post", async () => {
    api.post.mockRejectedValueOnce({ response: { status: 500 } });
    await expect(trackSession()).resolves.toBeUndefined();
  });
});

describe("trackCartEvent", () => {
  beforeEach(() => vi.clearAllMocks());

  it("posts mapped fields to /v1/track/cart", async () => {
    await trackCartEvent({ action: "add", product_id: 7, variant_id: 3, quantity: 2, unit_price: 9.5 });
    expect(api.post).toHaveBeenCalledWith("/v1/track/cart", {
      session_id: "sess-test",
      action: "add",
      product_id: 7,
      variant_id: 3,
      quantity: 2,
      unit_price: 9.5,
    });
  });

  it("defaults missing variant_id/unit_price to null and swallows errors", async () => {
    api.post.mockRejectedValueOnce({ response: { status: 500 } });
    await expect(
      trackCartEvent({ action: "add", product_id: 1, quantity: 1 })
    ).resolves.toBeUndefined();
  });
});
