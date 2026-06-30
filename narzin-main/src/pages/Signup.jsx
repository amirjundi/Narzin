import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useDispatch, useSelector } from 'react-redux';
import { register, clearRegistrationError, clearRegistrationState } from '../Store/slices/Auth/RegistrationSlice';
import {
  Mail,
  Lock,
  EyeOff,
  Eye,
  User,
  Phone,
  CheckCircle,
  XCircle,
  AlertCircle,
  Loader
} from 'lucide-react';
import { useTranslation } from 'react-i18next';

const SignUp = () => {
  const navigate = useNavigate();

  const isAuthenticatedState = useSelector((state) => state.auth.isAuthenticated);

  // Redirect away from signup if already authenticated. Must run in an effect,
  // not during render, otherwise navigate() fires on every render and loops.
  useEffect(() => {
    if (isAuthenticatedState) {
      navigate("/my-account", { replace: true });
    }
  }, [isAuthenticatedState, navigate]);

  const dispatch = useDispatch();
  const { loading, success, error, fieldErrors, message } = useSelector((state) => state.registration);
  const [animateError, setAnimateError] = useState(false);

  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    password: '',
    confirmPassword: ''
  });
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [agreeToTerms, setAgreeToTerms] = useState(false);
  const [formErrors, setFormErrors] = useState({
    firstName: false,
    lastName: false,
    email: false,
    password: false,
    confirmPassword: false
  });

  const {t } = useTranslation();

  // Handle success message and redirection
  useEffect(() => {
    if (success) {
      // Show success message for a few seconds then redirect to login
      const timer = setTimeout(() => {
        dispatch(clearRegistrationState());
        navigate('/signin');
      }, 3000);
      
      return () => clearTimeout(timer);
    }
  }, [success, navigate, dispatch]);

  // Clean up when component unmounts
  useEffect(() => {
    return () => {
      dispatch(clearRegistrationState());
    };
  }, [dispatch]);

  // Handle error animation and field highlighting
  useEffect(() => {
    if (error) {
      // Set form field errors based on fieldErrors from state
      const newFormErrors = { ...formErrors };
      Object.keys(fieldErrors).forEach(field => {
        if (field === 'email') {
          newFormErrors.email = true;
        } else if (field === 'password') {
          newFormErrors.password = true;
        } else if (field === 'name') {
          newFormErrors.firstName = true;
          newFormErrors.lastName = true;
        }
      });
      
      setFormErrors(newFormErrors);
      
      // Trigger error animation
      setAnimateError(true);
      setTimeout(() => setAnimateError(false), 500);
    }
  }, [error, fieldErrors]);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });

    // Clear field error when typing
    if (formErrors[e.target.name]) {
      setFormErrors({
        ...formErrors,
        [e.target.name]: false
      });
    }

    // Clear global error when user starts typing again
    if (error) {
      dispatch(clearRegistrationError());
    }
  };

  const validateForm = () => {
    const newFormErrors = {
      firstName: !formData.firstName,
      lastName: !formData.lastName,
      email: !formData.email || !formData.email.includes('@'),
      password: !formData.password || formData.password.length < 8,
      confirmPassword: formData.password !== formData.confirmPassword
    };
    
    setFormErrors(newFormErrors);
    
    return !Object.values(newFormErrors).some(error => error);
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
      setAnimateError(true);
      setTimeout(() => setAnimateError(false), 500);
      return;
    }
    
    if (!agreeToTerms) {
      return;
    }
    
    dispatch(register(formData));
  };

  // Password validation rules
  const passwordRules = [
    {
      text: t('auth.password_validation_password_length'),
      valid: formData.password.length >= 8
    },
    {
      text: t('auth.password_validation_password_uppercase'),
      valid: /[A-Z]/.test(formData.password)
    },
    {
      text: t('auth.password_validation_password_number'),
      valid: /\d/.test(formData.password)
    },
    {
      text: t('auth.password_validation_password_special_character'),
      valid: /[!@#$%^&*]/.test(formData.password)
    }
  ];

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className={`max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg ${animateError ? 'animate-shake' : ''}`}>
        {/* Header */}
        <div className="text-center">
          <h2 className="text-3xl font-bold text-gray-900">
            {t('auth.register')}
          </h2>
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
                  {error}
                </h3>
              </div>
            </div>
          </div>
        )}

        {/* Success Alert */}
        {success && (
          <div className="rounded-md bg-green-50 p-4 border border-green-200">
            <div className="flex">
              <div className="flex-shrink-0">
                <CheckCircle className="h-5 w-5 text-green-400" aria-hidden="true" />
              </div>
              <div className="ml-3">
                <h3 className="text-sm font-medium text-green-800">
                  {message}
                </h3>
                <p className="mt-2 text-sm text-green-700">
                  Redirecting you to the login page...
                </p>
              </div>
            </div>
          </div>
        )}

        {/* Form */}
        <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
          <div className="space-y-4">
            {/* Name */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label htmlFor="firstName" className="block text-sm font-medium text-gray-700 mb-2">
                  {t('auth.first_name')}
                </label>
                <div className="relative">
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <User className={`h-5 w-5 ${formErrors.firstName ? 'text-red-400' : 'text-gray-400'}`} />
                  </div>
                  <input
                    id="firstName"
                    name="firstName"
                    type="text"
                    required
                    value={formData.firstName}
                    onChange={handleChange}
                    className={`appearance-none block w-full pl-10 px-3 py-2 border ${
                      formErrors.firstName ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-[#3084C2] focus:border-[#3084C2]'
                    } rounded-lg placeholder-gray-400 focus:outline-none`}
                    placeholder="First name"
                    disabled={loading || success}
                  />
                  {formErrors.firstName && (
                    <div className="absolute inset-y-0 right-0 pr-3 flex items-center">
                      <AlertCircle className="h-5 w-5 text-red-500" />
                    </div>
                  )}
                </div>
              </div>
              <div>
                <label htmlFor="lastName" className="block text-sm font-medium text-gray-700 mb-2">
                  {t('auth.last_name')}
                </label>
                <div className="relative">
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <User className={`h-5 w-5 ${formErrors.lastName ? 'text-red-400' : 'text-gray-400'}`} />
                  </div>
                  <input
                    id="lastName"
                    name="lastName"
                    type="text"
                    required
                    value={formData.lastName}
                    onChange={handleChange}
                    className={`appearance-none block w-full pl-10 px-3 py-2 border ${
                      formErrors.lastName ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-[#3084C2] focus:border-[#3084C2]'
                    } rounded-lg placeholder-gray-400 focus:outline-none`}
                    placeholder="Last name"
                    disabled={loading || success}
                  />
                  {formErrors.lastName && (
                    <div className="absolute inset-y-0 right-0 pr-3 flex items-center">
                      <AlertCircle className="h-5 w-5 text-red-500" />
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* Email */}
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                {t('auth.email')}
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Mail className={`h-5 w-5 ${formErrors.email ? 'text-red-400' : 'text-gray-400'}`} />
                </div>
                <input
                  id="email"
                  name="email"
                  type="email"
                  autoComplete="email"
                  required
                  value={formData.email}
                  onChange={handleChange}
                  className={`appearance-none block w-full pl-10 px-3 py-2 border ${
                    formErrors.email ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-[#3084C2] focus:border-[#3084C2]'
                  } rounded-lg placeholder-gray-400 focus:outline-none`}
                  placeholder="Enter your email"
                  disabled={loading || success}
                />
                {formErrors.email && (
                  <div className="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <AlertCircle className="h-5 w-5 text-red-500" />
                  </div>
                )}
              </div>
              {fieldErrors.email && (
                <p className="mt-1 text-sm text-red-600">{fieldErrors.email}</p>
              )}
            </div>



            {/* Password */}
            <div>
              <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                {t('auth.password')}
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Lock className={`h-5 w-5 ${formErrors.password ? 'text-red-400' : 'text-gray-400'}`} />
                </div>
                <input
                  id="password"
                  name="password"
                  type={showPassword ? "text" : "password"}
                  required
                  value={formData.password}
                  onChange={handleChange}
                  className={`appearance-none block w-full pl-10 pr-10 px-3 py-2 border ${
                    formErrors.password ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-[#3084C2] focus:border-[#3084C2]'
                  } rounded-lg placeholder-gray-400 focus:outline-none`}
                  placeholder="Create a password"
                  disabled={loading || success}
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute inset-y-0 right-0 pr-3 flex items-center"
                  disabled={loading || success}
                >
                  {showPassword ? (
                    <EyeOff className={`h-5 w-5 ${formErrors.password ? 'text-red-400' : 'text-gray-400'}`} />
                  ) : (
                    <Eye className={`h-5 w-5 ${formErrors.password ? 'text-red-400' : 'text-gray-400'}`} />
                  )}
                </button>
              </div>
              {/* Password validation rules */}
              <div className="mt-2 space-y-2">
                {passwordRules.map((rule, index) => (
                  <div key={index} className="flex items-center text-sm">
                    <CheckCircle 
                      className={`h-4 w-4 mr-2 ${
                        rule.valid ? 'text-green-500' : 'text-gray-300'
                      }`} 
                    />
                    <span className={rule.valid ? 'text-green-600' : 'text-gray-500'}>
                      {rule.text}
                    </span>
                  </div>
                ))}
              </div>
            </div>

            {/* Confirm Password */}
            <div>
              <label htmlFor="confirmPassword" className="block text-sm font-medium text-gray-700 mb-2">
                {t('auth.confirm_password')}
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Lock className={`h-5 w-5 ${formErrors.confirmPassword ? 'text-red-400' : 'text-gray-400'}`} />
                </div>
                <input
                  id="confirmPassword"
                  name="confirmPassword"
                  type={showConfirmPassword ? "text" : "password"}
                  required
                  value={formData.confirmPassword}
                  onChange={handleChange}
                  className={`appearance-none block w-full pl-10 pr-10 px-3 py-2 border ${
                    formErrors.confirmPassword ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-[#3084C2] focus:border-[#3084C2]'
                  } rounded-lg placeholder-gray-400 focus:outline-none`}
                  placeholder="Confirm your password"
                  disabled={loading || success}
                />
                <button
                  type="button"
                  onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                  className="absolute inset-y-0 right-0 pr-3 flex items-center"
                  disabled={loading || success}
                >
                  {showConfirmPassword ? (
                    <EyeOff className={`h-5 w-5 ${formErrors.confirmPassword ? 'text-red-400' : 'text-gray-400'}`} />
                  ) : (
                    <Eye className={`h-5 w-5 ${formErrors.confirmPassword ? 'text-red-400' : 'text-gray-400'}`} />
                  )}
                </button>
              </div>
              {/* Password match indicator */}
              {formData.password && formData.confirmPassword && (
                <div className="mt-2 flex items-center text-sm">
                  <CheckCircle 
                    className={`h-4 w-4 mr-2 ${
                      formData.password === formData.confirmPassword
                        ? 'text-green-500'
                        : 'text-red-500'
                    }`} 
                  />
                  <span className={
                    formData.password === formData.confirmPassword
                      ? 'text-green-600'
                      : 'text-red-600'
                  }>
                    {formData.password === formData.confirmPassword
                      ? 'Passwords match'
                      : 'Passwords do not match'}
                  </span>
                </div>
              )}
            </div>

            {/* Terms and Conditions */}
            <div className="flex items-center">
              <input
                id="terms"
                name="terms"
                type="checkbox"
                checked={agreeToTerms}
                onChange={(e) => setAgreeToTerms(e.target.checked)}
                className="h-4 w-4 text-[#3084C2] focus:ring-[#3084C2] border-gray-300 rounded"
                disabled={loading || success}
              />
              <label htmlFor="terms" className="ml-2 block text-sm text-gray-700">
                {t('auth.agree_terms')}{' '}
                <Link to="/terms" className="text-[#3084C2] hover:text-[#1a5c94]">
                  Terms and Conditions
                </Link>
                {' '}& {' '}
                <Link to="/privacy" className="text-[#3084C2] hover:text-[#1a5c94]">
                  Privacy Policy
                </Link>
              </label>
            </div>
          </div>

          {/* Submit button */}
          <button
            type="submit"
            disabled={!agreeToTerms || loading || success}
            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-[#3084C2] hover:bg-[#1a5c94] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#3084C2] disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? (
              <>
                <Loader className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" />
                Creating Account...
              </>
            ) : (
              'Create Account'
            )}
          </button>

          {/* Social signup */}
          <div>
            <div className="relative">
              <div className="absolute inset-0 flex items-center">
                <div className="w-full border-t border-gray-300" />
              </div>
              <div className="relative flex justify-center text-sm">
                <span className="px-2 bg-white text-gray-500">
                  Or continue with
                </span>
              </div>
            </div>
          </div>
        </form>

        {/* Sign in link */}
        <p className="mt-4 text-center text-sm text-gray-600">
          Already have an account?{' '}
          <Link to="/signin" className="font-medium text-[#3084C2] hover:text-[#1a5c94]">
            Sign in instead
          </Link>
        </p>
      </div>
    </div>
  );
};

export default SignUp;