import axios from "axios";

export default {
  findByCompany(id) {
    return axios.get("/api/stocksByCompany/getByCompany/" + id);
  },
};
