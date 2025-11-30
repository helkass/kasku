import { computed, reactive } from "vue";

export const useFormErrors = (initialErrors = {}) => {
  const errors = reactive({ ...initialErrors });

  const setErrors = (serverErrors = {}) => {
    // Reset semua errors
    Object.keys(errors).forEach((key) => {
      errors[key] = "";
    });

    // Set errors dari server
    Object.keys(serverErrors).forEach((key) => {
      if (Object.prototype.hasOwnProperty.call(serverErrors, key)) {
        errors[key] = serverErrors[key][0];
      }
    });
  };

  const resetErrors = () => {
    Object.keys(initialErrors).forEach((key) => {
      if (Object.prototype.hasOwnProperty.call(initialErrors, key)) {
        initialErrors[key] = "";
      }
    });
  };

  const hasErrors = computed(() => {
    return Object.values(errors).some((error) => error !== "");
  });

  return {
    errors,
    setErrors,
    resetErrors,
    hasErrors,
  };
};
