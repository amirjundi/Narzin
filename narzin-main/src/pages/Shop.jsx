import React, { useEffect, useState } from "react";
import ProductCard from "../components/Product/ProductCard";
import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  Disclosure,
  DisclosureButton,
  DisclosurePanel,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
} from "@headlessui/react";
import { XMarkIcon } from "@heroicons/react/24/outline";
import {
  ChevronDownIcon,
  FunnelIcon,
  MinusIcon,
  PlusIcon,
  Squares2X2Icon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from "@heroicons/react/20/solid";
import { useDispatch, useSelector } from "react-redux";
import { fetchProductsStore } from "../Store/slices/StoreSlice";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { useTranslation } from "react-i18next";

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
    perPage: 15
  });
  const [activeFilters, setActiveFilters] = useState({
    category_id: null,
    color: [],
    size: [],
    price_from: "",
    price_to: "",
    sort_by: "newest",
    page: 1
  });

  const { t, i18n } = useTranslation();

  const {
    items: store,
    StoreStatus,
    StoreError,
  } = useSelector((state) => state.store);

  // Parse query params on mount and when URL changes
  useEffect(() => {
    const searchParams = new URLSearchParams(location.search);
    
    setActiveFilters({
      category_id: searchParams.get('category_id') || null,
      color: searchParams.getAll('color') || [],
      size: searchParams.getAll('size') || [],
      price_from: searchParams.get('price_from') || "",
      price_to: searchParams.get('price_to') || "",
      sort_by: searchParams.get('sort_by') || "newest",
      page: searchParams.get('page') || 1
    });
    
    // Call API with the current filters
    const queryString = location.search || "?";
    dispatch(fetchProductsStore(queryString));
  }, [dispatch, location.search]);

  // Update state when store data changes
  useEffect(() => {
    if (store?.data) {
      if (store.data.products?.data) {
        setStoreData(store.data.products.data);
        
        // Set pagination data
        setPagination({
          currentPage: store.data.products.current_page || 1,
          lastPage: store.data.products.last_page || 1,
          total: store.data.products.total || 0,
          perPage: store.data.products.per_page || 15
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

  // Apply filters and update URL
  const applyFilters = (newFilters = {}) => {
    const updatedFilters = { ...activeFilters, ...newFilters };
    
    // Reset to page 1 when changing filters (except when explicitly changing the page)
    if (newFilters.page === undefined && Object.keys(newFilters).length > 0) {
      updatedFilters.page = 1;
    }
    
    const searchParams = new URLSearchParams();
    
    if (updatedFilters.category) {
      searchParams.append('category_id', updatedFilters.category);
    }
    
    updatedFilters.color.forEach(color => {
      searchParams.append('color', color);
    });
    
    updatedFilters.size.forEach(size => {
      searchParams.append('size', size);
    });
    
    if (updatedFilters.price_from) {
      searchParams.append('price_from', updatedFilters.price_from);
    }
    
    if (updatedFilters.price_to) {
      searchParams.append('price_to', updatedFilters.price_to);
    }
    
    if (updatedFilters.sort_by) {
      searchParams.append('sort_by', updatedFilters.sort_by);
    }
    
    if (updatedFilters.page && updatedFilters.page > 1) {
      searchParams.append('page', updatedFilters.page);
    }
    
    navigate(`?${searchParams.toString()}`);
  };

  // Handle category filter change
  const handleCategoryChange = (categoryId) => {
    applyFilters({ category: categoryId });
  };

  // Handle color filter change
  const handleColorChange = (color) => {
    const updatedColors = activeFilters.color.includes(color)
      ? activeFilters.color.filter(c => c !== color)
      : [...activeFilters.color, color];
    
    applyFilters({ color: updatedColors });
  };

  // Handle size filter change
  const handleSizeChange = (size) => {
    const updatedSizes = activeFilters.size.includes(size)
      ? activeFilters.size.filter(s => s !== size)
      : [...activeFilters.size, size];
    
    applyFilters({ size: updatedSizes });
  };

  // Handle price range change
  const handlePriceChange = (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const priceFrom = formData.get('price_from');
    const priceTo = formData.get('price_to');
    
    applyFilters({
      price_from: priceFrom,
      price_to: priceTo
    });
  };

  // Handle sort change
  const handleSortChange = (sortKey) => {
    applyFilters({ sort_by: sortKey });
  };

  // Handle page change
  const handlePageChange = (page) => {
    // Only navigate if the page is valid and different from current
    if (page >= 1 && page <= pagination.lastPage && page !== pagination.currentPage) {
      applyFilters({ page: page });
      // Scroll to top after navigation
      window.scrollTo(0, 0);
    }
  };
  // Generate pagination items
  const getPaginationItems = () => {
    const { currentPage, lastPage } = pagination;
    const items = [];
    
    // Previous button
    items.push({
      label: 'Previous',
      page: currentPage - 1,
      isEnabled: currentPage > 1,
      isCurrent: false,
      isNav: true,
    });
    
    // First page
    if (lastPage > 5) {
      items.push({
        label: '1',
        page: 1,
        isEnabled: true,
        isCurrent: currentPage === 1,
        isNav: false,
      });
    }
    
    // Ellipsis after first page
    if (currentPage > 3 && lastPage > 5) {
      items.push({
        label: '...',
        page: null,
        isEnabled: false,
        isCurrent: false,
        isNav: false,
        isEllipsis: true,
      });
    }
    
    // Pages around current
    const startPage = Math.max(lastPage <= 5 ? 1 : Math.min(currentPage - 1, lastPage - 4), 1);
    const endPage = Math.min(startPage + 4, lastPage);
    
    for (let i = startPage; i <= endPage; i++) {
      // Skip first page if we already added it
      if (lastPage > 5 && i === 1) continue;
      
      items.push({
        label: i.toString(),
        page: i,
        isEnabled: true,
        isCurrent: currentPage === i,
        isNav: false,
      });
    }
    
    // Ellipsis before last page
    if (currentPage < lastPage - 2 && lastPage > 5) {
      items.push({
        label: '...',
        page: null,
        isEnabled: false,
        isCurrent: false,
        isNav: false,
        isEllipsis: true,
      });
    }
    
    // Last page
    if (lastPage > 5 && endPage !== lastPage) {
      items.push({
        label: lastPage.toString(),
        page: lastPage,
        isEnabled: true,
        isCurrent: currentPage === lastPage,
        isNav: false,
      });
    }
    
    // Next button
    items.push({
      label: 'Next',
      page: currentPage + 1,
      isEnabled: currentPage < lastPage,
      isCurrent: false,
      isNav: true,
    });
    
    return items;
  };

  // Find category name by ID
  const getCategoryName = (categoryId) => {
    // First check main categories
    for (const category of categories) {
      if (category.id.toString() === categoryId) {
        return i18n.language === "du" ? category.name_german : category.name_arabic;
      }
      
      // Check subcategories
      if (category.subcategories) {
        for (const subcategory of category.subcategories) {
          if (subcategory.id.toString() === categoryId) {
            return i18n.language === "du" ? subcategory.name_german : subcategory.name_arabic;
          }
        }
      }
    }
    return categoryId;
  };

  // Generate color name from hex code for display
  const getColorName = (hexCode) => {
    // Simple mapping of some common colors
    const colorMap = {
      '#FF0000': 'Red',
      '#00FF00': 'Green',
      '#0000FF': 'Blue',
      '#FFFF00': 'Yellow',
      '#000000': 'Black',
      '#FFFFFF': 'White',
      // Add more as needed
    };

    return colorMap[hexCode.toUpperCase()] || hexCode;
  };

  // Format category tree for display
  const formatCategoryTree = () => {
    const result = [];
    
    categories.forEach(category => {
      result.push({
        id: category.id,
        name: i18n.language === "du" ? category.name_german : category.name_arabic,
        isParent: true
      });
      
      if (category.subcategories && category.subcategories.length) {
        category.subcategories.forEach(subcat => {
          result.push({
            id: subcat.id,
            name: i18n.language === "du" ? subcat.name_german : subcat.name_arabic,
            isParent: false,
            parentId: category.id
          });
        });
      }
    });
    
    return result;
  };

  const formattedCategories = formatCategoryTree();
  
  // Clear all filters
  const clearAllFilters = () => {
    navigate('/store');
  };
  
  // Render component
  return (
    <div className="bg-white  container mx-auto px-4">
      <div>
        {/* Mobile filter dialog */}
        <Dialog
          open={mobileFiltersOpen}
          onClose={setMobileFiltersOpen}
          className="relative z-40 lg:hidden"
        >
          <DialogBackdrop
            transition
            className="fixed inset-0 bg-black/25 transition-opacity duration-300 ease-linear data-closed:opacity-0"
          />

          <div className="fixed inset-0 z-40 flex">
            <DialogPanel
              transition
              className="relative ml-auto flex size-full max-w-xs transform flex-col overflow-y-auto bg-white py-4 pb-12 shadow-xl transition duration-300 ease-in-out data-closed:translate-x-full"
            >
              <div className="flex items-center justify-between px-4">
                <h2 className="text-lg font-medium text-gray-900">{t("shop.filters") || "Filters"}</h2>
                <button
                  type="button"
                  onClick={() => setMobileFiltersOpen(false)}
                  className="-mr-2 flex size-10 items-center justify-center rounded-md bg-white p-2 text-gray-400"
                >
                  <span className="sr-only">Close menu</span>
                  <XMarkIcon aria-hidden="true" className="size-6" />
                </button>
              </div>

              {/* Filters */}
              <div className="mt-4 border-t border-gray-200">
                {/* Categories */}
                <Disclosure as="div" className="border-t border-gray-200 px-4 py-6" defaultOpen>
                  <h3 className="-mx-2 -my-3 flow-root">
                    <DisclosureButton className="group flex w-full items-center justify-between bg-white px-2 py-3 text-gray-400 hover:text-gray-500">
                      <span className="font-medium text-gray-900">
                        {t("shop.categories") || "Categories"}
                      </span>
                      <span className="ml-6 flex items-center">
                        <PlusIcon
                          aria-hidden="true"
                          className="size-5 group-data-open:hidden"
                        />
                        <MinusIcon
                          aria-hidden="true"
                          className="size-5 group-not-data-open:hidden"
                        />
                      </span>
                    </DisclosureButton>
                  </h3>
                  <DisclosurePanel className="pt-6">
                    <div className="space-y-6">
                      {formattedCategories.map((category) => (
                        <div key={category.id} className={classNames(
                          category.isParent ? "font-semibold" : "pl-4",
                          "flex gap-3"
                        )}>
                          <label
                            className={classNames(
                              activeFilters.category_id === category.id.toString() ? "text-indigo-600" : "text-gray-500",
                              "cursor-pointer"
                            )}
                            onClick={() => handleCategoryChange(category.id.toString())}
                          >
                            {category.name}
                          </label>
                        </div>
                      ))}
                    </div>
                  </DisclosurePanel>
                </Disclosure>
                
                {/* Colors */}
                <Disclosure as="div" className="border-t border-gray-200 px-4 py-6" defaultOpen>
                  <h3 className="-mx-2 -my-3 flow-root">
                    <DisclosureButton className="group flex w-full items-center justify-between bg-white px-2 py-3 text-gray-400 hover:text-gray-500">
                      <span className="font-medium text-gray-900">
                        {t("shop.colors") || "Colors"}
                      </span>
                      <span className="ml-6 flex items-center">
                        <PlusIcon
                          aria-hidden="true"
                          className="size-5 group-data-open:hidden"
                        />
                        <MinusIcon
                          aria-hidden="true"
                          className="size-5 group-not-data-open:hidden"
                        />
                      </span>
                    </DisclosureButton>
                  </h3>
                  <DisclosurePanel className="pt-6">
                    <div className="grid grid-cols-3 gap-4">
                      {colors.slice(0, 15).map((color) => (
                        <div 
                          key={color} 
                          className="flex flex-col items-center gap-1 cursor-pointer"
                          onClick={() => handleColorChange(color)}
                        >
                          <div 
                            className={classNames(
                              "size-8 rounded-full border",
                              activeFilters.color.includes(color) ? "ring-2 ring-indigo-500" : ""
                            )}
                            style={{ backgroundColor: color.startsWith('#') ? color : color.replace('0xFF', '#') }}
                          />
                          <span className="text-xs text-gray-500">
                          
                          </span>
                        </div>
                      ))}
                    </div>
                  </DisclosurePanel>
                </Disclosure>
                {/* Sizes */}
                <Disclosure as="div" className="border-t border-gray-200 px-4 py-6" defaultOpen>
                  <h3 className="-mx-2 -my-3 flow-root">
                    <DisclosureButton className="group flex w-full items-center justify-between bg-white px-2 py-3 text-gray-400 hover:text-gray-500">
                      <span className="font-medium text-gray-900">
                        {t("shop.sizes") || "Sizes"}
                      </span>
                      <span className="ml-6 flex items-center">
                        <PlusIcon
                          aria-hidden="true"
                          className="size-5 group-data-open:hidden"
                        />
                        <MinusIcon
                          aria-hidden="true"
                          className="size-5 group-not-data-open:hidden"
                        />
                      </span>
                    </DisclosureButton>
                  </h3>
                  <DisclosurePanel className="pt-6">
                    <div className="flex flex-wrap gap-2">
                      {sizes.map((size) => (
                        <div 
                          key={size}
                          onClick={() => handleSizeChange(size)}
                          className={classNames(
                            "size-10 flex items-center  text-sm justify-center border rounded cursor-pointer w-fit", 
                            activeFilters.size.includes(size) 
                              ? "bg-indigo-600 text-white" 
                              : "bg-white text-gray-700 hover:bg-gray-50"
                          )}
                        >
                          {size}
                        </div>
                      ))}
                    </div>
                  </DisclosurePanel>
                </Disclosure>
                
                {/* Price Range */}
                <Disclosure as="div" className="border-t border-gray-200 px-4 py-6" defaultOpen>
                  <h3 className="-mx-2 -my-3 flow-root">
                    <DisclosureButton className="group flex w-full items-center justify-between bg-white px-2 py-3 text-gray-400 hover:text-gray-500">
                      <span className="font-medium text-gray-900">
                        {t("shop.price") || "Price"}
                      </span>
                      <span className="ml-6 flex items-center">
                        <PlusIcon
                          aria-hidden="true"
                          className="size-5 group-data-open:hidden"
                        />
                        <MinusIcon
                          aria-hidden="true"
                          className="size-5 group-not-data-open:hidden"
                        />
                      </span>
                    </DisclosureButton>
                  </h3>
                  <DisclosurePanel className="pt-6">
                    <form onSubmit={handlePriceChange}>
                      <div className="flex gap-4">
                        <div>
                          <label className="text-xs text-gray-500">
                            {t("shop.from") || "From"}
                          </label>
                          <input
                            type="number"
                            name="price_from"
                            defaultValue={activeFilters.price_from || ""}
                            min={priceRange.min}
                            max={priceRange.max}
                            className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            placeholder={`${priceRange.min}`}
                          />
                        </div>
                        <div>
                          <label className="text-xs text-gray-500">
                            {t("shop.to") || "To"}
                          </label>
                          <input
                            type="number"
                            name="price_to"
                            defaultValue={activeFilters.price_to || ""}
                            min={priceRange.min}
                            max={priceRange.max}
                            className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            placeholder={`${priceRange.max}`}
                          />
                        </div>
                      </div>
                      <button
                        type="submit"
                        className="mt-4 w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                      >
                        {t("shop.apply") || "Apply"}
                      </button>
                    </form>
                  </DisclosurePanel>
                </Disclosure>
              </div>
            </DialogPanel>
          </div>
        </Dialog>

        <main className="px-4 sm:px-6 lg:px-8">
          <div className="flex items-baseline justify-between border-b border-gray-200 pt-24 pb-6">
            <h1 className="text-lg  font-bold text-gray-900 leading-tight">
              {t("shop.products") || "Products"}
            </h1>

            <div className="flex items-center">
              <Menu as="div" className="relative inline-block text-left">
                <div>
                  <MenuButton className="group inline-flex justify-center text-sm font-medium text-gray-700 hover:text-gray-900">
                    {t("shop.sort") || "Sort"}
                    <ChevronDownIcon
                      aria-hidden="true"
                      className="-mr-1 ml-1 size-5 shrink-0 text-gray-400 group-hover:text-gray-500"
                    />
                  </MenuButton>
                </div>

                <MenuItems
                  transition
                  className={`absolute ${i18n.language === "du" ? `right-0` : `left-0`} z-10 mt-2 w-40 origin-top-right rounded-md bg-white ring-1 shadow-2xl ring-black/5 transition focus:outline-hidden data-closed:scale-95 data-closed:transform data-closed:opacity-0 data-enter:duration-100 data-enter:ease-out data-leave:duration-75 data-leave:ease-in`}
                >
                  <div className="py-1">
                    {sortOptions.map((option) => (
                      <MenuItem key={option.key}>
                        <button
                          onClick={() => handleSortChange(option.key)}
                          className={classNames(
                            activeFilters.sort_by === option.key
                              ? "font-medium text-gray-900"
                              : "text-gray-500",
                            "block w-full text-left px-4 py-2 text-sm data-focus:bg-gray-100 data-focus:outline-hidden"
                          )}
                        >
                          {i18n.language === "du" ? option.name_german : option.name_arabic}
                        </button>
                      </MenuItem>
                    ))}
                  </div>
                </MenuItems>
              </Menu>


              <button
                type="button"
                onClick={() => setMobileFiltersOpen(true)}
                className="-m-2 ml-4 p-2 text-gray-400 hover:text-gray-500 sm:ml-6 lg:hidden"
              >
                <span className="sr-only">
                  {t("shop.filter") || "Filter"}
                </span>
                <FunnelIcon aria-hidden="true" className="size-5" />
              </button>
            </div>
          </div>

          <section aria-labelledby="products-heading" className="pt-6 pb-24">
            <h2 id="products-heading" className="sr-only">
              {t("shop.products") || "Products"}
            </h2>

            <div className="grid grid-cols-1 gap-x-8 gap-y-10 lg:grid-cols-6">
              {/* Filters - Desktop */}

              <div className="hidden lg:block">
{/* Categories */}
<Disclosure as="div" className="border-b border-gray-200 py-6" defaultOpen>
  <h3 className="-my-3 flow-root">
    <DisclosureButton className="group flex w-full items-center justify-between bg-white py-3 text-sm text-gray-400 hover:text-gray-500">
      <span className="font-medium text-gray-900">
        {t("shop.categories")}
      </span>
      <span className="ml-6 flex items-center">
        <PlusIcon
          aria-hidden="true"
          className="size-5 group-data-open:hidden"
        />
        <MinusIcon
          aria-hidden="true"
          className="size-5 group-not-data-open:hidden"
        />
      </span>
    </DisclosureButton>
  </h3>
  <DisclosurePanel className="pt-6">
    <div className="space-y-4">
      {formattedCategories.map((category) => (
        <div key={category.id} className={classNames(
          category.isParent ? "font-semibold" : "pl-4",
          "flex gap-3"
        )}>
          <label
            className={classNames(
              activeFilters.category_id === category.id.toString() ? "text-indigo-600" : "text-gray-500",
              "cursor-pointer hover:text-gray-800 text-sm"
            )}
            onClick={() => handleCategoryChange(category.id.toString())}
          >
            { category.name }
          </label>
        </div>
      ))}
    </div>
  </DisclosurePanel>
</Disclosure>

{/* Colors */}
<Disclosure as="div" className="border-b border-gray-200 py-6" defaultOpen>
  <h3 className="-my-3 flow-root">
    <DisclosureButton className="group flex w-full items-center justify-between bg-white py-3 text-sm text-gray-400 hover:text-gray-500">
      <span className="font-medium text-gray-900">
      {t('shop.colors')}
      </span>
      <span className="ml-6 flex items-center">
        <PlusIcon
          aria-hidden="true"
          className="size-5 group-data-open:hidden"
        />
        <MinusIcon
          aria-hidden="true"
          className="size-5 group-not-data-open:hidden"
        />
      </span>
    </DisclosureButton>
  </h3>
  <DisclosurePanel className="pt-6">
    <div className="grid grid-cols-4 gap-4">
      {colors.slice(0, 20).map((color) => (
        <div 
          key={color} 
          className="flex flex-col items-center gap-1 cursor-pointer"
          onClick={() => handleColorChange(color)}
        >
          <div 
            className={classNames(
              "size-8 rounded-full border",
              activeFilters.color.includes(color) ? "ring-2 ring-indigo-500" : ""
            )}
            style={{ backgroundColor: color.startsWith('#') ? color : color.replace('0xFF', '#') }}
          />
          <span className="text-xs text-gray-500">
          </span>
        </div>
      ))}
    </div>
  </DisclosurePanel>
</Disclosure>

{/* Sizes */}
<Disclosure as="div" className="border-b border-gray-200 py-6" defaultOpen>
  <h3 className="-my-3 flow-root">
    <DisclosureButton className="group flex w-full items-center justify-between bg-white py-3 text-sm text-gray-400 hover:text-gray-500">
      <span className="font-medium text-gray-900">
        {t("shop.sizes")}
      </span>
      <span className="ml-6 flex items-center">
        <PlusIcon
          aria-hidden="true"
          className="size-5 group-data-open:hidden"
        />
        <MinusIcon
          aria-hidden="true"
          className="size-5 group-not-data-open:hidden"
        />
      </span>
    </DisclosureButton>
  </h3>
  <DisclosurePanel className="pt-6">
    <div className="flex flex-wrap gap-2">
      {sizes.map((size) => (
        <div 
          key={size}
          onClick={() => handleSizeChange(size)}
          className={classNames(
            "size-10 flex items-center text-sm justify-center border rounded cursor-pointer min-w-[60px] w-fit", 
            activeFilters.size.includes(size) 
              ? "bg-indigo-600 text-white" 
              : "bg-white text-gray-700 hover:bg-gray-50"
          )}
        >
          {size}
        </div>
      ))}
    </div>
  </DisclosurePanel>
</Disclosure>

{/* Price Range */}
<Disclosure as="div" className="border-b border-gray-200 py-6" defaultOpen>
  <h3 className="-my-3 flow-root">
    <DisclosureButton className="group flex w-full items-center justify-between bg-white py-3 text-sm text-gray-400 hover:text-gray-500">
      <span className="font-medium text-gray-900">
        {t("shop.price")}
      </span>
      <span className="ml-6 flex items-center">
        <PlusIcon
          aria-hidden="true"
          className="size-5 group-data-open:hidden"
        />
        <MinusIcon
          aria-hidden="true"
          className="size-5 group-not-data-open:hidden"
        />
      </span>
    </DisclosureButton>
  </h3>
  <DisclosurePanel className="pt-6">
    <form onSubmit={handlePriceChange}>
      <div className="flex gap-4">
        <div>
          <label className="text-xs text-gray-500">
            {t("shop.from")}
          </label>
          <input
            type="number"
            name="price_from"
            defaultValue={activeFilters.price_from || ""}
            min={priceRange.min}
            max={priceRange.max}
            className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
            placeholder={`From ${priceRange.min}`}
          />
        </div>
        <div>
          <label className="text-xs text-gray-500">
            {t("shop.to")}
          </label>
          <input
            type="number"
            name="price_to"
            defaultValue={activeFilters.price_to || ""}
            min={priceRange.min}
            max={priceRange.max}
            className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
            placeholder={`To ${priceRange.max}`}
          />
        </div>
      </div>
      <button
        type="submit"
        className="mt-4 w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
      >
        {t("shop.apply")}
      </button>
    </form>
  </DisclosurePanel>
</Disclosure>
</div>

              <div className="lg:col-span-5">
                {/* Active Filters */}
                {(activeFilters.category_id || activeFilters.color.length > 0 || activeFilters.size.length > 0 || activeFilters.price_from || activeFilters.price_to) && (
                  <div className="mb-4">
                    <div className="flex flex-wrap items-center gap-2">
                      <span className="text-sm font-medium text-gray-700">
                        {t("shop.active_filters") || "Active filters"}:
                      </span>
                      {activeFilters.category_id && (
                        <button
                          onClick={() => applyFilters({ category: null })}
                          className="inline-flex items-center rounded-full bg-gray-100 py-1 pl-3 pr-2 text-sm font-medium text-gray-800"
                        >
                          {t("shop.category") || "Category"}
                          : {getCategoryName(activeFilters.category_id)}
                          <XMarkIcon className="ml-1 size-4 text-gray-600" />
                        </button>
                      )}
                      {activeFilters.color.map(color => (
                        <button
                          key={color}
                          onClick={() => handleColorChange(color)}
                          className="inline-flex items-center rounded-full bg-gray-100 py-1 pl-3 pr-2 text-sm font-medium text-gray-800"
                        >
                          {t("shop.color") || "Color"}
                          :
                          <XMarkIcon className="ml-1 size-4 text-gray-600" />
                        </button>
                      ))}
                      {activeFilters.size.map(size => (
                        <button
                          key={size}
                          onClick={() => handleSizeChange(size)}
                          className="inline-flex items-center rounded-full bg-gray-100 py-1 pl-3 pr-2 text-sm font-medium text-gray-800"
                        >
                          {t("shop.size") || "Size"}
                          : {size}
                          <XMarkIcon className="ml-1 size-4 text-gray-600" />
                        </button>
                      ))}
                      {activeFilters.price_from && (
                        <button
                          onClick={() => applyFilters({ price_from: "" })}
                          className="inline-flex items-center rounded-full bg-gray-100 py-1 pl-3 pr-2 text-sm font-medium text-gray-800"
                        >
                          {t("shop.min") || "Min"}
                          : {activeFilters.price_from}
                          <XMarkIcon className="ml-1 size-4 text-gray-600" />
                        </button>
                      )}
                      {activeFilters.price_to && (
                        <button
                          onClick={() => applyFilters({ price_to: "" })}
                          className="inline-flex items-center rounded-full bg-gray-100 py-1 pl-3 pr-2 text-sm font-medium text-gray-800"
                        >
                          {t("shop.max") || "Max"}
                          : {activeFilters.price_to}
                          <XMarkIcon className="ml-1 size-4 text-gray-600" />
                        </button>
                      )}
                      <button
                        onClick={clearAllFilters}
                        className="inline-flex items-center rounded-full bg-indigo-100 py-1 px-3 text-sm font-medium text-indigo-800"
                      >
                        {t("shop.clear_filters") || "Clear all filters"}
                      </button>
                    </div>
                  </div>
                )}

                {/* Product grid */}
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                  {isLoading ? (
                    <div className="col-span-full flex justify-center items-center py-10">
                      <div className="flex flex-col items-center">
                        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
                        <p className="mt-4 text-gray-500">
                          {t("shop.loading") || "Loading products..."}
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
                    <div className="col-span-full flex justify-center items-center py-10">
                      <div className="text-center">
                        <p className="text-gray-500 mb-2">
                          {t("shop.no_products") || "No products found matching your filters"}
                        </p>
                        <button
                          onClick={clearAllFilters}
                          className="text-indigo-600 hover:text-indigo-800 font-medium"
                        >
                          {t("shop.clear_filters") || "Clear all filters"}
                        </button>
                      </div>
                    </div>
                  )}
                </div>

                {/* Pagination */}
                {pagination.lastPage > 1 && !isLoading && storeData.length > 0 && (
                  <div className="mt-6">
                    <div className="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
                      <div className="flex flex-1 justify-between sm:hidden">
                        <button
                          onClick={() => handlePageChange(pagination.currentPage - 1)}
                          disabled={pagination.currentPage === 1}
                          className={classNames(
                            pagination.currentPage === 1 ? "cursor-not-allowed opacity-50" : "",
                            "relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                          )}
                        >
                          {t("shop.previous") || "Previous"}
                        </button>
                        <button
                          onClick={() => handlePageChange(pagination.currentPage + 1)}
                          disabled={pagination.currentPage === pagination.lastPage}
                          className={classNames(
                            pagination.currentPage === pagination.lastPage ? "cursor-not-allowed opacity-50" : "",
                            "relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                          )}
                        >
                          {t("shop.next") || "Next"}
                        </button>
                      </div>
                      <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                          <p className="text-sm text-gray-700">
                            {t("shop.showing") || "Showing"} <span className="font-medium">{(pagination.currentPage - 1) * pagination.perPage + 1}</span>{" "}
                            {t("shop.to") || "to"}{" "}
                            <span className="font-medium">
                              {Math.min(pagination.currentPage * pagination.perPage, pagination.total)}
                            </span>{" "}
                            {t("shop.of") || "of"}{" "}
                            <span className="font-medium">{pagination.total}</span>{" "}
                            {t("shop.results") || "results"}
                          </p>
                        </div>
                        <div>
                          <nav className="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            {getPaginationItems().map((item, index) => {
                              if (item.isNav) {
                                return (
                                  <button
                                    key={index}
                                    onClick={() => handlePageChange(item.page)}
                                    disabled={!item.isEnabled}
                                    className={classNames(
                                      !item.isEnabled ? "cursor-not-allowed" : "hover:bg-gray-50",
                                      item.label === "Previous" ? "rounded-l-md" : "rounded-r-md",
                                      "relative inline-flex items-center border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500"
                                    )}
                                  >
                                    <span className="sr-only">{item.label}</span>
                                    {item.label === "Previous" ? (
                                      <ChevronLeftIcon className="h-5 w-5" aria-hidden="true" />
                                    ) : (
                                      <ChevronRightIcon className="h-5 w-5" aria-hidden="true" />
                                    )}
                                  </button>
                                );
                              } else if (item.isEllipsis) {
                                return (
                                  <span
                                    key={index}
                                    className="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700"
                                  >
                                    ...
                                  </span>
                                );
                              } else {
                                return (
                                  <button
                                    key={index}
                                    onClick={() => handlePageChange(item.page)}
                                    aria-current={item.isCurrent ? "page" : undefined}
                                    className={classNames(
                                      item.isCurrent
                                        ? "bg-indigo-50 border-indigo-500 text-indigo-600 z-10"
                                        : "bg-white border-gray-300 text-gray-500 hover:bg-gray-50",
                                      "relative inline-flex items-center border px-4 py-2 text-sm font-medium"
                                    )}
                                  >
                                    {item.label}
                                  </button>
                                );
                              }
                            })}
                          </nav>
                        </div>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </section>
        </main>
      </div>
    </div>
  );
};

export default Shop;

