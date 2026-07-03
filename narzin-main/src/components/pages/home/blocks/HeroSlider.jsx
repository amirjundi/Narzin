import React, { useEffect, useRef, useState } from "react";
import { SmartLink } from "./blockLink";

const SCROLL_SYNC_DEBOUNCE_MS = 150;
const TOUCH_RESUME_DELAY_MS = 3000;

const HeroSlider = ({ content }) => {
  const slides = content?.slides || [];
  const trackRef = useRef(null);
  const pausedRef = useRef(false);
  const touchResumeTimerRef = useRef(null);
  const scrollDebounceRef = useRef(null);
  const [active, setActive] = useState(0);

  const scrollToIndex = (index) => {
    const track = trackRef.current;
    if (!track || !track.children[index] || typeof track.scrollTo !== "function") return;
    track.scrollTo({
      left: track.children[index].offsetLeft,
      behavior: "smooth",
    });
  };

  const goTo = (index) => {
    scrollToIndex(index);
    setActive(index);
  };

  // Auto-advance, always stepping from whatever slide is currently synced
  // as active (manual swipes included), never a stale closed-over index.
  useEffect(() => {
    if (slides.length < 2) return undefined;
    const timer = setInterval(() => {
      if (pausedRef.current) return;
      setActive((current) => {
        const next = (current + 1) % slides.length;
        scrollToIndex(next);
        return next;
      });
    }, 4000);
    return () => clearInterval(timer);
  }, [slides.length]);

  // Keep `active` in sync with manual swipes/scrolls on the track itself.
  useEffect(() => {
    const track = trackRef.current;
    if (!track || slides.length < 2) return undefined;

    const handleScroll = () => {
      if (scrollDebounceRef.current) clearTimeout(scrollDebounceRef.current);
      scrollDebounceRef.current = setTimeout(() => {
        const width = track.clientWidth || 1;
        const nearest = Math.round(Math.abs(track.scrollLeft) / width);
        const clamped = Math.min(Math.max(nearest, 0), slides.length - 1);
        setActive(clamped);
      }, SCROLL_SYNC_DEBOUNCE_MS);
    };

    track.addEventListener("scroll", handleScroll);
    return () => {
      track.removeEventListener("scroll", handleScroll);
      if (scrollDebounceRef.current) clearTimeout(scrollDebounceRef.current);
    };
  }, [slides.length]);

  useEffect(() => {
    return () => {
      if (touchResumeTimerRef.current) clearTimeout(touchResumeTimerRef.current);
    };
  }, []);

  if (slides.length === 0) return null;

  const handleTouchStart = () => {
    pausedRef.current = true;
    if (touchResumeTimerRef.current) clearTimeout(touchResumeTimerRef.current);
  };

  const handleTouchEnd = () => {
    if (touchResumeTimerRef.current) clearTimeout(touchResumeTimerRef.current);
    touchResumeTimerRef.current = setTimeout(() => {
      pausedRef.current = false;
    }, TOUCH_RESUME_DELAY_MS);
  };

  return (
    <section
      className="relative"
      onPointerEnter={() => (pausedRef.current = true)}
      onPointerLeave={() => (pausedRef.current = false)}
      onTouchStart={handleTouchStart}
      onTouchEnd={handleTouchEnd}
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
