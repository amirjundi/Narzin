import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import api from "../../api/axios";

const LAYOUT_TYPES = ["announcement_bar", "popup"];

export const fetchHome = createAsyncThunk(
  "home/fetchHome",
  async (locale, { rejectWithValue }) => {
    try {
      const response = await api.get("/v1/home", {
        params: { platform: "web", locale },
      });
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || error.message);
    }
  }
);

const HomeSlice = createSlice({
  name: "home",
  initialState: { blocks: [], status: "idle", error: null },
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchHome.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchHome.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.blocks = Array.isArray(action.payload) ? action.payload : [];
      })
      .addCase(fetchHome.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload || "Failed to fetch homepage";
      });
  },
});

export const selectHomeStatus = (state) => state.home.status;
export const selectLayoutBlocks = (state) =>
  state.home.blocks.filter((b) => LAYOUT_TYPES.includes(b.type));
export const selectPageBlocks = (state) =>
  state.home.blocks.filter((b) => !LAYOUT_TYPES.includes(b.type));

export default HomeSlice.reducer;
