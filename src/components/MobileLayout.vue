<template>
  <n-config-provider :theme="lightTheme">
    <div class="mobile-layout">
      <!-- Header Redesign -->
      <header class="header">
        <div class="header-content">
          <div class="header-brand">
            <div class="brand-logo">
              <n-icon size="24" class="logo-icon">
                <WalletOutline />
              </n-icon>
              <n-text class="brand-name capitalize" strong>{{ username }}</n-text>
            </div>
            <n-text class="header-date">{{ currentDate }}</n-text>
          </div>
          <div class="header-actions">
            <n-button
              text
              @click="handleNotification"
              class="action-btn"
              size="small"
            >
              <n-icon size="20" class="text-gray-600">
                <NotificationsOutline />
              </n-icon>
            </n-button>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="main-content">
        <router-view />
      </main>

      <!-- Bottom Navigation Redesign -->
      <nav class="bottom-nav">
        <div
          v-for="item in navItems"
          :key="item.key"
          class="nav-item"
          :class="{ 'nav-item--active': activeTab === item.key }"
          @click="handleNavigation(item)"
        >
          <div class="nav-icon-container">
            <n-icon
              size="20"
              :component="item.icon"
              :class="
                activeTab === item.key ? 'text-blue-600' : 'text-gray-500'
              "
            />
          </div>
          <n-text
            class="nav-label"
            :class="activeTab === item.key ? 'text-blue-600' : 'text-gray-500'"
          >
            {{ item.label }}
          </n-text>
        </div>
      </nav>
    </div>
  </n-config-provider>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { NConfigProvider, NText, NButton, NIcon, lightTheme, useMessage } from "naive-ui";
import {useAuth} from "@/composables/useAuth";
import {
  HomeOutline,
  WalletOutline,
  ReceiptOutline,
  PersonOutline,
  NotificationsOutline,
} from "@vicons/ionicons5";

const router = useRouter();
const route = useRoute();
const {getUser, user} = useAuth();
const message = useMessage();

// Navigation state
const activeTab = ref("home");

// Navigation items
const navItems = [
  {
    key: "home",
    label: "Home",
    icon: HomeOutline,
    route: "/m",
    title: "Beranda",
  },
  {
    key: "dompet",
    label: "Dompet",
    icon: WalletOutline,
    route: "/m/wallets",
    title: "Dompet Saya",
  },
  {
    key: "transaksi",
    label: "Transaksi",
    icon: ReceiptOutline,
    route: "/m/transactions",
    title: "Riwayat Transaksi",
  },
  {
    key: "account",
    label: "Akun",
    icon: PersonOutline,
    route: "/m/account",
    title: "Akun Saya",
  },
];

// Computed current date
const currentDate = computed(() => {
  return new Date().toLocaleDateString("id-ID", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  });
});

// Navigation handler
const handleNavigation = (item) => {
  activeTab.value = item.key;
  router.push(item.route);
};

const handleNotification = () => {
  console.log("Notification clicked");
};

// Sync active tab with route
watch(
  () => route.path,
  (newPath) => {
    const currentNav = navItems.find((item) => item.route === newPath);
    if (currentNav) {
      activeTab.value = currentNav.key;
    }
  },
  { immediate: true }
);

const username = user.value.username;
const getUserAuth = async () => {
  try {
    await getUser();
  } catch (error) {
    message.error('Gagal mengambil Nama Pengguna');
  }
}

onMounted(() => {
  getUserAuth();
});

</script>

<style scoped>
.mobile-layout {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: #ffffff;
  color: #1f2937;
  max-width: 480px;
  margin: 0 auto;
  position: relative;
  box-shadow: 0 0 25px rgba(0, 0, 0, 0.08);
}

/* Header Redesign */
.header {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  padding: 16px 20px 12px 20px;
  border-bottom: 1px solid #f1f5f9;
  position: sticky;
  top: 0;
  z-index: 100;
  backdrop-filter: blur(10px);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.header-brand {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 8px;
}

.logo-icon {
  color: #3b82f6;
}

.brand-name {
  font-size: 20px;
  font-weight: 700;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.header-date {
  font-size: 13px;
  color: #6b7280;
  font-weight: 500;
}

.header-actions {
  display: flex;
  gap: 4px;
}

.action-btn {
  padding: 8px;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: #f3f4f6;
  transform: translateY(-1px);
}

/* Main Content */
.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 0;
  background: linear-gradient(to bottom, #f8fafc, #ffffff);
}

/* Bottom Navigation Redesign */
.bottom-nav {
  background: #ffffff;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-around;
  padding: 12px 0 8px 0;
  position: sticky;
  bottom: 0;
  z-index: 100;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.06);
  backdrop-filter: blur(10px);
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  min-width: 64px;
  position: relative;
}

.nav-item:hover {
  background: #f8fafc;
  transform: translateY(-2px);
}

.nav-item--active {
  background: linear-gradient(135deg, #eff6ff, #f0f9ff);
}

.nav-item--active::before {
  content: "";
  position: absolute;
  top: -2px;
  left: 50%;
  transform: translateX(-50%);
  width: 24px;
  height: 3px;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  border-radius: 2px;
}

.nav-icon-container {
  padding: 6px;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.nav-item--active .nav-icon-container {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  transform: scale(1.1);
}

.nav-item--active .nav-icon-container :deep(svg) {
  color: white !important;
}

.nav-label {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: -0.01em;
  transition: all 0.3s ease;
}

.nav-item--active .nav-label {
  font-weight: 700;
}

/* Scrollbar styling */
.main-content::-webkit-scrollbar {
  width: 6px;
}

.main-content::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.main-content::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.main-content::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Responsive adjustments */
@media (max-width: 480px) {
  .mobile-layout {
    max-width: 100%;
    margin: 0;
    box-shadow: none;
  }

  .header {
    padding: 16px 16px 12px 16px;
  }

  .nav-item {
    padding: 8px 12px;
    min-width: 56px;
  }
}

/* Animation for page transitions */
.main-content {
  animation: fadeInUp 0.5s ease-out;
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

/* Ensure light theme consistency */
:deep(.n-button) {
  border-radius: 10px;
}

:deep(.n-card) {
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}
</style>
