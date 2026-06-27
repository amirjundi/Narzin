import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

// Create vendor signup thunk
export const createVendor = createAsyncThunk(
  'vendor/createVendor',
  async (formData, { rejectWithValue }) => {
    try {
      // Create FormData object for file uploads
      const submitData = new FormData();
      Object.keys(formData).forEach(key => {
        submitData.append(key, formData[key]);
      });

      const response = await api.post('/v1/vendors', submitData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: 'An error occurred' });
    }
  }
);

const vendorSlice = createSlice({
  name: 'vendor',
  initialState: {
    status: 'idle', // 'idle' | 'loading' | 'succeeded' | 'failed' | 'underReview'
    error: null,
    message: '',
    vendorData: null
  },
  reducers: {
    resetVendorState: (state) => {
      state.status = 'idle';
      state.error = null;
      state.message = '';
    }
  },
  extraReducers: (builder) => {
    builder
      .addCase(createVendor.pending, (state) => {
        state.status = 'loading';
        state.error = null;
      })
      .addCase(createVendor.fulfilled, (state, action) => {
        state.status = 'underReview';
        state.vendorData = action.payload.data || null;
        state.message = 'Your vendor application has been submitted and is now under review. We will contact you shortly.';
      })
      .addCase(createVendor.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload?.message || 'Failed to submit vendor application';
      });
  }
});

export const { resetVendorState } = vendorSlice.actions;

export default vendorSlice.reducer;