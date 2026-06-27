import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

// Fetch cart items
export const fetchCart = createAsyncThunk(
  'cart/fetchCart',
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get('/v1/cart');
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response.data);
    }
  }
);

// Add item to cart
export const addToCart = createAsyncThunk(
  'cart/addToCart',
  async ({ product_id, product_variant_id, quantity }, { rejectWithValue }) => {
    try {
      const response = await api.post('/v1/cart', {
        product_id,
        product_variant_id,
        quantity
      });
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response.data);
    }
  }
);

// Update cart item quantity
export const updateCartItem = createAsyncThunk(
  'cart/updateCartItem',
  async ({ cartItemId, quantity }, { rejectWithValue }) => {
    try {
      const response = await api.put(`/v1/cart/${cartItemId}`, {
        quantity
      });
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response.data);
    }
  }
);

// Remove item from cart
export const removeCartItem = createAsyncThunk(
  'cart/removeCartItem',
  async (cartItemId, { rejectWithValue }) => {
    try {
      await api.delete(`/v1/cart/${cartItemId}`);
      return cartItemId;
    } catch (error) {
      return rejectWithValue(error.response.data);
    }
  }
);

// Clear entire cart
export const clearCart = createAsyncThunk(
  'cart/clearCart',
  async (_, { rejectWithValue }) => {
    try {
      await api.get('/v1/clear/cart');
      return [];
    } catch (error) {
      return rejectWithValue(error.response.data);
    }
  }
);

const cartSlice = createSlice({
  name: 'cart',
  initialState: {
    items: [],
    status: 'idle',
    error: null,
    totalItems: 0,
  },
  reducers: {},
  extraReducers: (builder) => {
    builder
      // Fetch cart cases
      .addCase(fetchCart.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(fetchCart.fulfilled, (state, action) => {
        state.status = 'succeeded';
        const items = Array.isArray(action.payload) ? action.payload : [];
        state.items = items;
        state.totalItems = items.length === 0
          ? 0
          : items.reduce((total, item) => total + (item.quantity || 0), 0);
        state.error = null;
      })
      .addCase(fetchCart.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload || 'Failed to fetch cart';
      })
      
      // Add to cart cases
      .addCase(addToCart.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(addToCart.fulfilled, (state) => {
        state.status = 'succeeded';
        // Instead of manipulating the state directly, we'll fetch the cart again
      })
      .addCase(addToCart.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload || 'Failed to add item to cart';
      })
      
      // Update cart item cases
      .addCase(updateCartItem.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(updateCartItem.fulfilled, (state) => {
        state.status = 'succeeded';
        // We'll fetch the cart again to get the updated state
      })
      .addCase(updateCartItem.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload || 'Failed to update cart item';
      })
      
      // Remove cart item cases
      .addCase(removeCartItem.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(removeCartItem.fulfilled, (state, action) => {
        state.status = 'succeeded';
        // Filter out the removed item
        state.items = state.items.filter(item => item.id !== action.payload);
        state.totalItems = state.items.reduce((total, item) => total + item.quantity, 0);
      })
      .addCase(removeCartItem.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload || 'Failed to remove cart item';
      })
      
      // Clear cart cases
      .addCase(clearCart.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(clearCart.fulfilled, (state) => {
        state.status = 'succeeded';
        state.items = [];
        state.totalItems = 0;
      })
      .addCase(clearCart.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload || 'Failed to clear cart';
      });
  },
});

export default cartSlice.reducer;