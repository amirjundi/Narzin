import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import "./index.css";
import "./i18n.js";
import App from "./App.jsx";
import { Provider } from "react-redux";
import { store } from "./Store/store.js";
import { PrimeReactProvider } from "primereact/api";

createRoot(document.getElementById("root")).render(
  <StrictMode>
    <Provider store={store}>
      <PrimeReactProvider>
        <App />
      </PrimeReactProvider>
    </Provider>
  </StrictMode>
);
