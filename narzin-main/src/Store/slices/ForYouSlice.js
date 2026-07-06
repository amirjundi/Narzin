import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import api from "../../api/axios";
import { getSessionId } from "../../helpers/session";

// Personalized "For You" rails (recently viewed + recommended), derived from
// the visitor's product-view history on the backend. Empty for new visitors.
export const fetchForYou = createAsyncThunk(
  "forYou/fetch",
  async (locale, { rejectWithValue }) => {
    try {
      const response = await api.get("/v1/home/for-you", {
        params: { locale, session_id: getSessionId() },
      });
      return Array.isArray(response.data?.data) ? response.data.data : [];
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || error.message);
    }
  }
);

const ForYouSlice = createSlice({
  name: "forYou",
  initialState: { blocks: [], status: "idle" },
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchForYou.pending, (state) => {
        state.status = "loading";
      })
      .addCase(fetchForYou.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.blocks = action.payload;
      })
      .addCase(fetchForYou.rejected, (state) => {
        state.status = "failed";
        state.blocks = [];
      });
  },
});

export const selectForYouBlocks = (state) => state.forYou.blocks;

export default ForYouSlice.reducer;
