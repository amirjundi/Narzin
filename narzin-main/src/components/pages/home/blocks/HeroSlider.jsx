import React, { useEffect, useRef, useState } from "react";
import { SmartLink } from "./blockLink";

const HeroSlider = ({ content }) => {
  const slides = content?.slides || [];
  const trackRef = useRef(null);
  const pausedRef = useRef(false);
  const [active, setActive] = useState(0);

  const goTo = (index) => {
    const track = trackRef.current;
    if (!track || !track.children[index]) return;
    track.children[index].scrollIntoView({
      behavior: "smooth",
      block: "nearest",
      inline: "start",
    });
    setActive(index);
  };

  useEffect(() => {
    if (slides.length < 2) return undefined;
    const timer = setInterval(() => {
      if (pausedRef.current) return;
      setActive((current) => {
        const next = (current + 1) % slides.length;
        const track = trackRef.current;
        track?.children[next]?.scrollIntoView({
          behavior: "smooth",
          block: "nearest",
          inline: "start",
        });
        return next;
      });
    }, 4000);
    return () => clearInterval(timer);
  }, [slides.length]);

  if (slides.length === 0) return null;

  return (
    <section
      className="relative"
      onPointerEnter={() => (pausedRef.current = true)}
      onPointerLeave={() => (pausedRef.current = false)}
      onTouchStart={() => (pausedRef.current = true)}
    >
      <div
        ref={trackRef}
        className="flex overflow-x-auto snap-x snap-mandatory scroll-smooth"
        style={{ scrollbarWidth: "none" }}
      >
        {slides.map((slide, index) => (
          <SmartLink
            key={index}
            link={slide.link}
            className="relative w-full flex-shrink-0 snap-start"
          >
            <img
              src={slide.image}
              alt={slide.title || ""}
              loading={index === 0 ? "eager" : "lazy"}
              className="w-full h-52 sm:h-72 md:h-96 object-cover"
            />
            {(slide.title || slide.subtitle) && (
              <div className="absolute inset-0 bg-gradient-to-t from-narzin-navy/70 via-transparent to-transparent flex flex-col justify-end items-start p-4 sm:p-8">
                {slide.title && (
                  <h2 className="text-white text-xl sm:text-3xl md:text-4xl font-bold drop-shadow">
                    {slide.title}
                  </h2>
                )}
                {slide.subtitle && (
                  <p className="text-narzin-sand text-sm sm:text-lg mt-1 drop-shadow">
                    {slide.subtitle}
                  </p>
                )}
              </div>
            )}
          </SmartLink>
        ))}
      </div>

      {slides.length > 1 && (
        <div className="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
          {slides.map((_, index) => (
            <button
              key={index}
              type="button"
              aria-label={`go to slide ${index + 1}`}
              onClick={() => goTo(index)}
              className={`h-1.5 rounded-full transition-all ${
                index === active ? "w-5 bg-white" : "w-1.5 bg-white/50"
              }`}
            />
          ))}
        </div>
      )}
    </section>
  );
};

export default HeroSlider;
