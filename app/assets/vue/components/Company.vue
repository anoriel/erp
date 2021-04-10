<template>
  <div class="card w-100 mt-2">
    <div
      v-if="!isUpdating"
      class="card-body parentShow"
    >
      <i
        class="fas fa-trash-alt float-right cursorPointer childShow margin5"
        @click="deleteCompany()"
      />
      <i
        class="fas fa-edit float-right cursorPointer childShow margin5"
        @click="editCompany()"
      />
      <h5 class="card-title">
        {{ getName }}
        <span
          v-if="getBalance"
          class="badge badge-pill badge-primary"
        >
          {{ getBalance }}&euro;
        </span>
        <span class="badge badge-secondary">{{ getCountry }}</span>
      </h5>
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
        v-model="getBalance"
        type="number"
        class="form-control"
        min="0"
        max="100"
      >
      <input
        v-model="getCountry"
        type="text"
        class="form-control"
      >
      <button
        :disabled="getName.length === 0 || getBalance.length === 0 || getCountry.length === 0 || isLoading"
        type="button"
        class="btn btn-primary"
        @click="updateCompany()"
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
  name: "Company",
  props: {
    company: {
      type: Object,
      required: true
    },
  },
  data: function () {
    return {
      getName: this.company.name,
      getBalance: this.company.balance,
      getCountry: this.company.country,
      isUpdating: false,
    };
  },
  computed: {
    isLoading() {
      return this.$store.getters["company/isLoading"];
    },
  },
  methods: {
    editCompany() {
      this.isUpdating = true;
    },
    async updateCompany() {
      let payload = { id: this.company.id, name: this.getName, balance: this.getBalance, country: this.getCountry };

      const result = await this.$store.dispatch("company/update", payload);
      if (result !== null) {
        this.isUpdating = false;
      }
    },
    async deleteCompany() {
      let payload = { id: this.company.id };

      const result = await this.$store.dispatch("company/delete", payload);
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