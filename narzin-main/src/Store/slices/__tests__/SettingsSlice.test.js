import { describe, it, expect } from "vitest";
import reducer, { fetchPublicSettings, selectWhatsappNumber } from "../SettingsSlice";

const initial = { whatsapp_number: null, support_hours: null, status: "idle" };

describe("SettingsSlice", () => {
  it("sets loading on pending", () => {
    const state = reducer(initial, { type: fetchPublicSettings.pending.type });
    expect(state.status).toBe("loading");
  });

  it("stores settings on fulfilled", () => {
    const state = reducer(initial, {
      type: fetchPublicSettings.fulfilled.type,
      payload: { whatsapp_number: "+964770", support_hours: "9-18" },
    });
    expect(state.status).toBe("succeeded");
    expect(selectWhatsappNumber({ settings: state })).toBe("+964770");
    expect(state.support_hours).toBe("9-18");
  });

  it("marks failed and keeps number null on rejected", () => {
    const state = reducer(initial, { type: fetchPublicSettings.rejected.type });
    expect(state.status).toBe("failed");
    expect(state.whatsapp_number).toBeNull();
  });
});
