import Vue from "vue";
import VueRouter from "vue-router";
import store from "../store";
import Companies from "../views/Companies";
import Customers from "../views/Customers";
import Home from "../views/Home";
import Login from "../views/Login";
import Products from "../views/Products";
import Providers from "../views/Providers";
import StocksByCompany from "../views/StocksByCompany";
import Transactions from "../views/Transactions";

Vue.use(VueRouter);

let router = new VueRouter({
  mode: "history",
  routes: [
    { path: "/home", component: Home },
    { path: "/login", component: Login },
    { path: "/companies", component: Companies },
    { path: "/customers", component: Customers },
    { path: "/providers", component: Providers },
    { path: "/products", component: Products },
    { path: "/stocksByCompany", component: StocksByCompany },
    { path: "/transactions", component: Transactions },
    { path: "*", redirect: "/home" }
  ]
});


router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.requiresAuth)) {
    // this route requires auth, check if logged in
    // if not, redirect to login page.
    if (store.getters["security/isAuthenticated"]) {
      next();
    } else {
      next({
        path: "/login",
        query: { redirect: to.fullPath }
      });
    }
  } else {
    next(); // make sure to always call next()!
  }
});

export default router;