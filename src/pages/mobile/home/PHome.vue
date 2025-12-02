<template>
  <div class="min-h-screen p-4 mb-3">
    <!-- Quick Stats -->
    <div class="grid grid-cols-3 gap-3 mb-6">
      <n-card class="bg-white rounded-2xl border-0 shadow-sm">
        <div class="text-center flex justify-center items-center flex-col">
          <div class="flex items-center justify-center mx-auto mb-2">
            <n-icon size="32" class="text-blue-600">
              <WalletOutline />
            </n-icon>
          </div>
          <n-spin v-if="fetchFinance.loading.value" size="small" />
          <n-text v-else class="text-sm font-bold text-gray-800">
            {{ formatCurrencySimple(totalMountFinance) }}
          </n-text>
        </div>
      </n-card>
      <div
        class="bg-white rounded-2xl border-0 shadow-sm flex justify-center items-center"
        style="padding: 0"
      >
        <div class="text-center flex justify-center items-center flex-col">
          <!-- <div class="text-xs text-gray-500 mb-1">Pengeluaran</div> -->
          <div class="flex items-center justify-center mx-auto mb-2">
            <n-icon size="32" class="text-red-600">
              <TrendingDownOutline />
            </n-icon>
          </div>
          <n-spin v-if="fetchFinance.loading.value" size="small" />
          <n-text v-else class="text-sm font-bold text-red-500">
            {{ formatCurrencySimple(totalIncomeExpanse.expanse) }}
          </n-text>
        </div>
      </div>
      <n-card class="bg-white rounded-2xl border-0 shadow-sm">
        <div class="text-center flex justify-center items-center flex-col">
          <div class="flex items-center justify-center mx-auto mb-2">
            <n-icon size="32" class="text-emerald-600">
              <TrendingUpOutline />
            </n-icon>
          </div>
          <n-spin v-if="fetchFinance.loading.value" size="small" />
          <n-text v-else class="text-sm font-bold text-green-500">
            {{ formatCurrencySimple(totalIncomeExpanse.income) }}
          </n-text>
        </div>
      </n-card>
    </div>

    <!-- Quick Actions -->
    <div class="mb-6">
      <n-card class="rounded-2xl border-0 shadow-sm bg-white">
        <div class="grid grid-cols-4 gap-4">
          <div class="text-center cursor-pointer" @click="handleTransfer">
            <div
              @click="message.warning('Maaf, fitur ini belum tersedia')"
              class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-2"
            >
              <n-icon size="24" class="text-blue-600">
                <NavigateOutline />
              </n-icon>
            </div>
            <div class="text-xs font-medium text-gray-700">Transfer</div>
          </div>
          <div class="text-center cursor-pointer" @click="handleRequest">
            <div
              @click="message.warning('Maaf, fitur ini belum tersedia')"
              class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-2"
            >
              <n-icon size="24" class="text-green-600">
                <GitPullRequestOutline />
              </n-icon>
            </div>
            <div class="text-xs font-medium text-gray-700">Minta</div>
          </div>
          <div class="text-center cursor-pointer" @click="handleTopUp">
            <div
              @click="message.warning('Maaf, fitur ini belum tersedia')"
              class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-2"
            >
              <n-icon size="24" class="text-purple-600">
                <CardOutline />
              </n-icon>
            </div>
            <div class="text-xs font-medium text-gray-700">Top Up</div>
          </div>
          <div class="text-center cursor-pointer" @click="handleMore">
            <div
              class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-2"
            >
              <n-icon size="24" class="text-gray-600">
                <EllipsisHorizontalOutline />
              </n-icon>
            </div>
            <div class="text-xs font-medium text-gray-700">Lainnya</div>
          </div>
        </div>
      </n-card>
    </div>

    <!-- Date Range Filter -->
    <div class="mb-6">
      <n-card class="rounded-2xl border-0 shadow-sm bg-white">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-gray-800">Statistik Pengeluaran</h3>
          <n-button size="small" @click="showDateFilter = !showDateFilter">
            <template #icon>
              <n-icon><FilterIcon /></n-icon>
            </template>
            Filter
          </n-button>
        </div>

        <!-- Date Range Picker -->
        <div v-if="showDateFilter" class="mb-4 p-3 bg-gray-50 rounded-xl">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-gray-500 mb-1"
                >Dari Tanggal</label
              >
              <n-date-picker
                v-model:value="dateRange.start"
                type="date"
                size="small"
                class="w-full"
                :is-date-disabled="disableFutureDate"
              />
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1"
                >Sampai Tanggal</label
              >
              <n-date-picker
                v-model:value="dateRange.end"
                type="date"
                size="small"
                class="w-full"
                :is-date-disabled="disableFutureDate"
              />
            </div>
          </div>
          <div class="flex gap-2 mt-3">
            <n-button
              size="small"
              @click="applyDateFilter"
              type="primary"
              class="flex-1"
            >
              Terapkan
            </n-button>
            <n-button size="small" @click="resetDateFilter" class="flex-1">
              Reset
            </n-button>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-if="
            dailyExpansesStatistics === null ||
            dailyExpansesStatistics.length == 0
          "
          class="text-center py-8"
        >
          <n-icon size="48" class="text-gray-300 mb-3">
            <ReceiptIcon />
          </n-icon>
          <div class="text-gray-500 mb-2">Belum ada transaksi hari ini</div>
          <div class="text-xs text-gray-400">
            Transaksi yang dilakukan hari ini akan muncul di sini
          </div>
        </div>

        <!-- Statistics Chart -->
        <swiper
          v-if="dailyExpansesStatistics.length > 0"
          :slides-per-view="1.2"
          :space-between="10"
          :centered-slides="true"
          :pagination="{ clickable: true }"
          class="my-swiper"
        >
          <swiper-slide v-for="item in dailyExpansesStatistics" :key="item.id">
            <div
              class="bg-gradient-to-r w-full from-blue-50 to-indigo-50 rounded-xl p-4"
            >
              <div class="flex justify-between items-center mb-4">
                <div>
                  <div class="text-sm text-gray-600">Pengeluaran Hari Ini</div>
                  <div class="text-2xl font-bold text-gray-800">
                    Rp {{ formatCurrency(item.total) }}
                  </div>
                </div>
                <n-tag :bordered="false" type="warning" size="small">
                  -{{ item.percentage_from_yesterday }}% dari kemarin
                </n-tag>
              </div>

              <!-- Simple Bar Chart -->
              <div class="space-y-2">
                <div
                  v-for="day in item.expanses"
                  :key="day.name"
                  class="flex items-center gap-3"
                >
                  <div class="w-12 text-xs text-gray-500">{{ day.name }}</div>
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <div
                        class="h-3 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full"
                        :style="{ width: day.percentage + '%' }"
                      ></div>
                      <div class="text-xs text-gray-600 w-16">
                        {{ day.percentage }}%
                      </div>
                    </div>
                  </div>
                  <div
                    class="w-16 text-right text-sm font-medium text-gray-800"
                  >
                    Rp {{ formatCurrency(day.amount) }}
                  </div>
                </div>
              </div>
            </div>
          </swiper-slide>
        </swiper>
      </n-card>
    </div>

    <!-- Today's Transactions -->
    <div>
      <div class="flex justify-between items-center mb-4">
        <h3 class="font-semibold text-gray-800">Transaksi Hari Ini</h3>
        <n-button
          @click="router.push('/m/transactions')"
          text
          size="small"
          class="text-blue-500"
        >
          Lihat Semua
          <template #icon>
            <n-icon><ChevronRightIcon /></n-icon>
          </template>
        </n-button>
      </div>

      <!-- list transactions -->
      <n-card class="rounded-2xl border-0 shadow-sm bg-white">
        <div class="space-y-3">
          <div
            v-for="transaction in todaysTransactions"
            :key="transaction.id"
            class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl transition-colors duration-200 cursor-pointer"
            @click="viewTransactionDetail(transaction.id)"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-xl flex items-center justify-center"
                :class="
                  transaction.type === 'pengeluaran'
                    ? 'bg-red-100'
                    : 'bg-green-100'
                "
              >
                <n-icon
                  size="18"
                  :class="
                    transaction.type === 'pengeluaran'
                      ? 'text-red-600'
                      : 'text-green-600'
                  "
                >
                  <component :is="getTransactionIcon(transaction.category)" />
                </n-icon>
              </div>
              <div>
                <div class="font-medium text-gray-800 capitalize">
                  {{ transaction.title }}
                </div>
                <div class="text-xs text-gray-500 flex items-center gap-1">
                  <n-icon size="12">
                    <ClockIcon />
                  </n-icon>
                  {{ transaction.time }}
                  <span class="mx-1">•</span>
                  {{ transaction.category }}
                </div>
              </div>
            </div>
            <div
              class="text-right"
              :class="
                transaction.type === 'pengeluaran'
                  ? 'text-red-600'
                  : 'text-green-600'
              "
            >
              <div class="font-semibold">
                {{ transaction.type === "pengeluaran" ? "-" : "+" }}Rp
                {{ formatCurrency(transaction.amount) }}
              </div>
              <div class="text-xs text-gray-500">{{ transaction.wallet }}</div>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-8">
          <n-spin size="large" />
          <div class="text-gray-500 mt-4">Memuat transaksi...</div>
        </div>

        <!-- Empty State -->
        <div v-if="todaysTransactions.length === 0" class="text-center py-8">
          <n-icon size="48" class="text-gray-300 mb-3">
            <ReceiptIcon />
          </n-icon>
          <div class="text-gray-500 mb-2">Belum ada transaksi hari ini</div>
          <div class="text-xs text-gray-400">
            Transaksi yang dilakukan hari ini akan muncul di sini
          </div>
        </div>
      </n-card>
    </div>
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
          <n-card title="Informasi User" class="w-full">
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
import { ref, onMounted } from "vue";
import { NCard, NButton, NIcon, NTag, NDatePicker, useMessage } from "naive-ui";

// Icons
import {
  Send as SendIcon,
  FilterOutline as FilterIcon,
  ChevronForwardOutline as ChevronRightIcon,
  TimeOutline as ClockIcon,
  Receipt as ReceiptIcon,
  FastFood as FoodIcon,
  CarOutline as TransportIcon,
  CartOutline as ShoppingIcon,
  MedicalOutline as HealthIcon,
  BusinessOutline as EntertainmentIcon,
  GitPullRequestOutline,
  CardOutline,
  EllipsisHorizontalOutline,
  WalletOutline,
  FileTrayOutline,
  NavigateOutline,
  TrendingDownOutline,
  TrendingUpOutline,
} from "@vicons/ionicons5";
import { useRouter } from "vue-router";
import { useTransaction } from "@/composables/useTransaction";
import {
  formatCurrencySimple,
  formatDate,
  formatDateTime,
  formatNumber,
  formatTime,
} from "@/utils/formatter";
import { useFinance } from "@/composables/useFinance";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/pagination";

const router = useRouter();
const message = useMessage();
const {
  fetchTransactions,
  transactions,
  loading,
  getTransactionById,
  response,
} = useTransaction();
const fetchFinance = useFinance();

// Reactive State
const showDateFilter = ref(false);
const dateRange = ref({
  start: Date.now(),
  end: Date.now(),
});

// Data
// const dailyExpenses = ref([
//   { name: "Sen", amount: 320000, percentage: 100 },
//   { name: "Sel", amount: 280000, percentage: 88 },
//   { name: "Rab", amount: 350000, percentage: 109 },
//   { name: "Kam", amount: 250000, percentage: 78 },
//   { name: "Jum", amount: 300000, percentage: 94 },
//   { name: "Sab", amount: 420000, percentage: 131 },
//   { name: "Min", amount: 380000, percentage: 119 },
// ]);

const todaysTransactions = ref([]);

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat("id-ID").format(amount);
};

const getTransactionIcon = (category) => {
  const icons = {
    Makanan: FoodIcon,
    Transportasi: TransportIcon,
    Belanja: ShoppingIcon,
    Kesehatan: HealthIcon,
    Hiburan: EntertainmentIcon,
    Transfer: SendIcon,
    umum: SendIcon,
  };
  return icons[category] || ReceiptIcon;
};

const disableFutureDate = (timestamp) => {
  return timestamp > Date.now();
};

const applyDateFilter = () => {
  getIncomeExpanseTotal();
  // showDateFilter.value = false;
};

const resetDateFilter = () => {
  dateRange.value.start = null;
  dateRange.value.end = null;

  getIncomeExpanseTotal();
  showDateFilter.value = false;
};

const handleTransfer = () => {
  console.log("Transfer clicked");
};

const handleRequest = () => {
  console.log("Request clicked");
};

const handleTopUp = () => {
  console.log("Top Up clicked");
};

const handleMore = () => {
  message.warning("Maaf, fitur ini belum tersedia");
};

const loadTransactions = async () => {
  try {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");
    const todayFormatted = `${year}-${month}-${day}`;

    await fetchTransactions({ itemsPerPage: 5, start_at: todayFormatted });
    const dataTransactions = transactions.value;
    if (response.value.success) {
      dataTransactions.map((t) => {
        todaysTransactions.value.push({
          id: t.id,
          title: t.title,
          category: t.category || "umum",
          amount: t.amount,
          type: t.type,
          time: formatTime(new Date(t.created_at)),
          wallet: t.finance.title,
        });
      });
    }
  } catch (error) {
    console.error("Error loading transactions:", error);
    message.error("Terjadi kesalahan saat memuat transaksi");
  }
};

const totalMountFinance = ref(0);

const getTotalAmountFinance = async () => {
  try {
    await fetchFinance.getTotalFinance();
    totalMountFinance.value = fetchFinance.response.value;
  } catch (error) {
    message.error("Gagal mengambil total saldo");
  }
};

const totalIncomeExpanse = ref({
  income: 0,
  expanse: 0,
});

const getIncomeExpanseTotal = async () => {
  try {
    const params = {};

    if (dateRange.value.start && dateRange.value.end) {
      params.start_at = new Date(dateRange.value.start)
        .toISOString()
        .split("T")[0];
      params.end_at = new Date(dateRange.value.end).toISOString().split("T")[0];
    }

    await fetchFinance.getIncomeExpanse(params);

    const resData = fetchFinance.response.value;

    totalIncomeExpanse.value.income = resData.income;
    totalIncomeExpanse.value.expanse = resData.expanse;
  } catch (error) {
    message.error("Terjadi kesalahan perhitungan pemasukan & pengeluaran");
  }
};

const dailyExpansesStatistics = ref([]);

const getFinanceDailyExpenses = async () => {
  try {
    await fetchFinance.getFinanceDailyExpenses();
    const dataExpanseFinances = fetchFinance.response.value.expanses;
    dataExpanseFinances.map((item) => {
      const days = item.days;
      const template = {
        title: item.title,
        id: item.id,
        total: item.total,
        percentage_from_yesterday: item.percentage_from_yesterday,
        expanses: [
          {
            name: "Sen",
            amount: days.monday,
            percentage:
              item.daily_limit > 0
                ? Math.floor((days.monday / item.daily_limit) * 100)
                : 0,
          },
          {
            name: "Sel",
            amount: days.tuesday,
            percentage:
              item.daily_limit > 0
                ? Math.floor((days.tuesday / item.daily_limit) * 100)
                : 0,
          },
          {
            name: "Rab",
            amount: days.wednesday,
            percentage:
              item.daily_limit > 0
                ? Math.floor((days.wednesday / item.daily_limit) * 100)
                : 0,
          },
          {
            name: "Kam",
            amount: days.thursday,
            percentage:
              item.daily_limit > 0
                ? Math.floor((days.thursday / item.daily_limit) * 100)
                : 0,
          },
          {
            name: "Jum",
            amount: days.friday,
            percentage:
              item.daily_limit > 0
                ? Math.floor((days.friday / item.daily_limit) * 100)
                : 0,
          },
          {
            name: "Sab",
            amount: days.saturday,
            percentage:
              item.daily_limit > 0
                ? Math.floor((days.saturday / item.daily_limit) * 100)
                : 0,
          },
          {
            name: "Min",
            amount: days.sunday,
            percentage:
              item.daily_limit > 0
                ? Math.floor((days.sunday / item.daily_limit) * 100)
                : 0,
          },
        ],
      };

      dailyExpansesStatistics.value.push(template);
    });
  } catch (error) {
    message.error(
      "Terjadi kesalahan saat pengambilan data statistik pengeluaran mingguan"
    );
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

onMounted(() => {
  loadTransactions();
  getTotalAmountFinance();
  getIncomeExpanseTotal();
  getFinanceDailyExpenses();
});
</script>

<style scoped>
.n-card {
  border-radius: 16px;
}

.n-button {
  border-radius: 12px;
}
</style>
