import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';
import { trackCartEvent } from '../../helpers/tracking';

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
  async ({ product_id, product_variant_id, quantity, unit_price }, { rejectWithValue }) => {
    try {
      const response = await api.post('/v1/cart', {
        product_id,
        product_variant_id,
        quantity
      });
      // Best-effort analytics — must never break the cart.
      try {
        trackCartEvent({
          action: 'add',
          product_id,
          variant_id: product_variant_id ?? null,
          quantity,
          unit_price,
        });
      } catch { /* ignore */ }
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response.data);
    }
  }
);

// Best-effort: recover product_id/variant_id/unit_price for a cart line from
// Redux state, since the update/remove thunks only receive the cart-item id.
// Cart line shape (see CartController@index / Card.jsx): item.id, item.product_id,
// item.product_variant_id, item.product (relation), item.product_variant (relation,
// with .price = raw unit price), item.price (converted/marked-up line total), item.quantity.
function resolveCartTrackFields(item) {
  const product_id = item?.product_id ?? item?.product?.id ?? null;
  if (product_id == null) return null;
  const variant_id = item?.product_variant_id ?? item?.product_variant?.id ?? null;
  const rawUnitPrice = item?.product_variant?.price ?? item?.price ?? null;
  const unit_price = rawUnitPrice != null ? parseFloat(rawUnitPrice) : null;
  return { product_id, variant_id, unit_price };
}

// Update cart item quantity
export const updateCartItem = createAsyncThunk(
  'cart/updateCartItem',
  async ({ cartItemId, quantity }, { getState, rejectWithValue }) => {
    try {
      const response = await api.put(`/v1/cart/${cartItemId}`, {
        quantity
      });
      // Best-effort analytics — must never break the cart.
      try {
        const item = (getState().cart.items || []).find((i) => i.id === cartItemId);
        const fields = item ? resolveCartTrackFields(item) : null;
        if (fields) {
          trackCartEvent({
            action: 'update',
            product_id: fields.product_id,
            variant_id: fields.variant_id,
            quantity,
            unit_price: fields.unit_price,
          });
        }
      } catch { /* ignore */ }
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response.data);
    }
  }
);

// Remove item from cart
export const removeCartItem = createAsyncThunk(
  'cart/removeCartItem',
  async (cartItemId, { getState, rejectWithValue }) => {
    const item = (getState().cart.items || []).find((i) => i.id === cartItemId);
    try {
      await api.delete(`/v1/cart/${cartItemId}`);
      // Best-effort analytics — must never break the cart.
      try {
        const fields = item ? resolveCartTrackFields(item) : null;
        if (fields) {
          trackCartEvent({
            action: 'remove',
            product_id: fields.product_id,
            variant_id: fields.variant_id,
            quantity: item.quantity ?? 1,
            unit_price: fields.unit_price,
          });
        }
      } catch { /* ignore */ }
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