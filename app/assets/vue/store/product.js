import ProductAPI from "../api/product";

const CREATING_PRODUCT = "CREATING_PRODUCT",
  CREATING_PRODUCT_SUCCESS = "CREATING_PRODUCT_SUCCESS",
  CREATING_PRODUCT_ERROR = "CREATING_PRODUCT_ERROR",
  DELETING_PRODUCT = "DELETING_PRODUCT",
  DELETING_PRODUCT_SUCCESS = "DELETING_PRODUCT_SUCCESS",
  DELETING_PRODUCT_ERROR = "DELETING_PRODUCT_ERROR",
  FETCHING_PRODUCTS = "FETCHING_PRODUCTS",
  FETCHING_PRODUCTS_SUCCESS = "FETCHING_PRODUCTS_SUCCESS",
  FETCHING_PRODUCTS_ERROR = "FETCHING_PRODUCTS_ERROR",
  UPDATING_PRODUCT = "UPDATING_PRODUCT",
  UPDATING_PRODUCT_SUCCESS = "UPDATING_PRODUCT_SUCCESS",
  UPDATING_PRODUCT_ERROR = "UPDATING_PRODUCT_ERROR";

export default {
  namespaced: true,
  state: {
    isLoading: false,
    error: null,
    products: []
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
    hasProducts(state) {
      return state.products.length > 0;
    },
    products(state) {
      return state.products;
    }
  },
  mutations: {
    [CREATING_PRODUCT](state) {
      state.isLoading = true;
      state.error = null;
    },
    [CREATING_PRODUCT_SUCCESS](state, product) {
      state.isLoading = false;
      state.error = null;
      state.products.unshift(product);
    },
    [CREATING_PRODUCT_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.products = [];
    },
    [DELETING_PRODUCT](state) {
      state.isLoading = true;
      state.error = null;
    },
    [DELETING_PRODUCT_SUCCESS](state, product) {
      state.isLoading = false;
      state.error = null;
      var foundKeys = state.products.findIndex(function (item) {
        return item.id == product.id;
      })
      if (typeof foundKeys != "undefined") state.products.splice(foundKeys, 1);
    },
    [DELETING_PRODUCT_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.products = [];
    },
    [FETCHING_PRODUCTS](state) {
      state.isLoading = true;
      state.error = null;
      state.products = [];
    },
    [FETCHING_PRODUCTS_SUCCESS](state, products) {
      state.isLoading = false;
      state.error = null;
      state.products = products;
    },
    [FETCHING_PRODUCTS_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.products = [];
    },
    [UPDATING_PRODUCT](state) {
      state.isLoading = true;
      state.error = null;
    },
    [UPDATING_PRODUCT_SUCCESS](state, product) {
      state.isLoading = false;
      state.error = null;
      var foundKeys = state.products.findIndex(function (item) {
        return item.id == product.id;
      })
      if (typeof foundKeys != "undefined") state.products[foundKeys] = product;
    },
    [UPDATING_PRODUCT_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.products = [];
    },
  },
  actions: {
    async create({ commit }, payload) {
      commit(CREATING_PRODUCT);
      try {
        let response = await ProductAPI.create(payload.name, payload.price);
        commit(CREATING_PRODUCT_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(CREATING_PRODUCT_ERROR, error);
        return null;
      }
    },
    async delete({ commit }, payload) {
      commit(DELETING_PRODUCT);
      try {
        let response = await ProductAPI.delete(payload.id);
        commit(DELETING_PRODUCT_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(DELETING_PRODUCT_ERROR, error);
        return null;
      }
    },
    async findAll({ commit }) {
      commit(FETCHING_PRODUCTS);
      try {
        let response = await ProductAPI.findAll();
        commit(FETCHING_PRODUCTS_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(FETCHING_PRODUCTS_ERROR, error);
        return null;
      }
    },
    async update({ commit }, payload) {
      commit(UPDATING_PRODUCT);
      try {
        let response = await ProductAPI.update(payload.id, payload.name, payload.price);
        commit(UPDATING_PRODUCT_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(UPDATING_PRODUCT_ERROR, error);
        return null;
      }
    },
  }
};
