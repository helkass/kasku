<template>
  <n-space vertical size="large">
    <!-- Heading & Filter -->
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <p class="text-gray-500">Selamat datang kembali 👋</p>
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

    <!-- Stats -->
    <n-grid :cols="4" :x-gap="16" :y-gap="16" responsive="screen">
      <n-gi
        v-for="(item, index) in stats"
        :key="index"
        :span="screenWidth < 600 ? 4 : 1"
      >
        <n-card :bordered="false" class="shadow-md hover:shadow-lg transition">
          <n-space align="center">
            <n-avatar
              :style="{ backgroundColor: item.color }"
              round
              size="large"
            >
              <component :is="item.icon" />
            </n-avatar>
            <div>
              <p class="text-gray-500 text-sm">{{ item.label }}</p>
              <h2 class="text-xl font-bold">{{ item.value }}</h2>
            </div>
          </n-space>
        </n-card>
      </n-gi>
    </n-grid>

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
    <n-card title="Aktivitas Terbaru">
      <div v-if="loading">
        <n-skeleton text :repeat="4" />
      </div>

      <div v-else-if="transactions.length === 0">
        <n-empty description="Tidak ada data transaksi">
          <template #extra>
            <n-button size="small" @click="refreshData">
              Refresh Data
            </n-button>
          </template>
        </n-empty>
      </div>

      <div v-else>
        <n-data-table
          :bordered="false"
          :columns="columns"
          :data="transactions"
          :pagination="pagination"
        />
      </div>
    </n-card>
  </n-space>
</template>

<script setup>
import { ref, onMounted, markRaw, h, computed } from "vue";
import { useMessage, NButton, NTag, NAlert } from "naive-ui";
import { useFinance } from "@/composables/useFinance";
import { useUser } from "@/composables/useUser";
import { useTransaction } from "@/composables/useTransaction";
import { formatNumber } from "@/utils/formatter";

// icons from naive
import { CashOutline, PersonOutline, BarChartOutline } from "@vicons/ionicons5";

const message = useMessage();
const loading = ref(true);
const screenWidth = window.innerWidth;

// Initialize composables
const fetchFinance = useFinance();
const fetchUser = useUser();
const fetchTransaction = useTransaction();

// Date Range Filter
const dateRange = ref(null);
const activeFilter = ref({
  start_at: "",
  end_at: "",
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

// sample stats
const stats = ref([]);

// Data untuk table
const transactions = ref([]);
const totalTransaction = ref(0);

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
    title: "Judul",
    key: "title",
    ellipsis: {
      tooltip: true,
    },
  },
  {
    title: "Jumlah",
    key: "amount",
    render: (row) => {
      if (!row.amount) return "-";
      return h(
        "span",
        {},
        new Intl.NumberFormat("id-ID", {
          style: "currency",
          currency: "IDR",
        }).format(row.amount)
      );
    },
  },
  {
    title: "Status",
    key: "type",
    render: (row) => {
      const status = row.type || row.status || "unknown";
      const tagType =
        {
          pemasukan: "success",
          pengeluaran: "error",
          income: "success",
          expense: "error",
        }[status] || "default";

      return h(
        NTag,
        { type: tagType },
        { default: () => status.toUpperCase() }
      );
    },
  },
  {
    title: "Aksi",
    render: (row) => {
      return h(
        NButton,
        {
          size: "small",
          onClick: () => handleViewDetail(row),
        },
        { default: () => "Detail" }
      );
    },
  },
];

const handleViewDetail = (rowData) => {
  message.info(`Detail transaksi: ${rowData.title}`);
};

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
      start_at: new Date(startTimestamp).toISOString().split("T")[0],
      end_at: new Date(endTimestamp).toISOString().split("T")[0],
    };

    // Fetch data dengan filter
    await fetchTransaction.fetchTransactions(activeFilter.value);
    updateTransactions();

    message.success("Filter diterapkan");
  } catch (error) {
    console.error("Error applying filter:", error);
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
    };

    // Fetch data tanpa filter
    await fetchTransaction.fetchTransactions();
    updateTransactions();

    message.info("Filter direset");
  } catch (error) {
    console.error("Error resetting filter:", error);
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
    await fetchTransaction.fetchTransactions(params);
    updateTransactions();

    message.success("Data diperbarui");
  } catch (error) {
    message.error("Gagal refresh data");
  } finally {
    loading.value = false;
  }
};

const updateTransactions = () => {
  transactions.value = fetchTransaction.transactions.value;
  totalTransaction.value = transactions.value.length;
};

const updateStats = () => {
  stats.value = [
    {
      label: "Total Finance",
      value: extractValue(fetchFinance.response),
      color: "#18A058",
      icon: markRaw(CashOutline),
    },
    {
      label: "Total Transaksi",
      value: extractValue(fetchTransaction.response),
      color: "#2080F0",
      icon: markRaw(BarChartOutline),
    },
    {
      label: "Total Pengguna",
      value: extractValue(fetchUser.response),
      color: "#F0A020",
      icon: markRaw(PersonOutline),
    },
  ];
};

const extractValue = (responseRef) => {
  if (!responseRef || !responseRef.value) return "0";

  const value = responseRef.value;

  if (typeof value === "number") {
    return formatNumber(value);
  }

  if (typeof value === "object") {
    const count =
      value.data?.total ||
      value.total ||
      value.count ||
      value.total_users ||
      value.total_transactions ||
      0;
    return formatNumber(count);
  }

  return formatNumber(value);
};

// Load initial data
const loadInitialData = async () => {
  try {
    loading.value = true;

    await Promise.all([
      fetchFinance.getCount(),
      fetchUser.getCount(),
      fetchTransaction.getCount(),
      fetchTransaction.fetchTransactions(), // Tanpa filter pertama kali
    ]);

    updateTransactions();
    updateStats();
  } catch (err) {
    console.error("Error loading data:", err);
    message.error("Gagal memuat data");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadInitialData();
});
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
