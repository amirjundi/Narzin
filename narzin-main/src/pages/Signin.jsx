import React, { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { login, clearError } from "../Store/slices/Auth/AuthSlice";
import {
  Mail,
  Lock,
  EyeOff,
  Eye,
  AlertCircle,
  Loader,
  XCircle,
} from "lucide-react";
import { useTranslation } from "react-i18next";

// Optional: import the shake animation CSS if you're using it
// import './auth-styles.css';

const SignIn = () => {

  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { loading, error, isAuthenticated } = useSelector(
    (state) => state.auth
  );
  const { t } = useTranslation();
  const [formData, setFormData] = useState({
    email: "",
    password: "",
  });
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);
  const [fieldErrors, setFieldErrors] = useState({
    email: false,
    password: false,
  });
  const [animateError, setAnimateError] = useState(false);

  // Redirect if already authenticated
  useEffect(() => {
    if (isAuthenticated) {
      navigate("/my-account");
    }
  }, [isAuthenticated]);

  // Clear errors when component unmounts
  useEffect(() => {
    return () => {
      if (error) {
        dispatch(clearError());
      }
    };
  }, [dispatch, error]);

  // Handle error animation and field highlighting
  useEffect(() => {
    if (error) {
      // Check if error message contains keywords to highlight specific fields
      const newFieldErrors = {
        email:
          error.toLowerCase().includes("email") ||
          error.toLowerCase().includes("credentials"),
        password:
          error.toLowerCase().includes("password") ||
          error.toLowerCase().includes("credentials"),
      };

      setFieldErrors(newFieldErrors);

      // Trigger error animation
      setAnimateError(true);
      setTimeout(() => setAnimateError(false), 500);
    } else {
      setFieldErrors({ email: false, password: false });
    }
  }, [error]);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });

    // Clear field error when typing
    if (fieldErrors[e.target.name]) {
      setFieldErrors({
        ...fieldErrors,
        [e.target.name]: false,
      });
    }

    // Clear global error when user starts typing again
    if (error) {
      dispatch(clearError());
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    // Basic validation
    let hasErrors = false;
    const newFieldErrors = { email: false, password: false };

    if (!formData.email || !formData.email.includes("@")) {
      newFieldErrors.email = true;
      hasErrors = true;
    }

    if (!formData.password || formData.password.length < 6) {
      newFieldErrors.password = true;
      hasErrors = true;
    }

    if (hasErrors) {
      setFieldErrors(newFieldErrors);
      setAnimateError(true);
      setTimeout(() => setAnimateError(false), 500);
      return;
    }

    dispatch(
      login({
        email: formData.email,
        password: formData.password,
        rememberMe,
      })
    );
  };

  // Get a user-friendly error message
  const getErrorMessage = (errorMsg) => {
    if (!errorMsg) return "";

    // Check for specific error messages and provide friendly alternatives
    if (errorMsg.includes("credentials") || errorMsg.includes("422")) {
      return "Invalid email or password. Please check your credentials and try again.";
    }

    if (errorMsg.includes("network")) {
      return "Network error. Please check your internet connection and try again.";
    }

    return errorMsg;
  };

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div
        className={`max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg ${
          animateError ? "animate-shake" : ""
        }`}
      >
        {/* Header */}
        <div className="text-center">
          <h2 className="text-3xl font-bold text-gray-900">
            {t("auth.welcome_back")}
          </h2>
          <p className="mt-2 text-gray-600">
            {t("auth.login")}
          </p>
        </div>

        {/* Error Alert */}
        {error && (
          <div className="rounded-md bg-red-50 p-4 border border-red-200">
            <div className="flex">
              <div className="flex-shrink-0">
                <XCircle className="h-5 w-5 text-red-400" aria-hidden="true" />
              </div>
              <div className="ml-3">
                <h3 className="text-sm font-medium text-red-800">
                  {getErrorMessage(error)}
                </h3>
              </div>
            </div>
          </div>
        )}

        {/* Form */}
        <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
          <div className="space-y-4">
            {/* Email */}
            <div>
              <label
                htmlFor="email"
                className="block text-sm font-medium text-gray-700 mb-2"
              >
                {t("auth.email")}
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Mail
                    className={`h-5 w-5 ${
                      fieldErrors.email ? "text-red-400" : "text-gray-400"
                    }`}
                  />
                </div>
                <input
                  id="email"
                  name="email"
                  type="email"
                  autoComplete="email"
                  required
                  value={formData.email}
                  onChange={handleChange}
                  className={`appearance-none block w-full pl-10 pr-10 px-3 py-2 border ${
                    fieldErrors.email
                      ? "border-red-300 focus:ring-red-500 focus:border-red-500"
                      : "border-gray-300 focus:ring-[#3084C2] focus:border-[#3084C2]"
                  } rounded-lg placeholder-gray-400 focus:outline-none transition-colors duration-200`}
                  placeholder="Enter your email"
                  disabled={loading}
                />
                {fieldErrors.email && (
                  <div className="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <AlertCircle className="h-5 w-5 text-red-500" />
                  </div>
                )}
              </div>
              {fieldErrors.email && !error && (
                <p className="mt-1 text-sm text-red-600">
                  Please enter a valid email address
                </p>
              )}
            </div>

            {/* Password */}
            <div>
              <label
                htmlFor="password"
                className="block text-sm font-medium text-gray-700 mb-2"
              >
                {t("auth.password")}
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Lock
                    className={`h-5 w-5 ${
                      fieldErrors.password ? "text-red-400" : "text-gray-400"
                    }`}
                  />
                </div>
                <input
                  id="password"
                  name="password"
                  type={showPassword ? "text" : "password"}
                  autoComplete="current-password"
                  required
                  value={formData.password}
                  onChange={handleChange}
                  className={`appearance-none block w-full pl-10 pr-10 px-3 py-2 border ${
                    fieldErrors.password
                      ? "border-red-300 focus:ring-red-500 focus:border-red-500"
                      : "border-gray-300 focus:ring-[#3084C2] focus:border-[#3084C2]"
                  } rounded-lg placeholder-gray-400 focus:outline-none transition-colors duration-200`}
                  placeholder="Enter your password"
                  disabled={loading}
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute inset-y-0 right-0 pr-3 flex items-center"
                  disabled={loading}
                >
                  {showPassword ? (
                    <EyeOff
                      className={`h-5 w-5 ${
                        fieldErrors.password ? "text-red-400" : "text-gray-400"
                      }`}
                    />
                  ) : (
                    <Eye
                      className={`h-5 w-5 ${
                        fieldErrors.password ? "text-red-400" : "text-gray-400"
                      }`}
                    />
                  )}
                </button>
              </div>
              {fieldErrors.password && !error && (
                <p className="mt-1 text-sm text-red-600">
                  {t("auth.password_validation_password_length")}
                </p>
              )}
            </div>
          </div>

          {/* Remember me & Forgot password */}
          <div className="flex items-center justify-between">
            <div className="flex items-center">
              <input
                id="remember-me"
                name="remember-me"
                type="checkbox"
                checked={rememberMe}
                onChange={(e) => setRememberMe(e.target.checked)}
                className="h-4 w-4 text-[#3084C2] focus:ring-[#3084C2] border-gray-300 rounded"
                disabled={loading}
              />
              <label
                htmlFor="remember-me"
                className="ml-2 block text-sm text-gray-700"
              >
                {t("auth.remmember_me")}
              </label>
            </div>
            <Link
              to="/forgot-password"
              className="text-sm text-[#3084C2] hover:text-[#1a5c94]"
            >
              {t("auth.forgot_password")}
            </Link>
          </div>

          {/* Submit button */}
          <button
            type="submit"
            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-[#3084C2] hover:bg-[#1a5c94] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#3084C2] disabled:opacity-70 disabled:cursor-not-allowed transition-colors duration-200"
            disabled={loading}
          >
            {loading ? (
              <>
                <Loader className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" />
                Signing in...
              </>
            ) : (
              "Sign in"
            )}
          </button>


        </form>

        {/* Sign up link */}
        <p className="mt-4 text-center text-sm text-gray-600">
          {t("auth.dont_have_account")}{" "}
          <Link
            to="/signup"
            className="font-medium text-[#3084C2] hover:text-[#1a5c94]"
          >
            {t("auth.register")}
          </Link>
        </p>
      </div>
    </div>
  );
};

// Add to tailwind.config.js to support the shake animation:
// extend: {
//   keyframes: {
//     shake: {
//       '10%, 90%': { transform: 'translate3d(-1px, 0, 0)' },
//       '20%, 80%': { transform: 'translate3d(2px, 0, 0)' },
//       '30%, 50%, 70%': { transform: 'translate3d(-4px, 0, 0)' },
//       '40%, 60%': { transform: 'translate3d(4px, 0, 0)' },
//     }
//   },
//   animation: {
//     shake: 'shake 0.5s cubic-bezier(.36,.07,.19,.97) both'
//   }
// }

export default SignIn;
