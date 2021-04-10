import CompanyAPI from "../api/company";

const CREATING_COMPANY = "CREATING_COMPANY",
  CREATING_COMPANY_SUCCESS = "CREATING_COMPANY_SUCCESS",
  CREATING_COMPANY_ERROR = "CREATING_COMPANY_ERROR",
  DELETING_COMPANY = "DELETING_COMPANY",
  DELETING_COMPANY_SUCCESS = "DELETING_COMPANY_SUCCESS",
  DELETING_COMPANY_ERROR = "DELETING_COMPANY_ERROR",
  UPDATING_COMPANY = "UPDATING_COMPANY",
  UPDATING_COMPANY_SUCCESS = "UPDATING_COMPANY_SUCCESS",
  UPDATING_COMPANY_ERROR = "UPDATING_COMPANY_ERROR",
  FETCHING_COMPANIES = "FETCHING_COMPANIES",
  FETCHING_COMPANIES_SUCCESS = "FETCHING_COMPANIES_SUCCESS",
  FETCHING_COMPANIES_ERROR = "FETCHING_COMPANIES_ERROR";

export default {
  namespaced: true,
  state: {
    isLoading: false,
    error: null,
    companies: []
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
    hasCompanies(state) {
      return state.companies.length > 0;
    },
    companies(state) {
      return state.companies;
    }
  },
  mutations: {
    [CREATING_COMPANY](state) {
      state.isLoading = true;
      state.error = null;
    },
    [CREATING_COMPANY_SUCCESS](state, company) {
      state.isLoading = false;
      state.error = null;
      state.companies.unshift(company);
    },
    [CREATING_COMPANY_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.companies = [];
    },
    [DELETING_COMPANY](state) {
      state.isLoading = true;
      state.error = null;
    },
    [DELETING_COMPANY_SUCCESS](state, company) {
      state.isLoading = false;
      state.error = null;
      var foundKeys = state.companies.findIndex(function (item) {
        return item.id == company.id;
      })
      if (typeof foundKeys != "undefined") state.companies.splice(foundKeys, 1);
    },
    [DELETING_COMPANY_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.companies = [];
    },
    [UPDATING_COMPANY](state) {
      state.isLoading = true;
      state.error = null;
    },
    [UPDATING_COMPANY_SUCCESS](state, company) {
      state.isLoading = false;
      state.error = null;
      var foundKeys = state.companies.findIndex(function (item) {
        return item.id == company.id;
      })
      if (typeof foundKeys != "undefined") state.companies[foundKeys] = company;
    },
    [UPDATING_COMPANY_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.companies = [];
    },
    [FETCHING_COMPANIES](state) {
      state.isLoading = true;
      state.error = null;
      state.companies = [];
    },
    [FETCHING_COMPANIES_SUCCESS](state, companies) {
      state.isLoading = false;
      state.error = null;
      state.companies = companies;
    },
    [FETCHING_COMPANIES_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.companies = [];
    }
  },
  actions: {
    async create({ commit }, payload) {
      commit(CREATING_COMPANY);
      try {
        let response = await CompanyAPI.create(payload.name, payload.balance, payload.country);
        commit(CREATING_COMPANY_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(CREATING_COMPANY_ERROR, error);
        return null;
      }
    },
    async delete({ commit }, payload) {
      commit(DELETING_COMPANY);
      try {
        let response = await CompanyAPI.delete(payload.id);
        commit(DELETING_COMPANY_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(DELETING_COMPANY_ERROR, error);
        return null;
      }
    },
    async findAll({ commit }) {
      commit(FETCHING_COMPANIES);
      try {
        let response = await CompanyAPI.findAll();
        commit(FETCHING_COMPANIES_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(FETCHING_COMPANIES_ERROR, error);
        return null;
      }
    },
    async update({ commit }, payload) {
      commit(UPDATING_COMPANY);
      try {
        let response = await CompanyAPI.update(payload.id, payload.name, payload.balance, payload.country);
        commit(UPDATING_COMPANY_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(UPDATING_COMPANY_ERROR, error);
        return null;
      }
    },
  }
};
