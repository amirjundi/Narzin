import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

export const fetchSingleProduct = createAsyncThunk(
    'Narzin/SingleProduct',
    async (productId) => {
        const response = await api.get('/v1/products/' + productId);
        return response.data.data;
    }
);

const SingleProductSlice = createSlice({
    name: 'SingleProduct',
    initialState: {
        items: [],
        SingleProductStatus: 'idle',
        SingleProductError: null
    },
    reducers: {},
    extraReducers: (builder) => {
        builder
            .addCase(fetchSingleProduct.pending, (state) => {
                state.SingleProductStatus = 'loading';
            })
            .addCase(fetchSingleProduct.fulfilled, (state, action) => {
                state.SingleProductStatus = 'succeeded';
                state.items = action.payload;
            })
            .addCase(fetchSingleProduct.rejected, (state, action) => {
                state.SingleProductStatus = 'failed';
                state.SingleProductError = action.error.message;
            });
    }
});

export default SingleProductSlice.reducer;
