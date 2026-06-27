import React, { useState, useEffect } from "react";
import { ArrowRight } from 'lucide-react';

const Banners = ({ isRTL = false }) => {
  const [currentSlide, setCurrentSlide] = useState(0);
  // Auto-rotate hero slides
  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % heroSlides.length);
    }, 4000);
    return () => clearInterval(timer);
  }, []);

  const heroSlides = [
    {
      title: "Summer Flash Sale",
      subtitle: "Up to 70% OFF",
      description: "Limited time offer on selected items",
      image:
        "https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1200&h=600&fit=crop",
      cta: "Shop Sale",
      badge: "Hot Deal",
    },
    {
      title: "Tech Revolution",
      subtitle: "Latest Gadgets",
      description: "Innovation at your fingertips",
      image:
        "https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=1200&h=600&fit=crop",
      cta: "Explore Tech",
      badge: "New",
    },
    {
      title: "Home Makeover",
      subtitle: "Transform Your Space",
      description: "Premium furniture & decor",
      image:
        "https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=1200&h=600&fit=crop",
      cta: "Shop Home",
      badge: "Trending",
    },
  ];

  return (
    <section className="relative h-[70vh] mt-14 flex items-center justify-center overflow-hidden">
      {/* Background Slider */}
      <div className="absolute inset-0">
        {heroSlides.map((slide, index) => (
          <div
            key={index}
            className={`absolute inset-0 transition-opacity duration-1000 ${
              index === currentSlide ? "opacity-100" : "opacity-0"
            }`}
            style={{
              backgroundImage: `linear-gradient(135deg, rgba(48, 132, 194, 0.9), rgba(48, 132, 194, 0.7)), url(${slide.image})`,
              backgroundSize: "cover",
              backgroundPosition: "center",
            }}
          />
        ))}
      </div>

      {/* Professional Banner Content */}
      <div className="relative z-10 container mx-auto px-4 text-center text-white">
        <div className="max-w-2xl mx-auto">
          {/* Badge */}
          <div className="inline-block bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium mb-4 animate-pulse">
            {heroSlides[currentSlide].badge}
          </div>

          <h1 className="text-4xl md:text-5xl font-bold mb-3 leading-tight">
            {heroSlides[currentSlide].title}
          </h1>
          <h2 className="text-2xl md:text-3xl font-light mb-3 text-yellow-300">
            {heroSlides[currentSlide].subtitle}
          </h2>
          <p className="text-lg mb-6 opacity-90">
            {heroSlides[currentSlide].description}
          </p>

          <div className="flex flex-col sm:flex-row gap-3 justify-center">
            <button className="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-lg flex items-center justify-center space-x-2 group">
              <span>{heroSlides[currentSlide].cta}</span>
              <ArrowRight className="w-4 h-4 transform group-hover:translate-x-1 transition-transform" />
            </button>
            <button className="border-2 border-white/50 text-white px-6 py-3 rounded-lg font-semibold hover:bg-white/10 transition-all duration-200">
              Learn More
            </button>
          </div>
        </div>

        {/* Slide Indicators */}

      </div>
              <div className="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex space-x-2">
          {heroSlides.map((_, index) => (
            <button
              key={index}
              onClick={() => setCurrentSlide(index)}
              className={`w-2 h-2 rounded-full transition-all ${
                index === currentSlide ? "bg-white w-6" : "bg-white/50"
              }`}
            />
          ))}
        </div>
    </section>
  );
};

export default Banners;
