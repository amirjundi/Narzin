import { describe, it, expect, beforeEach } from "vitest";
import { shouldShowPopup, markPopupSeen } from "../popupFrequency";

const sessionPopup = {
  id: 5,
  frequency: { mode: "once_per_session", days: 0 },
};
const daysPopup = { id: 6, frequency: { mode: "once_per_days", days: 7 } };

describe("popup frequency capping", () => {
  beforeEach(() => {
    sessionStorage.clear();
    localStorage.clear();
  });

  it("shows a session popup once per session", () => {
    expect(shouldShowPopup(sessionPopup)).toBe(true);
    markPopupSeen(sessionPopup);
    expect(shouldShowPopup(sessionPopup)).toBe(false);
  });

  it("shows a days popup again only after N days", () => {
    const now = Date.parse("2026-07-03T10:00:00Z");
    expect(shouldShowPopup(daysPopup, now)).toBe(true);
    markPopupSeen(daysPopup, now);
    const sixDaysLater = now + 6 * 24 * 60 * 60 * 1000;
    const eightDaysLater = now + 8 * 24 * 60 * 60 * 1000;
    expect(shouldShowPopup(daysPopup, sixDaysLater)).toBe(false);
    expect(shouldShowPopup(daysPopup, eightDaysLater)).toBe(true);
  });

  it("popups are independent per block id", () => {
    markPopupSeen(sessionPopup);
    expect(shouldShowPopup({ ...sessionPopup, id: 99 })).toBe(true);
  });
});
