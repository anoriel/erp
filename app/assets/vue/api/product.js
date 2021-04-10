import axios from "axios";

export default {
  create(name, price) {
    return axios.post("/api/products", {
      name: name,
      price: price
    });
  },
  update(id, name, price) {
    return axios.put("/api/products/" + id, {
      name: name,
      price: price
    });
  },
  delete(id) {
    return axios.delete("/api/products/" + id);
  },
  findAll() {
    return axios.get("/api/products");
  }
};
