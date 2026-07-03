import {
  ChevronDown,
  ShoppingCart,
  Menu,
  X,
  ArrowRight,
  User,
  Globe,
  LogOut,
} from "lucide-react";
import React, { useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import Logo from "../Logo";
import { Link } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { logout } from "../../Store/slices/Auth/AuthSlice";
import AnnouncementBar from "../pages/home/blocks/AnnouncementBar";

const NavBar = ({ data }) => {
  const { t, i18n } = useTranslation();
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [hoveredCategory, setHoveredCategory] = useState(null);
  const [expandedMobileCategory, setExpandedMobileCategory] = useState(null);
  const isRTL = i18n.language === "ar";
  const [categories, setCategories] = useState([]);
  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);
  const { items: cartItems, totalItems } = useSelector((state) => state.cart);
  const dispatch = useDispatch();
  console.log("language", i18n.language);

  // Language switcher
  const changeLanguage = (lng) => {
    i18n.changeLanguage(lng);
    document.documentElement.dir = i18n.dir();
    document.documentElement.lang = lng;
    setIsMenuOpen(false);
  };

  // Logout handler
  const handleLogout = () => {
    dispatch(logout());
    setIsMenuOpen(false);
    setTimeout(() => {
      window.location.href = "/signin";
    }, 1000);
  };

  // Process categories data
  useEffect(() => {
    if (data && data.length > 0) {
      const processedCategories = data
        .filter(
          (category) =>
            Array.isArray(category.sub_categories) &&
            category.sub_categories.length > 0
        )
        .map((category) => {
          const categoryName = isRTL
            ? category.name_arabic
            : category.name_german;
          const slug = isRTL ? category.slug_arabic : category.slug_german;

          const sections = {
            id: "subcategories",
            name: t("home.sub_categories"),
            items: (category.sub_categories ?? []).map((subcat) => ({
              name: isRTL ? subcat.name_arabic : subcat.name_german,
              href: `/store?category_id=${subcat.id}`,
            })),
          };

          return {
            id: category.id?.toString(),
            name: categoryName,
            slug,
            image: category.image,
            sections,
            discount: category.discount,
          };
        });

      setCategories(processedCategories);
    }
  }, [data, i18n.language, isRTL, t]);

  // Close mobile menu on outside click
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (isMenuOpen && !event.target.closest(".mobile-menu-container")) {
        setIsMenuOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [isMenuOpen]);

  // Close mobile menu on escape key
  useEffect(() => {
    const handleEscapeKey = (event) => {
      if (event.key === "Escape" && isMenuOpen) {
        setIsMenuOpen(false);
      }
    };

    document.addEventListener("keydown", handleEscapeKey);
    return () => document.removeEventListener("keydown", handleEscapeKey);
  }, [isMenuOpen]);

  return (
    <>

      <header className="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-lg border-b border-gray-200/60">
      <AnnouncementBar />
        <div className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
          <div className="flex items-center justify-between h-14 sm:h-16">
            {/* Logo Section */}
            <div className="flex items-center flex-shrink-0">
              <Link to="/" className="flex items-center">
                <Logo />
              </Link>
            </div>

            {/* Desktop Navigation */}
            <nav className="hidden lg:flex items-center space-x-1 xl:space-x-2 flex-1 justify-center max-w-4xl">
              {categories.slice(0, 6).map((category) => (
                <div
                  key={category.id}
                  className="relative group"
                  onMouseEnter={() => setHoveredCategory(category.name)}
                  onMouseLeave={() => setHoveredCategory(null)}
                >
                  <button className="flex items-center space-x-1 px-2 sm:px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 rounded-lg hover:bg-blue-50/80 transition-all duration-200">
                    <span className="truncate max-w-[100px] xl:max-w-[140px]">
                      {category.name}
                    </span>
                    <ChevronDown className="w-3 h-3 transform group-hover:rotate-180 transition-transform duration-200 flex-shrink-0" />
                  </button>

                  {/* Mega Menu Dropdown */}
                  <div
                    className={`absolute top-full left-1/2 transform -translate-x-1/2 pt-2 w-[420px] xl:w-[480px] transition-all duration-300 bg-white z-50 ${
                      hoveredCategory === category.name
                        ? "opacity-100 visible translate-y-0"
                        : "opacity-0 invisible translate-y-2 pointer-events-none"
                    }`}
                  >
                    <div className="p-5">
                      <div className="flex gap-5">
                        {/* Category Image */}
                        <div className="w-32 h-32 flex-shrink-0">
                          <div className="relative overflow-hidden rounded-lg group/img">
                            <img
                              src={category.image}
                              alt={category.name}
                              className="w-full h-full object-cover transition-transform duration-300 group-hover/img:scale-105"
                            />
                            {category.discount && (
                              <div className="absolute top-2 left-2">
                                <span className="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-md">
                                  {category.discount}
                                </span>
                              </div>
                            )}
                          </div>
                        </div>

                        {/* Subcategories */}
                        <div className="flex-1 min-w-0">
                          <div className="mb-4">
                            <h3 className="font-semibold text-gray-900 text-base mb-1">
                              {category.name}
                            </h3>
                            <div className="w-8 h-0.5 bg-blue-500 rounded"></div>
                          </div>

                          <div className="space-y-1 max-h-40 overflow-y-auto custom-scrollbar">
                            {category.sections?.items
                              .slice(0, 8)
                              .map((sub, index) => (
                                <Link
                                  key={index}
                                  to={sub.href}
                                  className="flex items-center justify-between py-2 px-3 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 group/sub"
                                >
                                  <span className="truncate">{sub.name}</span>
                                  <ArrowRight className="w-3 h-3 opacity-0 group-hover/sub:opacity-100 transform translate-x-0 group-hover/sub:translate-x-1 transition-all duration-200" />
                                </Link>
                              ))}
                          </div>

                          <div className="mt-4 pt-3 border-t border-gray-200">
                            <Link
                              to={`/category/${category.slug}`}
                              className="flex items-center space-x-2 text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors group/all"
                            >
                              <span>
                                {t("home.view_all")} {category.name}
                              </span>
                              <ArrowRight className="w-3 h-3 transform group-hover/all:translate-x-1 transition-transform duration-200" />
                            </Link>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </nav>

            {/* Desktop Auth & Actions */}
            <div className="hidden lg:flex items-center space-x-3 flex-shrink-0">
              {/* Language Switcher */}
              <div className="flex items-center">
                <button
                  onClick={() =>
                    changeLanguage(i18n.language === "ar" ? "du" : "ar")
                  }
                  className="flex items-center space-x-1.5 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-all duration-200"
                >
                  <span className="text-lg">
                    {i18n.language === "ar" ? "🇩🇪" : "🇸🇦"}
                  </span>
                  <span className="text-xs font-medium text-gray-700 hidden xl:block">
                    {i18n.language === "ar" ? "DE" : "عربي"}
                  </span>
                </button>
              </div>

              {/* Auth Links */}
              {!isAuthenticated ? (
                <div className="flex items-center space-x-2">
                  <Link
                    to="/signin"
                    className="text-sm font-medium text-gray-700 hover:text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-all duration-200"
                  >
                    {t("auth.login")}
                  </Link>
                  <Link
                    to="/signup"
                    className="text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 px-4 py-1.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                  >
                    {t("auth.register")}
                  </Link>
                </div>
              ) : (
                <div className="flex items-center space-x-2">
                  <Link
                    to="/my-account"
                    className="flex items-center space-x-1.5 text-sm font-medium text-gray-700 hover:text-blue-600 px-2 py-1.5 rounded-lg hover:bg-blue-50 transition-all duration-200"
                  >
                    <User className="w-4 h-4" />
                    <span className="hidden xl:block">
                      {t("auth.my_account")}
                    </span>
                  </Link>
                  <button
                    onClick={handleLogout}
                    className="text-sm font-medium text-red-600 hover:text-red-700 px-2 py-1.5 rounded-lg hover:bg-red-50 transition-all duration-200"
                  >
                    <LogOut className="w-4 h-4" />
                  </button>
                </div>
              )}

              {/* Shopping Cart */}
              <Link
                to="/cart"
                className="relative p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 group"
              >
                <ShoppingCart className="w-5 h-5 group-hover:scale-110 transition-transform duration-200" />
                {isAuthenticated && totalItems > 0 && (
                  <span className="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center shadow-lg">
                    {totalItems > 99 ? "99+" : totalItems}
                  </span>
                )}
              </Link>
            </div>

            {/* Mobile Actions */}
            <div className="flex lg:hidden items-center space-x-2">
              {/* Mobile Cart */}
              <Link
                to="/cart"
                className="relative p-2 text-gray-600 hover:text-blue-600 rounded-lg transition-colors"
              >
                <ShoppingCart className="w-5 h-5" />
                {isAuthenticated && totalItems > 0 && (
                  <span className="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                    {totalItems > 9 ? "9+" : totalItems}
                  </span>
                )}
              </Link>

              {/* Mobile Menu Toggle */}
              <button
                onClick={() => setIsMenuOpen(!isMenuOpen)}
                className="p-2 text-gray-600 hover:text-blue-600 rounded-lg transition-colors"
                aria-label="Toggle menu"
              >
                {isMenuOpen ? (
                  <X className="w-5 h-5" />
                ) : (
                  <Menu className="w-5 h-5" />
                )}
              </button>
            </div>
          </div>
        </div>

        {/* Mobile Menu */}
        <div
          className={`lg:hidden mobile-menu-container bg-white border-t border-gray-200 transition-all duration-300 ${
            isMenuOpen
              ? "max-h-screen opacity-100"
              : "max-h-0 opacity-0 overflow-hidden"
          }`}
        >
          <div className="max-w-7xl mx-auto px-3 sm:px-4 py-4 max-h-[calc(100vh-4rem)] overflow-y-auto custom-scrollbar">
            {/* Mobile Auth Section */}
            <div className="pb-4 mb-4 border-b border-gray-200">
              {!isAuthenticated ? (
                <div className="space-y-2">
                  <Link
                    to="/signin"
                    onClick={() => setIsMenuOpen(false)}
                    className="flex items-center space-x-3 w-full text-left py-3 px-4 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                  >
                    <User className="w-5 h-5" />
                    <span className="font-medium">{t("auth.login")}</span>
                  </Link>
                  <Link
                    to="/signup"
                    onClick={() => setIsMenuOpen(false)}
                    className="flex items-center justify-center space-x-2 w-full py-3 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all duration-200"
                  >
                    <span>{t("auth.register")}</span>
                    <ArrowRight className="w-4 h-4" />
                  </Link>
                </div>
              ) : (
                <div className="space-y-2">
                  <Link
                    to="/my-account"
                    onClick={() => setIsMenuOpen(false)}
                    className="flex items-center space-x-3 w-full text-left py-3 px-4 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                  >
                    <User className="w-5 h-5" />
                    <span className="font-medium">{t("auth.my_account")}</span>
                  </Link>
                  <button
                    onClick={handleLogout}
                    className="flex items-center space-x-3 w-full text-left py-3 px-4 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all duration-200"
                  >
                    <LogOut className="w-5 h-5" />
                    <span className="font-medium">{t("auth.logout")}</span>
                  </button>
                </div>
              )}
            </div>

            {/* Mobile Language Section */}
            <div className="pb-4 mb-4 border-b border-gray-200">
              <div className="flex items-center space-x-2 mb-3 px-2">
                <Globe className="w-4 h-4 text-gray-600" />
                <span className="text-sm font-medium text-gray-700">
                  Language
                </span>
              </div>
              <div className="grid grid-cols-2 gap-2">
                <button
                  onClick={() => changeLanguage("ar")}
                  className={`flex items-center justify-center space-x-2 py-2.5 px-3 rounded-lg transition-all duration-200 ${
                    i18n.language === "ar"
                      ? "bg-blue-100 text-blue-700 border border-blue-300"
                      : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                  }`}
                >
                  <span className="text-base">🇸🇦</span>
                  <span className="text-sm font-medium">عربي</span>
                </button>
                <button
                  onClick={() => changeLanguage("du")}
                  className={`flex items-center justify-center space-x-2 py-2.5 px-3 rounded-lg transition-all duration-200 ${
                    i18n.language === "du"
                      ? "bg-blue-100 text-blue-700 border border-blue-300"
                      : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                  }`}
                >
                  <span className="text-base">🇩🇪</span>
                  <span className="text-sm font-medium">Deutsch</span>
                </button>
              </div>
            </div>

            {/* Mobile Categories */}
            <div className="space-y-2">
              <h3 className="text-sm font-semibold text-gray-600 px-2 mb-3">
                Categories
              </h3>
              {categories.map((category) => (
                <div
                  key={category.id}
                  className="border border-gray-200 rounded-lg overflow-hidden"
                >
                  <button
                    onClick={() =>
                      setExpandedMobileCategory(
                        expandedMobileCategory === category.name
                          ? null
                          : category.name
                      )
                    }
                    className="flex items-center justify-between w-full px-4 py-3 text-left text-gray-700 hover:bg-gray-50 transition-colors"
                  >
                    <span className="font-medium truncate pr-2">
                      {category.name}
                    </span>
                    <ChevronDown
                      className={`w-4 h-4 transition-transform duration-200 flex-shrink-0 ${
                        expandedMobileCategory === category.name
                          ? "rotate-180"
                          : ""
                      }`}
                    />
                  </button>

                  <div
                    className={`transition-all duration-300 ${
                      expandedMobileCategory === category.name
                        ? "max-h-80 opacity-100"
                        : "max-h-0 opacity-0 overflow-hidden"
                    }`}
                  >
                    <div className="bg-gray-50 border-t border-gray-200 p-3">
                      <div className="space-y-1 max-h-64 overflow-y-auto custom-scrollbar">
                        {category.sections?.items
                          .slice(0, 10)
                          .map((sub, index) => (
                            <Link
                              key={index}
                              to={sub.href}
                              onClick={() => setIsMenuOpen(false)}
                              className="flex items-center justify-between py-2 px-3 text-sm text-gray-600 hover:text-blue-600 hover:bg-white rounded-lg transition-all duration-200"
                            >
                              <span className="truncate">{sub.name}</span>
                              <ArrowRight className="w-3 h-3 flex-shrink-0" />
                            </Link>
                          ))}
                        <Link
                          to={`/category/${category.slug}`}
                          onClick={() => setIsMenuOpen(false)}
                          className="flex items-center justify-center space-x-2 mt-3 py-2 px-3 bg-white border border-blue-200 text-blue-600 font-medium rounded-lg hover:bg-blue-50 transition-all duration-200"
                        >
                          <span className="text-sm">{t("home.view_all")}</span>
                          <ArrowRight className="w-3 h-3" />
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </header>

      {/* Custom Styles */}
      <style>{`
        .custom-scrollbar::-webkit-scrollbar {
          width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
          background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
          background: #cbd5e1;
          border-radius: 2px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
          background: #94a3b8;
        }
      `}</style>
    </>
  );
};

export default NavBar;
