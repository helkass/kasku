<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <!-- Search & Filter Section -->
    <div class="mb-6">
      <n-card class="rounded-2xl border-0 shadow-sm bg-white">
        <!-- Search Input -->
        <div class="mb-4">
          <n-input
            v-model:value="searchQuery"
            placeholder="Cari transaksi..."
            size="large"
            round
            @input="handleSearch"
            :loading="loading"
          >
            <template #prefix>
              <n-icon><Search /></n-icon>
            </template>
          </n-input>
        </div>

        <!-- Date Range & Quick Filters -->
        <div class="space-y-4">
          <!-- Quick Date Filters -->
          <div class="flex gap-2 overflow-x-auto pb-2">
            <n-button
              v-for="filter in quickFilters"
              :key="filter.key"
              size="small"
              :type="activeQuickFilter === filter.key ? 'primary' : 'default'"
              @click="applyQuickFilter(filter)"
              class="whitespace-nowrap"
              :loading="loading"
            >
              {{ filter.label }}
            </n-button>
          </div>

          <!-- Custom Date Range -->
          <div class="border-t pt-4">
            <div class="flex items-center justify-between mb-3">
              <span class="text-sm font-medium text-gray-700"
                >Rentang Tanggal</span
              >
              <n-button
                text
                size="small"
                @click="showDateRange = !showDateRange"
                class="text-blue-500"
              >
                {{ showDateRange ? "Sembunyikan" : "Tampilkan" }}
              </n-button>
            </div>

            <div v-if="showDateRange" class="space-y-3">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs text-gray-500 mb-2"
                    >Dari Tanggal</label
                  >
                  <n-date-picker
                    v-model:value="dateRange.start"
                    type="date"
                    size="small"
                    class="w-full"
                    :is-date-disabled="disableFutureDate"
                    placeholder="Pilih tanggal"
                  />
                </div>
                <div>
                  <label class="block text-xs text-gray-500 mb-2"
                    >Sampai Tanggal</label
                  >
                  <n-date-picker
                    v-model:value="dateRange.end"
                    type="date"
                    size="small"
                    class="w-full"
                    :is-date-disabled="disableFutureDate"
                    placeholder="Pilih tanggal"
                  />
                </div>
              </div>
              <div class="flex gap-2">
                <n-button
                  size="small"
                  @click="applyDateRange"
                  type="primary"
                  class="flex-1"
                  :loading="loading"
                >
                  Terapkan
                </n-button>
                <n-button
                  size="small"
                  @click="resetDateRange"
                  class="flex-1"
                  :disabled="loading"
                >
                  Reset
                </n-button>
              </div>
            </div>

            <!-- Selected Date Range Display -->
            <div
              v-if="selectedDateRangeLabel"
              class="mt-3 p-3 bg-blue-50 rounded-xl"
            >
              <div class="flex items-center justify-between">
                <span class="text-sm text-blue-700 font-medium">
                  {{ selectedDateRangeLabel }}
                </span>
                <n-button
                  text
                  size="tiny"
                  @click="resetDateRange"
                  class="text-blue-500"
                  :disabled="loading"
                >
                  <template #icon>
                    <n-icon><Close /></n-icon>
                  </template>
                </n-button>
              </div>
            </div>
          </div>
        </div>
      </n-card>
    </div>

    <!-- Transaction Summary -->
    <div class="mb-6">
      <n-card class="rounded-2xl border-0 shadow-sm bg-white">
        <div class="grid grid-cols-2 gap-4">
          <div class="text-center">
            <div class="text-sm text-gray-500 mb-1">Total Pemasukan</div>
            <div class="text-lg font-bold text-green-600">
              +Rp {{ formatCurrency(summary.pemasukan) }}
            </div>
          </div>
          <div class="text-center">
            <div class="text-sm text-gray-500 mb-1">Total Pengeluaran</div>
            <div class="text-lg font-bold text-red-600">
              -Rp {{ formatCurrency(summary.pengeluaran) }}
            </div>
          </div>
        </div>

        <!-- button ad transaction -->
        <div class="flex justify-center items-center mt-3">
          <n-button
            @click="openModalCreateTransaction"
            strong
            secondary
            type="info"
            size="large"
            class="w-full"
          >
            Buat Transaksi
          </n-button>
        </div>
      </n-card>
    </div>

    <!-- Transactions List -->
    <div>
      <div class="flex justify-between items-center mb-4">
        <h3 class="font-semibold text-gray-800">{{ total }} Transaksi</h3>
        <n-button
          text
          size="small"
          class="text-blue-500"
          @click="toggleSortOrder"
        >
          {{ sortOrder === "desc" ? "Terlama" : "Terbaru" }}
          <template #icon>
            <n-icon><FunnelOutline /></n-icon>
          </template>
        </n-button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-8">
        <n-spin size="large" />
        <div class="text-gray-500 mt-4">Memuat transaksi...</div>
      </div>

      <!-- Transactions Grouped by Date -->
      <div v-else class="space-y-4">
        <div
          v-for="group in groupedTransactions"
          :key="group.date"
          class="transaction-group"
        >
          <!-- Date Header -->
          <div class="flex items-center gap-3 mb-3 px-2">
            <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
            <div class="text-sm font-semibold text-gray-700">
              {{ group.date }}
            </div>
            <div class="flex-1 border-t border-gray-200"></div>
            <div class="text-xs text-gray-500">
              {{ group.transactions.length }} transaksi
            </div>
          </div>

          <!-- Transactions for this date -->
          <n-card class="rounded-2xl border-0 shadow-sm bg-white">
            <div class="space-y-3">
              <div
                v-for="transaction in group.transactions"
                :key="transaction.id"
                class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl transition-colors duration-200 cursor-pointer"
                @click="viewTransactionDetail(transaction.id)"
              >
                <div class="flex items-center gap-3 flex-1 min-w-0">
                  <div
                    class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                    :class="
                      transaction.type === 'pemasukan'
                        ? 'bg-green-100'
                        : 'bg-red-100'
                    "
                  >
                    <n-icon
                      size="18"
                      :class="
                        transaction.type === 'pemasukan'
                          ? 'text-green-600'
                          : 'text-red-600'
                      "
                    >
                      <component
                        :is="getTransactionIcon(transaction.category)"
                      />
                    </n-icon>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="font-medium text-gray-800 truncate capitalize">
                      {{ transaction.title }}
                    </div>
                    <div
                      class="text-xs text-gray-500 flex items-center gap-1 truncate"
                    >
                      <n-icon size="12">
                        <Wallet />
                      </n-icon>
                      {{ transaction.wallet }}
                      <span class="mx-1">•</span>
                      {{ transaction.category }}
                    </div>
                  </div>
                </div>
                <div class="text-right flex-shrink-0 ml-3">
                  <div
                    class="font-semibold"
                    :class="
                      transaction.type === 'pemasukan'
                        ? 'text-green-600'
                        : 'text-red-600'
                    "
                  >
                    {{ transaction.type === "pemasukan" ? "+" : "-" }}Rp
                    {{ formatCurrency(transaction.amount) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ transaction.time }}
                  </div>
                </div>
              </div>
            </div>
          </n-card>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="!loading && transactions.length === 0"
        class="text-center py-12"
      >
        <n-icon size="64" class="text-gray-300 mb-4">
          <Receipt />
        </n-icon>
        <div class="text-gray-500 mb-2">Tidak ada transaksi</div>
        <div class="text-xs text-gray-400 mb-4">
          {{
            searchQuery || selectedDateRangeLabel
              ? "Coba ubah pencarian atau filter tanggal"
              : "Mulai catat transaksi pertama Anda"
          }}
        </div>
        <n-button v-if="!searchQuery && !selectedDateRangeLabel" type="primary">
          Tambah Transaksi Pertama
        </n-button>
        <n-button v-else size="small" @click="clearFilters" :disabled="loading">
          Hapus Filter
        </n-button>
      </div>

      <!-- Pagination -->
      <div
        v-if="!loading && transactions.length > 0"
        class="flex justify-center items-center gap-4 mt-6"
      >
        <n-button size="small" :disabled="currentPage === 1" @click="prevPage">
          Sebelumnya
        </n-button>

        <span class="text-sm text-gray-600">
          Halaman {{ currentPage }} dari {{ totalPages }}
        </span>

        <n-button
          size="small"
          :disabled="currentPage >= totalPages"
          @click="nextPage"
        >
          Selanjutnya
        </n-button>
      </div>
    </div>
  </div>

  <!-- modal create transaction -->
  <n-modal
    v-model:show="showModalCreateTransaction"
    :mask-closable="false"
    preset="card"
    :title="'Buat Transaksi'"
    class="max-w-md"
    :bordered="false"
  >
    <n-form :model="form">
      <n-form-item label="Judul" path="title">
        <n-input v-model:value="form.title" placeholder="belanja sayur" />
      </n-form-item>

      <n-form-item label="Nominal" path="amount">
        <n-input-number
          v-model:value="form.amount"
          :min="0"
          class="w-full"
          placeholder="0"
        >
          <template #prefix>
            <span class="text-gray-500">Rp</span>
          </template>
        </n-input-number>
      </n-form-item>

      <n-form-item label="Tipe" path="type">
        <n-select v-model:value="form.type" :options="optionsTransactionType" />
      </n-form-item>

      <n-form-item label="Kategori" path="category">
        <n-select
          v-model:value="form.category"
          :options="optionsTransactionCategory"
        />
      </n-form-item>

      <n-form-item label="Sumber Dana" path="source_id">
        <n-select v-model:value="form.source_id" :options="userFinances" />
      </n-form-item>
    </n-form>

    <template #footer>
      <div class="flex gap-2 justify-end">
        <n-button
          @click="showModalCreateTransaction = false"
          :disabled="submitting"
          >Batal</n-button
        >
        <n-button type="primary" @click="handleSubmit" :loading="submitting">
          Simpan
        </n-button>
      </div>
    </template>
  </n-modal>

  <!-- modal detail transaction -->
  <n-modal style="max-height: 90vh" v-model:show="showModalDetailTransaction">
    <n-card
      style="max-width: 800px"
      title="Detail Transaksi"
      :bordered="false"
      size="medium"
      role="dialog"
      aria-modal="true"
      class="mx-auto mx-2"
    >
      <template #header>
        <div class="flex items-center gap-2">
          <n-icon size="20" color="#18a058">
            <WalletOutline />
          </n-icon>
          <span>Detail Transaksi</span>
        </div>
      </template>

      <div class="flex justify-center items-center" v-if="loadingModalDetail">
        <n-spin size="large" />
      </div>

      <div
        v-else-if="detailTransaction"
        class="space-y-6"
        style="max-height: 75vh; overflow-y: auto"
      >
        <!-- Header Transaksi -->
        <n-card
          embedded
          :bordered="false"
          class="bg-gradient-to-r from-blue-50 to-indigo-50"
        >
          <div class="flex justify-between items-start">
            <div>
              <n-text class="text-lg font-bold">{{
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
                  class="font-semibold"
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
          </div>
        </n-card>

        <!-- Grid Informasi Transaksi -->
        <div class="w-full flex flex-col gap-3">
          <!-- Kolom 1 -->
          <n-card title="Informasi Transaksi" class="w-full">
            <div class="space-y-3 w-full">
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
                    detailTransaction.type === 'pemasukan' ? 'success' : 'error'
                  "
                >
                  {{ detailTransaction.type }}
                </n-tag>
              </div>
              <div class="flex justify-between">
                <n-text class="text-gray-500">Dibuat Oleh</n-text>
                <n-text strong>{{ detailTransaction.creator.username }}</n-text>
              </div>
            </div>
          </n-card>

          <!-- Kolom 2 -->
          <n-card title="Informasi Pengguna" class="w-full">
            <div class="space-y-3 w-full">
              <div class="flex justify-between">
                <n-text class="text-gray-500">Pemilik</n-text>
                <n-text strong>{{ detailTransaction.user.username }}</n-text>
              </div>
              <div class="flex justify-between">
                <n-text class="text-gray-500">Bergabung</n-text>
                <n-text>{{
                  formatDate(detailTransaction.user.created_at)
                }}</n-text>
              </div>
            </div>
          </n-card>
        </div>

        <!-- Sumber Dana -->
        <n-card title="Sumber Dana" size="small">
          <div
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center gap-3">
              <n-avatar round size="medium" class="bg-blue-100">
                <n-icon color="#2080f0">
                  <CardOutline />
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
                </div>
              </div>
            </div>
            <div class="text-right">
              <n-text class="text-lg font-bold text-blue-600">
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
                <FileTrayOutline />
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
        <div class="flex justify-end gap-2">
          <div v-if="detailTransaction && !loadingModalDetail">
            <div v-if="detailTransaction.can_deleted === 1">
              <n-button
                type="error"
                @click="handleDeleteTransaction(detailTransaction.id)"
              >
                Hapus
              </n-button>
            </div>
          </div>
          <n-button @click="showModalDetailTransaction = false">
            Tutup
          </n-button>
        </div>
      </template>
    </n-card>
  </n-modal>
</template>

<script setup>
import { ref, computed, onMounted, watch, reactive } from "vue";
import {
  NCard,
  NButton,
  NIcon,
  NInput,
  NDatePicker,
  useMessage,
  NSpin,
  useDialog,
} from "naive-ui";

// Icons
import {
  Search,
  Close,
  Receipt,
  CardOutline as Wallet,
  FastFood,
  CarOutline,
  CartOutline,
  MedicalOutline,
  BusinessOutline,
  CodeWorking,
  GiftOutline,
  CardOutline,
  FileTrayOutline,
  WalletOutline,
  FunnelOutline,
} from "@vicons/ionicons5";

const FoodIcon = FastFood;
const TransportIcon = CarOutline;
const ShoppingIcon = CartOutline;
const HealthIcon = MedicalOutline;
const EntertainmentIcon = BusinessOutline;
const SalaryIcon = CodeWorking;
const GiftIcon = GiftOutline;

import { useTransaction } from "@/composables/useTransaction";
import {
  formatCurrency,
  formatDate,
  formatDateTime,
  formatNumber,
  formatTime,
} from "@/utils/formatter";
import { useFinance } from "@/composables/useFinance";
import { useAuth } from "@/composables/useAuth";
const dialog = useDialog();

// Reactive State
const searchQuery = ref("");
const showDateRange = ref(false);
const activeQuickFilter = ref("week");
const currentPage = ref(1);
const sortOrder = ref("desc");
const itemsPerPage = 20; // Sesuaikan dengan backend
const showModalCreateTransaction = ref(false);
const form = reactive({
  amount: "",
  type: "pengeluaran",
  category: "umum",
  title: "",
  source_id: "",
  note: "",
});
const optionsTransactionType = [
  {
    label: "Pengeluaran",
    value: "pengeluaran",
  },
  {
    label: "Pemasukan",
    value: "pemasukan",
  },
];
const optionsTransactionCategory = [
  {
    label: "Umum",
    value: "mum",
  },
  {
    label: "Belanja",
    value: "belanja",
  },
  {
    label: "Kesehatan",
    value: "kesehatan",
  },
  {
    label: "Hiburan",
    value: "hiburan",
  },
  {
    label: "Transfer",
    value: "transfer",
  },
  {
    label: "Perbaikan",
    value: "perbaikan",
  },
  {
    label: "Lain-lain",
    value: "lain-lain",
  },
];
const submitting = ref(false);

const message = useMessage();
const {
  fetchTransactions,
  transactions,
  loading,
  total,
  storeTransaction,
  response,
  getTransactionById,
  deleteTransaction,
} = useTransaction();
const fetchFinance = useFinance();
const fetchUser = useAuth();

const dateRange = ref({
  start: getStartOfWeek(),
  end: Date.now(),
});

// Quick Filters
const quickFilters = [
  { key: "today", label: "Hari Ini" },
  { key: "week", label: "Minggu Ini" },
  { key: "month", label: "Bulan Ini" },
  { key: "year", label: "Tahun Ini" },
  { key: "all", label: "Semua" },
];

// Computed Properties
const groupedTransactions = computed(() => {
  const groups = {};

  transactions.value.forEach((transaction) => {
    const date = new Date(transaction.created_at).toLocaleDateString("id-ID", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    });

    if (!groups[date]) {
      groups[date] = {
        date,
        transactions: [],
      };
    }

    groups[date].transactions.push({
      id: transaction.id,
      title: transaction.title,
      category: transaction.category || "Umum",
      amount: transaction.amount,
      type: transaction.type,
      time: formatTime(new Date(transaction.created_at)),
      wallet: transaction.finance?.title || "Dompet Utama",
      timestamp: new Date(transaction.created_at).getTime(),
    });
  });

  return Object.values(groups);
});

const summary = computed(() => {
  const pemasukan = transactions.value
    .filter((t) => t.type === "pemasukan")
    .reduce((sum, t) => Number(sum) + Number(t.amount), 0);

  const pengeluaran = transactions.value
    .filter((t) => t.type === "pengeluaran")
    .reduce((sum, t) => Number(sum) + Number(t.amount), 0);

  return { pemasukan, pengeluaran };
});

const selectedDateRangeLabel = computed(() => {
  if (!dateRange.value.start || !dateRange.value.end) return "";

  const start = new Date(dateRange.value.start).toLocaleDateString("id-ID");
  const end = new Date(dateRange.value.end).toLocaleDateString("id-ID");

  return `${start} - ${end}`;
});

const totalPages = computed(() => {
  return Math.ceil(total.value / itemsPerPage);
});

const getTransactionIcon = (category) => {
  const icons = {
    Makanan: FoodIcon,
    Transportasi: TransportIcon,
    Belanja: ShoppingIcon,
    Kesehatan: HealthIcon,
    Hiburan: EntertainmentIcon,
    Gaji: SalaryIcon,
    Hadiah: GiftIcon,
  };
  return icons[category] || Receipt;
};

const disableFutureDate = (timestamp) => {
  return timestamp > Date.now();
};

const buildApiParams = () => {
  const params = {
    page: currentPage.value,
    itemsPerPage: itemsPerPage,
    sort: sortOrder.value,
  };

  // Search parameter
  if (searchQuery.value) {
    params.search = searchQuery.value;
  }

  // Date range parameters
  if (dateRange.value.start && dateRange.value.end) {
    params.start_at = new Date(dateRange.value.start)
      .toISOString()
      .split("T")[0];
    params.end_at = new Date(dateRange.value.end).toISOString().split("T")[0];
  }

  return params;
};

const loadTransactions = async () => {
  try {
    const params = buildApiParams();
    await fetchTransactions(params);
  } catch (error) {
    console.error("Error loading transactions:", error);
    message.error("Terjadi kesalahan saat memuat transaksi");
  }
};

const applyQuickFilter = async (filter) => {
  activeQuickFilter.value = filter.key;

  const now = new Date();
  let startDate,
    endDate = Date.now();

  switch (filter.key) {
    case "today":
      startDate = new Date(
        now.getFullYear(),
        now.getMonth(),
        now.getDate()
      ).getTime();
      break;
    case "week":
      startDate = getStartOfWeek();
      break;
    case "month":
      startDate = new Date(now.getFullYear(), now.getMonth(), 1).getTime();
      break;
    case "year":
      startDate = new Date(now.getFullYear(), 0, 1).getTime();
      break;
    case "all":
      startDate = null;
      endDate = null;
      break;
  }

  dateRange.value = { start: startDate, end: endDate };
  showDateRange.value = false;
  currentPage.value = 1; // Reset ke halaman pertama

  await loadTransactions();
};

const applyDateRange = async () => {
  showDateRange.value = false;
  activeQuickFilter.value = "custom";
  currentPage.value = 1;
  await loadTransactions();
};

const resetDateRange = async () => {
  dateRange.value = { start: null, end: null };
  activeQuickFilter.value = "all";
  currentPage.value = 1;
  await loadTransactions();
};

const clearFilters = async () => {
  searchQuery.value = "";
  await resetDateRange();
};

const handleSearch = debounce(async () => {
  currentPage.value = 1;
  await loadTransactions();
}, 500);

const toggleSortOrder = async () => {
  sortOrder.value = sortOrder.value === "desc" ? "asc" : "desc";
  currentPage.value = 1;
  await loadTransactions();
};

const nextPage = async () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    await loadTransactions();
  }
};

const prevPage = async () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    await loadTransactions();
  }
};

const detailTransaction = ref(null);
const loadingModalDetail = ref(false);
const showModalDetailTransaction = ref(false);

const viewTransactionDetail = async (id) => {
  showModalDetailTransaction.value = true;

  if (detailTransaction.value && detailTransaction.value.id === id) {
    return;
  }

  loadingModalDetail.value = true;

  try {
    await getTransactionById(`/${id}`);
    if (response.value.success) {
      detailTransaction.value = response.value.transaction;
    } else {
      message.error(response.value.message);
    }
  } catch (error) {
    message.error(error.response?.data?.message || "Terjadi kesalahan");
  } finally {
    loadingModalDetail.value = false;
  }
};

// Helper functions
function getStartOfWeek() {
  const now = new Date();
  const day = now.getDay();
  const diff = now.getDate() - day + (day === 0 ? -6 : 1);
  return new Date(now.setDate(diff)).getTime();
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

const resetForm = () => {
  form.title = "";
  form.type = "pengeluaran";
  form.amount = 0;
  form.note = "";
  form.source_id = null;
};

const handleSubmit = async () => {
  submitting.value = true;
  try {
    // get user id
    await fetchUser.getUser();

    const formData = {
      ...form,
      user_id: fetchUser.user.value.id,
    };

    await storeTransaction(formData);

    if (response.value.success) {
      await loadTransactions();
      resetForm();
      showModalCreateTransaction.value = false;
      message.success(response.value.message);
    }
  } catch (error) {
    console.error("Error saving transaction", error);
    message.error("Terjadi kesalahan");
  }
  submitting.value = false;
};

const userFinances = ref([]);

const openModalCreateTransaction = () => {
  getFinanceUser();
  showModalCreateTransaction.value = true;
};

const getFinanceUser = async () => {
  userFinances.value = [];
  try {
    await fetchFinance.fetchFinances();

    fetchFinance.finances.value.map((f) => {
      userFinances.value.push({
        label: f.title + " - RP." + formatCurrency(f.amount),
        value: f.id,
      });
    });
  } catch (error) {
    message.error("Terjadi kesalahan saat pengambilan data keuangan");
  }
};

const handleDeleteTransaction = async (id) => {
  showModalDetailTransaction.value = false;
  dialog.error({
    title: "Konfirmasi",
    content:
      "Apakah anda yakin ingin menhapus data transaksi ini?, nominal transaksi akan dikalkulasikan pada dompet anda.",
    positiveText: "Ya, hapus",
    negativeText: "Batal",
    draggable: true,
    onPositiveClick: async () => {
      try {
        await deleteTransaction(`/${id}`);
        if (response.value.success) {
          message.success(
            response.value.message || "Berhasil menghapus data transaksi"
          );
          detailTransaction.value = null;
          await loadTransactions();
        }
      } catch (error) {
        message.error(error.response?.data?.message || "Terjadi kesalahan");
      }
    },
    onNegativeClick: () => {},
  });
};

// Watchers
watch([currentPage], async () => {
  await loadTransactions();
});

onMounted(async () => {
  await loadTransactions();
});
</script>

<style scoped>
.transaction-group {
  animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.overflow-x-auto::-webkit-scrollbar {
  height: 4px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
