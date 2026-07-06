import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import api from "../../api/axios";

// Public, admin-configurable storefront settings (WhatsApp number, etc.).
export const fetchPublicSettings = createAsyncThunk(
  "settings/fetchPublic",
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get("/v1/settings/public");
      return response.data?.data || {};
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || error.message);
    }
  }
);

const initialState = {
  whatsapp_number: null,
  support_hours: null,
  status: "idle",
};

const SettingsSlice = createSlice({
  name: "settings",
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchPublicSettings.pending, (state) => {
        state.status = "loading";
      })
      .addCase(fetchPublicSettings.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.whatsapp_number = action.payload.whatsapp_number ?? null;
        state.support_hours = action.payload.support_hours ?? null;
      })
      .addCase(fetchPublicSettings.rejected, (state) => {
        state.status = "failed";
      });
  },
});

export const selectWhatsappNumber = (state) => state.settings.whatsapp_number;
export const selectSupportHours = (state) => state.settings.support_hours;

export default SettingsSlice.reducer;
