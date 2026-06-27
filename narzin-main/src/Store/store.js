import { configureStore } from "@reduxjs/toolkit";
import CategorySlice from "./slices/CategorySlice";
import ProductSlice from "./slices/ProductSlice";
import SingleProductSlice from "./slices/SingleProductSlice";
import SingleVendorSlice from "./slices/SingleVendorSlice";
import ReviewsSlice from "./slices/Reviews/GetReviewsSlice";
import PostReviewSlice from "./slices/Reviews/PostReviewSlice";
import StoreSlice from "./slices/StoreSlice";
import authReducer from "./slices/Auth/authTestSlice";

import userLoginReducer from "./slices/Auth/AuthSlice";

import registrationReducer from "./slices/Auth/RegistrationSlice";

import cartReducer from "./slices/CardSlice";

import AddressReducer from "./slices/AddressSlice";

import couponReducer from "./slices/CouponSlice";

import checkoutReducer from "./slices/CheckoutSlice";

import walletReducer from "./slices/WalletSlice";

import ShippingSlice from "./slices/ShippingSlice";

import myOrdersSlice from "./slices/MyOrdersSlice";

import ProfileSlice from "./slices/ProfileSlice";

import wishlistReducer from "./slices/WishlistSlice";

import VendorSlice from "./slices/VendorSlice";

import BeforeNavSlice from "./slices/BeforeNavSlice";


export const store = configureStore({
  reducer: {
    categories: CategorySlice,
    products: ProductSlice,
    SingleProduct: SingleProductSlice,
    SingleVendor: SingleVendorSlice,
    Reviews: ReviewsSlice,
    postReview: PostReviewSlice,
    store: StoreSlice,
    auth: authReducer,
    userLogin: userLoginReducer,
    registration: registrationReducer,
    cart: cartReducer,
    wishlist: wishlistReducer,
    address: AddressReducer,
    coupon: couponReducer,
    checkout: checkoutReducer,
    wallet: walletReducer,
    shippingPrices: ShippingSlice,
    myOrders: myOrdersSlice,
    profile: ProfileSlice,
    vendor: VendorSlice,
    beforeNav: BeforeNavSlice,
  },
});
