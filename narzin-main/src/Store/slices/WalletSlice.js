import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../api/axios';

// Fetch wallet
export const fetchWallet = createAsyncThunk(
  'wallet/fetchWallet',
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get('/v1/wallet');
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: 'Failed to fetch wallet' });
    }
  }
);

// Fetch wallet transactions
export const fetchWalletTransactions = createAsyncThunk(
  'wallet/fetchWalletTransactions',
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get('/v1/get-wallet-transactions');
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: 'Failed to fetch wallet transactions' });
    }
  }
);

const walletSlice = createSlice({
  name: 'wallet',
  initialState: {
    wallet: null,
    transactions: [],
    status: 'idle',
    transactionsStatus: 'idle',
    error: null,
    transactionsError: null,
  },
  reducers: {},
  extraReducers: (builder) => {
    builder
      // Fetch wallet cases
      .addCase(fetchWallet.pending, (state) => {
        state.status = 'loading';
        state.error = null;
      })
      .addCase(fetchWallet.fulfilled, (state, action) => {
        state.status = 'succeeded';
        state.wallet = action.payload;
        state.error = null;
      })
      .addCase(fetchWallet.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload?.message || 'Failed to fetch wallet';
        state.wallet = null;
      })
      
      // Fetch wallet transactions cases
      .addCase(fetchWalletTransactions.pending, (state) => {
        state.transactionsStatus = 'loading';
        state.transactionsError = null;
      })
      .addCase(fetchWalletTransactions.fulfilled, (state, action) => {
        state.transactionsStatus = 'succeeded';
        state.transactions = action.payload;
        state.transactionsError = null;
      })
      .addCase(fetchWalletTransactions.rejected, (state, action) => {
        state.transactionsStatus = 'failed';
        state.transactionsError = action.payload?.message || 'Failed to fetch wallet transactions';
      });
  },
});

export default walletSlice.reducer;