<template>
  <n-space vertical size="large">
    <!-- Heading & Filter -->
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold">Dompet</h1>
        <p class="text-gray-500">Daftar dompet pengguna</p>
      </div>

      <div class="flex gap-2">
        <div class="flex gap-1">
          <n-date-picker v-model:value="dateRange" type="daterange" clearable />
          <n-button type="success" @click="applyDateFilter" :loading="loading">
            Tampilkan
          </n-button>
        </div>
        <n-button
          type="info"
          @click="resetDateFilter"
          :disabled="!hasActiveFilter"
        >
          Reset
        </n-button>
      </div>
    </div>

    <!-- Filter Info -->
    <n-alert
      v-if="hasActiveFilter"
      type="info"
      closable
      @close="resetDateFilter"
    >
      Menampilkan data dari <strong>{{ formattedStartDate }}</strong> hingga
      <strong>{{ formattedEndDate }}</strong>
      <template #action>
        <n-button text @click="resetDateFilter" type="info">
          Reset Filter
        </n-button>
      </template>
    </n-alert>

    <!-- Recent Activity -->
    <n-card title="Daftar Dompet">
      <div v-if="loading">
        <n-skeleton text :repeat="4" />
      </div>

      <div v-else-if="totalFinances === 0">
        <n-empty description="Tidak ada data transaksi">
          <template #extra>
            <n-button size="small" @click="refreshData">
              Refresh Data
            </n-button>
          </template>
        </n-empty>
      </div>

      <div v-else>
        <div class="mb-2 w-full flex justify-between items-center">
          <div class="w-full max-w-44">
            <n-input
              v-model:value="activeFilter.search"
              @update:value="handleSearch"
              placeholder="Cari..."
              clearable
            >
              <template #prefix>
                <n-icon :component="SearchOutline" />
              </template>
            </n-input>
          </div>

          <n-button
            :disabled="loading"
            type="primary"
            @click="showModalCreateFinance = true"
          >
            Buat Dompet
          </n-button>
        </div>

        <n-data-table
          :bordered="false"
          :columns="columns"
          :data="finances"
          :pagination="pagination"
        />
      </div>
    </n-card>
  </n-space>

  <!-- modal -->
  <n-modal v-model:show="showModalCreateFinance">
    <n-card
      style="width: 600px"
      title="Tambah Dompet"
      :bordered="false"
      size="huge"
      role="dialog"
      aria-modal="true"
    >
      <n-form :model="formFinance" @submit.prevent="handleCreateFinance">
        <n-form-item label="Pemilik">
          <n-select
            v-model:value="formFinance.user_id"
            filterable
            remote
            clearable
            placeholder="Cari pengguna..."
            :loading="selectLoading"
            :options="userOptions"
            :filter="handleUserFilter"
            @search="handleUserSearch"
            @update:value="handleUserSelect"
          />
          <template #feedback>
            <span
              v-if="formErrors.user_id"
              style="color: #d03050; font-size: 12px"
            >
              {{ formErrors.user_id }}
            </span>
          </template>
        </n-form-item>

        <n-form-item
          label="Nama Dompet"
          path="title"
          :status="formErrors.title ? 'error' : undefined"
        >
          <n-input
            v-model:value="formFinance.title"
            type="text"
            placeholder="Rekening MANDIRI"
          />
          <template #feedback>
            <span
              v-if="formErrors.title"
              style="color: #d03050; font-size: 12px"
            >
              {{ formErrors.title }}
            </span>
          </template>
        </n-form-item>

        <n-form-item
          label="Jumlah"
          path="amount"
          :feedback="formErrors.amount"
          :status="formErrors.amount ? 'error' : undefined"
        >
          <n-input
            v-model:value="formFinance.amount"
            type="number"
            placeholder="100,000"
          />
          <template #feedback>
            <span
              v-if="formErrors.amount"
              style="color: #d03050; font-size: 12px"
            >
              {{ formErrors.amount }}
            </span>
          </template>
        </n-form-item>

        <n-form-item
          label="Batas per hari"
          path="daily_limit"
          :status="formErrors.daily_limit ? 'error' : undefined"
        >
          <n-input
            v-model:value="formFinance.daily_limit"
            type="number"
            placeholder="10,000"
          />
          <template #feedback>
            <span
              v-if="formErrors.daily_limit"
              style="color: #d03050; font-size: 12px"
            >
              {{ formErrors.daily_limit }}
            </span>
          </template>
        </n-form-item>

        <n-form-item
          label="Tipe"
          :status="formErrors.type ? 'error' : undefined"
        >
          <n-select v-model:value="formFinance.type" :options="financeTypes" />
        </n-form-item>

        <n-form-item
          label="Catatan"
          :status="formErrors.note ? 'error' : undefined"
        >
          <n-input
            v-model:value="formFinance.note"
            type="textarea"
            placeholder="catatan(opsional)"
          />
          <template #feedback>
            <span
              v-if="formErrors.note"
              style="color: #d03050; font-size: 12px"
            >
              {{ formErrors.note }}
            </span>
          </template>
        </n-form-item>
      </n-form>

      <div class="flex justify-end items-end">
        <n-form-item>
          <n-button
            @click="handleCreateFinance"
            :disabled="loading"
            :loading="loading"
            type="primary"
          >
            Tambahkan
          </n-button>
        </n-form-item>
      </div>
    </n-card>
  </n-modal>
</template>

<script setup>
import { ref, onMounted, h, computed, reactive } from "vue";
import { useMessage, NButton, NAlert, NIcon } from "naive-ui";
import { useFinance } from "@/composables/useFinance";
import { useUser } from "@/composables/useUser";
import { EyeOutline, SearchOutline, TrashOutline } from "@vicons/ionicons5";
import { debounce } from "lodash-es";
import { formatNumber } from "@/utils/formatter";
import { useFormErrors } from "@/composables/useFormError";
import { RouterLink } from "vue-router";

const message = useMessage();
const loading = ref(true);
const selectLoading = ref(false);

// Initialize composables
const fetchFinance = useFinance();
const fetchUser = useUser();

// Date Range Filter
const dateRange = ref(null);
const activeFilter = ref({
  start_at: "",
  end_at: "",
  search: "",
});

// Computed properties
const hasActiveFilter = computed(() => {
  return activeFilter.value.start_at !== "" || activeFilter.value.end_at !== "";
});

const formattedStartDate = computed(() => {
  if (!activeFilter.value.start_at) return "";
  return new Date(activeFilter.value.start_at).toLocaleDateString("id-ID");
});

const formattedEndDate = computed(() => {
  if (!activeFilter.value.end_at) return "";
  return new Date(activeFilter.value.end_at).toLocaleDateString("id-ID");
});

// Data untuk table
const finances = ref([]);
const totalFinances = ref(0);

const pagination = ref({
  pageSize: 10,
  showSizePicker: true,
  pageSizes: [10, 20, 50],
  showQuickJumper: true,
});

const columns = [
  {
    title: "No.",
    width: 80,
    render: (_, index) => {
      return h("span", {}, index + 1);
    },
  },
  {
    title: "Tanggal",
    key: "created_at",
    render: (row) => {
      if (!row.created_at) return "-";
      const date = new Date(row.created_at);
      return h("span", {}, date.toLocaleDateString("id-ID"));
    },
  },
  {
    title: "Nama Dompet",
    key: "title",
    ellipsis: {
      tooltip: true,
    },
  },
  {
    title: "Pemilik",
    render: (row) => {
      return row.user.username;
    },
  },
  {
    title: "Saldo",
    key: "amount",
    render: (row) => {
      return "Rp. " + formatNumber(row.amount);
    },
  },
  {
    title: "Aksi",
    render: (row) => {
      return h(
        "div",
        {
          class: "flex gap-1",
        },
        [
          // Button Detail dengan Icon
          h(
            RouterLink,
            {
              to: `/finances/history/`,
            },
            {
              default: () =>
                h(
                  NButton,
                  {
                    size: "small",
                    type: "info",
                    onClick: () => {
                      localStorage.setItem("finance_id", row.id);
                    },
                  },
                  { default: () => h(NIcon, { component: EyeOutline }) }
                ),
            }
          ),
          h(
            NButton,
            {
              size: "small",
              type: "error",
              //   onClick: () => handleDelete(row),
            },
            {
              default: () => h(NIcon, { component: TrashOutline }),
            }
          ),
        ]
      );
    },
  },
];

const handleSearch = debounce(async (searchValue) => {
  try {
    loading.value = true;

    // Update filter dengan nilai search terbaru
    const filterParams = {
      ...activeFilter.value,
      search: searchValue,
    };

    await fetchFinance.fetchFinances(filterParams);
    updateFinances();

    message.success("Pencarian selesai");
  } catch (error) {
    message.error("Gagal melakukan pencarian");
  } finally {
    loading.value = false;
  }
}, 500);

// Date Filter Functions
const applyDateFilter = async () => {
  if (!dateRange.value || dateRange.value.length !== 2) {
    message.warning("Pilih range tanggal terlebih dahulu");
    return;
  }

  try {
    loading.value = true;

    // Convert timestamp to YYYY-MM-DD format
    const [startTimestamp, endTimestamp] = dateRange.value;

    activeFilter.value = {
      ...activeFilter.value,
      start_at: new Date(startTimestamp).toISOString().split("T")[0],
      end_at: new Date(endTimestamp).toISOString().split("T")[0],
    };

    // Fetch data dengan filter
    await fetchFinance.fetchFinances(activeFilter.value);
    updateFinances();

    message.success("Filter diterapkan");
  } catch (error) {
    message.error("Gagal menerapkan filter");
  } finally {
    loading.value = false;
  }
};

const resetDateFilter = async () => {
  try {
    loading.value = true;

    // Reset semua filter
    dateRange.value = null;
    activeFilter.value = {
      start_at: "",
      end_at: "",
      search: activeFilter.value.search,
    };

    // Fetch data tanpa filter
    await fetchFinance.fetchFinances();
    updateFinances();

    message.info("Filter direset");
  } catch (error) {
    message.error("Gagal mereset filter");
  } finally {
    loading.value = false;
  }
};

const refreshData = async () => {
  try {
    loading.value = true;

    // Gunakan filter aktif jika ada
    const params = hasActiveFilter.value ? activeFilter.value : {};
    await fetchFinance.fetchFinances(params);
    updateFinances();

    message.success("Data diperbarui");
  } catch (error) {
    message.error("Gagal refresh data");
  } finally {
    loading.value = false;
  }
};

const updateFinances = () => {
  finances.value = fetchFinance.finances.value;
  totalFinances.value = fetchFinance.total.value;
};

// Load initial data
const loadInitialData = async () => {
  try {
    loading.value = true;

    await Promise.all([fetchFinance.fetchFinances()]);

    updateFinances();
  } catch (err) {
    message.error("Gagal memuat data");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadInitialData();
});

// create modal
const showModalCreateFinance = ref(false);
const formFinance = reactive({
  user_id: "",
  amount: 0,
  title: "",
  daily_limit: 0,
  note: "",
  type: "rekening",
});

const initialErrors = {
  user_id: "",
  amount: 0,
  title: "",
  daily_limit: 0,
  note: "",
  type: "rekening",
};

const {
  errors: formErrors,
  setErrors,
  resetErrors,
} = useFormErrors(initialErrors);

// User Select
const userOptions = ref([]);
const searchQuery = ref("");

// User Search Functions
const handleUserSearch = async (query) => {
  searchQuery.value = query;

  if (query.length < 2) {
    userOptions.value = [];
    return;
  }

  await searchUsers(query);
};

const handleUserFilter = () => {
  // Karena kita menggunakan remote search, biarkan kosong
  return true;
};

const searchUsers = async (query) => {
  try {
    selectLoading.value = true;

    await fetchUser.selectUsers({ search: query });

    // Convert dari format Select2 ke format Naive UI
    userOptions.value = fetchUser.response.value.map((item) => ({
      label: item.text,
      value: item.id,
    }));
  } catch (error) {
    console.error("Search error:", error);
    message.error("Gagal mencari pengguna");
    userOptions.value = [];
  } finally {
    selectLoading.value = false;
  }
};

const handleUserSelect = (value) => {
  formFinance.user_id = value;
  if (value) {
    message.success("Filter pengguna diterapkan");
  }
};

const financeTypes = [
  {
    label: "Rekening",
    value: "rekening",
  },
  {
    label: "Tunai",
    value: "tunai",
  },
];

const handleCreateFinance = async () => {
  loading.value = true;
  resetErrors();
  try {
    await fetchFinance.storeFinance(formFinance);

    if (fetchFinance.response.value?.success) {
      message.success(
        fetchFinance.response.value.message || "Data berhasil ditambahkan"
      );
      showModalCreateFinance.value = false;
      await refreshData();
    } else {
      if (fetchFinance.response.value.errors) {
        if (typeof fetchFinance.responsevalue.errors === "object") {
          setErrors(fetchFinance.response.value.errors);
        }
        message.error(fetchFinance.response.value.message);
      }
    }
  } catch (error) {
    if (fetchFinance.response.value.errors) {
      if (typeof fetchFinance.response.value.errors === "object") {
        setErrors(fetchFinance.response.value.errors);
      }
      message.error(fetchFinance.response.value.message);
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Optional: Custom styles untuk responsive design */
@media (max-width: 768px) {
  .flex.justify-between.items-end {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .flex.gap-2 {
    width: 100%;
    justify-content: space-between;
  }
}
</style>
