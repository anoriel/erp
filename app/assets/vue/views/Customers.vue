<template>
  <div>
    <div class="row col">
      <h1>Customers</h1>
    </div>

    <div class="card border-primary margin5">
      <div class="card-header">
        Explanations
      </div>
      <div class="card-body text-primary">
        <p class="card-text">
          On this page, you can add / edit / delete customers.
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
      title="Submit a new customer"
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
            <label>Address</label>
            <input
              v-model="address"
              type="text"
              class="form-control"
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
        :disabled="name.length === 0 || address.length === 0 || country.length === 0 || isLoading"
        type="button"
        class="btn btn-success margin5 float-right"
        @click="createCustomer()"
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
      v-else-if="!hasCustomers"
      class="row col"
    >
      No customers!
    </div>

    <div
      v-for="(customer, key) in customers"
      v-else
      :key="key"
      class="row col"
    >
      <customer :customer="customer" />
    </div>
  </div>
</template>

<script>
import Customer from "../components/Customer";
import ErrorMessage from "../components/ErrorMessage";

export default {
  name: "Customers",
  components: {
    Customer,
    ErrorMessage
  },
  data() {
    return {
      name: "",
      address: "",
      country: "",
    };
  },
  computed: {
    isLoading() {
      return this.$store.getters["customer/isLoading"];
    },
    hasError() {
      return this.$store.getters["customer/hasError"];
    },
    error() {
      return this.$store.getters["customer/error"];
    },
    hasCustomers() {
      return this.$store.getters["customer/hasCustomers"];
    },
    customers() {
      return this.$store.getters["customer/customers"];
    },
    canCreate() {
      return this.$store.getters["security/hasRole"]("ROLE_ADMIN");
    }
  },
  created() {
    this.$store.dispatch("customer/findAll");
  },
  methods: {
    async createCustomer() {
      let payload = { name: this.$data.name, address: this.$data.address, country: this.$data.country };

      const result = await this.$store.dispatch("customer/create", payload);
      if (result !== null) {
        this.$root.$emit('bv::hide::modal', 'modal-1', '#btnShow')
        this.$data.name = "";
        this.$data.address = 0;
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