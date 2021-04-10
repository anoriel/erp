<template>
  <div>
    <div class="row col">
      <h1>Transactions</h1>
    </div>

    <div class="card border-primary margin5">
      <div class="card-header">
        Explanations
      </div>
      <div class="card-body text-primary">
        <p class="card-text">
          On this page, you can add sale and purchase documents per Company.
        </p>
        <p class="card-text">
          When you sell something, stock is decreased for this product/company and balance is increased of the total amount (quantity * product price).
        </p>
        <p class="card-text">
          When you buy something, stock is increased and balance is decreased.
        </p>
      </div>
    </div>

    <div class="row">
      <label>Select Company:</label>
      <select
        v-model="company"
        @change="getTransactionsByCompany()"
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
      <b-button
        ref="btnShow"
        class="float-right margin5"
        variant="primary"
        @click="buyAProduct"
      >
        Buy a product
      </b-button>
      <b-button
        ref="btnShow"
        class="float-right margin5"
        variant="success"
        @click="sellAProduct"
      >
        Sell a product
      </b-button>

      <b-modal
        id="modal-1"
        :title="getModalTitle()"
        hide-footer
        class="row col"
      >
        <form>
          <div class="row">
            <div class="col-3">
              <label>Product</label>
            </div>
            <div class="col-9">
              <select
                id="productSelect"
                v-model="product"
                @change="changeProduct()"
              >
                <option
                  v-for="item in products"
                  :key="item.id"
                  :value="item"
                >
                  {{ item.name }}
                </option>
              </select>
              <span v-if="product!=null">
                (available stock: {{ getMaxStock() }})
              </span>
            </div>
          </div>
          <div
            v-if="!isASale"
            class="row"
          >
            <div class="col-3">
              <label>Provider</label>
            </div>
            <div class="col-9">
              <select v-model="provider">
                <option
                  v-for="item in providers"
                  :key="item.id"
                  :value="item"
                >
                  {{ item.name }}
                </option>
              </select>
            </div>
          </div>
          <div
            v-if="isASale"
            class="row"
          >
            <div class="col-3">
              <label>Customer</label>
            </div>
            <div class="col-9">
              <select v-model="customer">
                <option
                  v-for="item in customers"
                  :key="item.id"
                  :value="item"
                >
                  {{ item.name }}
                </option>
              </select>
            </div>
          </div>
          <div
            v-if="product!=null"
            class="row"
          >
            <div class="col-3">
              <label>Price</label>
            </div>
            <div class="col-9">
              {{ product.price }}&euro;
            </div>
          </div>
          <div class="row">
            <div class="col-3">
              <label>Quantity</label>
            </div>
            <div class="col-9">
              <input
                v-model="quantity"
                :disabled="disableQuantity()"
                type="number"
                class="form-control"
                min="0"
                :max="getMaxStock()"
              >
            </div>
          </div>
          <div
            v-if="product!=null"
            class="row"
          >
            <div class="col-3">
              <label>Total</label>
            </div>
            <div class="col-9">
              {{ getTotal() }}&euro;
            </div>
          </div>
        </form>
        <b-button
          :disabled="isLoading || quantity == 0"
          type="button"
          class="btn btn-success margin5 float-right"
          @click="createTransaction()"
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
        v-else-if="!hasTransactions"
        class="row col"
      >
        No transactions!
      </div>

      <div
        v-for="transaction in transactions"
        v-else
        :id="transaction.id"
        :key="transaction.id"
        class="row col"
      >
        <transaction :transaction="transaction" />
      </div>
    </div>
  </div>
</template>

<script>
import Transaction from "../components/Transaction";
import ErrorMessage from "../components/ErrorMessage";

export default {
  name: "Transactions",
  components: {
    Transaction,
    ErrorMessage
  },
  data() {
    return {
      company: null,
      customer: null,
      provider: null,
      product: null,
      quantity: 0,
      isASale: false,
    };
  },
  computed: {
    companies() {
      return this.$store.getters["company/companies"];
    },
    customers() {
      return this.$store.getters["customer/customers"];
    },
    isLoading() {
      return this.$store.getters["transaction/isLoading"];
    },
    hasError() {
      return this.$store.getters["transaction/hasError"];
    },
    error() {
      return this.$store.getters["transaction/error"];
    },
    hasTransactions() {
      return this.$store.getters["transaction/hasTransactions"];
    },
    providers() {
      return this.$store.getters["provider/providers"];
    },
    products() {
      return this.$store.getters["product/products"];
    },
    stocksByCompany() {
      return this.$store.getters["stockByCompany/stocksByCompany"];
    },
    transactions() {
      return this.$store.getters["transaction/transactions"];
    },
    canCreate() {
      return this.$store.getters["security/hasRole"]("ROLE_ADMIN");
    }
  },
  created() {
    this.$store.dispatch("customer/findAll");
    this.$store.dispatch("provider/findAll");
    this.$store.dispatch("product/findAll");
    this.$store.dispatch("company/findAll");
  },
  methods: {
    buyAProduct() {
      this.reset();
      this.isASale = false;
      this.showModal();
    },
    changeProduct() {
      this.quantity = 0;
    },
    async createTransaction() {
      let payload = {
        companyId: this.company != null && "id" in this.company ? this.company.id : null,
        customerId: this.customer != null && "id" in this.customer ? this.customer.id : null,
        providerId: this.provider != null && "id" in this.provider ? this.provider.id : null,
        productId: this.product.id,
        quantity: this.quantity
      };

      const result = await this.$store.dispatch("transaction/create", payload);
      if (result !== null) {
        this.$root.$emit('bv::hide::modal', 'modal-1', '#btnShow')
        this.customer = null;
        this.provider = null;
        this.product = null;
        this.quantity = 0;
        this.$store.dispatch("stockByCompany/findByCompany", { id: "id" in this.company ? this.company.id : null });
      }
    },
    disableQuantity() {
      if (this.isASale) {
        return this.product == null || this.customer == null;
      }
      return this.product == null || this.provider == null;
    },
    getMaxStock() {
      if (null == this.product) return 0;

      if (this.isASale) {
        var id = this.product.id;
        var stockProduct = this.stocksByCompany.find(function (item) {
          return item.product.id == id;
        })
        return stockProduct != null ? stockProduct.stock : 0;
      }

      return Math.floor(this.company.balance / this.product.price);
    },
    getModalTitle() {
      if (this.isASale) return "Sale a product to a customer";
      return "Buy a product from a provider";
    },
    getTotal() {
      return this.quantity * (this.product != null ? this.product.price : 0);
    },
    async getTransactionsByCompany() {
      this.$store.dispatch("stockByCompany/findByCompany", { id: "id" in this.company ? this.company.id : null });
      this.$store.dispatch("transaction/findByCompany", { id: "id" in this.company ? this.company.id : null });
    },
    hideModal() {
      this.$root.$emit('bv::hide::modal', 'modal-1', '#btnShow')
    },
    reset() {
      this.customer = null;
      this.maxStock = 5;
      this.provider = null;
      this.product = null;
      this.quantity = 0;
    },
    sellAProduct() {
      this.reset();
      this.isASale = true;
      this.showModal();
    },
    showModal() {
      this.$root.$emit('bv::show::modal', 'modal-1', '#btnShow')
    },
  }
};
</script>