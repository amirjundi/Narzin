import api, { getCsrfCookie } from "../api/axios";
import { getSessionId } from "./session";

// Pure: build the attribution payload from location/referrer values.
export function readAttribution(
  search = window.location.search,
  referrer = document.referrer,
  href = window.location.href
) {
  const p = new URLSearchParams(search || "");
  const pick = (k) => p.get(k) || undefined;
  return {
    utm_source: pick("utm_source"),
    utm_medium: pick("utm_medium"),
    utm_campaign: pick("utm_campaign"),
    utm_term: pick("utm_term"),
    utm_content: pick("utm_content"),
    referrer: referrer || undefined,
    landing_url: href || undefined,
  };
}

// Best-effort POST with the app's 419 CSRF-retry pattern. Never throws.
async function postTracking(url, body) {
  try {
    await api.post(url, body);
  } catch (e) {
    if (e?.response?.status === 419) {
      try {
        await getCsrfCookie();
        await api.post(url, body);
      } catch {
        /* ignore */
      }
    }
    // swallow all other errors — tracking must never break the app
  }
}

export function trackSession() {
  return postTracking("/v1/track/session", {
    session_id: getSessionId(),
    ...readAttribution(),
  });
}

export function trackCartEvent({ action, product_id, variant_id, quantity, unit_price }) {
  return postTracking("/v1/track/cart", {
    session_id: getSessionId(),
    action,
    product_id,
    variant_id: variant_id ?? null,
    quantity,
    unit_price: unit_price ?? null,
  });
}
