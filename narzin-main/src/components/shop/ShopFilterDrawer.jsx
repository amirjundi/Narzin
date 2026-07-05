import React from "react";
import { Dialog, DialogBackdrop, DialogPanel } from "@headlessui/react";
import { X } from "lucide-react";
import FilterSections from "./FilterSections";

/**
 * Mobile bottom-sheet filter drawer (SHEIN-style). Slides up from the bottom,
 * frosted glass, with a sticky footer: clear-all + show-results.
 * Filters apply immediately as you tap; the footer button just closes the sheet.
 */
export default function ShopFilterDrawer({
  open,
  onClose,
  onClear,
  resultCount,
  t,
  ...sectionProps
}) {
  return (
    <Dialog open={open} onClose={onClose} className="relative z-50 lg:hidden">
      <DialogBackdrop
        transition
        className="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity duration-300 data-[closed]:opacity-0"
      />
      <div className="fixed inset-x-0 bottom-0 flex max-h-[88vh]">
        <DialogPanel
          transition
          className="relative w-full nz-glass rounded-t-nz-lg flex flex-col transition duration-300 ease-out data-[closed]:translate-y-full"
        >
          <div className="flex items-center justify-between px-4 py-3 border-b border-nz-border">
            <h2 className="text-base font-semibold text-nz-ink">
              {t("shop.filters", "الفلاتر")}
            </h2>
            <button
              onClick={onClose}
              className="p-1.5 text-nz-muted hover:text-nz-ink"
              aria-label={t("shop.close", "إغلاق")}
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          <div className="flex-1 overflow-y-auto px-4 nz-scroll">
            <FilterSections t={t} {...sectionProps} />
          </div>

          <div className="flex gap-3 p-4 border-t border-nz-border">
            <button
              onClick={onClear}
              className="flex-1 rounded-nz border border-nz-border py-3 text-sm font-medium text-nz-ink hover:bg-black/[0.03] transition"
            >
              {t("shop.clear_filters", "مسح الفلاتر")}
            </button>
            <button
              onClick={onClose}
              className="flex-[1.6] rounded-nz bg-narzin-gold py-3 text-sm font-semibold text-white shadow-nz hover:opacity-95 transition"
            >
              {t("shop.apply", "تطبيق")}
              {resultCount ? ` (${resultCount})` : ""}
            </button>
          </div>
        </DialogPanel>
      </div>
    </Dialog>
  );
}
