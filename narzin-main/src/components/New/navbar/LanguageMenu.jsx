import { Menu } from "@headlessui/react";
import { Globe, Check } from "lucide-react";
import { useTranslation } from "react-i18next";

const LANGS = [
  { code: "ar", label: "العربية", flag: "🇸🇦" },
  { code: "du", label: "Deutsch", flag: "🇩🇪" },
];

const LanguageMenu = () => {
  const { i18n } = useTranslation();

  const changeLanguage = (lng) => {
    i18n.changeLanguage(lng);
    document.documentElement.dir = i18n.dir();
    document.documentElement.lang = lng;
  };

  return (
    <Menu as="div" className="relative">
      <Menu.Button
        aria-label="Language"
        className="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
      >
        <Globe className="w-5 h-5" />
      </Menu.Button>
      <Menu.Items className="absolute end-0 mt-2 w-44 origin-top-end rounded-xl bg-white shadow-lg ring-1 ring-gray-200/70 focus:outline-none z-50 p-1.5">
        {LANGS.map((lng) => (
          <Menu.Item key={lng.code}>
            {({ active }) => (
              <button
                onClick={() => changeLanguage(lng.code)}
                className={`flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm ${
                  active ? "bg-blue-50 text-blue-700" : "text-gray-700"
                }`}
              >
                <span className="flex items-center gap-2">
                  <span className="text-base">{lng.flag}</span>
                  {lng.label}
                </span>
                {i18n.language === lng.code && <Check className="w-4 h-4" />}
              </button>
            )}
          </Menu.Item>
        ))}
        <div className="mt-1 border-t border-gray-100 px-3 py-2 text-xs text-gray-500">
          Currency: EUR (€)
        </div>
      </Menu.Items>
    </Menu>
  );
};

export default LanguageMenu;
