import React, { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import {
  Wallet,
  Clock,
  ArrowUpRight,
  ArrowDownRight,
  DollarSign,
  TrendingUp,
  ArrowUpLeftFromCircle,
  ArrowDownRightFromCircle,
  AlertCircle,
  Loader,
  Filter,
  ShoppingBag
} from 'lucide-react';
import { fetchWallet, fetchWalletTransactions } from '../../../Store/slices/WalletSlice';

const WalletComponent = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  
  // Component state
  const [selectedPeriod, setSelectedPeriod] = useState('all');
  const [selectedType, setSelectedType] = useState('all');
  
  // Redux state
  const { 
    wallet, 
    transactions, 
    status, 
    transactionsStatus, 
    error, 
    transactionsError 
  } = useSelector(state => state.wallet);
  
  // Fetch wallet data on component mount
  useEffect(() => {
    dispatch(fetchWallet());
    dispatch(fetchWalletTransactions());
  }, [dispatch]);
  
  // Calculate monthly totals
  const calculateMonthlyTotals = () => {
    if (!transactions || transactions.length === 0) {
      return { income: 0, expense: 0 };
    }
    
    const currentDate = new Date();
    const currentMonth = currentDate.getMonth();
    const currentYear = currentDate.getFullYear();
    
    // Filter transactions for current month
    const thisMonthTransactions = transactions.filter(transaction => {
      const txDate = new Date(transaction.created_at);
      return txDate.getMonth() === currentMonth && txDate.getFullYear() === currentYear;
    });
    
    // Calculate income (deposits) and expenses (withdrawals and order payments)
    const income = thisMonthTransactions
      .filter(tx => tx.type === 'deposit')
      .reduce((sum, tx) => sum + parseFloat(tx.amount), 0);
      
    const expense = thisMonthTransactions
      .filter(tx => tx.type === 'withdraw' || tx.type === 'order')
      .reduce((sum, tx) => sum + parseFloat(tx.amount), 0);
      
    return { income, expense };
  };
  
  const monthlyTotals = calculateMonthlyTotals();
  
  // Filter transactions based on period and type
  const getFilteredTransactions = () => {
    if (!transactions || transactions.length === 0) {
      return [];
    }
    
    let filtered = [...transactions];
    
    // Apply time period filter
    if (selectedPeriod !== 'all') {
      const currentDate = new Date();
      const currentMonth = currentDate.getMonth();
      const currentYear = currentDate.getFullYear();
      
      if (selectedPeriod === 'month') {
        // Filter for current month
        filtered = filtered.filter(tx => {
          const txDate = new Date(tx.created_at);
          return txDate.getMonth() === currentMonth && txDate.getFullYear() === currentYear;
        });
      } else if (selectedPeriod === 'week') {
        // Filter for current week (last 7 days)
        const oneWeekAgo = new Date();
        oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
        
        filtered = filtered.filter(tx => {
          const txDate = new Date(tx.created_at);
          return txDate >= oneWeekAgo;
        });
      }
    }
    
    // Apply transaction type filter
    if (selectedType !== 'all') {
      filtered = filtered.filter(tx => tx.type === selectedType);
    }
    
    // Sort by date (newest first)
    return filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  };
  
  const filteredTransactions = getFilteredTransactions();
  
  // Get transaction icon based on type
  const getTransactionIcon = (type) => {
    switch (type) {
      case 'deposit':
        return <ArrowDownRight className="w-5 h-5 text-green-500" />;
      case 'withdraw':
        return <ArrowUpRight className="w-5 h-5 text-red-500" />;
      case 'order':
        return <ShoppingBag className="w-5 h-5 text-blue-500" />;
      default:
        return <DollarSign className="w-5 h-5 text-gray-500" />;
    }
  };
  
  // Format transaction description based on type
  const getTransactionDescription = (transaction) => {
    switch (transaction.type) {
      case 'deposit':
        return t('wallet.depositDescription');
      case 'withdraw':
        return t('wallet.withdrawDescription');
      case 'order':
        return t('wallet.orderPayment', { id: transaction.id });
      default:
        return transaction.type;
    }
  };
  
  // Format date
  const formatDate = (dateString) => {
    if (!dateString) return '';
    
    return new Date(dateString).toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };
  
  // Loading state
  if ((status === 'loading' && !wallet) || (transactionsStatus === 'loading' && (!transactions || transactions.length === 0))) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="flex flex-col items-center">
          <Loader className="w-12 h-12 text-[#3084C2] animate-spin mb-4" />
          <p className="text-gray-600">{t('wallet.loading') || 'Loading wallet data...'}</p>
        </div>
      </div>
    );
  }
  
  // Error state
  if (status === 'failed' || transactionsStatus === 'failed') {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center max-w-md">
          <AlertCircle className="w-12 h-12 text-red-500 mx-auto mb-4" />
          <h2 className="text-xl font-semibold mb-2">{t('wallet.loadError') || 'Error Loading Wallet'}</h2>
          <p className="text-gray-600 mb-4">{error || transactionsError || t('wallet.loadErrorMessage')}</p>
          <button
            onClick={() => {
              dispatch(fetchWallet());
              dispatch(fetchWalletTransactions());
            }}
            className="px-4 py-2 bg-[#3084C2] text-white rounded-lg"
          >
            {t('wallet.tryAgain') || 'Try Again'}
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-[1200px] mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-2xl font-bold">{t('wallet.myWallet') || 'My Wallet'}</h2>
      </div>

      {/* Wallet Balance Section */}
      <div className="mb-8 bg-gradient-to-br from-[#3084C2] to-[#1a5c94] rounded-xl p-6 text-white">
        <div className="flex flex-col">
          <span className="text-sm opacity-80">{t('wallet.totalBalance') || 'Total Balance'}</span>
          <span className="text-3xl font-bold mt-1">€{wallet ? parseFloat(wallet.balance).toFixed(2) : '0.00'}</span>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <div className="flex flex-col">
              <div className="flex items-center gap-2 text-sm opacity-80">
                <ArrowDownRightFromCircle className="w-4 h-4" />
                {t('wallet.monthlyIncome') || 'Monthly Income'}
              </div>
              <span className="text-lg font-semibold mt-1">
                +€{monthlyTotals.income.toFixed(2)}
              </span>
            </div>
            <div className="flex flex-col">
              <div className="flex items-center gap-2 text-sm opacity-80">
                <ArrowUpLeftFromCircle className="w-4 h-4" />
                {t('wallet.monthlyExpense') || 'Monthly Expense'}
              </div>
              <span className="text-lg font-semibold mt-1">
                -€{monthlyTotals.expense.toFixed(2)}
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* Transaction History Section */}
      <div>
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
          <h3 className="text-lg font-semibold">{t('wallet.transactionHistory') || 'Transaction History'}</h3>
          <div className="flex flex-wrap gap-3">
            <select
              value={selectedType}
              onChange={(e) => setSelectedType(e.target.value)}
              className="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
            >
              <option value="all">{t('wallet.allTypes') || 'All Types'}</option>
              <option value="deposit">{t('wallet.deposits') || 'Deposits'}</option>
              <option value="withdraw">{t('wallet.withdrawals') || 'Withdrawals'}</option>
              <option value="order">{t('wallet.orders') || 'Orders'}</option>
            </select>
            
            <select
              value={selectedPeriod}
              onChange={(e) => setSelectedPeriod(e.target.value)}
              className="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#3084C2] focus:outline-none"
            >
              <option value="all">{t('wallet.allTime') || 'All Time'}</option>
              <option value="month">{t('wallet.thisMonth') || 'This Month'}</option>
              <option value="week">{t('wallet.thisWeek') || 'This Week'}</option>
            </select>
          </div>
        </div>

        <div className="space-y-4">
          {filteredTransactions.length > 0 ? (
            filteredTransactions.map((transaction) => (
              <div
                key={transaction.id}
                className="bg-white border rounded-lg p-4"
              >
                <div className="flex items-start justify-between">
                  <div className="flex items-center gap-3">
                    <div className="p-2 bg-gray-50 rounded-full">
                      {getTransactionIcon(transaction.type)}
                    </div>
                    <div>
                      <h4 className="font-medium">{getTransactionDescription(transaction)}</h4>
                      <p className="text-sm text-gray-600">
                        {formatDate(transaction.created_at)}
                      </p>
                    </div>
                  </div>
                  <div className="text-right">
                    <span className={`font-medium ${
                      transaction.type === 'deposit' ? 'text-green-600' : 'text-red-600'
                    }`}>
                      {transaction.type === 'deposit' ? '+' : '-'}€{parseFloat(transaction.amount).toFixed(2)}
                    </span>
                    <div className="text-sm">
                      <span className={`px-2 py-1 rounded-full text-xs ${
                        transaction.type === 'deposit'
                          ? 'bg-green-100 text-green-800'
                          : transaction.type === 'withdraw'
                          ? 'bg-red-100 text-red-800'
                          : 'bg-blue-100 text-blue-800'
                      }`}>
                        {transaction.type}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            ))
          ) : (
            <div className="text-center py-12 bg-gray-50 rounded-lg">
              <Wallet className="w-12 h-12 mx-auto text-gray-400 mb-4" />
              <h3 className="text-lg font-medium text-gray-900 mb-2">
                {t('wallet.noTransactions') || 'No transactions found'}
              </h3>
              <p className="text-gray-600">
                {(selectedPeriod !== 'all' || selectedType !== 'all')
                  ? t('wallet.noMatchingTransactions') || "No transactions match your filter criteria"
                  : t('wallet.noTransactionsYet') || "You don't have any wallet transactions yet"}
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default WalletComponent;