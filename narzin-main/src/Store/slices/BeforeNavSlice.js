import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

// Fetch beforeNav banner
export const fetchBeforeNav = createAsyncThunk(
  'beforeNav/fetchBeforeNav',
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get('/v1/before-nav/current');
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || 'Failed to fetch banner');
    }
  }
);

const BeforeNavSlice = createSlice({
  name: 'beforeNav',
  initialState: {
    items: null, // Changed from [] to null since it's a single object
    status: 'idle',
    error: null,
  },
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchBeforeNav.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(fetchBeforeNav.fulfilled, (state, action) => {
        state.status = 'succeeded';
        state.items = action.payload; // Just assign the payload directly
      })
      .addCase(fetchBeforeNav.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload || 'Failed to fetch banner';
      });
  },
});

export default BeforeNavSlice.reducer;