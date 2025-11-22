import "./bootstrap";
import "./bootstrap";

// Global utility functions
window.showLoading = function () {
    document.getElementById("loadingOverlay").style.display = "flex";
};

window.hideLoading = function () {
    document.getElementById("loadingOverlay").style.display = "none";
};

window.showToast = function (message, type = "success") {
    let toastContainer = document.getElementById("toastContainer");
    if (!toastContainer) {
        toastContainer = document.createElement("div");
        toastContainer.id = "toastContainer";
        toastContainer.style.cssText =
            "position: fixed; top: 20px; right: 20px; z-index: 10000;";
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement("div");
    const bgColor =
        type === "success"
            ? "#10b981"
            : type === "error"
            ? "#ef4444"
            : "#f59e0b";
    toast.style.cssText = `background: ${bgColor}; color: white; padding: 12px 20px; border-radius: 6px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transform: translateX(100%); transition: transform 0.3s ease;`;
    toast.textContent = message;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.style.transform = "translateX(0)";
    }, 100);
    setTimeout(() => {
        toast.style.transform = "translateX(100%)";
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
};

window.scrollToTop = function () {
    window.scrollTo({ top: 0, behavior: "smooth" });
};

// Format currency
window.formatRupiah = function (number) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(number);
};

// Confirm delete
window.confirmDelete = function (
    message = "Apakah Anda yakin ingin menghapus?"
) {
    return confirm(message);
};

console.log("Sistem Inventaris STTI Cirebon - Ready");
