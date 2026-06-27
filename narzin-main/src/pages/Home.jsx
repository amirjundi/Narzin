import React, { useEffect, useState } from "react";
import Banners from "../components/pages/home/Banners";
import Categories from "../components/pages/home/Categories";
import ProductsSection from "../components/pages/home/ProductsSection";
import { useTranslation } from "react-i18next";
import { TrendingUp, Shield, Truck, Users } from "lucide-react";
const Home = ({ categories, products }) => {
  const { t } = useTranslation();
  const [scrollY, setScrollY] = useState(0);

  const features = [
    { icon: Truck, title: "Free Shipping", desc: "Orders $50+" },
    { icon: Shield, title: "Secure Pay", desc: "100% Protected" },
    { icon: Users, title: "24/7 Support", desc: "Always Here" },
    { icon: TrendingUp, title: "Best Price", desc: "Guaranteed" },
  ];

  // Handle scroll for parallax effects
  useEffect(() => {
    const handleScroll = () => setScrollY(window.scrollY);
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  return (
    <div>
      <Banners />

      <Categories data={categories} />
      <ProductsSection data={products} title={t("home.best_sellers")} />
      <ProductsSection data={products} title={t("home.recently_added")} />
      <ProductsSection data={products} title={t("home.for_you")} />
      {/* Features Section - Compact */}
      <section className="py-12 bg-white">
        <div className="container mx-auto px-4">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            {features.map((feature, index) => (
              <div
                key={index}
                className="text-center group cursor-pointer transform hover:scale-105 transition-all duration-300"
              >
                <div className="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:from-blue-500 group-hover:to-blue-600 transition-all duration-300">
                  <feature.icon className="w-6 h-6 text-blue-600 group-hover:text-white transition-colors" />
                </div>
                <h3 className="font-semibold text-sm mb-1 text-gray-800">
                  {feature.title}
                </h3>
                <p className="text-xs text-gray-600">{feature.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
};

export default Home;
