import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

export const fetchProductsStore = createAsyncThunk(
    'Narzin/store',
    async (queryString = '', { rejectWithValue }) => {
        try {
            // Use the provided query string or default to empty search
            const endpoint = `/v1/products/search${queryString && queryString !== '?' ? queryString : ''}`;
            const response = await api.get(endpoint);
            return response.data;
        } catch (error) {
            return rejectWithValue(error.response?.data || 'Failed to fetch products');
        }
    }
);

const StoreSlice = createSlice({
    name: 'store',
    initialState: {
        items: [],
        StoreStatus: 'idle',
        StoreError: null
    },
    reducers: {
        resetStoreState: (state) => {
            state.items = [];
            state.StoreStatus = 'idle';
            state.StoreError = null;
        }
    },
    extraReducers: (builder) => {
        builder
            .addCase(fetchProductsStore.pending, (state) => {
                state.StoreStatus = 'loading';
            })
            .addCase(fetchProductsStore.fulfilled, (state, action) => {
                state.StoreStatus = 'succeeded';
                state.items = action.payload;
                state.StoreError = null;
            })
            .addCase(fetchProductsStore.rejected, (state, action) => {
                state.StoreStatus = 'failed';
                state.StoreError = action.payload || action.error.message;
            });
    }
});

export const { resetStoreState } = StoreSlice.actions;
export default StoreSlice.reducer;