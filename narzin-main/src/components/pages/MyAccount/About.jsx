import React from 'react';
import {
  Store,
  Users,
  Shield,
  Globe,
  Truck,
  Headphones,
  Package,
  CreditCard
} from 'lucide-react';

const About = () => {
  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-2xl font-bold">About Narzin</h2>
      </div>

      {/* Platform Overview */}
      <div className="space-y-8">
        <section>
          <h3 className="text-xl font-semibold mb-4">Welcome to Narzin</h3>
          <p className="text-gray-600 leading-relaxed">
            Narzin is a leading multi-vendor e-commerce platform connecting buyers with trusted sellers worldwide. 
            Our marketplace provides a secure and seamless shopping experience with a wide range of products from 
            verified vendors.
          </p>
        </section>

        {/* Key Features */}
        <section>
          <h3 className="text-xl font-semibold mb-4">Key Features</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="p-4 bg-gray-50 rounded-lg">
              <div className="flex items-start gap-3">
                <Store className="w-6 h-6 text-[#3084C2]" />
                <div>
                  <h4 className="font-medium mb-1">Multi-Vendor Platform</h4>
                  <p className="text-gray-600 text-sm">Shop from multiple verified sellers in one place</p>
                </div>
              </div>
            </div>
            <div className="p-4 bg-gray-50 rounded-lg">
              <div className="flex items-start gap-3">
                <Shield className="w-6 h-6 text-[#3084C2]" />
                <div>
                  <h4 className="font-medium mb-1">Secure Shopping</h4>
                  <p className="text-gray-600 text-sm">Protected payments and verified sellers</p>
                </div>
              </div>
            </div>
            <div className="p-4 bg-gray-50 rounded-lg">
              <div className="flex items-start gap-3">
                <Globe className="w-6 h-6 text-[#3084C2]" />
                <div>
                  <h4 className="font-medium mb-1">Global Marketplace</h4>
                  <p className="text-gray-600 text-sm">Access to international products and sellers</p>
                </div>
              </div>
            </div>
            <div className="p-4 bg-gray-50 rounded-lg">
              <div className="flex items-start gap-3">
                <Headphones className="w-6 h-6 text-[#3084C2]" />
                <div>
                  <h4 className="font-medium mb-1">24/7 Support</h4>
                  <p className="text-gray-600 text-sm">Round-the-clock customer assistance</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* How It Works */}
        <section>
          <h3 className="text-xl font-semibold mb-4">How It Works</h3>
          <div className="space-y-4">
            <div className="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
              <div className="w-8 h-8 bg-[#3084C2] text-white rounded-full flex items-center justify-center flex-shrink-0">
                1
              </div>
              <div>
                <h4 className="font-medium mb-1">Browse and Shop</h4>
                <p className="text-gray-600">Explore products from various verified sellers on our platform</p>
              </div>
            </div>
            <div className="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
              <div className="w-8 h-8 bg-[#3084C2] text-white rounded-full flex items-center justify-center flex-shrink-0">
                2
              </div>
              <div>
                <h4 className="font-medium mb-1">Secure Payment</h4>
                <p className="text-gray-600">Choose from multiple secure payment options</p>
              </div>
            </div>
            <div className="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
              <div className="w-8 h-8 bg-[#3084C2] text-white rounded-full flex items-center justify-center flex-shrink-0">
                3
              </div>
              <div>
                <h4 className="font-medium mb-1">Track Orders</h4>
                <p className="text-gray-600">Monitor your order status and delivery in real-time</p>
              </div>
            </div>
          </div>
        </section>

        {/* Trust & Safety */}
        <section>
          <h3 className="text-xl font-semibold mb-4">Trust & Safety</h3>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="p-4 bg-gray-50 rounded-lg text-center">
              <Shield className="w-8 h-8 text-[#3084C2] mx-auto mb-2" />
              <h4 className="font-medium mb-1">Secure Platform</h4>
              <p className="text-sm text-gray-600">End-to-end encryption for all transactions</p>
            </div>
            <div className="p-4 bg-gray-50 rounded-lg text-center">
              <Users className="w-8 h-8 text-[#3084C2] mx-auto mb-2" />
              <h4 className="font-medium mb-1">Verified Sellers</h4>
              <p className="text-sm text-gray-600">Thoroughly vetted vendors</p>
            </div>
            <div className="p-4 bg-gray-50 rounded-lg text-center">
              <Package className="w-8 h-8 text-[#3084C2] mx-auto mb-2" />
              <h4 className="font-medium mb-1">Buyer Protection</h4>
              <p className="text-sm text-gray-600">Money-back guarantee on eligible purchases</p>
            </div>
          </div>
        </section>

        {/* Contact Information */}
        <section className="bg-gray-50 rounded-lg p-6">
          <h3 className="text-xl font-semibold mb-4">Contact Us</h3>
          <div className="space-y-3">
            <p className="text-gray-600">
              <span className="font-medium">Customer Support:</span> support@narzin.com
            </p>
            <p className="text-gray-600">
              <span className="font-medium">Vendor Support:</span> vendors@narzin.com
            </p>
            <p className="text-gray-600">
              <span className="font-medium">Business Hours:</span> 24/7
            </p>
          </div>
        </section>
      </div>
    </div>
  );
};

export default About;