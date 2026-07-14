import { describe, it, expect, vi, beforeEach } from "vitest";

vi.mock("../../../api/axios", () => ({
  default: {
    put: vi.fn(() => Promise.resolve({ data: { ok: true } })),
    delete: vi.fn(() => Promise.resolve({ data: { ok: true } })),
  },
  getCsrfCookie: vi.fn(),
}));
vi.mock("../../../helpers/tracking", () => ({ trackCartEvent: vi.fn() }));

import { trackCartEvent } from "../../../helpers/tracking";
import { updateCartItem, removeCartItem } from "../CardSlice";

const cartItem = {
  id: 42,
  product_id: 7,
  product_variant_id: 3,
  quantity: 2,
  price: 19,
  product: { id: 7, name_arabic: "Test Product" },
  product_variant: { id: 3, price: 9.5, stock: 10 },
};

const stateWithItem = { cart: { items: [cartItem] } };
const stateWithoutItem = { cart: { items: [] } };

const runUpdate = (arg, state) =>
  updateCartItem(arg)(vi.fn(), () => state, undefined);

const runRemove = (arg, state) =>
  removeCartItem(arg)(vi.fn(), () => state, undefined);

describe("updateCartItem tracking", () => {
  beforeEach(() => vi.clearAllMocks());

  it("fires an 'update' cart-track with the looked-up product_id/variant_id/unit_price and new quantity", async () => {
    await runUpdate({ cartItemId: 42, quantity: 5 }, stateWithItem);
    expect(trackCartEvent).toHaveBeenCalledWith({
      action: "update",
      product_id: 7,
      variant_id: 3,
      quantity: 5,
      unit_price: 9.5,
    });
  });

  it("does not track (and does not throw) when the item isn't found in state", async () => {
    const result = await runUpdate({ cartItemId: 999, quantity: 1 }, stateWithoutItem);
    expect(trackCartEvent).not.toHaveBeenCalled();
    expect(result.type).toMatch(/fulfilled$/);
  });

  it("does not fail the thunk if tracking throws", async () => {
    trackCartEvent.mockImplementationOnce(() => {
      throw new Error("boom");
    });
    const result = await runUpdate({ cartItemId: 42, quantity: 3 }, stateWithItem);
    expect(result.type).toMatch(/fulfilled$/);
  });
});

describe("removeCartItem tracking", () => {
  beforeEach(() => vi.clearAllMocks());

  it("fires a 'remove' cart-track with the item's product_id/variant_id/quantity/unit_price", async () => {
    await runRemove(42, stateWithItem);
    expect(trackCartEvent).toHaveBeenCalledWith({
      action: "remove",
      product_id: 7,
      variant_id: 3,
      quantity: 2,
      unit_price: 9.5,
    });
  });

  it("does not track (and does not throw) when the item isn't found in state", async () => {
    const result = await runRemove(999, stateWithoutItem);
    expect(trackCartEvent).not.toHaveBeenCalled();
    expect(result.type).toMatch(/fulfilled$/);
  });

  it("does not fail the thunk if tracking throws", async () => {
    trackCartEvent.mockImplementationOnce(() => {
      throw new Error("boom");
    });
    const result = await runRemove(42, stateWithItem);
    expect(result.type).toMatch(/fulfilled$/);
  });
});
