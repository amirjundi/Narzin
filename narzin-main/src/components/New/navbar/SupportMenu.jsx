import { Popover } from "@headlessui/react";
import { Headphones, MessageCircle } from "lucide-react";
import { useSelector } from "react-redux";
import { useTranslation } from "react-i18next";
import { selectWhatsappNumber, selectSupportHours } from "../../../Store/slices/SettingsSlice";
import { buildWhatsappUrl } from "./waLink";

const SupportMenu = () => {
  const { t } = useTranslation();
  const number = useSelector(selectWhatsappNumber);
  const hours = useSelector(selectSupportHours);
  const waUrl = buildWhatsappUrl(number);

  if (!waUrl) return null;

  return (
    <Popover as="div" className="relative">
      <Popover.Button
        aria-label={t("topbar.support", "Support")}
        className="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
      >
        <Headphones className="w-5 h-5" />
      </Popover.Button>
      <Popover.Panel className="absolute end-0 mt-2 w-64 origin-top-end rounded-xl bg-white shadow-lg ring-1 ring-gray-200/70 focus:outline-none z-50 p-4">
        <p className="text-sm font-semibold text-gray-900 mb-1">
          {t("topbar.customer_support", "Customer Support")}
        </p>
        <p className="text-sm text-gray-700">{number}</p>
        {hours && <p className="text-xs text-gray-500 mt-0.5">{hours}</p>}
        <a
          href={waUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="mt-3 flex items-center justify-center gap-2 w-full py-2 px-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
        >
          <MessageCircle className="w-4 h-4" />
          {t("topbar.chat_whatsapp", "Chat on WhatsApp")}
        </a>
      </Popover.Panel>
    </Popover>
  );
};

export default SupportMenu;
