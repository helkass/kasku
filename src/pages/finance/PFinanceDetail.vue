<template>
  <div class="wallet-detail-page">
    <!-- Header -->
    <n-page-header
      class="page-header"
      @back="handleBack"
      title="Detail Dompet"
      subtitle="Informasi lengkap dan riwayat transaksi"
    >
      <template #avatar>
        <n-avatar
          round
          :style="{
            backgroundColor: getWalletColor(walletData.type),
            color: 'white',
          }"
        >
          <template #icon>
            <n-icon>
              <WalletIcon />
            </n-icon>
          </template>
        </n-avatar>
      </template>

      <template #extra>
        <n-space>
          <n-button type="primary" @click="handleEdit" :loading="loading">
            <template #icon>
              <n-icon>
                <EditIcon />
              </n-icon>
            </template>
            Edit Dompet
          </n-button>

          <n-button type="error" @click="handleDelete" :loading="loading">
            <template #icon>
              <n-icon>
                <DeleteIcon />
              </n-icon>
            </template>
            Hapus Dompet
          </n-button>
        </n-space>
      </template>
    </n-page-header>

    <!-- Main Content -->
    <n-grid cols="2" responsive="screen" :x-gap="12" :y-gap="12">
      <!-- Left Column: Wallet Information -->
      <n-gi span="1">
        <n-card
          title="Informasi Dompet"
          :bordered="false"
          class="wallet-card"
          :segmented="{
            content: true,
            footer: 'soft',
          }"
        >
          <div class="w-full flex flex-col gap-6">
            <!-- Wallet Title -->
            <n-grid cols="2">
              <n-gi span="1" class="flex flex-col gap-3">
                <n-text strong depth="3" class="info-label">Nama Dompet</n-text>
                <n-text class="info-value" depth="1">{{
                  walletData.title
                }}</n-text>
              </n-gi>

              <!-- Current Balance -->
              <n-gi span="1" class="flex flex-col gap-3">
                <n-text strong depth="3" class="info-label">Saldo</n-text>
                <div class="amount-display">
                  <n-text
                    class="amount-value"
                    :type="getAmountType(walletData.amount)"
                  >
                    {{ formatCurrency(walletData.amount) }}
                  </n-text>
                </div>
              </n-gi>
            </n-grid>

            <n-grid cols="2">
              <n-gi span="1" class="flex flex-col gap-3">
                <n-text strong depth="3" class="info-label"
                  >Nomor Dompet</n-text
                >
                <n-text class="info-value" depth="1">{{
                  walletData.finance_number
                }}</n-text>
              </n-gi>
            </n-grid>

            <n-grid cols="2">
              <!-- Daily Limit -->
              <n-gi
                span="1"
                v-if="walletData.daily_limit"
                class="flex flex-col gap-3"
              >
                <n-text strong depth="3" class="info-label"
                  >Batas Harian</n-text
                >
                <div class="daily-limit">
                  <n-text class="limit-value">
                    {{ formatCurrency(walletData.daily_limit) }}
                  </n-text>
                  <!-- Progress Bar -->
                  <n-progress
                    v-if="walletData.daily_spent"
                    type="line"
                    :percentage="
                      calculateDailyUsage(
                        walletData.daily_spent,
                        walletData.daily_limit
                      )
                    "
                    :status="
                      getUsageStatus(
                        walletData.daily_spent,
                        walletData.daily_limit
                      )
                    "
                    :height="8"
                    class="limit-progress"
                  />
                  <n-text
                    v-if="walletData.daily_spent"
                    depth="3"
                    class="limit-spent"
                  >
                    Terpakai: {{ formatCurrency(walletData.daily_spent) }}
                  </n-text>
                </div>
              </n-gi>

              <!-- Created Info -->
              <n-gi span="1" class="flex flex-col gap-3">
                <n-text strong depth="3" class="info-label">Dibuat Oleh</n-text>
                <div class="creator-info">
                  <n-avatar
                    round
                    size="small"
                    :src="walletData.created_by?.avatar"
                  >
                    {{ getInitials(walletData?.creator?.username || "") }}
                  </n-avatar>
                  <div class="creator-details">
                    <n-text>{{
                      walletData?.creator?.username || "Unknown"
                    }}</n-text>
                    <n-text depth="3" class="created-date">
                      {{ formatDate(walletData.created_at) }}
                    </n-text>
                  </div>
                </div>
              </n-gi>
            </n-grid>
          </div>
        </n-card>
      </n-gi>

      <!-- Right Column: Wallet Information -->
      <n-gi span="1">
        <!-- Quick Stats -->
        <n-card title="Statistik Cepat" :bordered="false" class="stats-card">
          <n-grid cols="2" :x-gap="12" :y-gap="12">
            <n-gi>
              <n-statistic
                label="Total Transaksi"
                :value="statistics.totalTransactions"
              >
                <template #suffix>
                  <n-icon :color="getStatIconColor('transactions')">
                    <ReceiptIcon />
                  </n-icon>
                </template>
              </n-statistic>
            </n-gi>
            <n-gi>
              <n-statistic
                label="Rata-rata Transaksi"
                :value="formatCurrency(statistics.averageTransaction)"
              >
                <template #prefix>
                  <n-icon :color="getStatIconColor('average')">
                    <TrendingUpIcon />
                  </n-icon>
                </template>
              </n-statistic>
            </n-gi>
            <n-gi>
              <n-statistic
                label="Transaksi Bulan Ini"
                :value="statistics.thisMonthTransactions"
              >
                <template #suffix>
                  <n-icon :color="getStatIconColor('monthly')">
                    <CalendarIcon />
                  </n-icon>
                </template>
              </n-statistic>
            </n-gi>
            <n-gi>
              <n-statistic
                label="Transaksi Tertinggi"
                :value="formatCurrency(statistics.highestTransaction)"
              >
                <template #prefix>
                  <n-icon :color="getStatIconColor('highest')">
                    <ArrowUpIcon />
                  </n-icon>
                </template>
              </n-statistic>
            </n-gi>
          </n-grid>

          <!-- Notes -->
          <div v-if="walletData.note" class="mt-3">
            <n-text strong depth="3" class="info-label">Catatan</n-text>
            <n-card
              content-style="padding: 12px;"
              :bordered="true"
              class="note-card"
            >
              <n-text>{{ walletData.note }}</n-text>
            </n-card>
          </div>

          <template #footer>
            <n-space justify="space-between">
              <n-text depth="3">
                Terakhir diupdate: {{ formatDate(walletData.updated_at) }}
              </n-text>
            </n-space>
          </template>
        </n-card>
      </n-gi>
    </n-grid>

    <!-- transactions -->
    <n-card
      title="Riwayat Transaksi"
      :bordered="false"
      class="transactions-card mt-3"
    >
      <template #header-extra>
        <n-space>
          <n-date-picker
            v-model:value="dateRange"
            type="daterange"
            clearable
            size="small"
            placeholder="Filter Tanggal"
            @update:value="handleDateFilter"
          />
        </n-space>
      </template>

      <!-- Transaction Table -->
      <n-data-table
        :columns="transactionColumns"
        :data="filteredTransactions"
        :loading="loading"
        :pagination="pagination"
        :row-key="(row) => row.id"
        :bordered="false"
      >
        <!-- Custom Type Column -->
        <template #type="{ row }">
          <n-tag
            :type="row.type === 'pemasukan' ? 'success' : 'error'"
            :bordered="false"
            size="small"
          >
            {{ row.type === "pemasukan" ? "Masuk" : "Keluar" }}
          </n-tag>
        </template>

        <!-- Custom Amount Column -->
        <template #amount="{ row }">
          <n-text :type="row.type === 'pemasukan' ? 'success' : 'error'">
            {{ row.type === "pemasukan" ? "+" : "-" }}
            {{ formatCurrency(row.amount) }}
          </n-text>
        </template>

        <!-- Custom Category Column -->
        <template #category="{ row }">
          <n-tag
            :bordered="false"
            size="small"
            :color="getCategoryColor(row.category)"
          >
            {{ row.category }}
          </n-tag>
        </template>

        <!-- Custom Created At Column -->
        <template #created_at="{ row }">
          <n-space vertical size="small">
            <n-text>{{ row.created_at }}</n-text>
          </n-space>
        </template>

        <!-- Empty State -->
        <template #empty>
          <div class="empty-state">
            <n-empty description="Belum ada transaksi" size="large">
              <template #extra>
                <n-button size="small" @click="handleAddTransaction">
                  Buat Transaksi Pertama
                </n-button>
              </template>
            </n-empty>
          </div>
        </template>
      </n-data-table>
    </n-card>

    <!-- Edit Wallet Modal -->
    <n-modal
      v-model:show="showEditModal"
      preset="dialog"
      title="Edit Dompet"
      positive-text="Simpan"
      negative-text="Batal"
      @positive-click="handleSaveEdit"
      @negative-click="showEditModal = false"
    >
      <n-form
        ref="editFormRef"
        :model="editForm"
        :rules="editRules"
        label-placement="left"
        label-width="auto"
        require-mark-placement="right-hanging"
      >
        <n-form-item label="Nama Dompet" path="title">
          <n-input
            v-model:value="editForm.title"
            placeholder="Masukkan nama dompet"
          />
        </n-form-item>

        <n-form-item label="Tipe" path="type">
          <n-select
            v-model:value="editForm.type"
            :options="typeOptions"
            placeholder="Pilih tipe dompet"
          />
        </n-form-item>

        <n-form-item label="Jumlah" path="amount">
          <n-input-number
            v-model:value="editForm.amount"
            :min="0"
            placeholder="Masukkan jumlah"
            style="width: 100%"
          />
        </n-form-item>

        <n-form-item label="Batas Harian" path="daily_limit">
          <n-input-number
            v-model:value="editForm.daily_limit"
            :min="0"
            placeholder="Masukkan batas harian (opsional)"
            style="width: 100%"
          />
        </n-form-item>

        <n-form-item label="Catatan" path="note">
          <n-input
            v-model:value="editForm.note"
            type="textarea"
            placeholder="Masukkan catatan (opsional)"
            :autosize="{
              minRows: 3,
              maxRows: 5,
            }"
          />
        </n-form-item>
      </n-form>
    </n-modal>
  </div>

  <!-- modal detail transaction -->
  <n-modal style="max-height: 90vh" v-model:show="showModalDetailTransaction">
    <n-card
      style="max-width: 800px"
      title="Detail Transaksi"
      :bordered="false"
      size="medium"
      role="dialog"
      aria-modal="true"
      class="mx-auto mt-2"
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
                @click="handleDeleteTransaction(detailTransaction)"
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
import { ref, computed, onMounted, defineProps, h } from "vue";
import { useRouter } from "vue-router";
import {
  NPageHeader,
  NAvatar,
  NIcon,
  NButton,
  NSpace,
  NCard,
  NGrid,
  NGi,
  NText,
  NTag,
  NProgress,
  NStatistic,
  NDataTable,
  NDatePicker,
  NModal,
  NForm,
  NFormItem,
  NInput,
  NSelect,
  NInputNumber,
  NEmpty,
  useMessage,
  useDialog,
} from "naive-ui";
import {
  Wallet as WalletIcon,
  PencilOutline as EditIcon,
  Trash as DeleteIcon,
  Receipt as ReceiptIcon,
  TrendingUp as TrendingUpIcon,
  Calendar as CalendarIcon,
  ArrowUp as ArrowUpIcon,
  TrashOutline,
  EyeOutline,
  FileTrayOutline,
  CardOutline,
  WalletOutline,
} from "@vicons/ionicons5";
import {
  formatCurrency,
  formatDate,
  formatDateTime,
  formatNumber,
} from "@/utils/formatter";
import { useFinance } from "@/composables/useFinance";
import { useTransaction } from "@/composables/useTransaction";

// Props & Router
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});

const router = useRouter();
const message = useMessage();
const dialog = useDialog();
const fetchFinance = useFinance();
const fetchTransaction = useTransaction();

// State
const walletData = ref({
  id: "",
  title: "",
  type: "pemasukan",
  amount: 0,
  daily_limit: null,
  daily_spent: 0,
  note: "",
  created_by: null,
  created_at: "",
  updated_at: "",
});

const transactions = ref([]);
const loading = ref(false);
const showEditModal = ref(false);
const showTransactionModal = ref(false);
const dateRange = ref(null);

// Statistics
const statistics = ref({
  totalTransactions: 0,
  averageTransaction: 0,
  thisMonthTransactions: 0,
  highestTransaction: 0,
});

// Edit Form
const editForm = ref({});
const editFormRef = ref(null);
const typeOptions = [
  { label: "Pemasukan", value: "pemasukan" },
  { label: "Pengeluaran", value: "pengeluaran" },
];

const editRules = {
  title: {
    required: true,
    message: "Nama dompet harus diisi",
    trigger: "blur",
  },
  type: {
    required: true,
    message: "Tipe harus dipilih",
    trigger: "change",
  },
  amount: {
    type: "number",
    required: true,
    message: "Jumlah harus diisi",
    trigger: "blur",
  },
};

// Pagination
const pagination = ref({
  page: 1,
  pageSize: 10,
  showSizePicker: true,
  pageSizes: [5, 10, 20, 50],
  onChange: (page) => {
    pagination.value.page = page;
  },
  onUpdatePageSize: (pageSize) => {
    pagination.value.pageSize = pageSize;
    pagination.value.page = 1;
  },
});

// Table Columns
const transactionColumns = [
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
    width: 150,
    render: (data) => {
      return formatDate(data.created_at);
    },
  },
  {
    title: "Judul",
    key: "title",
    ellipsis: true,
    width: 200,
  },
  {
    title: "Tipe",
    key: "type",
    width: 100,
    render: (data) => {
      return h(
        NTag,
        {
          type: data.type === "pemasukan" ? "success" : "error",
          size: "small",
        },
        () => data.type
      );
    },
  },
  {
    title: "Kategori",
    key: "category",
    width: 120,
  },
  {
    title: "Jumlah",
    key: "amount",
    align: "right",
    width: 120,
    render: (data) => {
      return formatCurrency(data.amount);
    },
  },
  {
    title: "Aksi",
    key: "actions",
    width: 120,
    align: "center",
    render: (data) => {
      // Buat array untuk children
      const children = [
        h(
          NButton,
          {
            size: "small",
            type: "info",
            onClick: () => viewTransactionDetail(data),
            quaternary: true,
            circle: true,
          },
          { default: () => h(NIcon, { component: EyeOutline }) }
        ),
      ];

      // Tambahkan delete button hanya jika can_deleted === 1
      if (data.can_deleted === 1) {
        children.push(
          h(
            NButton,
            {
              size: "small",
              type: "error",
              onClick: () => handleDeleteTransaction(data),
              quaternary: true,
              circle: true,
            },
            {
              default: () => h(NIcon, { component: TrashOutline }),
            }
          )
        );
      }

      return h(
        "div",
        {
          class: "flex gap-1 justify-center",
        },
        children
      );
    },
  },
];

// Computed
const walletId = computed(() => props.id);

const filteredTransactions = computed(() => {
  let result = transactions.value;

  // Filter by date range
  if (dateRange.value && dateRange.value.length === 2) {
    const [start, end] = dateRange.value;
    result = result.filter((transaction) => {
      const transDate = new Date(transaction.created_at).getTime();
      return transDate >= start && transDate <= end;
    });
  }

  // Sort by date (newest first)
  return result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

// Methods
const getWalletColor = (type) => {
  return type === "pemasukan" ? "#18a058" : "#d03050";
};

const getAmountType = (amount) => {
  return amount >= 0 ? "success" : "error";
};

const calculateDailyUsage = (spent, limit) => {
  if (!limit || limit === 0) return 0;
  return Math.min((spent / limit) * 100, 100);
};

const getUsageStatus = (spent, limit) => {
  if (!limit || limit === 0) return "default";
  const percentage = (spent / limit) * 100;
  if (percentage >= 90) return "error";
  if (percentage >= 70) return "warning";
  return "success";
};

const getInitials = (name) => {
  return name
    .split(" ")
    .map((word) => word[0])
    .join("")
    .toUpperCase()
    .substring(0, 2);
};

const getCategoryColor = (category) => {
  const colors = {
    umum: "#ff6b6b",
    belanja: "#4ecdc4",
    kesehatan: "#45b7d1",
    transfer: "#96ceb4",
    transportasi: "#feca57",
    perbaikan: "#ff9ff3",
    "lain-lain": "#5f27cd",
  };
  return colors[category] || "#8395a7";
};

const getStatIconColor = (stat) => {
  const colors = {
    transactions: "#3498db",
    average: "#2ecc71",
    monthly: "#9b59b6",
    highest: "#e74c3c",
  };
  return colors[stat];
};

// Handlers
const handleBack = () => {
  router.back();
};

const handleEdit = () => {
  editForm.value = { ...walletData.value };
  showEditModal.value = true;
};

const handleDelete = () => {
  dialog.error({
    title: "Konfirmasi Hapus",
    content:
      "Apakah Anda yakin ingin menghapus dompet ini? Semua transaksi terkait juga akan dihapus.",
    positiveText: "Ya, Hapus",
    negativeText: "Batal",
    onPositiveClick: async () => {
      loading.value = true;
      try {
        // Call delete API
        await fetchFinance.deleteFinance(`/${walletId.value}`);

        message.success(
          fetchFinance.response.value.message || "Dompet berhasil dihapus"
        );
        router.push("/finances");
      } catch (error) {
        message.error("Gagal menghapus dompet");
      } finally {
        loading.value = false;
      }
    },
  });
};

const handleSaveEdit = async () => {
  try {
    await editFormRef.value?.validate();
    loading.value = true;

    // Call update API
    await fetchFinance.fetchFinances();

    walletData.value = { ...editForm.value };
    showEditModal.value = false;
    message.success("Dompet berhasil diperbarui");
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const handleAddTransaction = () => {
  showTransactionModal.value = true;
};

const showModalDetailTransaction = ref(false);
const loadingModalDetail = ref(false);
const detailTransaction = ref(null);

const viewTransactionDetail = async (trans) => {
  showModalDetailTransaction.value = true;

  if (detailTransaction.value && detailTransaction.value.id === trans.id) {
    return;
  }

  loadingModalDetail.value = true;

  try {
    await fetchTransaction.getTransactionById(`/${trans.id}`);
    if (fetchTransaction.response.value.success) {
      detailTransaction.value = fetchTransaction.response.value.transaction;
    } else {
      message.error(fetchTransaction.response.value.message);
    }
  } catch (error) {
    message.error(error.response?.data?.message || "Terjadi kesalahan");
  } finally {
    loadingModalDetail.value = false;
  }
};

const handleDeleteTransaction = (transaction) => {
  dialog.error({
    title: "Hapus Transaksi",
    content: `Apakah Anda yakin ingin menghapus transaksi "${transaction.title}"?`,
    positiveText: "Hapus",
    negativeText: "Batal",
    onPositiveClick: async () => {
      try {
        // Call delete transaction API
        await fetchTransaction.deleteTransaction(`/${transaction.id}`);
        message.success("Transaksi berhasil dihapus");
        fetchTransactions();
      } catch (error) {
        message.error("Gagal menghapus transaksi");
      }
    },
  });
};

const handleDateFilter = () => {
  // Filter already handled in computed
};

// API Functions
const fetchWalletData = async () => {
  loading.value = true;
  try {
    await fetchFinance.getFinanceById(`/${walletId.value}`);
    walletData.value = fetchFinance.response.value.finance;
  } catch (error) {
    message.error("Gagal memuat data dompet");
    router.push("/finances");
  } finally {
    loading.value = false;
  }
};

const fetchTransactions = async () => {
  try {
    await fetchTransaction.fetchTransactions({
      finance_unique: walletId.value,
    });
    transactions.value = fetchTransaction.response.value.transactions;
  } catch (error) {
    console.error("Failed to fetch transactions:", error);
  }
};

const calculateStatistics = () => {
  const trans = transactions.value;
  statistics.value = {
    totalTransactions: trans.length,
    averageTransaction:
      trans.length > 0
        ? trans.reduce((sum, t) => Number(sum) + Number(t.amount), 0) /
          trans.length
        : 0,
    thisMonthTransactions: trans.filter((t) => {
      const transDate = new Date(t.created_at);
      const now = new Date();
      return (
        transDate.getMonth() === now.getMonth() &&
        transDate.getFullYear() === now.getFullYear()
      );
    }).length,
    highestTransaction:
      trans.length > 0 ? Math.max(...trans.map((t) => Number(t.amount))) : 0,
  };
};

// Lifecycle
onMounted(() => {
  Promise.all([fetchWalletData(), fetchTransactions()]).finally(() => {
    calculateStatistics();
  });
});
</script>

<style scoped>
.wallet-detail-page {
  background: #f8f9fa;
  min-height: 100vh;
}

.page-header {
  margin-bottom: 12px;
  background: white;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.wallet-card {
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
}

.stats-card {
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
}

.transactions-card {
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
}

.wallet-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.info-label {
  font-size: 14px;
  color: #6c757d;
}

.info-value {
  font-size: 16px;
  font-weight: 500;
}

.amount-display {
  display: flex;
  align-items: center;
  gap: 8px;
}

.amount-value {
  font-size: 24px;
  font-weight: 700;
}

.daily-limit {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.limit-value {
  font-size: 18px;
  font-weight: 600;
  color: #495057;
}

.limit-progress {
  margin-top: 4px;
}

.limit-spent {
  font-size: 13px;
}

.creator-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.creator-details {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.created-date {
  font-size: 13px;
}

.note-card {
  background: #f8f9fa;
  margin-top: 4px;
}

.empty-state {
  padding: 40px 0;
  text-align: center;
}

.time-ago {
  font-size: 12px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .wallet-detail-page {
    padding: 12px;
  }

  .page-header {
    padding: 16px;
  }

  .amount-value {
    font-size: 20px;
  }
}
</style>
