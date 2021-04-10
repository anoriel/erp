<template>
  <div class="card w-100 mt-2">
    <div class="card-body">
      <span
        v-if="hasProvider"
        class="badge badge-primary float-right margin5"
      >
        Purchase
      </span>
      <span
        v-if="hasCustomer"
        class="badge badge-success float-right margin5"
      >
        Sale
      </span>
      <h5 class="card-title">
        Product:
        {{ transaction.product.name }}
      </h5>
      <p
        v-if="hasCustomer"
        class="card-text"
      >
        Sent to:
        {{ getCustomer }}
      </p>
      <p
        v-if="hasProvider"
        class="card-text"
      >
        Bought to:
        {{ getProvider }}
      </p>
      <p class="card-text">
        Quantity:
        {{ transaction.quantity }}
      </p>
      <p class="card-text">
        Total:
        {{ getTotal }}&euro;
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: "Transaction",
  props: {
    transaction: {
      type: Object,
      required: true
    },
  },
  data: function () {
    return {
    };
  },
  computed: {
    getCustomer() {
      return this.hasCustomer ? this.transaction.customer.name : "";
    },
    getProvider() {
      return this.hasProvider ? this.transaction.provider.name : "";
    },
    getTotal() {
      return this.transaction.quantity * this.transaction.product.price;
    },
    hasCustomer() {
      return null != this.transaction.customer && "name" in this.transaction.customer;
    },
    hasProvider() {
      return null != this.transaction.provider && "name" in this.transaction.provider;
    },
    isLoading() {
      return this.$store.getters["transaction/isLoading"];
    },
  }
};
</script>