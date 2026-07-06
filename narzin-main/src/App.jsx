import "./App.css";
import AfterNav from "./components/includes/AfterNav";
import Navbar from "./components/includes/Navbar";
import "primereact/resources/themes/lara-light-cyan/theme.css";
import Home from "./pages/Home";
import { BrowserRouter as Router, Route, Routes, Link } from "react-router-dom";
import Layout from "./components/Layout";
import Shop from "./pages/Shop";
import ProductPage from "./pages/ProductPage";
import Checkout from "./pages/Checkout";
import { useTranslation } from "react-i18next";
import { useEffect } from "react";
import Card from "./pages/Card";
import OrderConfirmation from "./pages/OrderConfirmation";
import MyAccountLayout from "./pages/MyAccountLayout";
import SignIn from "./pages/Signin";
import SignUp from "./pages/Signup";
import { useDispatch, useSelector } from "react-redux";
import { fetchCategories } from "./Store/slices/CategorySlice";
import ShowToast from "./components/ShowToast";
import { fetchProducts } from "./Store/slices/ProductSlice";
import { verifyToken } from "./Store/slices/Auth/AuthSlice";
import { fetchPublicSettings } from "./Store/slices/SettingsSlice";
import Return from "./pages/Return";
import Privacy from "./pages/Privacy";
import PaymentCallback from "./pages/PaymentCallback";
import { fetchHome } from "./Store/slices/HomeSlice";

function App() {
  const { t, i18n } = useTranslation();

  useEffect(() => {
    document.documentElement.dir = i18n.dir();
    document.documentElement.lang = i18n.language;
  }, [i18n.language]);

  // Fetching the data from the API
  const dispatch = useDispatch();
  const {
    items: categories,
    CategoryStatus,
    CategoryError,
  } = useSelector((state) => state.categories);

  const {
    items: products,
    ProductStatus,
    ProductError,
  } = useSelector((state) => state.products);

  useEffect(() => {
    dispatch(verifyToken());
    dispatch(fetchCategories());
    dispatch(fetchProducts());
    dispatch(fetchPublicSettings());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchHome(i18n.language));
  }, [dispatch, i18n.language]);

  useEffect(() => {
    if (CategoryStatus === "failed") {
      ShowToast(CategoryError + " In Categories", "error");
    }
    if (ProductStatus === "failed") {
      ShowToast(ProductError + " In Products", "error");
    }
  }, [
    CategoryStatus,
    CategoryError,
    ProductStatus,
    ProductError,
  ]);


  return (
    <>
      <Router>
        <Routes>
          <Route
            path="/"
            element={
              <Layout data={categories.data} />
            }
          >
            <Route
              index
              element={
                <Home products={products.data} categories={categories.data} />
              }
            />
            <Route path="store" element={<Shop />} />
            <Route path="product/:id" element={<ProductPage />} />
            <Route path="cart" element={<Card />} />
            <Route path="checkout" element={<Checkout />} />
            <Route path="/thank-you" element={<PaymentCallback />} />

            <Route path="order-confirmation" element={<OrderConfirmation />} />
            <Route path="my-account" element={<MyAccountLayout />} />
            <Route path="signin" element={<SignIn />} />
            <Route path="signup" element={<SignUp />} />
            <Route path="return-policy" element={<Return />} />
            <Route path="privacy-policy" element={<Privacy />} />
          </Route>
        </Routes>
      </Router>
    </>
  );
}

export default App;
