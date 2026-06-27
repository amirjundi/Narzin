import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

export const fetchSingleVendor = createAsyncThunk(
    'Narzin/SingleVendor',
    async (vendorId) => {
        const response = await api.get('/v1/vendors/' + vendorId);
        return response.data.data;
    }
);

const SingleVendorSlice = createSlice({
    name: 'SingleVendor',
    initialState: {
        items: [],
        SingleVendorStatus: 'idle',
        SingleVendorError: null
    },
    reducers: {},
    extraReducers: (builder) => {
        builder
            .addCase(fetchSingleVendor.pending, (state) => {
                state.SingleVendorStatus = 'loading';
            })
            .addCase(fetchSingleVendor.fulfilled, (state, action) => {
                state.SingleVendorStatus = 'succeeded';
                state.items = action.payload;
            })
            .addCase(fetchSingleVendor.rejected, (state, action) => {
                state.SingleVendorStatus = 'failed';
                state.SingleVendorError = action.error.message;
            });
    }
});

export default SingleVendorSlice.reducer;
