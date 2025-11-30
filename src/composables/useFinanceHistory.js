import { ref } from "vue";
import api from "@/api/axios";

export function useFinanceHistory() {
  const financeHistories = ref([]);
  const loading = ref(false);
  const total = ref(0);
  const endpoint = "finance-histories/";
  const response = ref(null);

  const fetchFinanceHistories = async (params = {}) => {
    loading.value = true;
    try {
      const res = await api.get(endpoint, { params });
      financeHistories.value = res.data;
      total.value = res.data.data.length; // atau total dari API
    } finally {
      loading.value = false;
    }
  };

  const deleteFinanceHistory = async (id) => {
    loading.value = true;
    try {
      const res = await api.delete(endpoint + `${id}`);
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const getFinanceHistoryById = async (id = "") => {
    loading.value = true;
    try {
      const res = await api.get(endpoint + `${id}`);
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  return {
    financeHistories,
    total,
    loading,
    fetchFinanceHistories,
    deleteFinanceHistory,
    getFinanceHistoryById,
  };
}
