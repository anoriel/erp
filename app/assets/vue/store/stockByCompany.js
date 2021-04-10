import StockByCompanyAPI from "../api/stockByCompany";

const FETCHING_PRODUCTS = "FETCHING_PRODUCTS",
  FETCHING_PRODUCTS_SUCCESS = "FETCHING_PRODUCTS_SUCCESS",
  FETCHING_PRODUCTS_ERROR = "FETCHING_PRODUCTS_ERROR";

export default {
  namespaced: true,
  state: {
    isLoading: false,
    error: null,
    stocksByCompany: []
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
    hasStocksByCompany(state) {
      return state.stocksByCompany.length > 0;
    },
    stocksByCompany(state) {
      return state.stocksByCompany;
    }
  },
  mutations: {
    [FETCHING_PRODUCTS](state) {
      state.isLoading = true;
      state.error = null;
      state.stocksByCompany = [];
    },
    [FETCHING_PRODUCTS_SUCCESS](state, stocksByCompany) {
      state.isLoading = false;
      state.error = null;
      state.stocksByCompany = stocksByCompany;
    },
    [FETCHING_PRODUCTS_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.stocksByCompany = [];
    },
  },
  actions: {
    async findByCompany({ commit }, payload) {
      commit(FETCHING_PRODUCTS);
      try {
        let response = await StockByCompanyAPI.findByCompany(payload.id);
        commit(FETCHING_PRODUCTS_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(FETCHING_PRODUCTS_ERROR, error);
        return null;
      }
    },
  }
};
