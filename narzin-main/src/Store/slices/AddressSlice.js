import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

// Fetch addresses
export const fetchAddress = createAsyncThunk(
    'address/fetchAddress',
    async (_, { rejectWithValue }) => {
        try {
            const response = await api.get('/v1/address');
            return response.data.data;
        } catch (error) {
            return rejectWithValue(error.response?.data || { message: 'Failed to fetch addresses' });
        }
    }
);

// Create new address
export const createAddress = createAsyncThunk(
    'address/createAddress',
    async (addressData, { rejectWithValue }) => {
        try {
            const response = await api.post('/v1/address', addressData);
            return response.data.data;
        } catch (error) {
            return rejectWithValue(error.response?.data || { message: 'Failed to create address' });
        }
    }
);

// Update address
export const updateAddress = createAsyncThunk(
    'address/updateAddress',
    async ({ id, addressData }, { rejectWithValue }) => {
        try {
            const response = await api.put(`/v1/address/${id}`, addressData);
            return response.data.data;
        } catch (error) {
            return rejectWithValue(error.response?.data || { message: 'Failed to update address' });
        }
    }
);

// Delete address
export const deleteAddress = createAsyncThunk(
    'address/deleteAddress',
    async (id, { rejectWithValue }) => {
        try {
            const response = await api.delete(`/v1/address/${id}`);
            return id; // Return the id of the deleted address
        } catch (error) {
            return rejectWithValue(error.response?.data || { message: 'Failed to delete address' });
        }
    }
);

// Set default address - Updated to use the correct endpoint
export const setDefaultAddress = createAsyncThunk(
    'address/setDefaultAddress',
    async (id, { rejectWithValue }) => {
        try {
            // Updated to use the correct API endpoint
            const response = await api.post(`/v1/address/${id}/set-default`);
            return id; // Return the id of the address that was set as default
        } catch (error) {
            return rejectWithValue(error.response?.data || { message: 'Failed to set default address' });
        }
    }
);

const AddressSlice = createSlice({
    name: 'address',
    initialState: {
        items: [],
        status: 'idle',
        error: null,
        createStatus: 'idle',
        createError: null,
        updateStatus: 'idle',
        updateError: null,
        deleteStatus: 'idle',
        deleteError: null,
    },
    reducers: {
        resetStatus: (state) => {
            state.createStatus = 'idle';
            state.createError = null;
            state.updateStatus = 'idle';
            state.updateError = null;
            state.deleteStatus = 'idle';
            state.deleteError = null;
        }
    },
    extraReducers: (builder) => {
        builder
            // Fetch addresses cases
            .addCase(fetchAddress.pending, (state) => {
                state.status = 'loading';
                state.error = null;
            })
            .addCase(fetchAddress.fulfilled, (state, action) => {
                state.status = 'succeeded';
                state.items = action.payload;
                state.error = null;
            })
            .addCase(fetchAddress.rejected, (state, action) => {
                state.status = 'failed';
                state.error = action.payload?.message || 'Failed to fetch addresses';
            })
            
            // Create address cases
            .addCase(createAddress.pending, (state) => {
                state.createStatus = 'loading';
                state.createError = null;
            })
            .addCase(createAddress.fulfilled, (state, action) => {
                state.createStatus = 'succeeded';
                state.items.push(action.payload);
                state.createError = null;
            })
            .addCase(createAddress.rejected, (state, action) => {
                state.createStatus = 'failed';
                state.createError = action.payload?.message || 'Failed to create address';
            })
            
            // Update address cases
            .addCase(updateAddress.pending, (state) => {
                state.updateStatus = 'loading';
                state.updateError = null;
            })
            .addCase(updateAddress.fulfilled, (state, action) => {
                state.updateStatus = 'succeeded';
                const index = state.items.findIndex(item => item.id === action.payload.id);
                if (index !== -1) {
                    state.items[index] = action.payload;
                }
                state.updateError = null;
            })
            .addCase(updateAddress.rejected, (state, action) => {
                state.updateStatus = 'failed';
                state.updateError = action.payload?.message || 'Failed to update address';
            })
            
            // Delete address cases
            .addCase(deleteAddress.pending, (state) => {
                state.deleteStatus = 'loading';
                state.deleteError = null;
            })
            .addCase(deleteAddress.fulfilled, (state, action) => {
                state.deleteStatus = 'succeeded';
                state.items = state.items.filter(item => item.id !== action.payload);
                state.deleteError = null;
            })
            .addCase(deleteAddress.rejected, (state, action) => {
                state.deleteStatus = 'failed';
                state.deleteError = action.payload?.message || 'Failed to delete address';
            })
            
            // Set default address cases
            .addCase(setDefaultAddress.fulfilled, (state, action) => {
                state.items = state.items.map(item => ({
                    ...item,
                    is_default: item.id === action.payload ? 1 : 0
                }));
            });
    }
});

export const { resetStatus } = AddressSlice.actions;
export default AddressSlice.reducer;