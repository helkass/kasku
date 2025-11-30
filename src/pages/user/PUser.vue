<template>
  <n-space vertical size="large">
    <!-- Heading & Filter -->
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold">Pengguna</h1>
        <p class="text-gray-500">Daftar pengguna kasku</p>
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
    <n-card title="Daftar Pengguna">
      <div v-if="loading">
        <n-skeleton text :repeat="4" />
      </div>

      <div v-else-if="totalUsers === 0">
        <n-empty description="Tidak ada data transaksi">
          <template #extra>
            <n-button size="small" @click="refreshData">
              Refresh Data
            </n-button>
          </template>
        </n-empty>
      </div>

      <div v-else>
        <div class="mb-2 w-max">
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

        <n-data-table
          :bordered="false"
          :columns="columns"
          :data="users"
          :pagination="pagination"
        />
      </div>
    </n-card>
  </n-space>
</template>

<script setup>
import { ref, onMounted, h, computed } from "vue";
import { useMessage, NButton, NAlert } from "naive-ui";
import { useUser } from "@/composables/useUser";
import { SearchOutline } from "@vicons/ionicons5";
import { debounce } from "lodash-es";
const message = useMessage();
const loading = ref(true);

// Initialize composables
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
const users = ref([]);
const totalUsers = ref(0);

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
    key: "username",
    ellipsis: {
      tooltip: true,
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
  message.info(`Detail pengguna: ${rowData.username}`);
};

const handleSearch = debounce(async (searchValue) => {
  try {
    loading.value = true;

    // Update filter dengan nilai search terbaru
    const filterParams = {
      ...activeFilter.value,
      search: searchValue,
    };

    await fetchUser.fetchUsers(filterParams);
    updateUsers();

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
    await fetchUser.fetchUsers(activeFilter.value);
    updateUsers();

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
    await fetchUser.fetchUsers();
    updateUsers();

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
    await fetchUser.fetchUsers(params);
    updateUsers();

    message.success("Data diperbarui");
  } catch (error) {
    message.error("Gagal refresh data");
  } finally {
    loading.value = false;
  }
};

const updateUsers = () => {
  users.value = fetchUser.users.value;
  totalUsers.value = fetchUser.total.value;
};

// Load initial data
const loadInitialData = async () => {
  try {
    loading.value = true;

    await Promise.all([fetchUser.fetchUsers()]);

    updateUsers();
  } catch (err) {
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
