import React, { useState } from "react";
import { Star } from "lucide-react";
import { useTranslation } from "react-i18next";

const ReviewForm = ({ onSubmit, onCancel, productId }) => {
  const [rating, setRating] = useState(0);
  const [hoverRating, setHoverRating] = useState(0);
  const [review, setReview] = useState("");
  const [error, setError] = useState("");
  const { t } = useTranslation();

  const handleSubmit = (e) => {
    e.preventDefault();
    
    // Validate form
    if (rating === 0) {
      setError("Please select a rating");
      return;
    }
    
    if (review.trim() === "") {
      setError("Please write a review");
      return;
    }
    
    // Submit the review
    onSubmit({
      product_id: productId,
      review: review,
      rating: rating
    });
    
    // Reset form
    setRating(0);
    setReview("");
    setError("");
  };

  return (
    <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow-md border border-gray-200 p-6 space-y-5">
      {error && (
        <div className="bg-red-50 border border-red-200 text-red-700 p-3 rounded-md font-medium">
          {error}
        </div>
      )}
      
      <div>
        <label className="block text-sm font-semibold text-gray-800 mb-2">
          {t('reviews.rating')}
        </label>
        <div className="flex gap-1">
          {[1, 2, 3, 4, 5].map((star) => (
            <button
              key={star}
              type="button"
              onClick={() => setRating(star)}
              onMouseEnter={() => setHoverRating(star)}
              onMouseLeave={() => setHoverRating(0)}
              className="focus:outline-none transform transition-all duration-200 hover:scale-110"
            >
              <Star
                className={`w-8 h-8 transition-colors duration-200 ${
                  (hoverRating || rating) >= star
                    ? "fill-yellow-400 text-yellow-400"
                    : "text-gray-300 hover:text-gray-400"
                }`}
              />
            </button>
          ))}
        </div>
      </div>

      <div>
        <label
          htmlFor="review"
          className="block text-sm font-semibold text-gray-800 mb-2"
        >
          {t('reviews.comment')}
        </label>
        <textarea
          id="review"
          rows={4}
          value={review}
          onChange={(e) => setReview(e.target.value)}
          className="w-full border-2 border-gray-300 rounded-md shadow-sm focus:border-[#3084C2] focus:ring-2 focus:ring-[#3084C2]/20 transition-all duration-200 p-3 text-gray-700 placeholder-gray-500 resize-none"
          placeholder={t('reviews.write_review')}
        />
      </div>

      <div className="flex justify-end space-x-3 pt-4">
        <button
          type="button"
          onClick={onCancel}
          className="px-5 mx-2 py-2.5 border-2 border-gray-300 rounded-md text-gray-700 font-medium bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300"
        >
          {t('reviews.cancel')}
        </button>
        <button
          type="submit"
          className="px-6 py-2.5 bg-[#3084C2] hover:bg-[#275a8c] text-white font-semibold rounded-md shadow-md hover:shadow-lg transform transition-all duration-200 hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-[#3084C2]/50"
        >
          {t('reviews.submit')}
        </button>
      </div>
    </form>
  );
};

export default ReviewForm;