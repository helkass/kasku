import { ref } from "vue";
import api from "@/api/axios";
import { useRouter } from "vue-router";

const user = ref(null);

export function useAuth() {
  const router = useRouter();

  const login = async (username, password) => {
    const res = await api.post("/login", { username, password });
    if (res.data.success) {
      localStorage.setItem("token", res.data.token);
      localStorage.setItem("role", res.data.user.role);
      user.value = res.data.user;

      if (res.data.user.role === "superadmin") {
        router.push("/");
      }

      router.push("/m");
    }
    return res.data;
  };

  const logout = () => {
    localStorage.removeItem("token");
    user.value = null;
    router.push("/login");
  };

  const getUser = async () => {
    const res = await api.get("/me");
    user.value = res.data;
  };

  const validateToken = async () => {
    const res = await api.get("validate_token");
    return res;
  };

  return { user, login, logout, getUser, validateToken };
}
