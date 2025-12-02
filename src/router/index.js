import { createRouter, createWebHistory } from "vue-router";
import MainLayout from "@/components/MainLayout.vue";
import { useAuth } from "@/composables/useAuth";
import PLogin from "@/pages/auth/PLogin.vue";
import PDashboard from "@/pages/PDashboard.vue";
import PUser from "@/pages/user/PUser.vue";
import PFinance from "@/pages/finance/PFinance.vue";
import PTransaction from "@/pages/transaction/PTransaction.vue";
import MobileLayout from "@/components/MobileLayout.vue";
import PHome from "@/pages/mobile/home/PHome.vue";
import PWallet from "@/pages/mobile/wallet/PWallet.vue";
import PTransactionMobile from "@/pages/mobile/transaction/PTransaction.vue";
import PAccount from "@/pages/mobile/account/PAccount.vue";
import PFinanceDetail from "@/pages/finance/PFinanceDetail.vue";

const routes = [
  {
    path: "/login",
    name: "PLogin",
    component: PLogin,
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
        component: PDashboard,
        meta: { title: "Dashboard", requiresAuth: true },
      },
      {
        path: "/users",
        name: "users",
        component: PUser,
        meta: { title: "Pengguna", requiresAuth: true },
      },
      // finance routes
      {
        path: "/finances",
        name: "finances",
        component: PFinance,
        meta: { title: "Dompet Keuangan", requiresAuth: true },
      },
      {
        path: "/finances/:id",
        name: "finances-detail",
        component: PFinanceDetail,
        props: true,
        meta: { title: "Detail Dompet Keuangan", requiresAuth: true },
      },
      // transaction routes
      {
        path: "/transactions",
        name: "transactions",
        component: PTransaction,
        meta: { title: "Transaksi", requiresAuth: true },
      },
    ],
  },
  // general routes
  {
    path: "/m",
    name: "MobileLayout",
    component: MobileLayout,
    children: [
      {
        path: "", // path: "/m"
        name: "mobile-home",
        component: PHome,
        meta: { title: "Dashboard", requiresAuth: true },
      },
      {
        path: "home",
        name: "home",
        component: PHome,
        meta: { title: "Dashboard", requiresAuth: true },
      },
      {
        path: "wallets",
        name: "wallet",
        component: PWallet,
        meta: { title: "Dompet", requiresAuth: true },
      },
      {
        path: "transactions",
        name: "transaction",
        component: PTransactionMobile,
        meta: { title: "Transaksi", requiresAuth: true },
      },
      {
        path: "account",
        name: "account",
        component: PAccount,
        meta: { title: "Akun", requiresAuth: true },
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory("/helka/"),
  routes,
});

router.beforeEach(async (to, from, next) => {
  document.title = to.meta.title ? `${to.meta.title} | KASKU` : "KASKU App";

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
