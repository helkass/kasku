import { ref } from "vue";
import api from "@/api/axios";

export function useTransaction() {
  const transactions = ref([]);
  const loading = ref(false);
  const total = ref(0);
  const endpoint = "transactions";
  const response = ref(null);

  const fetchTransactions = async (params = {}) => {
    loading.value = true;
    try {
      const res = await api.get(endpoint, { params });
      transactions.value = res.data.transactions;
      total.value = res.data.total; // atau total dari API
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const storeTransaction = async (data) => {
    loading.value = true;
    try {
      const res = await api.post(endpoint, data);
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const deleteTransaction = async (id) => {
    try {
      const res = await api.delete(endpoint + `${id}`);
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const getTransactionById = async (id) => {
    try {
      const res = await api.get(endpoint + `${id}`);
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const getCount = async (params = {}) => {
    loading.value = true;
    try {
      const res = await api.get(endpoint + `/count`, { params });
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  return {
    transactions,
    total,
    loading,
    fetchTransactions,
    storeTransaction,
    deleteTransaction,
    getTransactionById,
    getCount,
    response,
  };
}
