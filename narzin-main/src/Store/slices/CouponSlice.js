import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

// Validate coupon
export const validateCoupon = createAsyncThunk(
  'coupon/validateCoupon',
  async (couponCode, { rejectWithValue }) => {
    try {
      const response = await api.post('/v1/get-coupon', { code: couponCode });
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: 'Failed to validate coupon' });
    }
  }
);

const couponSlice = createSlice({
  name: 'coupon',
  initialState: {
    coupon: null,
    status: 'idle',
    error: null,
  },
  reducers: {
    clearCoupon: (state) => {
      state.coupon = null;
      state.status = 'idle';
      state.error = null;
    }
  },
  extraReducers: (builder) => {
    builder
      // Validate coupon cases
      .addCase(validateCoupon.pending, (state) => {
        state.status = 'loading';
        state.error = null;
      })
      .addCase(validateCoupon.fulfilled, (state, action) => {
        state.status = 'succeeded';
        state.coupon = action.payload;
        state.error = null;
      })
      .addCase(validateCoupon.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload?.message || 'Failed to validate coupon';
        state.coupon = null;
      });
  },
});

export const { clearCoupon } = couponSlice.actions;
export default couponSlice.reducer;