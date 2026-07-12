import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import api from "../../api/axios";

export const fetchReturns = createAsyncThunk(
  "returns/fetchReturns",
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get("/v1/returns");
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: "Failed to load returns" });
    }
  }
);

export const requestReturn = createAsyncThunk(
  "returns/requestReturn",
  async ({ orderId, reason, note }, { rejectWithValue }) => {
    try {
      const response = await api.post(`/v1/orders/${orderId}/returns`, { reason, note });
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: "Failed to request return" });
    }
  }
);

const returnsSlice = createSlice({
  name: "returns",
  initialState: {
    returns: [],
    status: "idle",
    error: null,
    submitting: false,
    submitError: null,
  },
  reducers: {
    clearSubmitError: (state) => { state.submitError = null; },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchReturns.pending, (state) => { state.status = "loading"; state.error = null; })
      .addCase(fetchReturns.fulfilled, (state, action) => { state.status = "succeeded"; state.returns = action.payload || []; })
      .addCase(fetchReturns.rejected, (state, action) => { state.status = "failed"; state.error = action.payload?.message || "Failed to load returns"; })
      .addCase(requestReturn.pending, (state) => { state.submitting = true; state.submitError = null; })
      .addCase(requestReturn.fulfilled, (state, action) => {
        state.submitting = false;
        if (action.payload) state.returns.unshift(action.payload);
      })
      .addCase(requestReturn.rejected, (state, action) => {
        state.submitting = false;
        state.submitError = action.payload?.message || "Failed to request return";
      });
  },
});

export const { clearSubmitError } = returnsSlice.actions;
export default returnsSlice.reducer;
