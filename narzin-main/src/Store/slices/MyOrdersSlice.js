import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

// Fetch a specific order by ID
export const fetchOrder = createAsyncThunk(
  'myOrders/fetchOrder',
  async (orderId, { rejectWithValue }) => {
    try {
      const response = await api.get(`/v1/orders/${orderId}`);
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: 'Failed to fetch order' });
    }
  }
);

// Fetch all orders for the current user
export const fetchOrders = createAsyncThunk(
  'myOrders/fetchOrders',
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get('/v1/orders');
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: 'Failed to fetch orders' });
    }
  }
);

const myOrdersSlice = createSlice({
  name: 'myOrders',
  initialState: {
    order: null,
    orders: [],
    status: 'idle',
    error: null,
    pagination: {
      currentPage: 1,
      lastPage: 1,
      total: 0
    }
  },
  reducers: {
    clearOrderData: (state) => {
      state.order = null;
      state.status = 'idle';
      state.error = null;
    },
    setOrderFromCheckout: (state, action) => {
      state.order = action.payload;
      state.status = 'succeeded';
    }
  },
  extraReducers: (builder) => {
    builder
      // Fetch specific order cases
      .addCase(fetchOrder.pending, (state) => {
        state.status = 'loading';
        state.error = null;
      })
      .addCase(fetchOrder.fulfilled, (state, action) => {
        state.status = 'succeeded';
        state.order = action.payload;
        state.error = null;
      })
      .addCase(fetchOrder.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload?.message || 'Failed to fetch order';
      })
      
      // Fetch all orders cases
      .addCase(fetchOrders.pending, (state) => {
        state.status = 'loading';
        state.error = null;
      })
      .addCase(fetchOrders.fulfilled, (state, action) => {
        state.status = 'succeeded';
        state.orders = action.payload.data || [];
        // Store pagination info
        if (action.payload) {
          state.pagination = {
            currentPage: action.payload.current_page || 1,
            lastPage: action.payload.last_page || 1,
            total: action.payload.total || 0
          };
        }
        state.error = null;
      })
      .addCase(fetchOrders.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload?.message || 'Failed to fetch orders';
      });
  },
});

export const { clearOrderData, setOrderFromCheckout } = myOrdersSlice.actions;
export default myOrdersSlice.reducer;