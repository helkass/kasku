import { ref } from "vue";
import api from "@/api/axios";

export function useUser() {
  const users = ref([]);
  const loading = ref(false);
  const total = ref(0);
  const endpoint = "users/";
  const response = ref(null);

  const fetchUsers = async (params = {}) => {
    loading.value = true;
    try {
      const res = await api.get(endpoint, { params });
      users.value = res.data.users;
      total.value = res.data.total; // atau total dari API
    } finally {
      loading.value = false;
    }
  };

  const storeUser = async (data) => {
    loading.value = true;
    try {
      const res = await api.post(endpoint, data);
      console.log("aut");
      response.value = res.data;
      return res.data;
    } finally {
      loading.value = false;
    }
  };

  const deleteUser = async (id) => {
    loading.value = true;
    try {
      const res = await api.delete(endpoint + `${id}`);
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const getUserById = async (id) => {
    loading.value = true;
    try {
      const res = await api.get(endpoint + `${id}`);
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const selectUsers = async (params = {}) => {
    loading.value = true;
    try {
      const res = await api.get(endpoint + `select`, { params });
      response.value = res.data.results;
    } finally {
      loading.value = false;
    }
  };

  const getCount = async (params = {}) => {
    loading.value = true;
    try {
      const res = await api.get(endpoint + `count`, { params });
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  return {
    users,
    total,
    loading,
    fetchUsers,
    storeUser,
    deleteUser,
    getUserById,
    getCount,
    selectUsers,
    response,
  };
}
