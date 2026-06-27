import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

// Fetch profile data
export const fetchProfile = createAsyncThunk(
    'profile/fetchProfile',
    async (_, { rejectWithValue }) => {
        try {
            const response = await api.get('/v1/profile');
            return response.data.data;
        } catch (error) {
            return rejectWithValue(error.response?.data || { message: 'Failed to fetch profile' });
        }
    }
);

// Update profile data
export const updateProfile = createAsyncThunk(
    'profile/updateProfile',
    async (profileData, { rejectWithValue }) => {
        try {
            const response = await api.post('/v1/profile/update', profileData);
            return response.data.data;
        } catch (error) {
            return rejectWithValue(error.response?.data || { message: 'Failed to update profile' });
        }
    }
);

const ProfileSlice = createSlice({
    name: 'profile',
    initialState: {
        user: null,
        devices: null,
        status: 'idle',
        error: null,
        updateStatus: 'idle',
        updateError: null,
        emailVerificationNeeded: false
    },
    reducers: {
        resetStatus: (state) => {
            state.updateStatus = 'idle';
            state.updateError = null;
            state.emailVerificationNeeded = false;
        }
    },
    extraReducers: (builder) => {
        builder
            // Fetch profile cases
            .addCase(fetchProfile.pending, (state) => {
                state.status = 'loading';
                state.error = null;
            })
            .addCase(fetchProfile.fulfilled, (state, action) => {
                state.status = 'succeeded';
                state.user = action.payload.user;
                state.devices = action.payload.devices;
                state.error = null;
            })
            .addCase(fetchProfile.rejected, (state, action) => {
                state.status = 'failed';
                state.error = action.payload?.message || 'Failed to fetch profile';
            })
            
            // Update profile cases
            .addCase(updateProfile.pending, (state) => {
                state.updateStatus = 'loading';
                state.updateError = null;
                state.emailVerificationNeeded = false;
            })
            .addCase(updateProfile.fulfilled, (state, action) => {
                state.updateStatus = 'succeeded';
                state.user = action.payload.user;
                
                // Check if email verification is needed based on the response
                if (action.payload.email_verification_needed) {
                    state.emailVerificationNeeded = true;
                }
                
                state.updateError = null;
            })
            .addCase(updateProfile.rejected, (state, action) => {
                state.updateStatus = 'failed';
                state.updateError = action.payload?.message || 'Failed to update profile';
            });
    }
});

export const { resetStatus } = ProfileSlice.actions;
export default ProfileSlice.reducer;