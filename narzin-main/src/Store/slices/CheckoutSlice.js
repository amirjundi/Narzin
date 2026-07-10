import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';
import { getSessionId } from '../../helpers/session';

// Initiate payment - creates order and gets payment URL
export const initiatePayment = createAsyncThunk(
  'checkout/initiatePayment',
  async (checkoutData, { rejectWithValue }) => {
    try {
      const response = await api.post('/v1/place-order', {
        ...checkoutData,
        session_id: getSessionId(),
      });
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: 'Failed to initiate payment' });
    }
  }
);

// Verify payment - called after returning from Nass
export const verifyPayment = createAsyncThunk(
  'checkout/verifyPayment',
  async (orderId, { rejectWithValue }) => {
    try {
      const response = await api.post('/v1/verify-payment', { orderId });
      return response.data;
    } catch (error) {
      // Handle 503 (pending) differently
      if (error.response?.status === 503) {
        return {
          status: false,
          pending: true,
          message: error.response?.data?.message || 'Payment verification pending',
          data: error.response?.data?.data
        };
      }
      return rejectWithValue(error.response?.data || { message: 'Failed to verify payment' });
    }
  }
);

// Get pending orders
export const fetchPendingOrders = createAsyncThunk(
  'checkout/fetchPendingOrders',
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get('/v1/pending-orders');
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: 'Failed to fetch pending orders' });
    }
  }
);

const checkoutSlice = createSlice({
  name: 'checkout',
  initialState: {
    status: 'idle',
    error: null,
    selectedShipping: '',
    useWallet: false,
    // Payment verification state
    verificationStatus: 'idle', // idle | loading | succeeded | failed | pending
    verificationError: null,
    verifiedOrder: null,
    // Current order being processed
    currentOrder: null,
    pendingOrders: [],
  },
  reducers: {
    setShippingOption: (state, action) => {
      state.selectedShipping = action.payload;
    },
    toggleWallet: (state) => {
      state.useWallet = !state.useWallet;
    },
    resetCheckout: (state) => {
      state.status = 'idle';
      state.error = null;
      state.verificationStatus = 'idle';
      state.verificationError = null;
      state.verifiedOrder = null;
      state.currentOrder = null;
    },
    setCurrentOrder: (state, action) => {
      state.currentOrder = action.payload;
    },
    clearVerification: (state) => {
      state.verificationStatus = 'idle';
      state.verificationError = null;
      state.verifiedOrder = null;
    }
  },
  extraReducers: (builder) => {
    builder
      // Initiate Payment
      .addCase(initiatePayment.pending, (state) => {
        state.status = 'loading';
        state.error = null;
      })
      .addCase(initiatePayment.fulfilled, (state, action) => {
        state.status = 'succeeded';
        state.error = null;
        state.currentOrder = action.payload.data;
      })
      .addCase(initiatePayment.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload?.message || 'Failed to initiate payment';
      })
      
      // Verify Payment
      .addCase(verifyPayment.pending, (state) => {
        state.verificationStatus = 'loading';
        state.verificationError = null;
      })
      .addCase(verifyPayment.fulfilled, (state, action) => {
        // Check if it's a pending response
        if (action.payload.pending) {
          state.verificationStatus = 'pending';
          state.verificationError = null;
          state.verifiedOrder = action.payload.data;
        } else if (action.payload.status) {
          state.verificationStatus = 'succeeded';
          state.verificationError = null;
          state.verifiedOrder = action.payload.data;
        } else {
          // Payment failed (e.g., refunded due to no stock)
          state.verificationStatus = 'failed';
          state.verificationError = action.payload.message;
          state.verifiedOrder = action.payload.data;
        }
      })
      .addCase(verifyPayment.rejected, (state, action) => {
        state.verificationStatus = 'failed';
        state.verificationError = action.payload?.message || 'Failed to verify payment';
      })
      
      // Fetch Pending Orders
      .addCase(fetchPendingOrders.fulfilled, (state, action) => {
        state.pendingOrders = action.payload.data || [];
      });
  },
});

export const { 
  setShippingOption, 
  toggleWallet, 
  resetCheckout, 
  setCurrentOrder,
  clearVerification 
} = checkoutSlice.actions;

export default checkoutSlice.reducer;