import React, { useEffect, useState } from "react";
import { ChevronDown } from "lucide-react";

/**
 * Collapsible section wrapper used by every filter group.
 */
function Section({ title, children, defaultOpen = true }) {
  const [open, setOpen] = useState(defaultOpen);
  return (
    <div className="border-b border-nz-border py-4">
      <button
        type="button"
        onClick={() => setOpen((o) => !o)}
        className="flex w-full items-center justify-between text-start"
      >
        <span className="text-sm font-semibold text-nz-ink">{title}</span>
        <ChevronDown
          className={`w-4 h-4 text-nz-muted transition-transform duration-200 ${
            open ? "rotate-180" : ""
          }`}
        />
      </button>
      {open && <div className="pt-4">{children}</div>}
    </div>
  );
}

/**
 * The shared filter body (categories / colors / sizes / price), used both in
 * the desktop glass sidebar and the mobile bottom sheet. All interactions apply
 * immediately through the handlers passed in from the Shop page.
 */
export default function FilterSections({
  categories = [],
  colors = [],
  sizes = [],
  priceRange = { min: 0, max: 0 },
  activeFilters,
  onCategory,
  onToggleColor,
  onToggleSize,
  onPrice,
  t,
  isRTL,
}) {
  const label = (o) => (isRTL ? o.name_arabic : o.name_german);
  const [expanded, setExpanded] = useState(null);
  const [from, setFrom] = useState(activeFilters.price_from || "");
  const [to, setTo] = useState(activeFilters.price_to || "");

  useEffect(() => {
    setFrom(activeFilters.price_from || "");
    setTo(activeFilters.price_to || "");
  }, [activeFilters.price_from, activeFilters.price_to]);

  const isCatActive = (id) => String(activeFilters.category_id) === String(id);

  return (
    <div>
      {/* Categories — parent rows expand to reveal subcategories */}
      <Section title={t("shop.categories", "الفئات")}>
        <div className="space-y-0.5 max-h-72 overflow-y-auto pe-1 nz-scroll">
          {categories.map((cat) => {
            const subs = cat.subcategories || [];
            const isOpen = expanded === cat.id;
            return (
              <div key={cat.id}>
                <div className="flex items-center">
                  <button
                    type="button"
                    onClick={() => onCategory(String(cat.id))}
                    className={`flex-1 text-start py-2 px-2 text-sm rounded-nz transition ${
                      isCatActive(cat.id)
                        ? "text-narzin-gold font-semibold"
                        : "text-nz-ink hover:bg-black/[0.03]"
                    }`}
                  >
                    {label(cat)}
                  </button>
                  {subs.length > 0 && (
                    <button
                      type="button"
                      onClick={() => setExpanded(isOpen ? null : cat.id)}
                      aria-label="toggle"
                      className="p-2 text-nz-muted hover:text-nz-ink"
                    >
                      <ChevronDown
                        className={`w-4 h-4 transition-transform duration-200 ${
                          isOpen ? "rotate-180" : ""
                        }`}
                      />
                    </button>
                  )}
                </div>
                {isOpen && subs.length > 0 && (
                  <div className="ps-3 space-y-0.5 pb-1">
                    {subs.map((sub) => (
                      <button
                        key={sub.id}
                        type="button"
                        onClick={() => onCategory(String(sub.id))}
                        className={`block w-full text-start py-1.5 px-2 text-sm rounded-nz transition ${
                          isCatActive(sub.id)
                            ? "text-narzin-gold font-semibold bg-narzin-gold/10"
                            : "text-nz-muted hover:text-nz-ink hover:bg-black/[0.03]"
                        }`}
                      >
                        {label(sub)}
                      </button>
                    ))}
                  </div>
                )}
              </div>
            );
          })}
        </div>
      </Section>

      {/* Colors */}
      {colors.length > 0 && (
        <Section title={t("shop.colors", "الألوان")}>
          <div className="flex flex-wrap gap-3">
            {colors.slice(0, 24).map((color) => {
              const hex = color.startsWith("#") ? color : color.replace("0xFF", "#");
              const active = activeFilters.color.includes(color);
              return (
                <button
                  key={color}
                  type="button"
                  onClick={() => onToggleColor(color)}
                  aria-pressed={active}
                  title={color}
                  className={`h-8 w-8 rounded-full border border-nz-border transition ${
                    active ? "ring-2 ring-narzin-gold ring-offset-2" : ""
                  }`}
                  style={{ backgroundColor: hex }}
                />
              );
            })}
          </div>
        </Section>
      )}

      {/* Sizes */}
      {sizes.length > 0 && (
        <Section title={t("shop.sizes", "الأحجام")}>
          <div className="flex flex-wrap gap-2">
            {sizes.map((size) => {
              const active = activeFilters.size.includes(size);
              return (
                <button
                  key={size}
                  type="button"
                  onClick={() => onToggleSize(size)}
                  aria-pressed={active}
                  className={`min-w-[3rem] px-3 py-2 rounded-nz text-sm border transition ${
                    active
                      ? "bg-narzin-gold text-white border-narzin-gold"
                      : "bg-nz-surface text-nz-ink border-nz-border hover:border-narzin-gold"
                  }`}
                >
                  {size}
                </button>
              );
            })}
          </div>
        </Section>
      )}

      {/* Price */}
      <Section title={t("shop.price", "السعر")}>
        <div className="flex items-end gap-2">
          <label className="flex-1">
            <span className="block text-xs text-nz-muted mb-1">{t("shop.from", "من")}</span>
            <input
              type="number"
              value={from}
              onChange={(e) => setFrom(e.target.value)}
              placeholder={String(Math.floor(priceRange.min || 0))}
              className="w-full rounded-nz border border-nz-border bg-nz-surface px-3 py-2 text-sm text-nz-ink focus:outline-none focus:ring-2 focus:ring-narzin-gold/60"
            />
          </label>
          <label className="flex-1">
            <span className="block text-xs text-nz-muted mb-1">{t("shop.to", "الى")}</span>
            <input
              type="number"
              value={to}
              onChange={(e) => setTo(e.target.value)}
              placeholder={String(Math.ceil(priceRange.max || 0))}
              className="w-full rounded-nz border border-nz-border bg-nz-surface px-3 py-2 text-sm text-nz-ink focus:outline-none focus:ring-2 focus:ring-narzin-gold/60"
            />
          </label>
          <button
            type="button"
            onClick={() => onPrice(from, to)}
            className="rounded-nz bg-narzin-navy text-white px-4 py-2 text-sm font-medium hover:opacity-90 transition"
          >
            {t("shop.apply", "تطبيق")}
          </button>
        </div>
      </Section>
    </div>
  );
}
