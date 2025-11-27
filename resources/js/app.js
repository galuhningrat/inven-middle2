// resources/js/app.js

// CSRF Token for AJAX (Ambil dari meta tag)
window.Laravel = {
    csrfToken: document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content"),
};

document.addEventListener("DOMContentLoaded", function () {
    initializeSidebar();
    initializeFlashMessages();
    initializeScrollHint();
    updateServerTime();
    // Perbarui waktu setiap 1 detik
    setInterval(updateServerTime, 1000);

    // Terapkan dark mode yang tersimpan saat load
    applySavedDarkMode();
});

// --- Fungsi Sidebar & Responsivitas Layout ---

function initializeSidebar() {
    const menuToggle = document.getElementById("menuToggle");
    const overlay = document.getElementById("overlay");

    if (menuToggle) {
        menuToggle.addEventListener("click", toggleSidebar);
    }

    if (overlay) {
        overlay.addEventListener("click", function () {
            // Tutup sidebar jika di layar mobile
            if (window.innerWidth < 1024) {
                closeMobileSidebar();
            }
        });
    }

    // Tangani perubahan ukuran layar untuk menyesuaikan tampilan
    window.addEventListener("resize", handleResize);
}

function toggleSidebar() {
    if (window.innerWidth < 1024) {
        // Mode Mobile: Toggle sidebar dan overlay
        toggleMobileSidebar();
    } else {
        // Mode Desktop: Toggle collapsed state
        toggleDesktopSidebar();
    }
}

function toggleDesktopSidebar() {
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("mainContent");
    // Toggle class 'collapsed' untuk mengubah lebar sidebar/main-content
    sidebar.classList.toggle("collapsed");
    mainContent.classList.toggle("sidebar-collapsed");
}

function toggleMobileSidebar() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("overlay");

    sidebar.classList.toggle("active");
    overlay.classList.toggle("active");
}

function closeMobileSidebar() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("overlay");

    sidebar.classList.remove("active");
    overlay.classList.remove("active");
}

function handleResize() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("overlay");

    // Pastikan mobile state dinonaktifkan jika layar menjadi besar
    if (window.innerWidth >= 1024) {
        sidebar.classList.remove("active");
        overlay.classList.remove("active");
    }
}

// --- Fungsi Dark Mode ---
window.toggleDarkMode = function () {
    document.body.classList.toggle("dark-mode");
    const isDarkMode = document.body.classList.contains("dark-mode");
    localStorage.setItem("darkMode", isDarkMode);

    const icon = document.getElementById("darkModeToggleIcon");
    icon.textContent = isDarkMode ? "☀️" : "🌙";
};

function applySavedDarkMode() {
    if (localStorage.getItem("darkMode") === "true") {
        document.body.classList.add("dark-mode");
        const icon = document.getElementById("darkModeToggleIcon");
        if (icon) {
            icon.textContent = "☀️";
        }
    }
}

// --- Fungsi UI Lainnya ---

window.scrollToTop = function () {
    window.scrollTo({ top: 0, behavior: "smooth" });
};

// Implementasi Isu II.H.8: Format waktu real-time yang benar
function updateServerTime() {
    const now = new Date();

    // Format waktu: HH.mm.ss
    const hours = String(now.getHours()).padStart(2, "0");
    const minutes = String(now.getMinutes()).padStart(2, "0");
    const seconds = String(now.getSeconds()).padStart(2, "0");
    const timeString = `${hours}.${minutes}.${seconds}`;

    // Format tanggal: Day, dd Mon yyyy
    const days = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
    const months = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "Mei",
        "Jun",
        "Jul",
        "Agu",
        "Sep",
        "Okt",
        "Nov",
        "Des",
    ];

    const dayName = days[now.getDay()];
    const day = String(now.getDate()).padStart(2, "0");
    const monthName = months[now.getMonth()];
    const year = now.getFullYear();

    const dateString = `${dayName}, ${day} ${monthName} ${year}`;

    // Gabungkan: HH.mm.ss • Day, dd Mon yyyy
    const element = document.getElementById("footerServerTime");
    if (element) {
        element.textContent = `${timeString} • ${dateString}`;
    }
}

function initializeFlashMessages() {
    const flashMessage = document.getElementById("flashMessage");
    if (flashMessage) {
        // Hapus pesan setelah 3 detik
        setTimeout(function () {
            flashMessage.style.opacity = "0";
            flashMessage.style.transition = "opacity 0.3s ease";
            setTimeout(function () {
                flashMessage.remove();
            }, 300);
        }, 3000);
    }
}

// Implementasi Isu I.3: Animasi UI Scroll Hint
function initializeScrollHint() {
    const tables = document.querySelectorAll(
        ".table-wrapper, .data-table-container"
    );
    let hintShown = localStorage.getItem("scrollHintShown");

    tables.forEach((table) => {
        // Cek jika konten horizontal lebih lebar dari wadahnya
        if (table.scrollWidth > table.clientWidth && !hintShown) {
            const hint = document.getElementById("scrollHint");
            if (hint) {
                hint.classList.add("show");
                setTimeout(() => {
                    hint.classList.remove("show");
                    localStorage.setItem("scrollHintShown", "true");
                }, 5000);
            }
        }
    });
}

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
    toast.style.cssText = `background: ${bgColor}; color: white; padding: 12px 20px; border-radius: 6px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateX(100%); transition: transform 0.3s ease;`;
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
