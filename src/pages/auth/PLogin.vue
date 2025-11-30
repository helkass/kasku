<template>
  <div class="login-page">
    <n-space
      vertical
      align="center"
      size="large"
      style="width: 100%; max-width: 400px"
    >
      <!-- Logo / Judul -->
      <h1 class="text-center">Kasku Finance</h1>

      <!-- Form Login -->
      <n-form :model="form" ref="loginForm" @submit.prevent="handleLogin">
        <n-alert
          v-if="errorMessage"
          type="error"
          class="mb-3"
          closable
          @close="errorMessage = ''"
        >
          {{ errorMessage }}
        </n-alert>
        <n-form-item label="Username" path="username">
          <n-input
            v-model:value="form.username"
            placeholder="Masukkan username"
          />
        </n-form-item>

        <n-form-item label="Password" path="password">
          <n-input
            v-model:value="form.password"
            type="password"
            placeholder="Masukkan password"
          />
        </n-form-item>

        <n-form-item>
          <n-button
            type="primary"
            block
            :loading="loading"
            @click="handleLogin"
          >
            Login
          </n-button>
        </n-form-item>
      </n-form>
    </n-space>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useAuth } from "@/composables/useAuth";

const { login } = useAuth();

const form = reactive({
  username: "",
  password: "",
});

const loginForm = ref(null);
const loading = ref(false);
const errorMessage = ref("");

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = "";

  try {
    const res = await login(form.username, form.password);
    if (!res.success) {
      errorMessage.value = res.message || "Login gagal";
    }
  } catch (error) {
    errorMessage.value = JSON.stringify(error) || "Terjadi kesalahan test";
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.login-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #fff;
}

h1 {
  margin-bottom: 2rem;
  font-weight: bold;
}

.n-form {
  background: #ffffff;
  padding: 2rem;
  border-radius: 10px;
  width: 100%;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  color: #333;
}

.n-form-item-label {
  color: #333 !important;
}

.n-alert {
  margin-bottom: 12px;
}
</style>
