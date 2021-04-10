<template>
  <div>
    <div class="row col">
      <h1>Companies</h1>
    </div>
    
    <div class="card border-primary margin5">
      <div class="card-header">
        Explanations
      </div>
      <div class="card-body text-primary">
        <p class="card-text">
          On this page, you can add / edit / delete companies.
        </p>
      </div>
    </div>

    <b-button
      ref="btnShow"
      class="float-right"
      variant="primary"
      @click="showModal"
    >
      Create a new one
    </b-button>
    <b-modal
      id="modal-1"
      title="Submit a new company"
      hide-footer
      class="row col"
    >
      <form>
        <div class="form-row">
          <div class="col-3">
            <label>Name</label>
            <input
              v-model="name"
              type="text"
              class="form-control"
            >
          </div>
          <div class="col-3">
            <label>Balance (&euro;)</label>
            <input
              v-model="balance"
              type="number"
              class="form-control"
              min="0"
              max="100"
            >
          </div>
          <div class="col-3">
            <label>Country</label>
            <input
              v-model="country"
              type="text"
              class="form-control"
            >
          </div>
        </div>
      </form>
      <b-button
        :disabled="name.length === 0 || balance.length === 0 || country.length === 0 || isLoading"
        type="button"
        class="btn btn-success margin5 float-right"
        @click="createCompany()"
      >
        Create
      </b-button>
      <b-button
        :disabled="isLoading"
        type="button"
        class="btn btn-danger margin5 float-right"
        @click="hideModal()"
      >
        Cancel
      </b-button>
    </b-modal>

    <div
      v-if="isLoading"
      class="row col"
    >
      <p>Loading...</p>
    </div>

    <div
      v-else-if="hasError"
      class="row col"
    >
      <error-message :error="error" />
    </div>

    <div
      v-else-if="!hasCompanies"
      class="row col"
    >
      No companies!
    </div>

    <div
      v-for="company in companies"
      v-else
      :key="company.id"
      class="row col"
    >
      <company :company="company" />
    </div>
  </div>
</template>

<script>
import Company from "../components/Company";
import ErrorMessage from "../components/ErrorMessage";

export default {
  name: "Companies",
  components: {
    Company,
    ErrorMessage
  },
  data() {
    return {
      name: "",
      balance: "",
      country: "",
    };
  },
  computed: {
    isLoading() {
      return this.$store.getters["company/isLoading"];
    },
    hasError() {
      return this.$store.getters["company/hasError"];
    },
    error() {
      return this.$store.getters["company/error"];
    },
    hasCompanies() {
      return this.$store.getters["company/hasCompanies"];
    },
    companies() {
      return this.$store.getters["company/companies"];
    },
    canCreate() {
      return this.$store.getters["security/hasRole"]("ROLE_ADMIN");
    }
  },
  created() {
    this.$store.dispatch("company/findAll");
  },
  methods: {
    async createCompany() {
      let payload = { name: this.$data.name, balance: this.$data.balance, country: this.$data.country };

      const result = await this.$store.dispatch("company/create", payload);
      if (result !== null) {
        this.$root.$emit('bv::hide::modal', 'modal-1', '#btnShow')
        this.$data.name = "";
        this.$data.balance = 0;
        this.$data.country = "";
      }
    },
    showModal() {
      this.$root.$emit('bv::show::modal', 'modal-1', '#btnShow')
    },
    hideModal() {
      this.$root.$emit('bv::hide::modal', 'modal-1', '#btnShow')
    },
  }
};
</script>