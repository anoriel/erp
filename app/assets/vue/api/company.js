import axios from "axios";

export default {
  create(name, balance, country) {
    return axios.post("/api/companies", {
      name: name,
      balance: balance,
      country: country
    });
  },
  update(id, name, balance, country) {
    return axios.put("/api/companies/" + id, {
      name: name,
      balance: balance,
      country: country
    });
  },
  delete(id) {
    return axios.delete("/api/companies/" + id);
  },
  findAll() {
    return axios.get("/api/companies");
  }
};
