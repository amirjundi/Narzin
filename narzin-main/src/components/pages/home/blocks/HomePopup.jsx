import React, { useEffect, useState } from "react";
import { useSelector } from "react-redux";
import { X } from "lucide-react";
import { selectLayoutBlocks } from "../../../../Store/slices/HomeSlice";
import { shouldShowPopup, markPopupSeen } from "./popupFrequency";
import { SmartLink } from "./blockLink";

const HomePopup = () => {
  const block = useSelector(selectLayoutBlocks).find((b) => b.type === "popup");
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    if (!block) return undefined;
    const content = { ...block.content, id: block.id };
    if (!shouldShowPopup(content)) return undefined;
    const timer = setTimeout(() => {
      markPopupSeen(content);
      setVisible(true);
    }, (Number(block.content.delay_seconds) || 0) * 1000);
    return () => clearTimeout(timer);
  }, [block]);

  if (!block || !visible) return null;
  const { image, title, text, button_label, link } = block.content;

  return (
    <div className="fixed inset-0 z-[60] flex items-end sm:items-center justify-center">
      <button
        type="button"
        aria-label="dismiss popup"
        className="absolute inset-0 bg-narzin-navy/50"
        onClick={() => setVisible(false)}
      />
      <div className="relative bg-white w-full sm:w-96 rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden">
        <button
          type="button"
          aria-label="close"
          onClick={() => setVisible(false)}
          className="absolute top-2 end-2 z-10 p-1.5 rounded-full bg-white/80 text-narzin-navy hover:bg-white"
        >
          <X className="w-4 h-4" />
        </button>
        {image && <img src={image} alt="" className="w-full h-44 sm:h-52 object-cover" />}
        <div className="p-5 text-center">
          <h3 className="text-lg font-bold text-narzin-navy">{title}</h3>
          {text && <p className="mt-1.5 text-sm text-gray-600">{text}</p>}
          {button_label && (
            <SmartLink
              link={link}
              className="inline-block mt-4 px-6 py-2.5 rounded-full bg-narzin-navy text-white text-sm font-semibold hover:bg-narzin-navy/90"
              onClick={() => setVisible(false)}
            >
              {button_label}
            </SmartLink>
          )}
        </div>
      </div>
    </div>
  );
};

export default HomePopup;
