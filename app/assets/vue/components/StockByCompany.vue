<template>
  <div class="card w-100 mt-2">
    <div class="card-body">
      <h5 class="card-title">
        Product:
        {{ stockByCompany.product.name }}
      </h5>
      <p class="card-text">
        Price:
        {{ stockByCompany.product.price }}&euro;
      </p>
      <p class="card-text">
        Stock:
        {{ stockByCompany.stock }}
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
  name: "StockByCompany",
  props: {
    stockByCompany: {
      type: Object,
      required: true
    },
  },
  data: function () {
    return {
    };
  },
  computed: {
    getStock() {
      return this.stockByCompany.stock;
    },
    getTotal() {
      return this.stockByCompany.stock * this.stockByCompany.product.price;
    },
    isLoading() {
      return this.$store.getters["stockByCompany/isLoading"];
    },
  }
};
</script>