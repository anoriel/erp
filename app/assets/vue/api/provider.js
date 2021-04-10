import axios from "axios";

export default {
  create(name, address, country) {
    return axios.post("/api/providers", {
      name: name,
      address: address,
      country: country
    });
  },
  update(id, name, address, country) {
    return axios.put("/api/providers/" + id, {
      name: name,
      address: address,
      country: country
    });
  },
  delete(id) {
    return axios.delete("/api/providers/" + id);
  },
  findAll() {
    return axios.get("/api/providers");
  }
};
