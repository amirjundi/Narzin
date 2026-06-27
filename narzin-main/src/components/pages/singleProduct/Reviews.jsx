import React, { useEffect, useState } from "react";
import { MessageCircle } from "lucide-react";
import ReviewCard from "./ReviewCard";
import ReviewForm from "./ReviewForm";
import Modal from "../../Modal";
import { useDispatch, useSelector } from "react-redux";
import { fetchReviews } from "../../../Store/slices/Reviews/GetReviewsSlice";
import { postNewReview, resetPostReviewState } from "../../../Store/slices/Reviews/PostReviewSlice";
import { useTranslation } from "react-i18next";

const Reviews = ({ productId }) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const dispatch = useDispatch();
  const { t } = useTranslation();

    const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);

  // Get reviews data from Redux
  const {
    items: reviewData,
    ReviewStatus,
  } = useSelector((state) => state.Reviews);

  // Get post review status from Redux
  // Updated to match your actual Redux state structure
  const {
    ReviewStatus: postStatus,
    ReviewError: postError,
    ReviewSuccess: postSuccess
  } = useSelector((state) => state.postReview);

  // Fetch reviews when component mounts or productId changes
  useEffect(() => {
    dispatch(fetchReviews(productId));
  }, [dispatch, productId]);

  // When review is successfully posted, close modal and refresh reviews
  useEffect(() => {
    if (postSuccess) {
      setIsModalOpen(false);
      dispatch(fetchReviews(productId));
      dispatch(resetPostReviewState());
    }
  }, [postSuccess, dispatch, productId]);

  const handleReviewSubmit = (reviewData) => {
    dispatch(postNewReview(reviewData));
  };

  return (
    <div className="mt-8">
      <div className="flex items-center justify-between mb-6 flex-wrap">
        <h3 className="text-2xl text-gray-800 font-bold">{t('reviews.ratings')}</h3>
        <button
        disabled={!isAuthenticated}
          onClick={() => setIsModalOpen(true)}
          className="bg-[#3084C2] text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-[#195e8f] transition-colors"
        >
          <MessageCircle className="w-5 h-5" />
          {isAuthenticated ? t('reviews.write_review') : t('reviews.login_to_review')}
        </button>
      </div>

      <Modal
        isOpen={isModalOpen}
        onClose={() => {
          setIsModalOpen(false);
          dispatch(resetPostReviewState());
        }}
        title={t('reviews.write_review')}
      >
        {postStatus === 'loading' && (
          <div className="text-center p-4">
            <p>{t('reviews.post_review_loading')}</p>
          </div>
        )}
        
        {postStatus === 'failed' && (
          <div className="bg-red-100 text-red-700 p-3 rounded-md mb-4">
            {postError || "Failed to submit review. Please try again."}
          </div>
        )}
        
        <ReviewForm
          onSubmit={handleReviewSubmit}
          onCancel={() => {
            setIsModalOpen(false);
            dispatch(resetPostReviewState());
          }}
          productId={productId}
        />
      </Modal>

      {ReviewStatus === 'loading' && (
        <div className="text-center p-4">
          <p>{t('reviews.loading')}</p>
        </div>
      )}

      {ReviewStatus === 'failed' && (
        <div className="bg-red-100 text-red-700 p-3 rounded-md">
          <p>{t('reviews.error')}</p>
        </div>
      )}

      {ReviewStatus === 'succeeded' && (
        <div className="space-y-4">
          {reviewData && reviewData.length > 0 ? (
            reviewData.map((review, index) => (
              <ReviewCard key={index} review={review} />
            ))
          ) : (
            <p className="text-gray-600">
              {t('reviews.no_reviews')}
            </p>
          )}
        </div>
      )}
    </div>
  );
};

export default Reviews;