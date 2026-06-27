import React from 'react';
import { motion } from 'framer-motion';
import { Star } from 'lucide-react';
const ReviewCard = ({ review }) => {
    return (
      <motion.div 
        className="border rounded-lg p-4 mb-4"
        initial={{ opacity: 0, x: -20 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ duration: 0.3 }}
      >
        <div className="flex items-center gap-3 mb-2">
          <img
            src="https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg"
            alt={review.user?.name}
            className="w-10 h-10 rounded-full"
          />
          <div>
            <h4 className="font-medium">{review.user?.name}</h4>
            <div className="flex items-center gap-1">
              {[...Array(5)].map((_, i) => (
                <Star
                  key={i}
                  className={`w-4 h-4 ${
                    i < review.rating
                      ? "fill-yellow-400 text-yellow-400"
                      : "fill-gray-200 text-gray-200"
                  }`}
                />
              ))}
            </div>
          </div>
          {/* <span className="ml-auto text-sm text-gray-500">{review.date}</span> */}
        </div>
        <p className="text-gray-600">{review.review}</p>
        
      </motion.div>
    );
  };
export default ReviewCard;  