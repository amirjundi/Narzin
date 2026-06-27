import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

// Fetch wallet
export const fetchShipping = createAsyncThunk(
  'shippingPrices/fetchShippingPrices',
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get('/v1/get-delivery-zones');
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: 'Failed to fetch Delivery Zones' });
    }
  }
);

const ShippingSlice = createSlice({
  name: 'shippingPrices',
  initialState: {
    shippingPrices: null,
    status: 'idle',
    error: null,
  },

  reducers: {},
  extraReducers: (builder) => {
    builder
    
        .addCase(fetchShipping.pending, (state) => {
          state.status = 'loading';
          state.error = null;
        })
        .addCase(fetchShipping.fulfilled, (state, action) => {
          state.status = 'succeeded';
          state.shippingPrices = action.payload;
        })
        .addCase(fetchShipping.rejected, (state, action) => {
          state.status = 'failed';
          state.error = action.payload;
        });


  },
});

export default ShippingSlice.reducer;