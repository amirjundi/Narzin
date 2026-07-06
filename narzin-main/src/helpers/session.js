// Stable per-browser session id for guest behavior tracking (telemetry /
// personalization). Persisted in localStorage so it survives reloads.
export function getSessionId() {
  let id = null;
  try {
    id = localStorage.getItem("nz_session_id");
    if (!id) {
      id =
        (typeof crypto !== "undefined" && crypto.randomUUID
          ? crypto.randomUUID()
          : `s-${Date.now()}-${Math.random().toString(36).slice(2)}`);
      localStorage.setItem("nz_session_id", id);
    }
  } catch {
    // localStorage unavailable (private mode) — fall back to a volatile id.
    id = `s-${Date.now()}-${Math.random().toString(36).slice(2)}`;
  }
  return id;
}
