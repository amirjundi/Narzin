import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../../api/axios';

export const postNewReview = createAsyncThunk(
    'Narzin/PostReview',
    async (reviewData) => {
        const response = await api.post('/v1/reviews', reviewData);
        return response.data.data;
    }
);

const PostReviewSlice = createSlice({
    name: 'PostReview',
    initialState: {
        ReviewStatus: 'idle',
        ReviewError: null,
        ReviewSuccess: false
    },
    reducers: {
        resetPostReviewState: (state) => {
            state.ReviewStatus = 'idle';
            state.ReviewError = null;
            state.ReviewSuccess = false;
        }
    },
    extraReducers: (builder) => {
        builder
            .addCase(postNewReview.pending, (state) => {
                state.ReviewStatus = 'loading';
                state.ReviewError = null;
                state.ReviewSuccess = false;
            })
            .addCase(postNewReview.fulfilled, (state) => {
                state.ReviewStatus = 'succeeded';
                state.ReviewError = null;
                state.ReviewSuccess = true;
            })
            .addCase(postNewReview.rejected, (state, action) => {
                state.ReviewStatus = 'failed';
                state.ReviewError = action.error.message; // Fixed: Changed from action.ReviewError to action.error
                state.ReviewSuccess = false;
            });
    }
});

export const { resetPostReviewState } = PostReviewSlice.actions;
export default PostReviewSlice.reducer;