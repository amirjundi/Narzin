import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { renderHook, act } from "@testing-library/react";
import { useCountdown } from "../useCountdown";

describe("useCountdown", () => {
  beforeEach(() => vi.useFakeTimers());
  afterEach(() => vi.useRealTimers());

  it("counts down and ticks", () => {
    vi.setSystemTime(new Date("2026-07-03T10:00:00Z"));
    const { result } = renderHook(() =>
      useCountdown("2026-07-04T11:01:05Z")
    );
    expect(result.current).toMatchObject({
      days: 1,
      hours: 1,
      minutes: 1,
      seconds: 5,
      expired: false,
    });
    act(() => vi.advanceTimersByTime(5000));
    expect(result.current.seconds).toBe(0);
    expect(result.current.minutes).toBe(1);
  });

  it("reports expired for past dates", () => {
    vi.setSystemTime(new Date("2026-07-03T10:00:00Z"));
    const { result } = renderHook(() => useCountdown("2026-07-01T00:00:00Z"));
    expect(result.current.expired).toBe(true);
  });
});
