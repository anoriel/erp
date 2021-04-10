<template>
  <div class="card w-100 mt-2">
    <div
      v-if="!isUpdating"
      class="card-body parentShow"
    >
      <i
        class="fas fa-trash-alt float-right cursorPointer childShow margin5"
        @click="deleteCustomer()"
      />
      <i
        class="fas fa-edit float-right cursorPointer childShow margin5"
        @click="editCustomer()"
      />
      <h5 class="card-title">
        {{ getName }}
      </h5>
      <p class="card-text">
        {{ getAddress }}
      </p>
      <p class="card-text">
        {{ getCountry }}
      </p>
    </div>

    <div
      v-if="isUpdating"
      class="card-body"
    >
      <input
        v-model="getName"
        type="text"
        class="form-control"
      >
      <input
        v-model="getAddress"
        type="text"
        class="form-control"
      >
      <input
        v-model="getCountry"
        type="text"
        class="form-control"
      >
      <button
        :disabled="getName.length === 0 || getAddress.length === 0 || getCountry.length === 0 || isLoading"
        type="button"
        class="btn btn-primary"
        @click="updateCustomer()"
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
</template>

<script>
export default {
  name: "Customer",
  props: {
    customer: {
      type: Object,
      required: true
    },
  },
  data: function () {
    return {
      getName: this.customer.name,
      getAddress: this.customer.address,
      getCountry: this.customer.country,
      isUpdating: false,
    };
  },
  computed: {
    isLoading() {
      return this.$store.getters["customer/isLoading"];
    },
  },
  methods: {
    editCustomer() {
      this.isUpdating = true;
    },
    async updateCustomer() {
      let payload = { id: this.customer.id, name: this.getName, address: this.getAddress, country: this.getCountry };

      const result = await this.$store.dispatch("customer/update", payload);
      if (result !== null) {
        this.isUpdating = false;
      }
    },
    async deleteCustomer() {
      let payload = { id: this.customer.id };

      const result = await this.$store.dispatch("customer/delete", payload);
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