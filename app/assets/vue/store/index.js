import Vue from "vue";
import Vuex from "vuex";
import CompanyModule from "./company";
import CustomerModule from "./customer";
import ProductModule from "./product";
import ProviderModule from "./provider";
import SecurityModule from "./security";
import StockByCompanyModule from "./stockByCompany";
import TransactionModule from "./transaction";

Vue.use(Vuex);

export default new Vuex.Store({
  modules: {
    security: SecurityModule,
    company: CompanyModule,
    customer: CustomerModule,
    provider: ProviderModule,
    stockByCompany: StockByCompanyModule,
    transaction: TransactionModule,
    product: ProductModule
  }
});
