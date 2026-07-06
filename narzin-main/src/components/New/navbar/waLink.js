// Build a wa.me chat URL from an admin-entered phone number.
// Strips everything except digits; returns null when there is nothing dialable.
export function buildWhatsappUrl(number) {
  if (!number) return null;
  const digits = String(number).replace(/\D/g, "");
  return digits ? `https://wa.me/${digits}` : null;
}
