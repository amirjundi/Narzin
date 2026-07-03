import React from "react";
import { render } from "@testing-library/react";
import { Provider } from "react-redux";
import { configureStore } from "@reduxjs/toolkit";
import { MemoryRouter } from "react-router-dom";
import { I18nextProvider } from "react-i18next";
import i18n from "i18next";
import { initReactI18next } from "react-i18next";

export function makeTestI18n(language = "du") {
  const instance = i18n.createInstance();
  instance.use(initReactI18next).init({
    lng: language,
    fallbackLng: "du",
    resources: { ar: { translation: {} }, du: { translation: {} } },
    interpolation: { escapeValue: false },
  });
  return instance;
}

export function renderWithProviders(
  ui,
  { reducers = {}, preloadedState = {}, language = "du", route = "/" } = {}
) {
  const store = configureStore({
    reducer: { _: (s = {}) => s, ...reducers },
    preloadedState,
  });
  const testI18n = makeTestI18n(language);

  const result = render(
    <Provider store={store}>
      <I18nextProvider i18n={testI18n}>
        <MemoryRouter initialEntries={[route]}>{ui}</MemoryRouter>
      </I18nextProvider>
    </Provider>
  );

  return { ...result, store };
}
