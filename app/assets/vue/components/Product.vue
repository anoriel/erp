<template>
  <div class="card w-100 mt-2">
    <div class="card-body parentShow">
      <i
        v-if="!isUpdating"
        class="fas fa-trash-alt float-right cursorPointer childShow margin5"
        @click="deleteProduct()"
      />
      <i
        v-if="!isUpdating"
        class="fas fa-edit float-right cursorPointer childShow margin5"
        @click="editProduct()"
      />
      <div class="row">
        <div class="col-2">
          Reference:
        </div>
        <div class="col-10">
          <h5
            v-if="!isUpdating"
            class="card-title"
          >
            {{ getName }}
          </h5>
          <input
            v-if="isUpdating"
            v-model="getName"
            type="text"
            class="form-control"
          >
        </div>
      </div>
      <div class="row">
        <div class="col-2">
          Price:
        </div>
        <div class="col-10">
          <p
            v-if="!isUpdating"
            class="card-text"
          >
            {{ getPrice }}&euro;
          </p>
          <input
            v-if="isUpdating"
            v-model="getPrice"
            type="number"
            class="form-control"
            min="0"
            max="100"
          >
        </div>
      </div>
      <div
        v-if="isUpdating"
        class="row"
      >
        <div class="card-body">
          <button
            :disabled="getName.length === 0 || getPrice.length === 0 || isLoading"
            type="button"
            class="btn btn-primary"
            @click="updateProduct()"
          >
            Update
          </button>
          <button
            :disabled="isLoading"
            type="button"
            class="btn btn-danger"
            @click="cancel()"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Product",
  props: {
    product: {
      type: Object,
      required: true
    },
  },
  data: function () {
    return {
      getName: this.product.name,
      getPrice: this.product.price,
      isUpdating: false,
    };
  },
  computed: {
    isLoading() {
      return this.$store.getters["product/isLoading"];
    },
  },
  methods: {
    editProduct() {
      this.isUpdating = true;
    },
    async updateProduct() {
      let payload = { id: this.product.id, name: this.getName, price: this.getPrice };

      const result = await this.$store.dispatch("product/update", payload);
      if (result !== null) {
        this.isUpdating = false;
      }
    },
    async deleteProduct() {
      let payload = { id: this.product.id };

      const result = await this.$store.dispatch("product/delete", payload);
      if (result !== null) {
        this.isUpdating = false;
      }
    },
    cancel() {
      this.isUpdating = false;
    },
  }
};
</script>