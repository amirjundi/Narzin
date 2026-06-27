import React from "react";
import Logo from "../Logo";
import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import { useSelector } from "react-redux";

const Footer = () => {
  const { t, i18n } = useTranslation();

  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);

  return (


      <footer className="bg-gray-900 text-white py-12">
        <div className="container mx-auto px-4">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
            <div className="col-span-2 md:col-span-1">
              <div className="flex items-center space-x-2 mb-4">
                <div className="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                  <Logo />
                </div>
                <span className="text-lg font-bold"> Narzin-Commerce</span>
              </div>
              <p className="text-gray-400 text-sm">{t("footer.description")}</p>
            </div>

            <Link to="/store" className="link link-hover">
              {t("footer.links.store")}
            </Link>
            {isAuthenticated ? (
              <Link to="/my-account" className="link link-hover">
                {t("footer.links.about")}
              </Link>
            ) : (
              <Link to="/login" className="link link-hover">
                {t("auth.login")}
              </Link>
            )}
          </div>

          <div className="border-t border-gray-800 pt-6 text-center text-gray-400">
            <p className="text-sm">&copy; {new Date().getFullYear()} Narzin. All rights reserved.</p>
          </div>
        </div>
      </footer>
  );
};

export default Footer;
