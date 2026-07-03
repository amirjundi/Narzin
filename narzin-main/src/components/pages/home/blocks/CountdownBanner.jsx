import React from "react";
import { useCountdown } from "./useCountdown";
import { SmartLink } from "./blockLink";

const pad = (n) => String(n).padStart(2, "0");

const CountdownBanner = ({ content }) => {
  const { days, hours, minutes, seconds, expired } = useCountdown(content?.ends_at);
  if (!content?.ends_at || expired) return null;

  return (
    <SmartLink link={content.link} className="block">
      <section
        className="relative flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-6 px-4 py-4 my-2 overflow-hidden"
        style={{
          backgroundColor: content.bg_color || "#141923",
          color: content.text_color || "#D4AF37",
        }}
      >
        {content.image && (
          <img
            src={content.image}
            alt=""
            loading="lazy"
            className="absolute inset-0 w-full h-full object-cover opacity-25"
          />
        )}
        <p className="relative font-semibold text-sm sm:text-lg">{content.text}</p>
        <p
          className="relative font-mono text-lg sm:text-2xl font-bold tracking-wider"
          style={{ fontVariantNumeric: "tabular-nums" }}
          dir="ltr"
        >
          {pad(days)}:{pad(hours)}:{pad(minutes)}:{pad(seconds)}
        </p>
      </section>
    </SmartLink>
  );
};

export default CountdownBanner;
