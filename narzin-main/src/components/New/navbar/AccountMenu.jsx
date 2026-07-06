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
    <Link
      to={to}
      role="link"
      className={`flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700`}
    >
      <Icon className="w-4 h-4" />
      {label}
    </Link>
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
          <div className="space-y-2 p-1.5">
            <Link
              to="/signin"
              role="link"
              className="flex text-center text-sm font-medium text-gray-700 border border-gray-200 rounded-lg py-2 px-3 hover:bg-gray-50 justify-center"
            >
              {t("auth.login", "Sign In")}
            </Link>
            <Link
              to="/signup"
              role="link"
              className="flex text-center text-sm font-medium text-white bg-blue-600 rounded-lg py-2 px-3 hover:bg-blue-700 justify-center"
            >
              {t("auth.register", "Register")}
            </Link>
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
          <button
            onClick={handleLogout}
            className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-red-600 mt-1 border-t border-gray-100 pt-1 hover:bg-red-50"
          >
            <LogOut className="w-4 h-4" />
            {t("auth.logout", "Logout")}
          </button>
        )}
      </Menu.Items>
    </Menu>
  );
};

export default AccountMenu;
