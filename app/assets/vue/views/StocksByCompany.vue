<template>
  <div>
    <div class="row col">
      <h1>Stock by company</h1>
    </div>

    <div class="card border-primary margin5">
      <div class="card-header">
        Explanations
      </div>
      <div class="card-body text-primary">
        <p class="card-text">
          On this page, you can see the product stocks per Company.
        </p>
      </div>
    </div>

    <div class="row">
      <label>Select Company:</label>
      <select
        v-model="company"
        @change="getStocksByCompany()"
      >
        <option
          v-for="item in companies"
          :key="item.id"
          :value="item"
        >
          {{ item.name }}
        </option>
      </select>
    </div>

    <div v-if="company">
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
        v-else-if="!hasStocksByCompany"
        class="row col"
      >
        No stocksByCompany!
      </div>

      <div
        v-for="(stockByCompany, key) in stocksByCompany"
        v-else
        :key="key"
        class="row col"
      >
        <stockByCompany :stock-by-company="stockByCompany" />
      </div>
    </div>
  </div>
</template>

<script>
import StockByCompany from "../components/StockByCompany";
import ErrorMessage from "../components/ErrorMessage";

export default {
  name: "StocksByCompany",
  components: {
    StockByCompany,
    ErrorMessage
  },
  data() {
    return {
      company: null,
      name: "",
      price: "",
      stock: "",
    };
  },
  computed: {
    companies() {
      return this.$store.getters["company/companies"];
    },
    isLoading() {
      return this.$store.getters["stockByCompany/isLoading"];
    },
    hasError() {
      return this.$store.getters["stockByCompany/hasError"];
    },
    error() {
      return this.$store.getters["stockByCompany/error"];
    },
    hasStocksByCompany() {
      return this.$store.getters["stockByCompany/hasStocksByCompany"];
    },
    stocksByCompany() {
      return this.$store.getters["stockByCompany/stocksByCompany"];
    },
    canCreate() {
      return this.$store.getters["security/hasRole"]("ROLE_ADMIN");
    }
  },
  created() {
    this.$store.dispatch("company/findAll");
  },
  methods: {
    async getStocksByCompany() {
      this.$store.dispatch("stockByCompany/findByCompany", { id: "id" in this.company ? this.company.id : null });
    },
  }
};
</script>