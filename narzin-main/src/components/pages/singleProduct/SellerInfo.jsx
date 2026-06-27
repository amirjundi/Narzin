import React, { useEffect, useState } from "react";
import { Star } from "lucide-react";
import { motion } from "framer-motion";
import { useDispatch, useSelector } from "react-redux";
import { useTranslation } from "react-i18next";
import { fetchSingleVendor } from "../../../Store/slices/SingleVendorSlice";

const SellerInfo = ({vendor}) => {

  const dispatch = useDispatch();
  const { i18n } = useTranslation();
  const [singleVendor, setSingleVendor] = useState(null);

  const {
    items: vendorData,
    singleVendorStatus,
    singleVendorError,
  } = useSelector((state) => state.SingleVendor);


  useEffect(() => {
    dispatch(fetchSingleVendor(vendor));
    setSingleVendor(vendorData);
  }
  , [dispatch, vendor]);
  


  return (
    <motion.div
      className="bg-white rounded-xl p-6 shadow-lg"
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5 }}
    >
      <div className="flex items-center gap-4">
        <div className="w-16 h-16 rounded-full overflow-hidden">
          <img
            src={`${vendorData.store_logo}`}
            alt="Seller Logo"
            className="w-full h-full object-cover"
          />
        </div>
        <div>
          <h3 className="text-xl font-semibold">{i18n.language == 'du' ? vendorData.store_name_in_german :vendorData.store_name_in_arabic}</h3>
          <p className="text-gray-600">{vendorData.address}</p>
          <div className="flex items-center gap-2 mt-1">
            <Star className="w-4 h-4 fill-yellow-400 text-yellow-400" />
            {/* <span className="text-sm">4.8 (2.3k+ reviews)</span> */}
          </div>
        </div>
      </div>

    </motion.div>
  );
};

export default SellerInfo;
