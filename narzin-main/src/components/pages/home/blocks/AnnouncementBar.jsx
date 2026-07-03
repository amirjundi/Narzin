import React, { useState } from "react";
import { useSelector } from "react-redux";
import { X } from "lucide-react";
import { selectLayoutBlocks } from "../../../../Store/slices/HomeSlice";
import { SmartLink } from "./blockLink";

const AnnouncementBar = () => {
  const block = useSelector(selectLayoutBlocks).find(
    (b) => b.type === "announcement_bar"
  );
  const dismissKey = block ? `home_announcement_dismissed_${block.id}` : null;
  const [dismissed, setDismissed] = useState(() =>
    dismissKey ? sessionStorage.getItem(dismissKey) === "1" : false
  );

  if (!block || dismissed || (dismissKey && sessionStorage.getItem(dismissKey) === "1")) {
    return null;
  }
  const { text, link, bg_color, text_color } = block.content ?? {};

  const dismiss = () => {
    sessionStorage.setItem(dismissKey, "1");
    setDismissed(true);
  };

  return (
    <div
      data-testid="announcement-bar"
      className="w-full text-center text-xs sm:text-sm py-2 px-8 relative"
      style={{ backgroundColor: bg_color || "#141923", color: text_color || "#C5A880" }}
    >
      <SmartLink link={link} className="inline-block hover:underline">
        {text}
      </SmartLink>
      <button
        type="button"
        aria-label="dismiss"
        onClick={dismiss}
        className="absolute end-2 top-1/2 -translate-y-1/2 opacity-70 hover:opacity-100"
      >
        <X className="w-4 h-4" />
      </button>
    </div>
  );
};

export default AnnouncementBar;
