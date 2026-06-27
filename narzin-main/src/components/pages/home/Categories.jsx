import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import Slider from "react-slick";
import PrimaryLink from "../../utils/PrimaryLink";
import CategoryCard from "../../Product/CategoryCard";
import { useTranslation } from "react-i18next";
import { Sparkles } from "lucide-react";

const Categories = ({ data }) => {
  const { t } = useTranslation();
  const [categories, setCategories] = useState([]);

  useEffect(() => {
    if (data) {
      setCategories(data);
      data.forEach((el) => {
        if (el.sub_categories && el.sub_categories.length > 0) {
          el.sub_categories.forEach((subcategory) => {
            setCategories((prevCategories) => [...prevCategories, subcategory]);
          });
        }
      });
    }
  }, [data]);

  var settings = {
    dots: true,
    infinite: false,
    speed: 500,
    slidesToShow: 7, // Back to 10 as requested
    slidesToScroll: 4,
    initialSlide: 0,
    arrows: true,
    responsive: [
      {
        breakpoint: 1400,
        settings: {
          slidesToShow: 6,
          slidesToScroll: 4,
          infinite: false,
          dots: true,
        },
      },
      {
        breakpoint: 1200,
        settings: {
          slidesToShow: 6,
          slidesToScroll: 3,
          infinite: false,
          dots: true,
        },
      },
      {
        breakpoint: 1024,
        settings: {
          slidesToShow: 5,
          slidesToScroll: 3,
          infinite: false,
          dots: true,
        },
      },
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 4,
          slidesToScroll: 2,
          initialSlide: 0,
          dots: true,
        },
      },
      {
        breakpoint: 600,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 2,
          initialSlide: 0,
          dots: false,
        },
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          dots: false,
        },
      },
    ],
  };

  return (
    <section className="py-12 bg-white">
      <div className="container mx-auto px-4">
        <div className="mx-4 overflow-hidden">
          {" "}
          {/* Added overflow-hidden to contain arrows */}
          <div className="flex justify-between items-center mt-10 mb-6 px-2">
            
              <div className="flex items-center gap-3">
            <div className="p-2 bg-blue-50 rounded-lg">
              <Sparkles  className="w-5 h-5 text-[#3084C2]" />
            </div>
            <div>
              <h2 className="text-lg font-bold text-gray-900">
                {t("home.discover")}
              </h2>
            </div>
          </div>

            <PrimaryLink text={t("home.view_all")} route="categories" />
          </div>
          <div className="category-slider relative">
            {" "}
            {/* Added relative positioning */}
            <Slider {...settings}>
              {categories.map((category, index) => (
                <div key={index} className="px-1">
                  {" "}
                  {/* Reduced padding to px-1 */}
                  <Link
                    to={`/store?category_id=${category.id}`}
                    className="block"
                  >
                    <CategoryCard category={category} />
                  </Link>
                </div>
              ))}
            </Slider>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Categories;
