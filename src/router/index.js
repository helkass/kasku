import { createRouter, createWebHashHistory } from "vue-router";
import MainLayout from "@/components/MainLayout.vue";
import { useAuth } from "@/composables/useAuth";

const routes = [
  {
    path: "/login",
    name: "PLogin",
    component: import("@/pages/auth/PLogin.vue"),
    meta: { guest: true },
  },
  // superadmin
  {
    path: "/",
    name: "MainLayout",
    component: MainLayout,
    meta: { requiresSuperAdmin: true },
    children: [
      {
        path: "",
        name: "dashboard",
        component: () => import("@/pages/PDashboard.vue"),
        meta: { title: "Dashboard", requiresAuth: true },
      },
      {
        path: "/users",
        name: "users",
        component: () => import("@/pages/user/PUser.vue"),
        meta: { title: "Pengguna", requiresAuth: true },
      },
      {
        path: "/finances",
        name: "finances",
        component: () => import("@/pages/finance/PFinance.vue"),
        meta: { title: "Dompet Keuangan", requiresAuth: true },
      },
      {
        path: "/transactions",
        name: "transactions",
        component: () => import("@/pages/transaction/PTransaction.vue"),
        meta: { title: "Transaksi", requiresAuth: true },
      },
    ],
  },
  // general routes
  {
    path: "/m",
    name: "MobileLayout",
    component: () => import("@/components/MobileLayout.vue"),
    children: [
      {
        path: "", // path: "/m"
        name: "mobile-home",
        component: () => import("@/pages/mobile/home/PHome.vue"),
        meta: { title: "Dashboard", requiresAuth: true },
      },
      {
        path: "home",
        name: "home",
        component: () => import("@/pages/mobile/home/PHome.vue"),
        meta: { title: "Dashboard", requiresAuth: true },
      },
      {
        path: "wallets",
        name: "wallet",
        component: () => import("@/pages/mobile/wallet/PWallet.vue"),
        meta: { title: "Dompet", requiresAuth: true },
      },
      {
        path: "transactions",
        name: "transaction",
        component: () => import("@/pages/mobile/transaction/PTransaction.vue"),
        meta: { title: "Transaksi", requiresAuth: true },
      },
      {
        path: "account",
        name: "account",
        component: () => import("@/pages/mobile/account/PAccount.vue"),
        meta: { title: "Akun", requiresAuth: true },
      },
    ],
  },
];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const { getUser, user } = useAuth();
  const token = localStorage.getItem("token");

  // 1. Jika ada token, WAIT proses getUser() selesai dulu
  if (token) {
    try {
      await getUser(); // TUNGGU sampai selesai
    } catch (error) {
      localStorage.removeItem("token");
      return next("/login");
    }
  }

  // 2. Cek jika route membutuhkan auth tapi tidak ada token
  if (to.meta.requiresAuth && !token) {
    return next("/login");
  }

  // 3. Cek guest routes (login page) - harus setelah await getUser()
  if (to.meta.guest && token) {
    // User sudah login, redirect berdasarkan role
    if (user.value?.role === "superadmin") {
      return next("/");
    }
    return next("/m");
  }

  // 4. CEK UTAMA: Superadmin tidak boleh akses route /m
  // Pastikan ini setelah await getUser() dan cek guest routes
  if (token && to.path.startsWith("/m") && user.value?.role === "superadmin") {
    console.log("Blocked: Superadmin cannot access /m routes");
    return next("/");
  }

  // 5. Cek jika route membutuhkan role superadmin
  if (to.meta.requiresSuperAdmin && user.value?.role !== "superadmin") {
    return next("/m");
  }

  next();
});

router.afterEach(() => {
  // Scroll to top setiap kali pindah route
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });
});

export default router;
