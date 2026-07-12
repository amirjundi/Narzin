import { describe, it, expect, vi, beforeEach } from "vitest";

vi.mock("../../../api/axios", () => ({
  default: {
    post: vi.fn(() => Promise.resolve({ data: { status: true, data: { id: 1, status: "requested" } } })),
    get: vi.fn(() => Promise.resolve({ data: { status: true, data: [{ id: 1, reason: "damaged", status: "requested" }] } })),
  },
  getCsrfCookie: vi.fn(),
}));

import api from "../../../api/axios";
import reducer, { requestReturn, fetchReturns } from "../ReturnsSlice";

const run = (thunk) => thunk(vi.fn(), vi.fn(), undefined);

describe("ReturnsSlice thunks", () => {
  beforeEach(() => vi.clearAllMocks());

  it("requestReturn posts reason+note to the order returns endpoint", async () => {
    await run(requestReturn({ orderId: 7, reason: "damaged", note: "cracked" }));
    expect(api.post).toHaveBeenCalledWith("/v1/orders/7/returns", { reason: "damaged", note: "cracked" });
  });

  it("requestReturn rejection surfaces the backend message", async () => {
    api.post.mockRejectedValueOnce({ response: { data: { status: false, message: "A return already exists for this order" } } });
    const result = await run(requestReturn({ orderId: 7, reason: "damaged" }));
    expect(result.type).toMatch(/rejected$/);
    expect(result.payload.message).toBe("A return already exists for this order");
  });

  it("fetchReturns gets the returns list", async () => {
    const result = await run(fetchReturns());
    expect(api.get).toHaveBeenCalledWith("/v1/returns");
    expect(result.payload[0].reason).toBe("damaged");
  });
});

describe("ReturnsSlice reducer", () => {
  it("sets submitting on pending and stores returns on fetch fulfilled", () => {
    let state = reducer(undefined, { type: requestReturn.pending.type });
    expect(state.submitting).toBe(true);

    state = reducer(state, { type: fetchReturns.fulfilled.type, payload: [{ id: 1 }] });
    expect(state.returns).toHaveLength(1);

    state = reducer(state, { type: requestReturn.rejected.type, payload: { message: "boom" } });
    expect(state.submitError).toBe("boom");
    expect(state.submitting).toBe(false);
  });
});
