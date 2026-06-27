import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

export const fetchProducts = createAsyncThunk(
    'Narzin/products',
    async () => {
        const response = await api.get('/v1/products');
        return response.data;
    }
);

const ProductSlice = createSlice({
    name: 'products',
    initialState: {
        items: [],
        ProductStatus: 'idle',
        ProductError: null
    },
    reducers: {},
    extraReducers: (builder) => {
        builder
            .addCase(fetchProducts.pending, (state) => {
                state.ProductStatus = 'loading';
            })
            .addCase(fetchProducts.fulfilled, (state, action) => {
                state.ProductStatus = 'succeeded';
                state.items = action.payload;
            })
            .addCase(fetchProducts.rejected, (state, action) => {
                state.ProductStatus = 'failed';
                state.ProductError = action.error.message;
            });
    }
});

export default ProductSlice.reducer;
