<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <!-- Profile Section -->
    <div class="mb-6">
      <n-card class="rounded-2xl border-0 shadow-sm bg-white">
        <div class="text-center py-6">
          <!-- Avatar dengan Upload -->
          <div class="relative inline-block mb-4">
            <n-avatar
              round
              size="80"
              :src="userData.avatar"
              class="border-4 border-white shadow-lg"
            >
              <template #fallback>
                <n-icon size="32" class="text-gray-400">
                  <PersonIcon />
                </n-icon>
              </template>
            </n-avatar>

            <!-- Upload Button -->
            <n-button
              circle
              size="small"
              class="absolute bottom-0 right-0 bg-blue-500 hover:bg-blue-600 shadow-lg"
              @click="triggerAvatarUpload"
            >
              <template #icon>
                <n-icon size="16"><CameraIcon /></n-icon>
              </template>
            </n-button>

            <!-- Hidden File Input -->
            <input
              ref="avatarInput"
              type="file"
              accept="image/*"
              class="hidden"
              @change="handleAvatarUpload"
            />
          </div>

          <!-- User Info -->
          <div class="mb-2">
            <h2 class="text-xl font-bold text-gray-800">
              {{ userData.username }}
            </h2>
            <p class="text-gray-500 text-sm">{{ userData.email }}</p>
          </div>

          <!-- Member Since -->
          <n-tag :bordered="false" type="info" size="small">
            <template #icon>
              <n-icon><CalendarIcon /></n-icon>
            </template>
            Bergabung {{ userData.joinDate }}
          </n-tag>
        </div>
      </n-card>
    </div>

    <!-- Account Settings -->
    <div class="mb-6">
      <n-card class="rounded-2xl border-0 shadow-sm bg-white">
        <div class="space-y-1">
          <!-- Username (Read-only) -->
          <div class="setting-item">
            <div class="setting-content">
              <div class="setting-icon">
                <n-icon size="18" class="text-blue-500">
                  <PersonIcon />
                </n-icon>
              </div>
              <div class="setting-info">
                <div class="setting-label">Username</div>
                <div class="setting-value">{{ userData.username }}</div>
              </div>
            </div>
            <n-tag size="small" type="warning" :bordered="false">
              Tidak dapat diubah
            </n-tag>
          </div>

          <!-- Email -->
          <div class="setting-item">
            <div class="setting-content">
              <div class="setting-icon">
                <n-icon size="18" class="text-green-500">
                  <EmailIcon />
                </n-icon>
              </div>
              <div class="setting-info">
                <div class="setting-label">Email</div>
                <div class="setting-value">{{ userData.email }}</div>
              </div>
            </div>
            <n-button size="small" text class="text-blue-500">
              Verifikasi
            </n-button>
          </div>

          <!-- Phone -->
          <div class="setting-item">
            <div class="setting-content">
              <div class="setting-icon">
                <n-icon size="18" class="text-purple-500">
                  <PhoneIcon />
                </n-icon>
              </div>
              <div class="setting-info">
                <div class="setting-label">Nomor Telepon</div>
                <div class="setting-value">
                  {{ userData.phone || "Belum ditambahkan" }}
                </div>
              </div>
            </div>
            <n-button size="small" text class="text-blue-500">
              {{ userData.phone ? "Ubah" : "Tambahkan" }}
            </n-button>
          </div>
        </div>
      </n-card>
    </div>

    <!-- Security Settings -->
    <div class="mb-6">
      <n-card class="rounded-2xl border-0 shadow-sm bg-white">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-gray-800">Keamanan</h3>
          <n-icon size="20" class="text-gray-400">
            <SecurityIcon />
          </n-icon>
        </div>

        <div class="space-y-1">
          <!-- Change Password -->
          <div
            class="setting-item cursor-pointer"
            @click="showPasswordModal = true"
          >
            <div class="setting-content">
              <div class="setting-icon">
                <n-icon size="18" class="text-red-500">
                  <LockIcon />
                </n-icon>
              </div>
              <div class="setting-info">
                <div class="setting-label">Ubah Password</div>
                <div class="setting-value">
                  Terakhir diubah {{ userData.lastPasswordChange }}
                </div>
              </div>
            </div>
            <n-icon size="16" class="text-gray-400">
              <ChevronRightIcon />
            </n-icon>
          </div>

          <!-- Two Factor Authentication -->
          <div class="setting-item">
            <div class="setting-content">
              <div class="setting-icon">
                <n-icon size="18" class="text-orange-500">
                  <ShieldIcon />
                </n-icon>
              </div>
              <div class="setting-info">
                <div class="setting-label">Two-Factor Authentication</div>
                <div class="setting-value">Tambah lapisan keamanan ekstra</div>
              </div>
            </div>
            <n-switch v-model:value="userData.twoFactorEnabled" size="small" />
          </div>

          <!-- Login Activity -->
          <div class="setting-item cursor-pointer" @click="viewLoginActivity">
            <div class="setting-content">
              <div class="setting-icon">
                <n-icon size="18" class="text-blue-500">
                  <HistoryIcon />
                </n-icon>
              </div>
              <div class="setting-info">
                <div class="setting-label">Aktivitas Login</div>
                <div class="setting-value">Lihat riwayat login terbaru</div>
              </div>
            </div>
            <n-icon size="16" class="text-gray-400">
              <ChevronRightIcon />
            </n-icon>
          </div>
        </div>
      </n-card>
    </div>

    <!-- Preferences -->
    <div class="mb-6">
      <n-card class="rounded-2xl border-0 shadow-sm bg-white">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-gray-800">Preferensi</h3>
          <n-icon size="20" class="text-gray-400">
            <PreferencesIcon />
          </n-icon>
        </div>

        <div class="space-y-1">
          <!-- Currency -->
          <div class="setting-item">
            <div class="setting-content">
              <div class="setting-icon">
                <n-icon size="18" class="text-green-500">
                  <CurrencyIcon />
                </n-icon>
              </div>
              <div class="setting-info">
                <div class="setting-label">Mata Uang Default</div>
                <div class="setting-value">Rupiah (IDR)</div>
              </div>
            </div>
            <n-button size="small" text class="text-blue-500"> Ubah </n-button>
          </div>

          <!-- Language -->
          <div class="setting-item">
            <div class="setting-content">
              <div class="setting-icon">
                <n-icon size="18" class="text-purple-500">
                  <LanguageIcon />
                </n-icon>
              </div>
              <div class="setting-info">
                <div class="setting-label">Bahasa</div>
                <div class="setting-value">Indonesia</div>
              </div>
            </div>
            <n-button size="small" text class="text-blue-500"> Ubah </n-button>
          </div>

          <!-- Notifications -->
          <div class="setting-item">
            <div class="setting-content">
              <div class="setting-icon">
                <n-icon size="18" class="text-yellow-500">
                  <NotificationsIcon />
                </n-icon>
              </div>
              <div class="setting-info">
                <div class="setting-label">Notifikasi</div>
                <div class="setting-value">Kelola notifikasi aplikasi</div>
              </div>
            </div>
            <n-icon size="16" class="text-gray-400">
              <ChevronRightIcon />
            </n-icon>
          </div>
        </div>
      </n-card>
    </div>

    <!-- Actions -->
    <div class="space-y-3">
      <n-button
        block
        size="large"
        class="bg-blue-500 hover:bg-blue-600 text-white rounded-2xl"
        @click="showSupport"
      >
        <template #icon>
          <n-icon><SupportIcon /></n-icon>
        </template>
        Hubungi Support
      </n-button>

      <div class="grid grid-cols-2 gap-3">
        <n-button
          block
          size="large"
          class="bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl"
          @click="showAbout"
        >
          Tentang Aplikasi
        </n-button>
        <n-button
          block
          size="large"
          class="bg-red-50 hover:bg-red-100 text-red-600 rounded-2xl"
          @click="handleLogout"
        >
          Keluar
        </n-button>
      </div>
    </div>

    <!-- Change Password Modal -->
    <n-modal
      v-model:show="showPasswordModal"
      preset="card"
      title="Ubah Password"
      class="max-w-md"
      :bordered="false"
    >
      <n-form
        ref="passwordFormRef"
        :model="passwordForm"
        :rules="passwordRules"
      >
        <n-form-item label="Password Saat Ini" path="currentPassword">
          <n-input
            v-model:value="passwordForm.currentPassword"
            type="password"
            placeholder="Masukkan password saat ini"
            size="large"
            round
          />
        </n-form-item>

        <n-form-item label="Password Baru" path="newPassword">
          <n-input
            v-model:value="passwordForm.newPassword"
            type="password"
            placeholder="Masukkan password baru"
            size="large"
            round
          />
        </n-form-item>

        <n-form-item label="Konfirmasi Password Baru" path="confirmPassword">
          <n-input
            v-model:value="passwordForm.confirmPassword"
            type="password"
            placeholder="Konfirmasi password baru"
            size="large"
            round
          />
        </n-form-item>
      </n-form>

      <template #footer>
        <div class="flex gap-2 justify-end">
          <n-button @click="showPasswordModal = false">Batal</n-button>
          <n-button
            type="primary"
            @click="updatePassword"
            :loading="updatingPassword"
          >
            Simpan Password
          </n-button>
        </div>
      </template>
    </n-modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useMessage } from "naive-ui";
import {
  NCard,
  NButton,
  NIcon,
  NAvatar,
  NTag,
  NSwitch,
  NModal,
  NForm,
  NFormItem,
  NInput,
} from "naive-ui";

// Icons
import {
  Person as PersonIcon,
  CameraOutline as CameraIcon,
  CalendarOutline as CalendarIcon,
  MailOutline as EmailIcon,
  PhonePortraitOutline as PhoneIcon,
  Key as LockIcon,
  Shield as ShieldIcon,
  ListOutline as HistoryIcon,
  CardOutline as CurrencyIcon,
  Language as LanguageIcon,
  Notifications as NotificationsIcon,
  ManOutline as SupportIcon,
  ChevronForwardOutline as ChevronRightIcon,
  KeyOutline as SecurityIcon,
  SettingsOutline as PreferencesIcon,
} from "@vicons/ionicons5";
import { useAuth } from "@/composables/useAuth";

const message = useMessage();
const fetchAuth = useAuth();

// Reactive State
const showPasswordModal = ref(false);
const updatingPassword = ref(false);
const avatarInput = ref(null);

// User Data
const userData = reactive({
  username: "helkauser",
  email: "helka@example.com",
  phone: "+62 812-3456-7890",
  avatar: "https://placehold.co/80",
  joinDate: "15 November 2024",
  lastPasswordChange: "2 bulan lalu",
  twoFactorEnabled: false,
});

// Password Form
const passwordForm = reactive({
  currentPassword: "",
  newPassword: "",
  confirmPassword: "",
});

// Password Validation Rules
const passwordRules = {
  currentPassword: {
    required: true,
    message: "Password saat ini harus diisi",
    trigger: "blur",
  },
  newPassword: {
    required: true,
    message: "Password baru harus diisi",
    trigger: "blur",
    validator: (rule, value) => {
      if (value.length < 8) {
        return new Error("Password minimal 8 karakter");
      }
      return true;
    },
  },
  confirmPassword: {
    required: true,
    message: "Konfirmasi password harus diisi",
    trigger: "blur",
    validator: (rule, value) => {
      if (value !== passwordForm.newPassword) {
        return new Error("Password tidak cocok");
      }
      return true;
    },
  },
};

// Methods
const triggerAvatarUpload = () => {
  avatarInput.value?.click();
};

const handleAvatarUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    // Validasi file
    if (!file.type.startsWith("image/")) {
      message.error("Harus memilih file gambar");
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      // 5MB
      message.error("Ukuran file maksimal 5MB");
      return;
    }

    // Simulasi upload
    const reader = new FileReader();
    reader.onload = (e) => {
      userData.avatar = e.target.result;
      message.success("Foto profil berhasil diubah");

      // Simpan ke localStorage atau API
      localStorage.setItem("userAvatar", e.target.result);
    };
    reader.readAsDataURL(file);
  }

  // Reset input
  event.target.value = "";
};

const updatePassword = async () => {
  try {
    updatingPassword.value = true;

    // Simulasi API call
    await new Promise((resolve) => setTimeout(resolve, 1500));

    // Reset form
    passwordForm.currentPassword = "";
    passwordForm.newPassword = "";
    passwordForm.confirmPassword = "";

    showPasswordModal.value = false;
    message.success("Password berhasil diubah");

    // Update last password change
    userData.lastPasswordChange = "Baru saja";
  } catch (error) {
    message.error("Gagal mengubah password");
  } finally {
    updatingPassword.value = false;
  }
};

const viewLoginActivity = () => {
  message.info("Membuka halaman aktivitas login");
};

const showSupport = () => {
  message.info("Membuka halaman support");
};

const showAbout = () => {
  message.info("Membuka halaman tentang aplikasi");
};

const handleLogout = () => {
  fetchAuth.logout();
};

// Load saved avatar from localStorage
onMounted(() => {
  const savedAvatar = localStorage.getItem("userAvatar");
  if (savedAvatar) {
    userData.avatar = savedAvatar;
  }
});
</script>

<style scoped>
.setting-item {
  @apply flex items-center justify-between p-3 rounded-xl transition-colors duration-200;
}

.setting-item:not(:last-child) {
  @apply border-b border-gray-100;
}

.setting-item:hover {
  @apply bg-gray-50;
}

.setting-content {
  @apply flex items-center gap-3 flex-1;
}

.setting-icon {
  @apply w-10 h-10 rounded-xl flex items-center justify-center bg-gray-100 flex-shrink-0;
}

.setting-info {
  @apply flex-1 min-w-0;
}

.setting-label {
  @apply font-medium text-gray-800 text-sm;
}

.setting-value {
  @apply text-gray-500 text-xs mt-1;
}

/* Animation for modal */
:deep(.n-card) {
  border-radius: 16px;
}

:deep(.n-button) {
  border-radius: 12px;
}
</style>
