import ProviderAPI from "../api/provider";

const CREATING_PROVIDER = "CREATING_PROVIDER",
  CREATING_PROVIDER_SUCCESS = "CREATING_PROVIDER_SUCCESS",
  CREATING_PROVIDER_ERROR = "CREATING_PROVIDER_ERROR",
  DELETING_PROVIDER = "DELETING_PROVIDER",
  DELETING_PROVIDER_SUCCESS = "DELETING_PROVIDER_SUCCESS",
  DELETING_PROVIDER_ERROR = "DELETING_PROVIDER_ERROR",
  FETCHING_PROVIDERS = "FETCHING_PROVIDERS",
  FETCHING_PROVIDERS_SUCCESS = "FETCHING_PROVIDERS_SUCCESS",
  FETCHING_PROVIDERS_ERROR = "FETCHING_PROVIDERS_ERROR",
  UPDATING_PROVIDER = "UPDATING_PROVIDER",
  UPDATING_PROVIDER_SUCCESS = "UPDATING_PROVIDER_SUCCESS",
  UPDATING_PROVIDER_ERROR = "UPDATING_PROVIDER_ERROR";

export default {
  namespaced: true,
  state: {
    isLoading: false,
    error: null,
    providers: []
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
    hasProviders(state) {
      return state.providers.length > 0;
    },
    providers(state) {
      return state.providers;
    }
  },
  mutations: {
    [CREATING_PROVIDER](state) {
      state.isLoading = true;
      state.error = null;
    },
    [CREATING_PROVIDER_SUCCESS](state, provider) {
      state.isLoading = false;
      state.error = null;
      state.providers.unshift(provider);
    },
    [CREATING_PROVIDER_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.providers = [];
    },
    [DELETING_PROVIDER](state) {
      state.isLoading = true;
      state.error = null;
    },
    [DELETING_PROVIDER_SUCCESS](state, provider) {
      state.isLoading = false;
      state.error = null;
      var foundKeys = state.providers.findIndex(function (item) {
        return item.id == provider.id;
      })
      if (typeof foundKeys != "undefined") state.providers.splice(foundKeys, 1);
    },
    [DELETING_PROVIDER_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.providers = [];
    },
    [FETCHING_PROVIDERS](state) {
      state.isLoading = true;
      state.error = null;
      state.providers = [];
    },
    [FETCHING_PROVIDERS_SUCCESS](state, providers) {
      state.isLoading = false;
      state.error = null;
      state.providers = providers;
    },
    [FETCHING_PROVIDERS_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.providers = [];
    },
    [UPDATING_PROVIDER](state) {
      state.isLoading = true;
      state.error = null;
    },
    [UPDATING_PROVIDER_SUCCESS](state, provider) {
      state.isLoading = false;
      state.error = null;
      var foundKeys = state.providers.findIndex(function (item) {
        return item.id == provider.id;
      })
      if (typeof foundKeys != "undefined") state.providers[foundKeys] = provider;
    },
    [UPDATING_PROVIDER_ERROR](state, error) {
      state.isLoading = false;
      state.error = error;
      state.providers = [];
    },
  },
  actions: {
    async create({ commit }, payload) {
      commit(CREATING_PROVIDER);
      try {
        let response = await ProviderAPI.create(payload.name, payload.address, payload.country);
        commit(CREATING_PROVIDER_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(CREATING_PROVIDER_ERROR, error);
        return null;
      }
    },
    async delete({ commit }, payload) {
      commit(DELETING_PROVIDER);
      try {
        let response = await ProviderAPI.delete(payload.id);
        commit(DELETING_PROVIDER_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(DELETING_PROVIDER_ERROR, error);
        return null;
      }
    },
    async findAll({ commit }) {
      commit(FETCHING_PROVIDERS);
      try {
        let response = await ProviderAPI.findAll();
        commit(FETCHING_PROVIDERS_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(FETCHING_PROVIDERS_ERROR, error);
        return null;
      }
    },
    async update({ commit }, payload) {
      commit(UPDATING_PROVIDER);
      try {
        let response = await ProviderAPI.update(payload.id, payload.name, payload.address, payload.country);
        commit(UPDATING_PROVIDER_SUCCESS, response.data);
        return response.data;
      } catch (error) {
        commit(UPDATING_PROVIDER_ERROR, error);
        return null;
      }
    },
  }
};
