import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../../api/axios';

// Registration thunk
export const register = createAsyncThunk(
  'auth/register',
  async (userData, { rejectWithValue }) => {
    try {
      // Extract required fields and restructure if needed
      const { firstName, lastName, email, password, confirmPassword } = userData;
      
      // Create payload with the required field names according to your API
      const payload = {
        name: `${firstName} ${lastName}`,
        email,
        password,
        password_confirmation: confirmPassword
      };
      
      const response = await api.post('/v1/register', payload);
      return response.data;
    } catch (error) {
      
      // Handle email duplication error (422 status code)
      if (error.response?.status === 422) {
        // Check if it's an email uniqueness validation error
        if (error.response.data?.errors?.email?.includes('validation.unique')) {
          return rejectWithValue({
            message: 'This email is already registered. Please use a different email or try to login.',
            field: 'email'
          });
        }
        
        // Handle other validation errors
        if (error.response.data?.errors) {
          const errorMessages = {};
          
          // Extract field-specific errors
          Object.keys(error.response.data.errors).forEach(field => {
            errorMessages[field] = error.response.data.errors[field].join(', ');
          });
          
          return rejectWithValue({
            message: error.response.data.message || 'Validation error',
            errors: errorMessages
          });
        }
      }
      
      // General error handling
      return rejectWithValue({
        message: error.response?.data?.message || error.message || 'Registration failed. Please try again.',
        generalError: true
      });
    }
  }
);

// Registration slice
const registrationSlice = createSlice({
  name: 'registration',
  initialState: {
    loading: false,
    success: false,
    error: null,
    fieldErrors: {},
    message: ''
  },
  reducers: {
    clearRegistrationState: (state) => {
      state.loading = false;
      state.success = false;
      state.error = null;
      state.fieldErrors = {};
      state.message = '';
    },
    clearRegistrationError: (state) => {
      state.error = null;
      state.fieldErrors = {};
    }
  },
  extraReducers: (builder) => {
    builder
      .addCase(register.pending, (state) => {
        state.loading = true;
        state.error = null;
        state.fieldErrors = {};
        state.success = false;
        state.message = '';
      })
      .addCase(register.fulfilled, (state, action) => {
        state.loading = false;
        state.success = true;
        state.error = null;
        state.fieldErrors = {};
        state.message = 'Registration successful! Please check your inbox to verify your email.';
      })
      .addCase(register.rejected, (state, action) => {
        state.loading = false;
        state.success = false;
        
        if (action.payload) {
          // Handle field-specific errors
          if (action.payload.errors) {
            state.fieldErrors = action.payload.errors;
          } 
          // Handle specific email error
          else if (action.payload.field === 'email') {
            state.fieldErrors = { email: action.payload.message };
          }
          
          state.error = action.payload.message;
        } else {
          state.error = 'Registration failed. Please try again.';
        }
      });
  }
});

export const { clearRegistrationState, clearRegistrationError } = registrationSlice.actions;
export default registrationSlice.reducer;