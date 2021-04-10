<template>
  <div class="card w-100 mt-2">
    <div
      v-if="!isUpdating"
      class="card-body parentShow"
    >
      <i
        class="fas fa-trash-alt float-right cursorPointer childShow margin5"
        @click="deleteProvider()"
      />
      <i
        class="fas fa-edit float-right cursorPointer childShow margin5"
        @click="editProvider()"
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
        @click="updateProvider()"
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
  name: "Provider",
  props: {
    provider: {
      type: Object,
      required: true
    },
  },
  data: function () {
    return {
      getName: this.provider.name,
      getAddress: this.provider.address,
      getCountry: this.provider.country,
      isUpdating: false,
    };
  },
  computed: {
    isLoading() {
      return this.$store.getters["provider/isLoading"];
    },
  },
  methods: {
    editProvider() {
      this.isUpdating = true;
    },
    async updateProvider() {
      let payload = { id: this.provider.id, name: this.getName, address: this.getAddress, country: this.getCountry };

      const result = await this.$store.dispatch("provider/update", payload);
      if (result !== null) {
        this.isUpdating = false;
      }
    },
    async deleteProvider() {
      let payload = { id: this.provider.id };

      const result = await this.$store.dispatch("provider/delete", payload);
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