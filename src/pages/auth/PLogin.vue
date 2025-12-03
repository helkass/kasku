<template>
  <div class="login-container">
    <!-- Background dengan efek glassmorphism -->
    <div class="background-decor">
      <div class="circle circle-1"></div>
      <div class="circle circle-2"></div>
      <div class="circle circle-3"></div>
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
    </div>

    <!-- Container utama -->
    <div class="login-wrapper">
      <!-- Kartu login dengan glass effect -->
      <div class="login-card glass-effect">
        <!-- Header dengan logo -->
        <div class="login-header">
          <div class="logo-container">
            <div class="logo-circle">
              <n-icon size="32" color="#4f46e5" :depth="3">
                <CashOutline />
              </n-icon>
            </div>
            <div class="app-title">
              <h1>Kasku<span>Finance</span></h1>
              <p class="app-subtitle">Smart Money Management</p>
            </div>
          </div>
          <p class="welcome-text">Selamat datang! Silakan masuk ke akun Anda</p>
        </div>

        <!-- Form Login -->
        <div class="login-form">
          <!-- Alert Error -->
          <n-alert
            v-if="errorMessage"
            type="error"
            :bordered="false"
            class="login-alert"
            closable
            @close="errorMessage = ''"
          >
            <template #icon>
              <n-icon>
                <AlertCircleOutline />
              </n-icon>
            </template>
            {{ errorMessage }}
          </n-alert>

          <n-form
            ref="loginFormRef"
            :model="form"
            :rules="rules"
            size="large"
            :disabled="loading"
          >
            <!-- Username Field -->
            <n-form-item path="username" :show-label="false">
              <n-input
                v-model:value="form.username"
                placeholder="Username"
                :input-props="{ autocomplete: 'username' }"
                round
              >
                <template #prefix>
                  <n-icon :size="20" color="#9ca3af">
                    <PersonOutline />
                  </n-icon>
                </template>
              </n-input>
            </n-form-item>

            <!-- Password Field -->
            <n-form-item path="password" :show-label="false">
              <n-input
                v-model:value="form.password"
                type="password"
                placeholder="Password"
                :input-props="{ autocomplete: 'current-password' }"
                round
                @keyup.enter="handleLogin"
              >
                <template #prefix>
                  <n-icon :size="20" color="#9ca3af">
                    <LockClosedOutline />
                  </n-icon>
                </template>
                <template #suffix>
                  <n-button
                    text
                    @click="showPassword = !showPassword"
                    class="password-toggle"
                  >
                    <n-icon :size="18">
                      <EyeOutline v-if="!showPassword" />
                      <EyeOffOutline v-else />
                    </n-icon>
                  </n-button>
                </template>
              </n-input>
            </n-form-item>

            <!-- Login Button -->
            <n-button
              type="primary"
              :loading="loading"
              :disabled="!form.username || !form.password"
              @click="handleLogin"
              round
              size="large"
              class="login-button"
              :block="true"
            >
              <template #icon>
                <n-icon v-if="!loading">
                  <LogInOutline />
                </n-icon>
              </template>
              {{ loading ? "Memproses..." : "Masuk" }}
            </n-button>

            <!-- Register Link -->
            <div class="register-link">
              <span class="register-text">Belum punya akun? </span>
              <n-button text type="primary" size="small">
                Daftar sekarang
              </n-button>
            </div>
          </n-form>
        </div>

        <!-- Footer -->
        <div class="login-footer">
          <p class="footer-text">
            © 2024 KaskuFinance. All rights reserved.
            <n-button text size="tiny" type="info"> Privacy Policy </n-button>
            •
            <n-button text size="tiny" type="info"> Terms of Service </n-button>
          </p>
        </div>
      </div>

      <!-- Side info panel (hanya tampil di desktop besar) -->
      <div class="side-info">
        <div class="info-content">
          <h2 class="info-title">
            Kelola Keuangan <br />
            Lebih <span class="highlight">Mudah</span>
          </h2>
          <div class="stats">
            <div class="stat-item">
              <n-icon size="24" color="#10b981">
                <TrendingUpOutline />
              </n-icon>
              <div>
                <p class="stat-number">10K+</p>
                <p class="stat-label">Pengguna Aktif</p>
              </div>
            </div>
            <div class="stat-item">
              <n-icon size="24" color="#3b82f6">
                <ShieldCheckmarkOutline />
              </n-icon>
              <div>
                <p class="stat-number">100%</p>
                <p class="stat-label">Keamanan Data</p>
              </div>
            </div>
          </div>
          <div class="testimonial">
            <n-avatar round size="medium" src="/api/placeholder/48/48" />
            <div class="testimonial-content">
              <p class="testimonial-text">
                "KaskuFinance membantu saya mengontrol pengeluaran dengan lebih
                baik."
              </p>
              <p class="testimonial-author">— Helka, Business Owner</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useAuth } from "@/composables/useAuth";
import {
  NForm,
  NFormItem,
  NInput,
  NButton,
  NIcon,
  NAlert,
  NAvatar,
} from "naive-ui";

// Icons dari ionicons5
import {
  PersonOutline,
  LockClosedOutline,
  EyeOutline,
  EyeOffOutline,
  LogInOutline,
  AlertCircleOutline,
  CashOutline,
  TrendingUpOutline,
  ShieldCheckmarkOutline,
} from "@vicons/ionicons5";

const { login } = useAuth();

const form = reactive({
  username: "",
  password: "",
});

const loginFormRef = ref(null);
const loading = ref(false);
const errorMessage = ref("");
const showPassword = ref(false);

// Validation rules
const rules = {
  username: [
    {
      required: true,
      message: "Username harus diisi",
      trigger: "blur",
    },
    {
      min: 3,
      message: "Username minimal 3 karakter",
      trigger: "blur",
    },
  ],
  password: [
    {
      required: true,
      message: "Password harus diisi",
      trigger: "blur",
    },
    {
      min: 6,
      message: "Password minimal 6 karakter",
      trigger: "blur",
    },
  ],
};

const handleLogin = async () => {
  errorMessage.value = "";

  // Validate form
  try {
    await loginFormRef.value?.validate();
  } catch (errors) {
    return;
  }

  loading.value = true;

  try {
    const res = await login(form.username, form.password);

    if (!res.success) {
      errorMessage.value = res.message || "Username atau password salah";

      // Shake animation effect
      const card = document.querySelector(".login-card");
      card.classList.add("shake");
      setTimeout(() => card.classList.remove("shake"), 500);
    }
  } catch (error) {
    errorMessage.value = "Terjadi kesalahan pada server. Silakan coba lagi.";
    console.error("Login error:", error);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
  overflow: hidden;
}

.background-decor {
  position: absolute;
  width: 100%;
  height: 100%;
  overflow: hidden;
  pointer-events: none;
}

.circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  animation: float 15s infinite ease-in-out;
}

.circle-1 {
  width: 300px;
  height: 300px;
  top: -150px;
  right: -100px;
  animation-delay: 0s;
}

.circle-2 {
  width: 200px;
  height: 200px;
  bottom: -50px;
  left: -50px;
  background: rgba(79, 70, 229, 0.2);
  animation-delay: 5s;
}

.circle-3 {
  width: 150px;
  height: 150px;
  top: 50%;
  right: 10%;
  background: rgba(168, 85, 247, 0.15);
  animation-delay: 10s;
}

.blob {
  position: absolute;
  border-radius: 40% 60% 60% 40% / 60% 30% 70% 40%;
  background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), transparent);
  animation: morph 20s infinite linear;
}

.blob-1 {
  width: 400px;
  height: 400px;
  bottom: -200px;
  right: 20%;
  animation-delay: 0s;
}

.blob-2 {
  width: 300px;
  height: 300px;
  top: 20%;
  left: -100px;
  animation-delay: 10s;
}

.login-wrapper {
  display: flex;
  gap: 2rem;
  max-width: 1200px;
  width: 100%;
  z-index: 1;
}

.login-card {
  flex: 1;
  max-width: 440px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 24px;
  padding: 2.5rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  display: flex;
  flex-direction: column;
}

.glass-effect {
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.95) 0%,
    rgba(255, 255, 255, 0.85) 100%
  );
  backdrop-filter: blur(20px);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.logo-circle {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
}

.app-title h1 {
  font-size: 2rem;
  font-weight: 800;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
}

.app-title h1 span {
  color: #1f2937;
}

.app-subtitle {
  color: #6b7280;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.welcome-text {
  color: #4b5563;
  font-size: 1rem;
  margin: 0;
}

.login-form {
  flex: 1;
}

.login-alert {
  margin-bottom: 1.5rem;
  border-radius: 12px;
}

:deep(.n-input .n-input-wrapper) {
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

:deep(.n-input .n-input-wrapper:hover),
:deep(.n-input .n-input-wrapper.n-input-wrapper--focus) {
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.password-toggle {
  color: #9ca3af;
}

.password-toggle:hover {
  color: #4f46e5;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 1rem 0 1.5rem;
}

.login-button {
  margin-top: 0.5rem;
  height: 48px;
  font-weight: 600;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  border: none;
  transition: all 0.3s ease;
}

.login-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
}

.login-button:active {
  transform: translateY(0);
}

.divider {
  display: flex;
  align-items: center;
  margin: 1.5rem 0;
}

.divider::before,
.divider::after {
  content: "";
  flex: 1;
  height: 1px;
  background: #e5e7eb;
}

.divider-text {
  padding: 0 1rem;
  color: #9ca3af;
  font-size: 0.875rem;
}

.register-link {
  text-align: center;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.register-text {
  color: #6b7280;
  font-size: 0.875rem;
}

.login-footer {
  margin-top: auto;
  padding-top: 1.5rem;
  text-align: center;
}

.footer-text {
  color: #9ca3af;
  font-size: 0.75rem;
  line-height: 1.5;
}

.side-info {
  flex: 1;
  max-width: 400px;
  display: none;
}

@media (min-width: 1200px) {
  .side-info {
    display: block;
    padding: 3rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }
}

.info-content {
  color: white;
}

.info-title {
  font-size: 2.5rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 2rem;
}

.highlight {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stats {
  display: flex;
  gap: 2rem;
  margin-bottom: 2rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.stat-label {
  font-size: 0.875rem;
  opacity: 0.9;
  margin: 0;
}

.testimonial {
  display: flex;
  gap: 1rem;
  align-items: center;
  background: rgba(255, 255, 255, 0.1);
  padding: 1.5rem;
  border-radius: 16px;
  backdrop-filter: blur(10px);
}

.testimonial-content {
  flex: 1;
}

.testimonial-text {
  font-style: italic;
  margin: 0 0 0.5rem 0;
  font-size: 0.95rem;
}

.testimonial-author {
  font-size: 0.875rem;
  opacity: 0.8;
  margin: 0;
}

/* Animations */
@keyframes float {
  0%,
  100% {
    transform: translateY(0) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

@keyframes morph {
  0%,
  100% {
    border-radius: 40% 60% 60% 40% / 60% 30% 70% 40%;
  }
  25% {
    border-radius: 70% 30% 50% 50% / 30% 40% 60% 70%;
  }
  50% {
    border-radius: 50% 50% 30% 70% / 50% 50% 50% 50%;
  }
  75% {
    border-radius: 40% 60% 70% 30% / 40% 40% 60% 50%;
  }
}

.shake {
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%,
  100% {
    transform: translateX(0);
  }
  10%,
  30%,
  50%,
  70%,
  90% {
    transform: translateX(-5px);
  }
  20%,
  40%,
  60%,
  80% {
    transform: translateX(5px);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .login-card {
    padding: 2rem;
  }

  .app-title h1 {
    font-size: 1.75rem;
  }

  .info-title {
    font-size: 2rem;
  }
}

@media (max-width: 480px) {
  .login-card {
    padding: 1.5rem;
  }

  .form-options {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }
}
</style>
