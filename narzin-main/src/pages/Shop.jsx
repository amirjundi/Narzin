import React, { useEffect, useState } from "react";
import ProductCard from "../components/Product/ProductCard";
import {
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
} from "@headlessui/react";
import { XMarkIcon } from "@heroicons/react/24/outline";
import {
  ChevronDownIcon,
  FunnelIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from "@heroicons/react/20/solid";
import { useDispatch, useSelector } from "react-redux";
import { fetchProductsStore } from "../Store/slices/StoreSlice";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { useTranslation } from "react-i18next";
import FilterSections from "../components/shop/FilterSections";
import ShopFilterDrawer from "../components/shop/ShopFilterDrawer";

function classNames(...classes) {
  return classes.filter(Boolean).join(" ");
}

const Shop = () => {
  const [mobileFiltersOpen, setMobileFiltersOpen] = useState(false);
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const location = useLocation();
  const [storeData, setStoreData] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [categories, setCategories] = useState([]);
  const [colors, setColors] = useState([]);
  const [sizes, setSizes] = useState([]);
  const [priceRange, setPriceRange] = useState({ min: 0, max: 100000 });
  const [sortOptions, setSortOptions] = useState([]);
  const [pagination, setPagination] = useState({
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage: 15,
  });
  const [activeFilters, setActiveFilters] = useState({
    category_id: null,
    color: [],
    size: [],
    price_from: "",
    price_to: "",
    sort_by: "newest",
    search: "",
    page: 1,
  });

  const { t, i18n } = useTranslation();
  const isRTL = i18n.language === "ar";

  const { items: store } = useSelector((state) => state.store);

  // Parse query params on mount and when the URL changes, then fetch.
  useEffect(() => {
    const searchParams = new URLSearchParams(location.search);
    setActiveFilters({
      category_id: searchParams.get("category_id") || null,
      color: searchParams.getAll("color") || [],
      size: searchParams.getAll("size") || [],
      price_from: searchParams.get("price_from") || "",
      price_to: searchParams.get("price_to") || "",
      sort_by: searchParams.get("sort_by") || "newest",
      search: searchParams.get("search") || "",
      page: searchParams.get("page") || 1,
    });

    const queryString = location.search || "?";
    dispatch(fetchProductsStore(queryString));
  }, [dispatch, location.search]);

  // Sync local state when store data changes.
  useEffect(() => {
    if (store?.data) {
      if (store.data.products?.data) {
        setStoreData(store.data.products.data);
        setPagination({
          currentPage: store.data.products.current_page || 1,
          lastPage: store.data.products.last_page || 1,
          total: store.data.products.total || 0,
          perPage: store.data.products.per_page || 15,
        });
      }
      if (store.data.filters) {
        setCategories(store.data.filters.categories || []);
        setColors(store.data.filters.colors || []);
        setSizes(store.data.filters.sizes || []);
        setPriceRange(store.data.filters.price_range || { min: 0, max: 100000 });
        setSortOptions(store.data.filters.sort_options || []);
      }
      setIsLoading(false);
    }
  }, [store]);

  // Build the URL from the merged filters and navigate. All filter keys are
  // preserved on every change (previously category_id was dropped when another
  // filter changed, so combining filters silently lost the category).
  const applyFilters = (newFilters = {}) => {
    const updated = { ...activeFilters, ...newFilters };

    // Any filter change (except an explicit page change) resets to page 1.
    if (newFilters.page === undefined && Object.keys(newFilters).length > 0) {
      updated.page = 1;
    }

    const sp = new URLSearchParams();
    if (updated.search) sp.append("search", updated.search);
    if (updated.category_id) sp.append("category_id", updated.category_id);
    (updated.color || []).forEach((c) => sp.append("color", c));
    (updated.size || []).forEach((s) => sp.append("size", s));
    if (updated.price_from) sp.append("price_from", updated.price_from);
    if (updated.price_to) sp.append("price_to", updated.price_to);
    if (updated.sort_by) sp.append("sort_by", updated.sort_by);
    if (updated.page && updated.page > 1) sp.append("page", updated.page);

    navigate(`?${sp.toString()}`);
  };

  // Toggle a category on/off.
  const handleCategoryChange = (categoryId) => {
    applyFilters({
      category_id:
        String(activeFilters.category_id) === String(categoryId)
          ? null
          : categoryId,
    });
  };

  const handleColorChange = (color) => {
    const updated = activeFilters.color.includes(color)
      ? activeFilters.color.filter((c) => c !== color)
      : [...activeFilters.color, color];
    applyFilters({ color: updated });
  };

  const handleSizeChange = (size) => {
    const updated = activeFilters.size.includes(size)
      ? activeFilters.size.filter((s) => s !== size)
      : [...activeFilters.size, size];
    applyFilters({ size: updated });
  };

  const handlePriceChange = (from, to) => {
    applyFilters({ price_from: from || "", price_to: to || "" });
  };

  const handleSortChange = (sortKey) => applyFilters({ sort_by: sortKey });

  const handlePageChange = (page) => {
    if (page >= 1 && page <= pagination.lastPage && page !== pagination.currentPage) {
      applyFilters({ page });
      window.scrollTo(0, 0);
    }
  };

  const clearAllFilters = () => {
    setMobileFiltersOpen(false);
    navigate("/store");
  };

  // Find a category / subcategory name by id for the active chips.
  const getCategoryName = (categoryId) => {
    for (const category of categories) {
      if (String(category.id) === String(categoryId)) {
        return isRTL ? category.name_arabic : category.name_german;
      }
      for (const sub of category.subcategories || []) {
        if (String(sub.id) === String(categoryId)) {
          return isRTL ? sub.name_arabic : sub.name_german;
        }
      }
    }
    return categoryId;
  };

  // Pagination items with localized nav labels.
  const getPaginationItems = () => {
    const { currentPage, lastPage } = pagination;
    const items = [];
    items.push({ key: "prev", page: currentPage - 1, isEnabled: currentPage > 1, isNav: true, dir: "prev" });

    const startPage = Math.max(lastPage <= 5 ? 1 : Math.min(currentPage - 1, lastPage - 4), 1);
    const endPage = Math.min(startPage + 4, lastPage);

    if (lastPage > 5 && startPage > 1) {
      items.push({ key: "p1", label: "1", page: 1, isEnabled: true, isCurrent: currentPage === 1 });
      if (startPage > 2) items.push({ key: "e1", isEllipsis: true });
    }
    for (let i = startPage; i <= endPage; i++) {
      items.push({ key: `p${i}`, label: String(i), page: i, isEnabled: true, isCurrent: currentPage === i });
    }
    if (lastPage > 5 && endPage < lastPage) {
      if (endPage < lastPage - 1) items.push({ key: "e2", isEllipsis: true });
      items.push({ key: "plast", label: String(lastPage), page: lastPage, isEnabled: true, isCurrent: currentPage === lastPage });
    }

    items.push({ key: "next", page: currentPage + 1, isEnabled: currentPage < lastPage, isNav: true, dir: "next" });
    return items;
  };

  const hasActive =
    activeFilters.category_id ||
    activeFilters.color.length > 0 ||
    activeFilters.size.length > 0 ||
    activeFilters.price_from ||
    activeFilters.price_to ||
    activeFilters.search;

  const sectionProps = {
    categories,
    colors,
    sizes,
    priceRange,
    activeFilters,
    onCategory: handleCategoryChange,
    onToggleColor: handleColorChange,
    onToggleSize: handleSizeChange,
    onPrice: handlePriceChange,
    isRTL,
  };

  return (
    <div className="bg-narzin-bg min-h-screen">
      <div className="container mx-auto px-4">
        {/* Mobile filter drawer */}
        <ShopFilterDrawer
          open={mobileFiltersOpen}
          onClose={() => setMobileFiltersOpen(false)}
          onClear={clearAllFilters}
          resultCount={pagination.total}
          t={t}
          {...sectionProps}
        />

        <main className="px-1 sm:px-4 lg:px-6">
          {/* Header: title + sort + mobile filter button */}
          <div className="flex items-center justify-between pt-24 pb-5">
            <h1 className="text-xl font-bold text-nz-ink">
              {t("shop.products", "منتجات")}
              {pagination.total > 0 && (
                <span className="ms-2 text-sm font-normal text-nz-muted">
                  ({pagination.total})
                </span>
              )}
            </h1>

            <div className="flex items-center gap-2">
              <Menu as="div" className="relative inline-block text-start">
                <MenuButton className="inline-flex items-center gap-1 rounded-nz nz-glass px-3 py-2 text-sm font-medium text-nz-ink">
                  {t("shop.sort", "ترتيب")}
                  <ChevronDownIcon className="w-4 h-4 text-nz-muted" />
                </MenuButton>
                <MenuItems
                  transition
                  className={`absolute ${isRTL ? "start-0" : "end-0"} z-20 mt-2 w-52 origin-top rounded-nz nz-glass p-1 shadow-nz focus:outline-none data-[closed]:scale-95 data-[closed]:opacity-0`}
                >
                  {sortOptions.map((option) => (
                    <MenuItem key={option.key}>
                      <button
                        onClick={() => handleSortChange(option.key)}
                        className={classNames(
                          activeFilters.sort_by === option.key
                            ? "text-narzin-gold font-semibold"
                            : "text-nz-ink",
                          "block w-full text-start px-3 py-2 text-sm rounded-nz data-[focus]:bg-black/[0.04]"
                        )}
                      >
                        {isRTL ? option.name_arabic : option.name_german}
                      </button>
                    </MenuItem>
                  ))}
                </MenuItems>
              </Menu>

              <button
                type="button"
                onClick={() => setMobileFiltersOpen(true)}
                className="lg:hidden inline-flex items-center gap-1 rounded-nz nz-glass px-3 py-2 text-sm font-medium text-nz-ink"
              >
                <FunnelIcon className="w-4 h-4" />
                {t("shop.filter", "تصفية")}
              </button>
            </div>
          </div>

          <div className="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-6 pb-24">
            {/* Desktop glass sidebar */}
            <aside className="hidden lg:block lg:col-span-1">
              <div className="nz-glass rounded-nz-lg p-4 sticky top-24">
                <FilterSections t={t} {...sectionProps} />
              </div>
            </aside>

            <div className="lg:col-span-5">
              {/* Active filter chips */}
              {hasActive ? (
                <div className="mb-4 flex flex-wrap items-center gap-2">
                  <span className="text-sm font-medium text-nz-muted">
                    {t("shop.active_filters", "الفلاتر النشطة")}:
                  </span>

                  {activeFilters.search && (
                    <Chip onClear={() => applyFilters({ search: "" })}>
                      {t("shop.search", "بحث")}: {activeFilters.search}
                    </Chip>
                  )}
                  {activeFilters.category_id && (
                    <Chip onClear={() => applyFilters({ category_id: null })}>
                      {getCategoryName(activeFilters.category_id)}
                    </Chip>
                  )}
                  {activeFilters.color.map((color) => (
                    <Chip key={color} onClear={() => handleColorChange(color)}>
                      <span
                        className="inline-block h-3 w-3 rounded-full border border-nz-border me-1 align-middle"
                        style={{ backgroundColor: color.startsWith("#") ? color : color.replace("0xFF", "#") }}
                      />
                      {t("shop.color", "اللون")}
                    </Chip>
                  ))}
                  {activeFilters.size.map((size) => (
                    <Chip key={size} onClear={() => handleSizeChange(size)}>
                      {t("shop.size", "المقاس")}: {size}
                    </Chip>
                  ))}
                  {activeFilters.price_from && (
                    <Chip onClear={() => applyFilters({ price_from: "" })}>
                      {t("shop.min", "الأدنى")}: {activeFilters.price_from}
                    </Chip>
                  )}
                  {activeFilters.price_to && (
                    <Chip onClear={() => applyFilters({ price_to: "" })}>
                      {t("shop.max", "الأعلى")}: {activeFilters.price_to}
                    </Chip>
                  )}
                  <button
                    onClick={clearAllFilters}
                    className="inline-flex items-center rounded-full bg-narzin-gold/15 px-3 py-1 text-sm font-medium text-narzin-gold hover:bg-narzin-gold/25 transition"
                  >
                    {t("shop.clear_filters", "مسح الفلاتر")}
                  </button>
                </div>
              ) : null}

              {/* Product grid */}
              <div className="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                {isLoading ? (
                  <div className="col-span-full flex justify-center items-center py-16">
                    <div className="flex flex-col items-center">
                      <div className="animate-spin rounded-full h-10 w-10 border-2 border-narzin-gold border-t-transparent" />
                      <p className="mt-4 text-nz-muted text-sm">
                        {t("shop.loading", "جارٍ التحميل...")}
                      </p>
                    </div>
                  </div>
                ) : storeData && storeData.length > 0 ? (
                  storeData.map((product) => (
                    <Link key={product.id} to={`/product/${product.id}`} className="group">
                      <ProductCard product={product} />
                    </Link>
                  ))
                ) : (
                  <div className="col-span-full flex justify-center items-center py-16">
                    <div className="text-center">
                      <p className="text-nz-muted mb-2">
                        {t("shop.no_products", "لا توجد منتجات")}
                      </p>
                      <button
                        onClick={clearAllFilters}
                        className="text-narzin-gold font-medium hover:underline"
                      >
                        {t("shop.clear_filters", "مسح الفلاتر")}
                      </button>
                    </div>
                  </div>
                )}
              </div>

              {/* Pagination */}
              {pagination.lastPage > 1 && !isLoading && storeData.length > 0 && (
                <div className="mt-8 flex flex-col sm:flex-row items-center justify-between gap-3">
                  <p className="text-sm text-nz-muted">
                    {t("shop.showing", "عرض")}{" "}
                    <span className="font-medium text-nz-ink">
                      {(pagination.currentPage - 1) * pagination.perPage + 1}
                    </span>{" "}
                    {t("shop.to", "الى")}{" "}
                    <span className="font-medium text-nz-ink">
                      {Math.min(pagination.currentPage * pagination.perPage, pagination.total)}
                    </span>{" "}
                    {t("shop.of", "من")}{" "}
                    <span className="font-medium text-nz-ink">{pagination.total}</span>
                  </p>

                  <nav className="inline-flex items-center gap-1">
                    {getPaginationItems().map((item) => {
                      if (item.isNav) {
                        const Icon =
                          (item.dir === "prev") !== isRTL ? ChevronLeftIcon : ChevronRightIcon;
                        return (
                          <button
                            key={item.key}
                            onClick={() => handlePageChange(item.page)}
                            disabled={!item.isEnabled}
                            className={classNames(
                              !item.isEnabled ? "opacity-40 cursor-not-allowed" : "hover:bg-black/[0.04]",
                              "inline-flex items-center justify-center h-9 w-9 rounded-nz nz-glass text-nz-ink"
                            )}
                          >
                            <Icon className="h-5 w-5" />
                          </button>
                        );
                      }
                      if (item.isEllipsis) {
                        return (
                          <span key={item.key} className="px-2 text-nz-muted">
                            …
                          </span>
                        );
                      }
                      return (
                        <button
                          key={item.key}
                          onClick={() => handlePageChange(item.page)}
                          className={classNames(
                            item.isCurrent
                              ? "bg-narzin-gold text-white"
                              : "nz-glass text-nz-ink hover:bg-black/[0.04]",
                            "inline-flex items-center justify-center h-9 min-w-9 px-2 rounded-nz text-sm font-medium"
                          )}
                        >
                          {item.label}
                        </button>
                      );
                    })}
                  </nav>
                </div>
              )}
            </div>
          </div>
        </main>
      </div>
    </div>
  );
};

/** Small removable active-filter chip. */
function Chip({ children, onClear }) {
  return (
    <button
      onClick={onClear}
      className="inline-flex items-center gap-1 rounded-full bg-nz-surface border border-nz-border py-1 ps-3 pe-2 text-sm text-nz-ink hover:border-narzin-gold transition"
    >
      {children}
      <XMarkIcon className="h-4 w-4 text-nz-muted" />
    </button>
  );
}

export default Shop;
