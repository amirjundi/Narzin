const key = (content) => `home_popup_seen_${content.id}`;
const DAY_MS = 24 * 60 * 60 * 1000;

export function shouldShowPopup(content, now = Date.now()) {
  const mode = content?.frequency?.mode || "once_per_session";
  if (mode === "once_per_days") {
    const seenAt = Number(localStorage.getItem(key(content)) || 0);
    if (!seenAt) return true;
    const days = Number(content.frequency?.days || 0);
    return now - seenAt >= days * DAY_MS;
  }
  return sessionStorage.getItem(key(content)) === null;
}

export function markPopupSeen(content, now = Date.now()) {
  const mode = content?.frequency?.mode || "once_per_session";
  if (mode === "once_per_days") {
    localStorage.setItem(key(content), String(now));
  } else {
    sessionStorage.setItem(key(content), "1");
  }
}
