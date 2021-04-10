import CustomerAPI from "../api/customer";

const CREATING_CUSTOMER = "CREATING_CUSTOMER",
  CREATING_CUSTOMER_SUCCESS = "CREATING_CUSTOMER_SUCCESS",
  CREATING_CUSTOMER_ERROR = "CREATING_CUSTOMER_ERROR",
  DELETING_CUSTOMER = "DELETING_CUSTOMER",
  DELETING_CUSTOMER_SUCCESS = "DELETING_CUSTOMER_SUCCESS",
  DELETING_CUSTOMER_ERROR = "DELETING_CUSTOMER_ERROR",
  FETCHING_CUSTOMERS = "FETCHING_CUSTOMERS",
  FETCHING_CUSTOMERS_SUCCESS = "FETCHING_CUSTOMERS_SUCCESS",
  FETCHING_CUSTOMERS_ERROR = "FETCHING_CUSTOMERS_ERROR",
  UPDATING_CUSTOMER = "UPDATING_CUSTOMER",
  UPDATING_CUSTOMER_SUCCESS = "UPDATING_CUSTOMER_SUCCESS",
  UPDATING_CUSTOMER_ERROR = "UPDATING_CUSTOMER_ERROR";

export default {
  namespaced: true,
  state: {
    isLoading: false,
    error: null,
    customers: []
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
    hasCustomers(state) {
      return state.customers.length > 0;
    },
    customers(state) {
      return state.customers;
    }
  },
  mutations: {
    [CREATING_CUSTOMER](state) {
      state.isLoading = true;
      state.error = null;
    },
    [CREATING_CUSTOMER_SUCCESS](state, customer) {
      state.isLoading = false;
      state.error = null;
      state.customers.unshift(customer);
    },
    [CREATING_CUSTOMER_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.customers = [];
    },
    [DELETING_CUSTOMER](state) {
      state.isLoading = true;
      state.error = null;
    },
    [DELETING_CUSTOMER_SUCCESS](state, customer) {
      state.isLoading = false;
      state.error = null;
      var foundKeys = state.customers.findIndex(function (item) {
        return item.id == customer.id;
      })
      if (typeof foundKeys != "undefined") state.customers.splice(foundKeys, 1);
    },
    [DELETING_CUSTOMER_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.customers = [];
    },
    [FETCHING_CUSTOMERS](state) {
      state.isLoading = true;
      state.error = null;
      state.customers = [];
    },
    [FETCHING_CUSTOMERS_SUCCESS](state, customers) {
      state.isLoading = false;
      state.error = null;
      state.customers = customers;
    },
    [FETCHING_CUSTOMERS_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.customers = [];
    },
    [UPDATING_CUSTOMER](state) {
      state.isLoading = true;
      state.error = null;
    },
    [UPDATING_CUSTOMER_SUCCESS](state, customer) {
      state.isLoading = false;
      state.error = null;
      var foundKeys = state.customers.findIndex(function (item) {
        return item.id == customer.id;
      })
      if (typeof foundKeys != "undefined") state.customers[foundKeys] = customer;
    },
    [UPDATING_CUSTOMER_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.customers = [];
    },
  },
  actions: {
    async create({ commit }, payload) {
      commit(CREATING_CUSTOMER);
      try {
        let response = await CustomerAPI.create(payload.name, payload.address, payload.country);
        commit(CREATING_CUSTOMER_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(CREATING_CUSTOMER_ERROR, error);
        return null;
      }
    },
    async delete({ commit }, payload) {
      commit(DELETING_CUSTOMER);
      try {
        let response = await CustomerAPI.delete(payload.id);
        commit(DELETING_CUSTOMER_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(DELETING_CUSTOMER_ERROR, error);
        return null;
      }
    },
    async findAll({ commit }) {
      commit(FETCHING_CUSTOMERS);
      try {
        let response = await CustomerAPI.findAll();
        commit(FETCHING_CUSTOMERS_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(FETCHING_CUSTOMERS_ERROR, error);
        return null;
      }
    },
    async update({ commit }, payload) {
      commit(UPDATING_CUSTOMER);
      try {
        let response = await CustomerAPI.update(payload.id, payload.name, payload.address, payload.country);
        commit(UPDATING_CUSTOMER_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(UPDATING_CUSTOMER_ERROR, error);
        return null;
      }
    },
  }
};
