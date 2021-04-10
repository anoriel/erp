import axios from "axios";

export default {
  create(companyId, customerId, providerId, productId, quantity) {
    return axios.post("/api/transactions", {
      companyId: companyId,
      customerId: customerId,
      productId: productId,
      quantity: quantity,
      providerId: providerId
    });
  },
  update(id, companyId, customerId, providerId, productId, quantity) {
    return axios.put("/api/transactions/" + id, {
      companyId: companyId,
      customerId: customerId,
      productId: productId,
      quantity: quantity,
      providerId: providerId
    });
  },
  delete(id) {
    return axios.delete("/api/transactions/" + id);
  },
  findAll() {
    return axios.get("/api/transactions");
  },
  findByCompany(id) {
    return axios.get("/api/transactions/getByCompany/" + id);
  },
};
