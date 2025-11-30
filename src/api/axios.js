import axios from "axios";

const api = axios.create({
  baseURL: "https://re-teech.my.id/helka/api", // URL API Laravel kamu
  headers: {
    "Content-Type": "application/json",
  },
});

// Tambahkan interceptor untuk attach token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token"); // simpan token di localStorage
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if (error.response?.status === 401) {
      // Token expired atau invalid
      localStorage.removeItem("token");
      window.location.href = "/login";
    }
    return Promise.reject(error);
  }
);

export default api;
