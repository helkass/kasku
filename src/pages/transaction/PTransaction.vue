<template>
  <n-space vertical size="large">
    <!-- Heading & Filter -->
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold">Transaksi</h1>
        <p class="text-gray-500">Daftar transaksi</p>
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
    <n-card title="Daftar Riwayat Transaksi">
      <div v-if="loading">
        <n-skeleton text :repeat="4" />
      </div>

      <div v-else-if="totalTransactions === 0">
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
        </div>

        <n-data-table
          :bordered="false"
          :columns="columns"
          :data="transactions"
          :pagination="pagination"
        />
      </div>
    </n-card>
  </n-space>

  <!-- modal -->
  <n-modal v-model:show="showModalDetailTransaction">
    <n-card
      style="max-width: 800px"
      title="Detail Transaksi"
      :bordered="false"
      size="huge"
      role="dialog"
      aria-modal="true"
    >
      <template #header>
        <div class="flex items-center gap-2">
          <n-icon size="20" color="#18a058">
            <CashOutline />
          </n-icon>
          <span>Detail Transaksi</span>
        </div>
      </template>

      <div class="flex justify-center items-center" v-if="loadingModalDetail">
        <n-spin size="large" />
      </div>

      <div v-else-if="detailTransaction" class="space-y-6">
        <!-- Header Transaksi -->
        <n-card
          embedded
          :bordered="false"
          class="bg-gradient-to-r from-blue-50 to-indigo-50"
        >
          <div class="flex justify-between items-start">
            <div>
              <n-text class="text-2xl font-bold">{{
                detailTransaction.title
              }}</n-text>
              <div class="flex items-center gap-2 mt-2">
                <n-tag
                  :bordered="false"
                  :type="
                    detailTransaction.type === 'pemasukan' ? 'success' : 'error'
                  "
                >
                  {{ detailTransaction.type.toUpperCase() }}
                </n-tag>
                <n-text
                  class="text-lg font-semibold"
                  :class="
                    detailTransaction.type === 'pemasukan'
                      ? 'text-green-600'
                      : 'text-red-600'
                  "
                >
                  Rp {{ formatNumber(detailTransaction.amount) }}
                </n-text>
              </div>
            </div>
            <n-text class="text-gray-500">
              {{ formatDate(detailTransaction.created_at) }}
            </n-text>
          </div>
        </n-card>

        <!-- Grid Informasi Transaksi -->
        <n-grid :cols="2" :x-gap="16" :y-gap="12">
          <!-- Kolom 1 -->
          <n-gi>
            <n-card title="Informasi Transaksi" size="small">
              <div class="space-y-3">
                <div class="flex justify-between">
                  <n-text class="text-gray-500">ID Transaksi</n-text>
                  <n-text strong>#{{ detailTransaction.id }}</n-text>
                </div>
                <div class="flex justify-between">
                  <n-text class="text-gray-500">Tanggal</n-text>
                  <n-text>{{
                    formatDateTime(detailTransaction.created_at)
                  }}</n-text>
                </div>
                <div class="flex justify-between">
                  <n-text class="text-gray-500">Tipe</n-text>
                  <n-tag
                    size="small"
                    :type="
                      detailTransaction.type === 'pemasukan'
                        ? 'success'
                        : 'error'
                    "
                  >
                    {{ detailTransaction.type }}
                  </n-tag>
                </div>
                <div class="flex justify-between">
                  <n-text class="text-gray-500">Dibuat Oleh</n-text>
                  <n-text strong>{{
                    detailTransaction.creator.username
                  }}</n-text>
                </div>
              </div>
            </n-card>
          </n-gi>

          <!-- Kolom 2 -->
          <n-gi>
            <n-card title="Informasi User" size="small">
              <div class="space-y-3">
                <div class="flex justify-between">
                  <n-text class="text-gray-500">Pemilik</n-text>
                  <n-text strong>{{ detailTransaction.user.username }}</n-text>
                </div>
                <div class="flex justify-between">
                  <n-text class="text-gray-500">Role</n-text>
                  <n-tag size="small" type="info">
                    {{ detailTransaction.user.role }}
                  </n-tag>
                </div>
                <div class="flex justify-between">
                  <n-text class="text-gray-500">Bergabung</n-text>
                  <n-text>{{
                    formatDate(detailTransaction.user.created_at)
                  }}</n-text>
                </div>
              </div>
            </n-card>
          </n-gi>
        </n-grid>

        <!-- Sumber Dana -->
        <n-card title="Sumber Dana" size="small">
          <div
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center gap-3">
              <n-avatar round size="medium" class="bg-blue-100">
                <n-icon color="#2080f0">
                  <WalletOutline />
                </n-icon>
              </n-avatar>
              <div>
                <n-text strong class="text-lg">{{
                  detailTransaction.finance.title
                }}</n-text>
                <div class="flex items-center gap-2 mt-1">
                  <n-tag size="small" type="info">
                    {{ detailTransaction.finance.type }}
                  </n-tag>
                  <n-text class="text-gray-500 text-sm">
                    Limit Harian: Rp
                    {{ formatNumber(detailTransaction.finance.daily_limit) }}
                  </n-text>
                </div>
              </div>
            </div>
            <div class="text-right">
              <n-text class="text-xl font-bold text-blue-600">
                Rp {{ formatNumber(detailTransaction.finance.amount) }}
              </n-text>
              <n-text class="text-gray-500 text-sm block">
                Saldo Tersedia
              </n-text>
            </div>
          </div>

          <!-- Note Sumber Dana -->
          <div
            v-if="detailTransaction.finance.note"
            class="mt-4 p-3 bg-yellow-50 rounded border border-yellow-200"
          >
            <div class="flex items-start gap-2">
              <n-icon color="#f0a020" size="16">
                <InformationCircleOutline />
              </n-icon>
              <n-text class="text-sm text-yellow-800">
                {{ detailTransaction.finance.note }}
              </n-text>
            </div>
          </div>
        </n-card>

        <!-- Timeline -->
        <n-card title="Aktivitas" size="small">
          <n-timeline>
            <n-timeline-item
              type="success"
              title="Transaksi Dibuat"
              :time="formatDateTime(detailTransaction.created_at)"
            />
            <n-timeline-item
              type="default"
              title="Terakhir Diupdate"
              :time="formatDateTime(detailTransaction.updated_at)"
            />
          </n-timeline>
        </n-card>
      </div>

      <template #footer>
        <div class="flex justify-end">
          <n-button @click="showModalDetailTransaction = false">
            Tutup
          </n-button>
        </div>
      </template>
    </n-card>
  </n-modal>
</template>

<script setup>
import { ref, onMounted, h, computed } from "vue";
import { useMessage, NButton, NAlert, NIcon, NTag } from "naive-ui";
import { useTransaction } from "@/composables/useTransaction";
import {
  CashOutline,
  EyeOutline,
  InformationCircleOutline,
  SearchOutline,
  TrashOutline,
  WalletOutline,
} from "@vicons/ionicons5";
import { debounce } from "lodash-es";
import { formatDate, formatDateTime, formatNumber } from "@/utils/formatter";

const message = useMessage();
const loading = ref(true);

// Initialize composables
const fetchTransactions = useTransaction();

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
const transactions = ref([]);
const totalTransactions = ref(0);

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
    title: "Nama",
    render: (row) => {
      return row.user.username;
    },
  },
  {
    title: "Tipe",
    key: "type",
    render: (row) => {
      const typeConfig = {
        pemasukan: { type: "success", label: "Pemasukan" },
        pengeluaran: { type: "error", label: "Pengeluaran" },
        transfer: { type: "warning", label: "Transfer" },
      };

      const config = typeConfig[row.type] || {
        type: "default",
        label: row.type,
      };

      return h(
        NTag,
        {
          bordered: false,
          type: config.type,
        },
        { default: () => config.label }
      );
    },
  },
  {
    title: "Jumlah",
    key: "amount",
    render: (row) => {
      return "Rp. " + formatNumber(row.amount);
    },
  },
  {
    title: "Sumber Dana",
    render: (row) => {
      return h(
        NTag,
        {
          bordered: false,
          type: "default",
        },
        {
          default: () =>
            `${row.finance.title} - ${row.finance.type
              .toString()
              .toUpperCase()}`,
        }
      );
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
          h(
            NButton,
            {
              size: "small",
              type: "info",
              onClick: () => handleDetailTransaction(row.id),
            },
            { default: () => h(NIcon, { component: EyeOutline }) }
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

const detailTransaction = ref(null);
const loadingModalDetail = ref(false);

const handleDetailTransaction = async (id) => {
  loadingModalDetail.value = true;
  showModalDetailTransaction.value = true;

  try {
    await fetchTransactions.getTransactionById(id);
    if (fetchTransactions.response.value.success) {
      detailTransaction.value = fetchTransactions.response.value.transaction;
    } else {
      message.error(fetchTransactions.response.value.message);
    }
  } catch (error) {
    message.error(error.response?.data?.message || "Terjadi kesalahan");
  } finally {
    loadingModalDetail.value = false;
  }
};

const handleSearch = debounce(async (searchValue) => {
  try {
    loading.value = true;

    // Update filter dengan nilai search terbaru
    const filterParams = {
      ...activeFilter.value,
      search: searchValue,
    };

    await fetchTransactions.fetchTransactions(filterParams);
    updateTransactions();

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
    await fetchTransactions.fetchTransactions(activeFilter.value);
    updateTransactions();

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
    await fetchTransactions.fetchTransactions();
    updateTransactions();

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
    await fetchTransactions.fetchTransactions(params);
    updateTransactions();

    message.success("Data diperbarui");
  } catch (error) {
    message.error("Gagal refresh data");
  } finally {
    loading.value = false;
  }
};

const updateTransactions = () => {
  transactions.value = fetchTransactions.transactions.value;
  totalTransactions.value = fetchTransactions.total.value;
};

// Load initial data
const loadInitialData = async () => {
  try {
    loading.value = true;

    await Promise.all([fetchTransactions.fetchTransactions()]);

    updateTransactions();
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
const showModalDetailTransaction = ref(false);
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
