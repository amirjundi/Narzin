import "@testing-library/jest-dom/vitest";

// jsdom has no scrollIntoView; hero/rail carousels call it for navigation.
if (!Element.prototype.scrollIntoView) {
  Element.prototype.scrollIntoView = () => {};
}
