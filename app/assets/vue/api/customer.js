import axios from "axios";

export default {
  create(name, address, country) {
    return axios.post("/api/customers", {
      name: name,
      address: address,
      country: country
    });
  },
  update(id, name, address, country) {
    return axios.put("/api/customers/" + id, {
      name: name,
      address: address,
      country: country
    });
  },
  delete(id) {
    return axios.delete("/api/customers/" + id);
  },
  findAll() {
    return axios.get("/api/customers");
  }
};
