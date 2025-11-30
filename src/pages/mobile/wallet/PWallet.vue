<template>
  <div class="min-h-screen p-4">
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <n-spin size="large" />
      <div class="text-gray-500 mt-4">Memuat dompet...</div>
    </div>

    <!-- Content when not loading -->
    <div v-else>
      <!-- Total Balance Card -->
      <div class="mb-6">
        <n-card
          class="bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg rounded-2xl border-0"
        >
          <div class="text-center py-4">
            <div class="text-sm opacity-90">Total Saldo</div>
            <div class="text-3xl font-bold my-2">
              Rp {{ formatCurrency(totalBalance) }}
            </div>
            <n-tag :bordered="false" type="success" size="small">
              {{ finances.length }} Dompet
            </n-tag>
          </div>
        </n-card>
        <n-button
          v-if="finances.length > 0"
          class="w-full mt-2"
          size="large"
          secondary
          strong
          type="info"
          @click="showAddWalletModal = true"
        >
          <template #icon>
            <n-icon><PlusIcon /></n-icon>
          </template>
          Tambah Dompet
        </n-button>
      </div>

      <!-- Wallets Grid -->
      <div class="space-y-4">
        <n-card
          v-for="wallet in finances"
          :key="wallet.id"
          class="wallet-card rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 border-0 cursor-pointer"
          :class="getWalletCardClass(wallet.type)"
          @click="viewWalletDetails(wallet)"
        >
          <div class="p-4">
            <!-- Header -->
            <div class="flex justify-between items-start mb-4">
              <div class="flex items-center gap-3">
                <div
                  class="p-2 rounded-full"
                  :class="getWalletIconBgClass(wallet.type)"
                >
                  <n-icon size="20" :class="getWalletIconColor(wallet.type)">
                    <component :is="getWalletIcon(wallet.type)" />
                  </n-icon>
                </div>
                <div>
                  <div class="font-semibold text-gray-800">
                    {{ wallet.title }}
                  </div>
                  <n-tag
                    size="small"
                    :type="getWalletTagType(wallet.type)"
                    class="mt-1"
                  >
                    {{ getWalletTypeLabel(wallet.type) }}
                  </n-tag>
                </div>
              </div>
            </div>

            <!-- Balance -->
            <div class="mb-4">
              <div class="text-2xl font-bold text-gray-800">
                Rp {{ formatCurrency(wallet.amount) }}
              </div>
            </div>

            <!-- Footer -->
            <div
              class="flex justify-between items-center text-sm text-gray-500"
            >
              <div class="flex items-center gap-1">
                <n-icon size="14">
                  <TransactionIcon />
                </n-icon>
                <span>{{ wallet.total_transactions || 0 }} transaksi</span>
              </div>
              <div class="text-xs px-2 py-1 rounded-full bg-gray-100">
                {{ formatLastUpdated(wallet.updated_at) }}
              </div>
            </div>
          </div>
        </n-card>
      </div>

      <!-- Empty State -->
      <div v-if="finances.length === 0" class="text-center py-12">
        <n-icon size="64" class="text-gray-300 mb-4">
          <WalletIcon />
        </n-icon>
        <div class="text-gray-500 mb-4">Belum ada dompet</div>
        <n-button type="primary" @click="showAddWalletModal = true">
          <template #icon>
            <n-icon><PlusIcon /></n-icon>
          </template>
          Tambah Dompet Pertama
        </n-button>
      </div>
    </div>

    <!-- Add/Edit Wallet Modal -->
    <n-modal
      v-model:show="showAddWalletModal"
      :mask-closable="false"
      preset="card"
      :title="'Tambah Dompet'"
      class="max-w-md"
      :bordered="false"
    >
      <n-form ref="formRef" :model="formValue" :rules="rules">
        <n-form-item label="Nama Dompet" path="title">
          <n-input
            v-model:value="formValue.title"
            placeholder="Contoh: Dompet Utama"
            maxlength="50"
            show-count
          />
        </n-form-item>

        <n-form-item label="Tipe Dompet" path="type">
          <n-radio-group v-model:value="formValue.type" name="walletType">
            <n-space>
              <n-radio value="tunai">Tunai</n-radio>
              <n-radio value="rekening">Rekening</n-radio>
              <n-radio disabled="true" value="ewallet">E-Wallet</n-radio>
              <n-radio disabled="true" value="investment">Investasi</n-radio>
            </n-space>
          </n-radio-group>
        </n-form-item>

        <n-form-item label="Saldo Awal" path="amount">
          <n-input-number
            v-model:value="formValue.amount"
            :min="0"
            class="w-full"
            placeholder="0"
          >
            <template #prefix>
              <span class="text-gray-500">Rp</span>
            </template>
          </n-input-number>
        </n-form-item>

        <n-form-item label="Batas Harian" path="daily_limit">
          <n-input-number
            v-model:value="formValue.daily_limit"
            :min="0"
            class="w-full"
            placeholder="0"
          >
            <template #prefix>
              <span class="text-gray-500">Rp</span>
            </template>
          </n-input-number>
        </n-form-item>
        <n-alert type="warning">
          Limit harian tidak membatasi jumlah transaksi anda, limit harian hanya
          digunakan sebagai pengingat.
        </n-alert>
      </n-form>

      <template #footer>
        <div class="flex gap-2 justify-end">
          <n-button @click="showAddWalletModal = false" :disabled="submitting"
            >Batal</n-button
          >
          <n-button type="primary" @click="handleSubmit" :loading="submitting">
            Simpan
          </n-button>
        </div>
      </template>
    </n-modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import {
  NButton,
  NCard,
  NTag,
  NModal,
  NForm,
  NFormItem,
  NInput,
  NInputNumber,
  NRadioGroup,
  NRadio,
  NSpace,
  NIcon,
  useMessage,
  NSpin,
} from "naive-ui";

// Icons
import {
  Add as PlusIcon,
  CardOutline as WalletIcon,
  Receipt as TransactionIcon,
  CardOutline as BankIcon,
  CardOutline as CashIcon,
  PhonePortraitOutline as EwalletIcon,
  TrendingUp as InvestmentIcon,
} from "@vicons/ionicons5";

import { useFinance } from "@/composables/useFinance";
import { useAuth } from "@/composables/useAuth";

// Reactive State
const showAddWalletModal = ref(false);
const submitting = ref(false);
const message = useMessage();

const { fetchFinances, finances, loading, storeFinance } = useFinance();
const fetchUser = useAuth();

const formValue = ref({
  title: "",
  type: "tunai",
  amount: 0,
  daily_limit: 0,
});

// Computed
const totalBalance = computed(() => {
  return finances.value.reduce(
    (total, wallet) => total + (wallet.amount || 0),
    0
  );
});

// Form Validation
const rules = {
  title: {
    required: true,
    message: "Nama dompet harus diisi",
    trigger: "blur",
  },
  amount: {
    type: "number",
    required: true,
    message: "Saldo harus diisi",
    trigger: "blur",
  },
};

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat("id-ID").format(amount);
};

const formatLastUpdated = (dateString) => {
  if (!dateString) return "Baru saja";

  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now - date);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays === 1) return "1 hari lalu";
  if (diffDays < 7) return `${diffDays} hari lalu`;
  if (diffDays < 30) return `${Math.ceil(diffDays / 7)} minggu lalu`;
  return `${Math.ceil(diffDays / 30)} bulan lalu`;
};

const getWalletIcon = (type) => {
  const icons = {
    tunai: CashIcon,
    rekening: BankIcon,
    ewallet: EwalletIcon,
    investment: InvestmentIcon,
  };
  return icons[type] || WalletIcon;
};

const getWalletTypeLabel = (type) => {
  const labels = {
    tunai: "Tunai",
    rekening: "Rekening",
    ewallet: "E-Wallet",
    investment: "Investasi",
  };
  return labels[type] || "Lainnya";
};

const getWalletTagType = (type) => {
  const types = {
    tunai: "info",
    rekening: "success",
    ewallet: "warning",
    investment: "error",
  };
  return types[type] || "default";
};

const getWalletCardClass = (type) => {
  const classes = {
    tunai: "bg-blue-50 border-blue-200",
    rekening: "bg-green-50 border-green-200",
    ewallet: "bg-purple-50 border-purple-200",
    investment: "bg-amber-50 border-amber-200",
  };
  return classes[type] || "bg-gray-50 border-gray-200";
};

const getWalletIconBgClass = (type) => {
  const classes = {
    tunai: "bg-blue-100",
    rekening: "bg-green-100",
    ewallet: "bg-purple-100",
    investment: "bg-amber-100",
  };
  return classes[type] || "bg-gray-100";
};

const getWalletIconColor = (type) => {
  const classes = {
    tunai: "text-blue-600",
    rekening: "text-green-600",
    ewallet: "text-purple-600",
    investment: "text-amber-600",
  };
  return classes[type] || "text-gray-600";
};

const viewWalletDetails = (wallet) => {
  message.info(`Membuka detail: ${wallet.title}`);
  // Navigate to wallet detail page or show transactions for this wallet
};

const handleSubmit = async () => {
  submitting.value = true;

  try {
    // get user id
    await fetchUser.getUser();

    formValue.value = {
      ...formValue.value,
      user_id: fetchUser.user.value.id,
    };

    // Add new wallet
    await storeFinance(formValue.value);
    message.success("Dompet berhasil ditambahkan");

    showAddWalletModal.value = false;
    resetForm();

    // Refresh the list
    await loadFinances();
  } catch (error) {
    console.error("Error saving wallet:", error);
    message.error(error.response?.data?.message || "Terjadi kesalahan");
  } finally {
    submitting.value = false;
  }
};

const resetForm = () => {
  formValue.value = {
    title: "",
    type: "tunai",
    amount: 0,
    color: "#3B82F6",
  };
};

const loadFinances = async () => {
  try {
    await fetchFinances();
  } catch (error) {
    console.error("Error loading finances:", error);
    message.error("Gagal memuat data dompet");
  }
};

// Lifecycle
onMounted(async () => {
  await loadFinances();
});
</script>

<style scoped>
.wallet-card {
  transition: all 0.3s ease;
}

.wallet-card:hover {
  transform: translateY(-2px);
}

:deep(.n-card__content) {
  padding: 0;
}
</style>
