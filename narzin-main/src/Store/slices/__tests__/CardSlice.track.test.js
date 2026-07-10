import { describe, it, expect, vi, beforeEach } from "vitest";

vi.mock("../../../api/axios", () => ({
  default: { post: vi.fn(() => Promise.resolve({ data: { ok: true } })) },
  getCsrfCookie: vi.fn(),
}));
vi.mock("../../../helpers/tracking", () => ({ trackCartEvent: vi.fn() }));

import { trackCartEvent } from "../../../helpers/tracking";
import { addToCart } from "../CardSlice";

const run = (arg) => addToCart(arg)(vi.fn(), vi.fn(), undefined);

describe("addToCart tracking", () => {
  beforeEach(() => vi.clearAllMocks());

  it("fires an 'add' cart-track on success with variant_id + unit_price", async () => {
    await run({ product_id: 7, product_variant_id: 3, quantity: 2, unit_price: 9.5 });
    expect(trackCartEvent).toHaveBeenCalledWith({
      action: "add",
      product_id: 7,
      variant_id: 3,
      quantity: 2,
      unit_price: 9.5,
    });
  });

  it("does not fail the thunk if tracking throws", async () => {
    trackCartEvent.mockImplementationOnce(() => {
      throw new Error("boom");
    });
    const result = await run({ product_id: 1, product_variant_id: null, quantity: 1 });
    expect(result.type).toMatch(/fulfilled$/);
  });
});
