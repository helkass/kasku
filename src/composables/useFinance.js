import { ref } from "vue";
import api from "@/api/axios";

export function useFinance() {
  const finances = ref([]);
  const loading = ref(false);
  const total = ref(0);
  const endpoint = "finances/";
  const response = ref(null);

  const fetchFinances = async (params = {}) => {
    loading.value = true;
    try {
      const res = await api.get(endpoint, { params });
      finances.value = res.data.finances;
      total.value = res.data.total; // atau total dari API
    } finally {
      loading.value = false;
    }
  };

  const storeFinance = async (data) => {
    loading.value = true;
    try {
      const res = await api.post(endpoint, data);
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const deleteFinance = async (id) => {
    loading.value = true;
    try {
      const res = await api.delete(endpoint + `${id}`);
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const selectFinances = async (params = {}) => {
    loading.value = true;
    try {
      const res = await api.get(endpoint + `select`, { params });
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const getFinanceById = async (id) => {
    loading.value = true;
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
      const res = await api.get(endpoint + `count`, { params });
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const getTotalFinance = async (params = {}) => {
    loading.value = true;
    try {
      const res = await api.post(endpoint + `total-finance`, { params });
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const getIncomeExpanse = async (params = {}) => {
    loading.value = true;
    try {
      const res = await api.post(endpoint + `total-income-expanse`, params);
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  const getFinanceDailyExpenses = async () => {
    try {
      const res = await api.post(endpoint + `expanses`);
      response.value = res.data;
    } finally {
      loading.value = false;
    }
  };

  return {
    finances,
    total,
    loading,
    fetchFinances,
    storeFinance,
    deleteFinance,
    selectFinances,
    getFinanceById,
    getCount,
    response,
    getTotalFinance,
    getIncomeExpanse,
    getFinanceDailyExpenses,
  };
}
