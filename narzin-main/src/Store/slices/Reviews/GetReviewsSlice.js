import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../../api/axios';

export const fetchReviews = createAsyncThunk(
    'Narzin/Reviews',
    async (productId) => {
        const response = await api.post('/v1/products/get/reviews', { product_id : productId });
        return response.data.data;
    }
);

const ReviewsSlice = createSlice({
    name: 'Reviews',
    initialState: {
        items: [],
        ReviewStatus: 'idle',
        ReviewError: null
    },
    reducers: {},
    extraReducers: (builder) => {
        builder
            .addCase(fetchReviews.pending, (state) => {
                state.ReviewStatus = 'loading';
            })
            .addCase(fetchReviews.fulfilled, (state, action) => {
                state.ReviewStatus = 'succeeded';
                state.items = action.payload;
            })
            .addCase(fetchReviews.rejected, (state, action) => {
                state.ReviewStatus = 'failed';
                state.ReviewError = action.error.message;
            });
    }
});

export default ReviewsSlice.reducer;
