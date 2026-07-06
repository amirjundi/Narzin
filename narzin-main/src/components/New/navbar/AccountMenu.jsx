import { Menu } from "@headlessui/react";
import { User, Package, Clock, Heart, Wallet, MapPin, LogOut } from "lucide-react";
import { Link, useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { useTranslation } from "react-i18next";
import { logout } from "../../../Store/slices/Auth/AuthSlice";

const AccountMenu = () => {
  const { t } = useTranslation();
  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const handleLogout = () => {
    dispatch(logout());
    setTimeout(() => navigate("/signin"), 300);
  };

  const item = (to, Icon, label) => (
    <Menu.Item>
      {({ active }) => (
        <Link
          to={to}
          className={`flex items-center gap-3 rounded-lg px-3 py-2 text-sm ${
            active ? "bg-blue-50 text-blue-700" : "text-gray-700"
          }`}
        >
          <Icon className="w-4 h-4" />
          {label}
        </Link>
      )}
    </Menu.Item>
  );

  return (
    <Menu as="div" className="relative">
      <Menu.Button
        aria-label={t("topbar.account", "Account")}
        className="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
      >
        <User className="w-5 h-5" />
      </Menu.Button>
      <Menu.Items className="absolute end-0 mt-2 w-60 origin-top-end rounded-xl bg-white shadow-lg ring-1 ring-gray-200/70 focus:outline-none z-50 p-1.5">
        {!isAuthenticated && (
          <div className="flex gap-2 p-1.5">
            <Menu.Item>
              {() => (
                <Link
                  to="/signin"
                  className="flex-1 text-center text-sm font-medium text-gray-700 border border-gray-200 rounded-lg py-2 hover:bg-gray-50"
                >
                  {t("auth.login", "Sign In")}
                </Link>
              )}
            </Menu.Item>
            <Menu.Item>
              {() => (
                <Link
                  to="/signup"
                  className="flex-1 text-center text-sm font-medium text-white bg-blue-600 rounded-lg py-2 hover:bg-blue-700"
                >
                  {t("auth.register", "Register")}
                </Link>
              )}
            </Menu.Item>
          </div>
        )}

        <>
          {isAuthenticated && item("/my-account?tab=orders", Package, t("topbar.my_orders", "My Orders"))}
          {item("/recently-viewed", Clock, t("topbar.recently_viewed", "Recently Viewed"))}
          {isAuthenticated && item("/my-account?tab=wishlist", Heart, t("topbar.wishlist", "Wishlist"))}
          {isAuthenticated && item("/my-account?tab=wallet", Wallet, t("topbar.wallet", "My Wallet"))}
          {isAuthenticated && item("/my-account?tab=addresses", MapPin, t("topbar.addresses", "Addresses"))}
          {isAuthenticated && item("/my-account", User, t("topbar.my_account", "My Account"))}
        </>

        {isAuthenticated && (
          <div className="mt-1 border-t border-gray-100 pt-1">
            <Menu.Item>
              {({ active }) => (
                <button
                  onClick={handleLogout}
                  className={`flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-red-600 ${
                    active ? "bg-red-50" : ""
                  }`}
                >
                  <LogOut className="w-4 h-4" />
                  {t("auth.logout", "Logout")}
                </button>
              )}
            </Menu.Item>
          </div>
        )}
      </Menu.Items>
    </Menu>
  );
};

export default AccountMenu;
