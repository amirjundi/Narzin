import React from "react";
import { Truck, ShieldCheck, Star, RotateCcw, Headphones, Tag } from "lucide-react";
import { SmartLink } from "./blockLink";

const icons = {
  truck: Truck,
  shield: ShieldCheck,
  star: Star,
  returns: RotateCcw,
  support: Headphones,
  tag: Tag,
};

const InfoStrip = ({ content }) => {
  const items = content?.items || [];
  if (items.length === 0) return null;

  return (
    <section className="bg-narzin-sand/10 border-y border-narzin-sand/30">
      <div className="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-2 px-4 py-2.5 max-w-5xl mx-auto">
        {items.map((item, index) => {
          const Icon = icons[item.icon] || Tag;
          return (
            <SmartLink
              key={index}
              link={item.link}
              className="flex items-center gap-2 justify-center text-narzin-navy"
            >
              <Icon className="w-4 h-4 text-narzin-sand flex-shrink-0" />
              <span className="text-[11px] sm:text-xs font-medium truncate">{item.text}</span>
            </SmartLink>
          );
        })}
      </div>
    </section>
  );
};

export default InfoStrip;
