import { describe, it, expect, vi, beforeEach } from "vitest";

vi.mock("../../../api/axios", () => ({
  default: { post: vi.fn(() => Promise.resolve({ data: { ok: true } })) },
  getCsrfCookie: vi.fn(),
}));
vi.mock("../../../helpers/session", () => ({ getSessionId: () => "sess-test" }));

import api from "../../../api/axios";
import { initiatePayment } from "../CheckoutSlice";

describe("initiatePayment", () => {
  beforeEach(() => vi.clearAllMocks());

  it("includes session_id in the place-order payload", async () => {
    await initiatePayment({ address_id: 1, delivery_method_id: 2 })(vi.fn(), vi.fn(), undefined);
    expect(api.post).toHaveBeenCalledWith(
      "/v1/place-order",
      expect.objectContaining({ session_id: "sess-test", address_id: 1, delivery_method_id: 2 })
    );
  });
});
