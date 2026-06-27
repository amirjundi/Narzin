import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

// Fetch wishlist items
export const fetchWishlist = createAsyncThunk(
  'wishlist/fetchWishlist',
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get('/v1/wishlist');
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response.data);
    }
  }
);

// Add item to wishlist
export const addToWishlist = createAsyncThunk(
  'wishlist/addToWishlist',
  async ({ product_id }, { rejectWithValue }) => {
    try {
      const response = await api.post('/v1/wishlist', {
        product_id,
      });
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response.data);
    }
  }
);



// Remove item from wishlist
export const removeWishlistItem = createAsyncThunk(
  'wishlist/removeWishlistItem',
  async (wishlistItemId, { rejectWithValue }) => {
    try {
      await api.delete(`/v1/wishlist/${wishlistItemId}`);
      return wishlistItemId;
    } catch (error) {
      return rejectWithValue(error.response.data);
    }
  }
);

// Clear entire wishlist
export const clearWishlist = createAsyncThunk(
  'wishlist/clearWishlist',
  async (_, { rejectWithValue }) => {
    try {
      await api.get('/v1/clear/clear');
      return [];
    } catch (error) {
      return rejectWithValue(error.response.data);
    }
  }
);

const WishlistSlice = createSlice({
  name: 'wishlist',
  initialState: {
    items: [],
    status: 'idle',
    error: null,
    totalItems: 0,
  },
  reducers: {},
  extraReducers: (builder) => {
    builder
      // Fetch Wishlist cases
      .addCase(fetchWishlist.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(fetchWishlist.fulfilled, (state, action) => {
        state.status = 'succeeded';
        state.items = action.payload;
        state.totalItems = action.payload.reduce((total, item) => total + item.quantity, 0);
      })
      .addCase(fetchWishlist.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload || 'Failed to fetch wishlist';
      })
      
      // Add to  Wishlist cases
      .addCase(addToWishlist.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(addToWishlist.fulfilled, (state) => {
        state.status = 'succeeded';
        // Instead of manipulating the state directly, we'll fetch the wishlist again
      })
      .addCase(addToWishlist.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload || 'Failed to add item to wishlist';
      })
      

      
      // Remove wishlist Wishlist cases
      .addCase(removeWishlistItem.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(removeWishlistItem.fulfilled, (state, action) => {
        state.status = 'succeeded';
        // Filter out the removed item
        state.items = state.items.filter(item => item.id !== action.payload);
        state.totalItems = state.items.reduce((total, item) => total + item.quantity, 0);
      })
      .addCase(removeWishlistItem.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload || 'Failed to remove wishlist item';
      })
      
      // Clear Wishlist cases
      .addCase(clearWishlist.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(clearWishlist.fulfilled, (state) => {
        state.status = 'succeeded';
        state.items = [];
        state.totalItems = 0;
      })
      .addCase(clearWishlist.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload || 'Failed to clear wishlist';
      });
  },
});

export default WishlistSlice.reducer;