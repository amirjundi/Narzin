import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

export const fetchCategories = createAsyncThunk(
    'Narzin/categories',
    async () => {
        const response = await api.get('/v1/categories');
        return response.data;
    }
);

const CategorySlice = createSlice({
    name: 'categories',
    initialState: {
        items: [],
        CategoryStatus: 'idle',
        CategoryError: null
    },
    reducers: {},
    extraReducers: (builder) => {
        builder
            .addCase(fetchCategories.pending, (state) => {
                state.CategoryStatus = 'loading';
            })
            .addCase(fetchCategories.fulfilled, (state, action) => {
                state.CategoryStatus = 'succeeded';
                state.items = action.payload;
            })
            .addCase(fetchCategories.rejected, (state, action) => {
                state.CategoryStatus = 'failed';
                state.CategoryError = action.error.message;
            });
    }
});

export default CategorySlice.reducer;
