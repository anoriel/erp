<template>
  <div>
    <div class="row col">
      <h1>Products</h1>
    </div>

    <div class="card border-primary margin5">
      <div class="card-header">
        Explanations
      </div>
      <div class="card-body text-primary">
        <p class="card-text">
          On this page, you can add / edit / delete products.
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
      title="Submit a new product"
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
            <label>Price</label>
            <input
              v-model="price"
              type="number"
              class="form-control"
              min="0"
              max="100"
            >
          </div>
        </div>
      </form>
      <b-button
        :disabled="name.length === 0 || price.length === 0 || isLoading"
        type="button"
        class="btn btn-success margin5 float-right"
        @click="createProduct()"
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
      v-else-if="!hasProducts"
      class="row col"
    >
      No products!
    </div>

    <div
      v-for="(product, key) in products"
      v-else
      :key="key"
      class="row col"
    >
      <product :product="product" />
    </div>
  </div>
</template>

<script>
import Product from "../components/Product";
import ErrorMessage from "../components/ErrorMessage";

export default {
  name: "Products",
  components: {
    Product,
    ErrorMessage
  },
  data() {
    return {
      name: "",
      price: "",
    };
  },
  computed: {
    isLoading() {
      return this.$store.getters["product/isLoading"];
    },
    hasError() {
      return this.$store.getters["product/hasError"];
    },
    error() {
      return this.$store.getters["product/error"];
    },
    hasProducts() {
      return this.$store.getters["product/hasProducts"];
    },
    products() {
      return this.$store.getters["product/products"];
    },
    canCreate() {
      return this.$store.getters["security/hasRole"]("ROLE_ADMIN");
    }
  },
  created() {
    this.$store.dispatch("product/findAll");
  },
  methods: {
    async createProduct() {
      let payload = { name: this.$data.name, price: this.$data.price };

      const result = await this.$store.dispatch("product/create", payload);
      if (result !== null) {
        this.$root.$emit('bv::hide::modal', 'modal-1', '#btnShow')
        this.$data.name = "";
        this.$data.price = 0;
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