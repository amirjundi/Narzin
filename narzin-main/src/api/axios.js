import axios from "axios";

const API_URL = import.meta.env.VITE_API_URL || "http://localhost:8000/api/";

// Sanctum's CSRF cookie endpoint lives at the application root, not under /api.
const APP_ROOT = API_URL.replace(/\/api\/?$/, "");

const api = axios.create({
  baseURL: API_URL,
  // Send/receive the httpOnly session + XSRF cookies (Sanctum SPA auth).
  // The auth token is no longer kept in JS-readable storage.
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

// Call once before login (and any state-changing request after a cold load)
// so the browser receives the XSRF-TOKEN cookie that axios echoes back as the
// X-XSRF-TOKEN header on subsequent requests.
export const getCsrfCookie = () =>
  axios.get(`${APP_ROOT}/sanctum/csrf-cookie`, { withCredentials: true });

// Handle expired/invalid session globally.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Session expired or invalid — drop the local auth indicator.
      localStorage.removeItem("user");
      sessionStorage.removeItem("user");
    }
    return Promise.reject(error);
  }
);

export default api;
