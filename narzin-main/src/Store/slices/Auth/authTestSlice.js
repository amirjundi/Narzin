import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import api from "../../../api/axios";

// Verify the session (httpOnly cookie) is still valid by calling a protected
// endpoint. The auth indicator is the stored user object, not a token.
export const verifyToken = createAsyncThunk("auth/verifyToken", async () => {
    const hasUser = localStorage.getItem("user") || sessionStorage.getItem("user");
    if (!hasUser) return false;

    try {
        const response = await api.get("/v1/profile");
        return response.data.status === true;
    } catch (error) {
        console.error("Error verifying session:", error);
        return false;
    }
});

const authSlice = createSlice({
    name: "auth",
    initialState: {
        isAuthenticated: false,
        loading: false,
    },
    reducers: {
        logout: (state) => {
            localStorage.removeItem("user");
            sessionStorage.removeItem("user");
            state.isAuthenticated = false;
        },
    },
    extraReducers: (builder) => {
        builder
            .addCase(verifyToken.pending, (state) => {
                state.loading = true;
            })
            .addCase(verifyToken.fulfilled, (state, action) => {
                state.isAuthenticated = action.payload;
                state.loading = false;
            })
            .addCase(verifyToken.rejected, (state) => {
                state.isAuthenticated = false;
                state.loading = false;
            });
    },
});

export const { logout } = authSlice.actions;
export default authSlice.reducer;
