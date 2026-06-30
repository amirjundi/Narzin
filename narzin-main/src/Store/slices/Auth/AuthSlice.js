import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api, { getCsrfCookie } from '../../../api/axios';

// Auth is carried by an httpOnly session cookie that JS cannot read. We keep a
// non-sensitive copy of the user object as the "logged in" indicator only.
const user = (localStorage.getItem('user') || sessionStorage.getItem('user'))
  ? JSON.parse(localStorage.getItem('user') || sessionStorage.getItem('user'))
  : null;

// Login thunk
export const login = createAsyncThunk(
  'auth/login',
  async ({ email, password, rememberMe }, { rejectWithValue }) => {
    try {
      // Obtain the XSRF-TOKEN cookie before the authenticated POST.
      await getCsrfCookie();

      const response = await api.post('/v1/login', { email, password });

      // Persist only the (non-sensitive) user object as the auth indicator.
      // The session itself lives in an httpOnly cookie set by the server.
      const storage = rememberMe ? localStorage : sessionStorage;
      storage.setItem('user', JSON.stringify(response.data.data.user));

      return response.data;
    } catch (error) {
      
      // For 422 Validation Error
      if (error.response && error.response.status === 422) {
        return rejectWithValue('Invalid credentials. Please check your email and password.');
      }
      
      // For network errors (like ERR_BAD_REQUEST)
      if (error.code === 'ERR_BAD_REQUEST' || error.status === 422) {
        return rejectWithValue('Invalid credentials. Please check your email and password.');
      }
      
      // For other errors
      return rejectWithValue(
        error.message || 'An error occurred during login. Please try again.'
      );
    }
  }
);

// Verify the session (httpOnly cookie) is still valid by calling a protected
// endpoint. The auth indicator is the stored user object, not a token. This
// lives in the same slice as login/logout so there is a single source of truth
// for `isAuthenticated` (previously a second `authTestSlice` held a competing
// flag, which caused a post-login redirect loop).
export const verifyToken = createAsyncThunk('auth/verifyToken', async () => {
  const hasUser = localStorage.getItem('user') || sessionStorage.getItem('user');
  if (!hasUser) return false;

  try {
    const response = await api.get('/v1/profile');
    return response.data.status === true;
  } catch (error) {
    console.error('Error verifying session:', error);
    return false;
  }
});

// Logout thunk
export const logout = createAsyncThunk('auth/logout', async (_, { rejectWithValue }) => {
  try {
    await api.post('/v1/logout');

    // Clear the local auth indicator (the session cookie is cleared server-side).
    localStorage.removeItem('user');
    sessionStorage.removeItem('user');

    return null;
  } catch (error) {
    // Even if server logout fails, drop the local auth indicator.
    localStorage.removeItem('user');
    sessionStorage.removeItem('user');

    return rejectWithValue(error.message || 'Logout failed');
  }
});

// Auth slice
const authSlice = createSlice({
  name: 'auth',
  initialState: {
    user: user,
    loading: false,
    error: null,
    isAuthenticated: !!user
  },
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    setUser: (state, action) => {
      state.user = action.payload;
      state.isAuthenticated = true;
    }
  },
  extraReducers: (builder) => {
    builder
      // Login cases
      .addCase(login.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(login.fulfilled, (state, action) => {
        state.loading = false;
        state.user = action.payload.data.user;
        state.isAuthenticated = true;
        state.error = null;
      })
      .addCase(login.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload || 'Login failed';
      })
      
      // Logout cases
      .addCase(logout.pending, (state) => {
        state.loading = true;
      })
      .addCase(logout.fulfilled, (state) => {
        state.loading = false;
        state.user = null;
        state.isAuthenticated = false;
        state.error = null;
      })
      .addCase(logout.rejected, (state, action) => {
        state.loading = false;
        state.user = null;
        state.isAuthenticated = false;
        state.error = action.payload || 'Logout failed';
      })

      // Session verification cases
      .addCase(verifyToken.pending, (state) => {
        state.loading = true;
      })
      .addCase(verifyToken.fulfilled, (state, action) => {
        state.isAuthenticated = action.payload;
        if (!action.payload) {
          // Session is no longer valid — drop the stale auth indicator.
          state.user = null;
          localStorage.removeItem('user');
          sessionStorage.removeItem('user');
        }
        state.loading = false;
      })
      .addCase(verifyToken.rejected, (state) => {
        state.isAuthenticated = false;
        state.loading = false;
      });
  }
});

export const { clearError, setUser } = authSlice.actions;
export default authSlice.reducer;