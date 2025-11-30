export const formatNumber = (value) => {
  const num = Number(value);
  if (isNaN(num)) return "0";
  return num.toLocaleString("id-ID");
};

export const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

export const formatDateTime = (dateString) => {
  return new Date(dateString).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

export function formatTime(date) {
  return date.toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  });
}

/**
 *
 * @param {int} amount
 * @description Rp 2Jt / Rp 990Rb
 * @returns
 */
export function formatCurrencySimple(amount) {
  if (!amount && amount !== 0) return "Rp 0";

  const numAmount = typeof amount === "string" ? parseFloat(amount) : amount;

  if (numAmount >= 1000000) {
    // Hindari floating point division
    const juta = Math.floor(numAmount / 1000000);
    return `Rp ${juta}Jt`;
  } else if (numAmount >= 1000) {
    const rb = Math.floor(numAmount / 1000);
    return `Rp ${rb}Rb`;
  } else {
    return `Rp ${new Intl.NumberFormat("id-ID").format(numAmount)}`;
  }
}

export const formatCurrency = (amount) => {
  return new Intl.NumberFormat("id-ID").format(amount);
};
