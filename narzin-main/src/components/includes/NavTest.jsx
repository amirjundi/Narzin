import { Fragment, useState, useEffect } from "react";
import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  Popover,
  PopoverButton,
  PopoverGroup,
  PopoverPanel,
  Tab,
  TabGroup,
  TabList,
  TabPanel,
  TabPanels,
} from "@headlessui/react";
import {
  Bars3Icon,
  MagnifyingGlassIcon,
  ShoppingBagIcon,
  XMarkIcon,
} from "@heroicons/react/24/outline";
import { useTranslation } from "react-i18next";
import { Link, useNavigate } from "react-router-dom";
import Logo from "../Logo";
import { useDispatch, useSelector } from "react-redux";
import { logout } from "../../Store/slices/Auth/AuthSlice";
import { fetchCart } from "../../Store/slices/CardSlice";

const Nav = ({ data }) => {
  const { t, i18n } = useTranslation();
  const [open, setOpen] = useState(false);
  const [searchExpanded, setSearchExpanded] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const navigate = useNavigate();
  const isRTL = i18n.language === "ar";
  const dir = isRTL ? "rtl" : "ltr";
  const [categories, setCategories] = useState([]);
  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);

  // Process API data when it changes or language changes
  useEffect(() => {
    if (data && data.length > 0) {
      const processedCategories = data.map((category) => {
        // Determine category name based on language
        const categoryName = isRTL
          ? category.name_arabic
          : category.name_german;
        const slug = isRTL ? category.slug_arabic : category.slug_german;

        // Skip featured items and sections if no subcategories
        if (!category.sub_categories || category.sub_categories.length === 0) {
          return {
            id: category.id.toString(),
            name: categoryName,
            slug: slug,
            featured: [],
            sections: [],
          };
        }

        // Process featured items only for categories with subcategories
        const featured = [
          {
            name: `${categoryName} - ${t("home.recently_added")}`,
            href: `/store?category_id=${category.id}`,
            imageSrc: category.image,
            imageAlt: categoryName,
          },
          {
            name: `${categoryName} - ${t("home.best_sellers")}`,
            href: `/store?category_id=${category.id}`,
            imageSrc: category.image,
            imageAlt: categoryName,
          },
        ];

        // Group subcategories into sections
        const sections = [];

        // Create a section for subcategories
        sections.push({
          id: "subcategories",
          name: t("home.sub_categories"),
          items: category.sub_categories.map((subcat) => ({
            name: isRTL ? subcat.name_arabic : subcat.name_german,
            href: `/store?category_id=${subcat.id}`,
          })),
        });

        return {
          id: category.id,
          name: categoryName,
          slug: slug,
          featured: featured,
          sections: sections,
        };
      });

      setCategories(processedCategories);
    }
  }, [data, i18n.language, isRTL, t]);

  // Separate categories without subcategories to render them as simple links
  const mainCategories = categories.filter((cat) => cat.sections.length > 0);
  const simpleCategories = categories.filter(
    (cat) => cat.sections.length === 0
  );

  const navigation = {
    categories: mainCategories,
    pages: simpleCategories.map((cat) => ({
      name: cat.name,
      href: `/store?category_id=${cat.id}`,
    })),
  };

  const changeLanguage = (lng) => {
    i18n.changeLanguage(lng);
    document.documentElement.dir = i18n.dir();
    document.documentElement.lang = lng;
  };

  const dispatch = useDispatch();
  const handelLogout = () => {
    dispatch(logout());

    setTimeout(() => {
      window.location.href = "/signin";
    }, 1000);
  };

  const { items: cartItems, totalItems } = useSelector((state) => state.cart);
  useEffect(() => {
    if (isAuthenticated) {
      dispatch(fetchCart());
    }
  }, [dispatch, isAuthenticated]);

  // Handle search functionality
  const handleSearchSubmit = (e) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      navigate(`/store?search=${encodeURIComponent(searchQuery.trim())}`);
      setSearchExpanded(false);
      setSearchQuery("");
    }
  };

  const handleSearchIconClick = () => {
    if (!searchExpanded) {
      setSearchExpanded(true);
    }
  };

  const handleSearchBlur = () => {
    // Only collapse if there's no search query
    if (!searchQuery.trim()) {
      setSearchExpanded(false);
    }
  };

  return (
    <div className="bg-white" dir={dir}>
      {/* Mobile menu */}
      <Dialog open={open} onClose={setOpen} className="relative z-40 lg:hidden">
        <DialogBackdrop
          transition
          className="fixed inset-0 bg-black/25 transition-opacity duration-300 ease-linear data-closed:opacity-0"
        />

        <div className="fixed inset-0 z-40 flex">
          <DialogPanel
            transition
            className={`relative flex w-full max-w-xs flex-col overflow-y-auto bg-white pb-12 shadow-xl transition duration-300 ease-in-out ${
              isRTL
                ? "data-closed:translate-x-full"
                : "data-closed:-translate-x-full"
            }`}
          >
            <div className="flex px-4 pt-5 pb-2">
              <button
                type="button"
                onClick={() => setOpen(false)}
                className={`relative -m-2 inline-flex items-center justify-center rounded-md p-2 text-gray-400 ${
                  isRTL ? "mr-auto" : "ml-auto"
                }`}
              >
                <span className="absolute -inset-0.5" />
                <span className="sr-only">Close menu</span>
                <XMarkIcon aria-hidden="true" className="size-6" />
              </button>
            </div>

            {/* Mobile Search */}
            <div className="px-4 pb-4">
              <form onSubmit={handleSearchSubmit} className="flex">
                <input
                  type="text"
                  placeholder={
                    t("home.search_placeholder") || "Search products..."
                  }
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="flex-1 rounded-l-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <button
                  type="submit"
                  className="rounded-r-md bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                  <MagnifyingGlassIcon className="h-5 w-5" />
                </button>
              </form>
            </div>

            {/* Links */}
            <TabGroup className="mt-2">
              <div className="border-b border-gray-200">
                <TabList className="-mb-px flex space-x-8 px-4">
                  {navigation.categories.map((category) => (
                    <Tab
                      key={category.name}
                      className="flex-1 border-b-2 border-transparent px-1 py-4 text-base font-medium whitespace-nowrap text-gray-900 data-selected:border-indigo-600 data-selected:text-indigo-600"
                    >
                      {category.name}
                    </Tab>
                  ))}
                </TabList>
              </div>
              <TabPanels as={Fragment}>
                {navigation.categories.map((category) => (
                  <TabPanel
                    key={category.name}
                    className="space-y-10 px-4 pt-10 pb-8"
                  >
                    <div className="grid grid-cols-2 gap-x-4">
                      {category.featured.map((item) => (
                        <div key={item.name} className="group relative text-sm">
                          <div className="aspect-square w-full rounded-lg bg-gray-100 overflow-hidden">
                            <img
                              alt={item.imageAlt}
                              src={`https://admin.narzin.com/storage/${item.imageSrc}`}
                              className="h-full w-full object-cover group-hover:opacity-75"
                            />
                          </div>
                          <a
                            href={item.href}
                            className="mt-6 block font-medium text-gray-900"
                          >
                            <span
                              aria-hidden="true"
                              className="absolute inset-0 z-10"
                            />
                            {item.name}
                          </a>
                          <p aria-hidden="true" className="mt-1">
                            {t("home.shop_now")}
                          </p>
                        </div>
                      ))}
                    </div>
                    {category.sections.map((section) => (
                      <div key={section.name}>
                        <p
                          id={`${category.id}-${section.id}-heading-mobile`}
                          className="font-medium text-gray-900"
                        >
                          {section.name}
                        </p>
                        <ul
                          role="list"
                          aria-labelledby={`${category.id}-${section.id}-heading-mobile`}
                          className="mt-6 flex flex-col space-y-6"
                        >
                          {section.items.map((item) => (
                            <li key={item.name} className="flow-root">
                              <Link
                                to={`store?category_id=${item.id}`}
                                className="-m-2 block p-2 text-gray-500"
                              >
                                {item.name}
                              </Link>
                            </li>
                          ))}
                        </ul>
                      </div>
                    ))}
                  </TabPanel>
                ))}
              </TabPanels>
            </TabGroup>

            <div className="space-y-6 border-t border-gray-200 px-4 py-6">
              {navigation.pages.map((page) => (
                <div key={page.name} className="flow-root">
                  <a
                    href={page.href}
                    className="-m-2 block p-2 font-medium text-gray-900"
                  >
                    {page.name}
                  </a>
                </div>
              ))}
            </div>

            <div className="space-y-6 border-t border-gray-200 px-4 py-6">
              <div className="flow-root">
                <a
                  href="#"
                  className="-m-2 block p-2 font-medium text-gray-900"
                >
                  {t("auth.register")}
                </a>
              </div>
              <div className="flow-root">
                <a
                  href="#"
                  className="-m-2 block p-2 font-medium text-gray-900"
                >
                  {t("auth.create_account")}
                </a>
              </div>
            </div>

            <div className="border-t border-gray-200 px-4 py-6">
              <a href="#" className="-m-2 flex items-center p-2">
                <img
                  alt=""
                  src="/api/placeholder/20/20"
                  className="block h-auto w-5 shrink-0"
                />
                <span
                  className={`block text-base font-medium text-gray-900 ${isRTL ? "mr-3" : "ml-3"}`}
                >
                  {i18n.language === "ar" ? (
                    <button
                      className="flex justify-center items-center"
                      onClick={() => changeLanguage("du")}
                    >
                      <span className="text-[24px] mx-1">🇩🇪</span>
                      <span className="text-[12px]"> NL </span>
                    </button>
                  ) : (
                    <button
                      className="flex justify-center items-center"
                      onClick={() => changeLanguage("ar")}
                    >
                      <span className="text-[24px] mx-1">🇸🇦</span>
                      <span className="text-[12px]"> عربي </span>
                    </button>
                  )}
                </span>
              </a>
            </div>
          </DialogPanel>
        </div>
      </Dialog>

      <header className="relative bg-white">
        <p className="flex h-10 items-center justify-center bg-[#3084C2] px-4 text-sm font-medium text-white sm:px-6 lg:px-8">
          Get free delivery on orders over $100
        </p>

        <nav aria-label="Top" className="px-4 sm:px-6 lg:px-8">
          <div className="border-b border-gray-200">
            <div className="flex h-16 items-center">
              <button
                type="button"
                onClick={() => setOpen(true)}
                className="relative rounded-md bg-white p-2 text-gray-400 lg:hidden"
              >
                <span className="absolute -inset-0.5" />
                <span className="sr-only">Open menu</span>
                <Bars3Icon aria-hidden="true" className="size-6" />
              </button>

              {/* Logo */}
              <div className={`${isRTL ? "mr-4 lg:mr-0" : "ml-4 lg:ml-0"}`}>
                <Link to="/">
                  <Logo />
                </Link>
              </div>

              {/* Flyout menus */}
              <PopoverGroup
                className={`hidden lg:block lg:self-stretch ${
                  isRTL ? "lg:mr-8" : "lg:ml-8"
                }`}
              >
                <div className="flex h-full space-x-8">
                  {navigation.categories.map((category) => (
                    <Popover key={category.name} className="flex mx-5">
                      <div className="relative flex">
                        <PopoverButton className="relative z-10 -mb-px flex items-center border-b-2 border-transparent pt-px text-sm font-medium text-gray-700 transition-colors duration-200 ease-out hover:text-gray-800 data-open:border-indigo-600 data-open:text-indigo-600">
                          {category.name}
                        </PopoverButton>
                      </div>

                      <PopoverPanel className="absolute inset-x-0 top-full text-sm text-gray-500">
                        {/* Presentational element used to render the bottom shadow, if we put the shadow on the actual panel it pokes out the top, so we use this shorter element to hide the top of the shadow */}
                        <div
                          className="absolute inset-0 top-1/2 bg-white shadow"
                          aria-hidden="true"
                        />

                        <div
                          className="relative bg-white"
                          style={{ zIndex: 1 }}
                        >
                          <div className="px-8">
                            <div className="grid grid-cols-2 gap-x-8 gap-y-10 py-16">
                              <div className="col-start-2 grid grid-cols-2 gap-x-8">
                                {category.featured.map((item) => (
                                  <div
                                    key={item.name}
                                    className="group relative text-base sm:text-sm"
                                  >
                                    <div className="aspect-square w-full rounded-lg bg-gray-100 overflow-hidden">
                                      <img
                                        alt={item.imageAlt}
                                        src={`https://admin.narzin.com/storage/${item.imageSrc}`}
                                        className="h-full w-full object-cover group-hover:opacity-75"
                                      />
                                    </div>
                                    <a
                                      href={item.href}
                                      className="mt-6 block font-medium text-gray-900"
                                    >
                                      <span
                                        className="absolute inset-0 z-10"
                                        aria-hidden="true"
                                      />
                                      {item.name}
                                    </a>
                                    <p className="mt-1" aria-hidden="true">
                                      {t("home.shop_now")}
                                    </p>
                                  </div>
                                ))}
                              </div>
                              <div className="row-start-1 grid grid-cols-3 gap-x-8 gap-y-10 text-sm">
                                {category.sections.map((section) => (
                                  <div key={section.name}>
                                    <p
                                      id={`${section.name}-heading`}
                                      className="font-medium text-gray-900"
                                    >
                                      {section.name}
                                    </p>
                                    <ul
                                      role="list"
                                      aria-labelledby={`${section.name}-heading`}
                                      className="mt-6 space-y-6 sm:mt-4 sm:space-y-4"
                                    >
                                      {section.items.map((item) => (
                                        <li
                                          key={item.name}
                                          className="flex mx-5"
                                        >
                                          <a
                                            href={item.href}
                                            className="hover:text-gray-800"
                                          >
                                            {item.name}
                                          </a>
                                        </li>
                                      ))}
                                    </ul>
                                  </div>
                                ))}
                              </div>
                            </div>
                          </div>
                        </div>
                      </PopoverPanel>
                    </Popover>
                  ))}

                  {navigation.pages.map((page) => (
                    <a
                      key={page.name}
                      href={page.href}
                      className="flex items-center text-sm font-medium text-gray-700 hover:text-gray-800"
                    >
                      {page.name}
                    </a>
                  ))}
                </div>
              </PopoverGroup>

              <div
                className={`flex items-center ${isRTL ? "mr-auto" : "ml-auto"}`}
              >
                {!isAuthenticated ? (
                  <div className="hidden lg:flex lg:flex-1 lg:items-center lg:justify-end lg:space-x-6">
                    <Link
                      to={"/signin"}
                      className="text-sm font-medium mx-2 text-gray-700 hover:text-gray-800"
                    >
                      {t("auth.login")}
                    </Link>
                    <span className="h-6 w-px bg-gray-200" aria-hidden="true" />
                    <Link
                      to="signup"
                      className="text-sm font-medium text-gray-700 hover:text-gray-800"
                    >
                      {t("auth.register")}
                    </Link>
                  </div>
                ) : (
                  <div>
                    <Link
                      to={"/my-account"}
                      className="text-sm font-medium mx-2 text-gray-700 hover:text-gray-800"
                    >
                      {t("auth.my_account")}
                    </Link>

                    <button
                      onClick={handelLogout}
                      className="text-sm font-medium mx-2 text-red-700 hover:text-red-800"
                    >
                      {t("auth.logout")}
                    </button>
                  </div>
                )}

                <div
                  className={`hidden lg:flex ${isRTL ? "lg:mr-8" : "lg:ml-8"}`}
                >
                  <a
                    href="#"
                    className="flex items-center text-gray-700 hover:text-gray-800"
                  >
                    <img
                      alt=""
                      src="/api/placeholder/20/20"
                      className="block h-auto w-5 shrink-0"
                    />
                    <span
                      className={`block text-sm font-medium ${
                        isRTL ? "mr-3" : "ml-3"
                      }`}
                    >
                      {i18n.language === "ar" ? (
                        <button
                          className="flex justify-center items-center"
                          onClick={() => changeLanguage("du")}
                        >
                          <span className="text-[24px] mx-1">🇩🇪</span>
                          <span className="text-[12px]"> NL </span>
                        </button>
                      ) : (
                        <button
                          className="flex justify-center items-center"
                          onClick={() => changeLanguage("ar")}
                        >
                          <span className="text-[24px] mx-1">🇸🇦</span>
                          <span className="text-[12px]"> عربي </span>
                        </button>
                      )}
                    </span>
                    <span className="sr-only">change_lang</span>
                  </a>
                </div>

                {/* Desktop Search */}
                <div
                  className={`flex items-center ${
                    isRTL ? "lg:mr-6" : "lg:ml-6"
                  }`}
                >
                  <form
                    onSubmit={handleSearchSubmit}
                    className={`flex items-center transition-all duration-300 ${
                      searchExpanded ? "w-64" : "w-auto"
                    }`}
                  >
                    <div
                      className={`relative ${searchExpanded ? "flex-1" : ""}`}
                    >
                      <input
                        type="text"
                        placeholder={
                          t("home.search_placeholder") || "Search products..."
                        }
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        onBlur={handleSearchBlur}
                        className={`transition-all duration-300 ease-in-out ${
                          searchExpanded
                            ? `w-full ${
                                isRTL ? "pr-10 pl-3" : "pl-3 pr-10"
                              } py-2 border border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500`
                            : "w-0 opacity-0 pointer-events-none"
                        }`}
                        autoFocus={searchExpanded}
                      />
                      <button
                        type={searchExpanded ? "submit" : "button"}
                        onClick={handleSearchIconClick}
                        className={`${
                          searchExpanded
                            ? `absolute ${
                                isRTL ? "left-2" : "right-2"
                              } top-1/2 transform -translate-y-1/2`
                            : "p-2"
                        } text-gray-400 hover:text-gray-500 focus:outline-none`}
                      >
                        <span className="sr-only">Search</span>
                        <MagnifyingGlassIcon
                          className="size-6"
                          aria-hidden="true"
                        />
                      </button>
                    </div>
                  </form>
                </div>

                {/* Cart */}
                <div
                  className={`flow-root ${
                    isRTL ? "mr-4 lg:mr-6" : "ml-4 lg:ml-6"
                  }`}
                >
                  <Link
                    to="/cart"
                    className="group -m-2 flex items-center p-2 relative"
                  >
                    <ShoppingBagIcon
                      className="size-6 shrink-0 text-gray-400 group-hover:text-gray-500"
                      aria-hidden="true"
                    />
                    <span
                      className={`text-sm font-medium text-gray-700 group-hover:text-gray-800 ${
                        isRTL ? "mr-2" : "ml-2"
                      }`}
                    ></span>

                    {/* Badge for cart items if more than 0 */}
                    {isAuthenticated && totalItems > 0 && (
                      <span className="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-[#3084C2] text-white text-xs flex items-center justify-center">
                        {totalItems > 9 ? "9+" : totalItems}
                      </span>
                    )}

                    <span className="sr-only">Items in cart, view bag</span>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </nav>
      </header>
    </div>
  );
};
export default Nav;
