<!-- layouts/MainLayout.vue -->
<template>
  <n-layout has-sider class="h-screen">
    <!-- Sidebar -->
    <n-layout-sider
      v-model:collapsed="sidebarCollapsed"
      :width="240"
      :collapsed-width="72"
      collapse-mode="width"
      show-trigger="bar"
      bordered
      :native-scrollbar="false"
      class="h-full"
    >
      <!-- Logo Section -->
      <div class="flex items-center justify-center p-4 border-b">
        <n-gradient-text type="success" size="20">
          {{ sidebarCollapsed ? "K" : "KASKU" }}
        </n-gradient-text>
      </div>

      <!-- Navigation Menu -->
      <n-menu
        v-model:value="activeKey"
        :collapsed="sidebarCollapsed"
        :collapsed-width="72"
        :collapsed-icon-size="20"
        :options="menuOptions"
        :render-label="renderMenuLabel"
        @update:value="handleMenuSelect"
      />
    </n-layout-sider>

    <!-- Main Content Area -->
    <n-layout class="h-full">
      <!-- Header -->
      <n-layout-header
        bordered
        class="h-16 px-4 flex items-center justify-between w-full"
      >
        <div class="flex items-center gap-4">
          <n-breadcrumb>
            <n-breadcrumb-item
              v-for="(item, index) in breadcrumbs"
              :key="index"
              @click="handleBreadcrumbClick(item)"
            >
              {{ item.label }}
            </n-breadcrumb-item>
          </n-breadcrumb>
        </div>

        <div class="flex items-center gap-3">
          <n-dropdown :options="userOptions" @select="handleUserSelect">
            <n-button quaternary>
              <template #icon>
                <n-icon><PersonCircleOutline /></n-icon>
              </template>
              {{ user.name }}
            </n-button>
          </n-dropdown>
        </div>
      </n-layout-header>

      <!-- Main Content -->
      <n-layout-content
        :native-scrollbar="false"
        content-style="padding: 20px;"
        class="bg-gray-50 dark:bg-gray-900"
      >
        <div class="max-w-7xl mx-auto">
          <router-view />
        </div>
      </n-layout-content>
    </n-layout>
  </n-layout>
</template>

<script setup>
import { ref, computed, watch, h } from "vue"; // Jangan lupa import 'h'
import { useRoute, useRouter } from "vue-router";
import {
  NLayout,
  NLayoutSider,
  NLayoutHeader,
  NLayoutContent,
  NMenu,
  NBreadcrumb,
  NBreadcrumbItem,
  NButton,
  NIcon,
  NDropdown,
  NGradientText,
} from "naive-ui";
import {
  HomeOutline,
  PersonCircleOutline,
  SettingsOutline,
  LogOutOutline,
  WalletOutline,
  ListOutline,
} from "@vicons/ionicons5";
import { useAuth } from "@/composables/useAuth";

// Composables
const route = useRoute();
const router = useRouter();
const fetchAuth = useAuth();

// Reactive state
const sidebarCollapsed = ref(false);
const activeKey = ref(route.name);

// User data
const user = ref({
  name: "Superadmin",
  email: "superadmin@kasku.com",
});

const renderIcon = (icon) => {
  return () => h(NIcon, null, { default: () => h(icon) });
};

// Menu options
const menuOptions = computed(() => [
  {
    label: "Dashboard",
    key: "dashboard",
    icon: renderIcon(HomeOutline),
  },
  {
    label: "Pengguna",
    key: "users",
    icon: renderIcon(PersonCircleOutline),
  },
  {
    label: "Dompet",
    key: "finances",
    icon: renderIcon(WalletOutline),
  },
  {
    label: "Transaksi",
    key: "transactions",
    icon: renderIcon(ListOutline),
  },
]);

// User dropdown options
const userOptions = [
  {
    label: "Profile",
    key: "profile",
    icon: renderIcon(PersonCircleOutline),
  },
  {
    label: "Settings",
    key: "settings",
    icon: renderIcon(SettingsOutline),
  },
  {
    type: "divider",
    key: "divider",
  },
  {
    label: "Logout",
    key: "logout",
    icon: renderIcon(LogOutOutline),
  },
];

// Breadcrumbs
const breadcrumbs = computed(() => {
  const pathArray = route.path.split("/").filter((path) => path);
  const crumbs = [{ label: "Dashboard", path: "/" }];

  pathArray.forEach((path, index) => {
    const routePath = `/${pathArray.slice(0, index + 1).join("/")}`;
    crumbs.push({
      label: path.charAt(0).toUpperCase() + path.slice(1),
      path: routePath,
    });
  });

  return crumbs;
});

// Methods
const handleMenuSelect = (key) => {
  router.push({ name: key });
};

const handleBreadcrumbClick = (item) => {
  if (item.path) {
    router.push(item.path);
  }
};

const handleUserSelect = (key) => {
  switch (key) {
    case "profile":
      router.push("/profile");
      break;
    case "settings":
      router.push("/settings");
      break;
    case "logout":
      handleLogout();
      break;
  }
};

const handleLogout = () => {
  fetchAuth.logout();
};

const renderMenuLabel = (option) => {
  return option.label;
};

// Watch route changes
watch(
  () => route.name,
  (newName) => {
    activeKey.value = newName;
  }
);
</script>

<style scoped>
.h-screen {
  height: 100vh;
}

.bg-gray-50 {
  background-color: #f9fafb;
}

.dark .bg-gray-900 {
  background-color: #111827;
}

:deep(.n-layout-sider) {
  transition: all 0.3s ease;
}

:deep(.n-menu-item) {
  margin: 4px 8px;
  border-radius: 6px;
}

:deep(.n-menu-item--selected) {
  background-color: #10b981;
  color: white;
}

:deep(.custom-menu .n-menu-item) {
  margin: 4px 8px;
  border-radius: 6px;
}

:deep(.custom-menu .n-menu-item-content) {
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

:deep(.custom-menu .n-menu-item-content--collapsed) {
  justify-content: center;
}

:deep(.custom-menu .n-menu-item-content__icon) {
  margin-right: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

:deep(.custom-menu .n-menu-item-content--collapsed .n-menu-item-content__icon) {
  margin-right: 0;
}

:deep(.custom-menu .n-menu-item-content--collapsed .n-menu-item-content__icon) {
  width: 100%;
  display: flex;
  justify-content: center;
}
</style>
