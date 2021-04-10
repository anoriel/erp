import TransactionAPI from "../api/transaction";

const CREATING_TRANSACTION = "CREATING_TRANSACTION",
  CREATING_TRANSACTION_SUCCESS = "CREATING_TRANSACTION_SUCCESS",
  CREATING_TRANSACTION_ERROR = "CREATING_TRANSACTION_ERROR",
  DELETING_TRANSACTION = "DELETING_TRANSACTION",
  DELETING_TRANSACTION_SUCCESS = "DELETING_TRANSACTION_SUCCESS",
  DELETING_TRANSACTION_ERROR = "DELETING_TRANSACTION_ERROR",
  FETCHING_TRANSACTIONS = "FETCHING_TRANSACTIONS",
  FETCHING_TRANSACTIONS_SUCCESS = "FETCHING_TRANSACTIONS_SUCCESS",
  FETCHING_TRANSACTIONS_ERROR = "FETCHING_TRANSACTIONS_ERROR",
  UPDATING_TRANSACTION = "UPDATING_TRANSACTION",
  UPDATING_TRANSACTION_SUCCESS = "UPDATING_TRANSACTION_SUCCESS",
  UPDATING_TRANSACTION_ERROR = "UPDATING_TRANSACTION_ERROR";

export default {
  namespaced: true,
  state: {
    isLoading: false,
    error: null,
    transactions: []
  },
  getters: {
    isLoading(state) {
      return state.isLoading;
    },
    hasError(state) {
      return state.error !== null;
    },
    error(state) {
      return state.error;
    },
    hasTransactions(state) {
      return state.transactions.length > 0;
    },
    transactions(state) {
      return state.transactions;
    }
  },
  mutations: {
    [CREATING_TRANSACTION](state) {
      state.isLoading = true;
      state.error = null;
    },
    [CREATING_TRANSACTION_SUCCESS](state, transaction) {
      state.isLoading = false;
      state.error = null;
      state.transactions.unshift(transaction);
    },
    [CREATING_TRANSACTION_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.transactions = [];
    },
    [DELETING_TRANSACTION](state) {
      state.isLoading = true;
      state.error = null;
    },
    [DELETING_TRANSACTION_SUCCESS](state, transaction) {
      state.isLoading = false;
      state.error = null;
      var foundKeys = state.transactions.findIndex(function (item) {
        return item.id == transaction.id;
      })
      if (typeof foundKeys != "undefined") state.transactions.splice(foundKeys, 1);
    },
    [DELETING_TRANSACTION_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.transactions = [];
    },
    [FETCHING_TRANSACTIONS](state) {
      state.isLoading = true;
      state.error = null;
      state.transactions = [];
    },
    [FETCHING_TRANSACTIONS_SUCCESS](state, transactions) {
      state.isLoading = false;
      state.error = null;
      state.transactions = transactions;
    },
    [FETCHING_TRANSACTIONS_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.transactions = [];
    },
    [UPDATING_TRANSACTION](state) {
      state.isLoading = true;
      state.error = null;
    },
    [UPDATING_TRANSACTION_SUCCESS](state, transaction) {
      state.isLoading = false;
      state.error = null;
      var foundKeys = state.transactions.findIndex(function (item) {
        return item.id == transaction.id;
      })
      if (typeof foundKeys != "undefined") state.transactions[foundKeys] = transaction;
    },
    [UPDATING_TRANSACTION_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.transactions = [];
    },
  },
  actions: {
    async create({ commit }, payload) {
      commit(CREATING_TRANSACTION);
      try {
        let response = await
        TransactionAPI.create(payload.companyId, payload.customerId, payload.providerId, payload.productId, payload.quantity);
        commit(CREATING_TRANSACTION_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(CREATING_TRANSACTION_ERROR, error);
        return null;
      }
    },
    async delete({ commit }, payload) {
      commit(DELETING_TRANSACTION);
      try {
        let response = await TransactionAPI.delete(payload.id);
        commit(DELETING_TRANSACTION_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(DELETING_TRANSACTION_ERROR, error);
        return null;
      }
    },
    async findAll({ commit }) {
      commit(FETCHING_TRANSACTIONS);
      try {
        let response = await TransactionAPI.findAll();
        commit(FETCHING_TRANSACTIONS_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(FETCHING_TRANSACTIONS_ERROR, error);
        return null;
      }
    },
    async findByCompany({ commit }, payload) {
      commit(FETCHING_TRANSACTIONS);
      try {
        let response = await TransactionAPI.findByCompany(payload.id);
        commit(FETCHING_TRANSACTIONS_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(FETCHING_TRANSACTIONS_ERROR, error);
        return null;
      }
    },
    async update({ commit }, payload) {
      commit(UPDATING_TRANSACTION);
      try {
        let response = await TransactionAPI.update(payload.companyId, payload.customerId, payload.providerId, payload.productId, payload.quantity);
        commit(UPDATING_TRANSACTION_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(UPDATING_TRANSACTION_ERROR, error);
        return null;
      }
    },
  }
};
