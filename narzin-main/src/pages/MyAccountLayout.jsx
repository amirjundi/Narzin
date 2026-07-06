import React, { useState, useEffect } from 'react';
import {
  User,
  Package,
  RefreshCcw,
  MapPin,
  Heart,
  Wallet,
  Info,
  Store,
  ChevronRight,
  LogOut
} from 'lucide-react';

// Import all components
import MyAccount from '../components/pages/MyAccount/MyAccount';
import Orders from '../components/pages/MyAccount/Orders'; 
import Returns from '../components/pages/MyAccount/Returns'; 
import Addresses from '../components/pages/MyAccount/Addresses'; 
import Wishlist from '../components/pages/MyAccount/Wishlist'; 
import WalletComponent from '../components/pages/MyAccount/WalletComponent'; 
import About from '../components/pages/MyAccount/About';
import VendorSignup from '../components/pages/MyAccount/VendorSignup';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';

export const ACCOUNT_TABS = ["my-account", "orders", "addresses", "wishlist", "wallet", "about", "vendor"];

export function getInitialTab(param) {
  return ACCOUNT_TABS.includes(param) ? param : "my-account";
}

const MyAccountLayout = () => {
  const [searchParams] = useSearchParams();
  const [activeTab, setActiveTab] = useState(getInitialTab(searchParams.get("tab")));
  const tabParam = searchParams.get("tab");
  useEffect(() => {
    setActiveTab(getInitialTab(tabParam));
  }, [tabParam]);
  const {t}  = useTranslation();
  
  
  const navigate = useNavigate();

  // Guard on the single auth slice. Redirect from an effect (not during render)
  // and use the router instead of a hard reload, which previously ping-ponged
  // with the login page and refreshed continuously.
  const { isAuthenticated } = useSelector((state) => state.auth);

  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/signin', { replace: true });
    }
  }, [isAuthenticated, navigate]);

  const tabs = [
    { id: 'my-account', label: t('accountPage.myAccount') , icon: User },
    { id: 'orders', label: t('accountPage.orders') 
      , icon: Package },
    { id: 'addresses', label: t('accountPage.addresses') 
      , icon: MapPin },
    { id: 'wishlist', label: t('accountPage.wishlist') 
      
      , icon: Heart },
    { id: 'wallet', label: t('accountPage.wallet') 
      , icon: Wallet },
    { id: 'about', label: t('accountPage.about') 
      , icon: Info },
    { id: 'vendor', label: t('accountPage.vendor') 
      , icon: Store },
  ];

  const getComponent = () => {
    switch (activeTab) {
      case 'my-account':
        return <MyAccount   />;
      case 'orders':
        return <Orders />;
      case 'addresses':
        return <Addresses />;
      case 'wishlist':
        return <Wishlist />;
      case 'wallet':
        return <WalletComponent />;
      case 'about':
        return <About />;
      case 'vendor':
        return <VendorSignup />;
      default:
        return <MyAccount />;
    }
  };

  return (
    <div className="mt-24 min-h-screen bg-gray-50 py-8">
      <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col md:flex-row gap-8">
          {/* Sidebar */}
          <div className="w-full md:w-64 flex-shrink-0">
            <div className="bg-white rounded-lg shadow-md p-4">

              {/* Navigation */}
              <nav className="space-y-1">
                {tabs.map((tab) => (
                  <button
                    key={tab.id}
                    onClick={() => setActiveTab(tab.id)}
                    className={`w-full flex items-center gap-3 p-3 rounded-lg text-left transition-colors
                      ${
                        activeTab === tab.id
                          ? 'bg-[#3084C2] text-white'
                          : 'text-gray-600 hover:bg-gray-50'
                      }`}
                  >
                    <tab.icon className="w-5 h-5" />
                    <span className="flex-1">{tab.label}</span>
                    <ChevronRight className={`w-5 h-5 ${
                      activeTab === tab.id ? 'opacity-100' : 'opacity-0'
                    }`} />
                  </button>
                ))}

                {/* Logout Button */}
                <button
                  className="w-full flex items-center gap-3 p-3 rounded-lg text-left text-red-500 hover:bg-red-50 transition-colors"
                >
                  <LogOut className="w-5 h-5" />
                  <span>Logout</span>
                </button>
              </nav>
            </div>
          </div>

          {/* Main Content */}
          <div className="flex-1">
            <div className="bg-white rounded-lg shadow-md p-6">
              {getComponent()}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default MyAccountLayout;