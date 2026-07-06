import "@testing-library/jest-dom/vitest";

// jsdom has no scrollIntoView; this shim exists for legacy jsdom compatibility only.
// HeroSlider now uses track.scrollTo, which this shim does not cover — jsdom no-ops are handled in-component.
if (!Element.prototype.scrollIntoView) {
  Element.prototype.scrollIntoView = () => {};
}

// ResizeObserver polyfill for jsdom (required by Headless UI)
if (!global.ResizeObserver) {
  global.ResizeObserver = class ResizeObserver {
    constructor(callback) {
      this.callback = callback;
    }
    observe() {}
    unobserve() {}
    disconnect() {}
  };
}
