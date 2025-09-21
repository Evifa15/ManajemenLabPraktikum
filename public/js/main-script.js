// File: ManajemenLabPraktikum/public/js/main-script.js

document.addEventListener('DOMContentLoaded', () => {

    // === LOGIKA UTAMA UNTUK SIDEBAR TOGGLE ===
    const sidebar = document.getElementById('sidebar');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');

    if (sidebar && sidebarToggleBtn) {
        const setSidebarState = (state) => {
            localStorage.setItem('sidebarState', state);
        };

        const getSidebarState = () => {
            return localStorage.getItem('sidebarState');
        };

        if (getSidebarState() === 'collapsed') {
            sidebar.classList.add('collapsed');
        }

        sidebarToggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            
            if (sidebar.classList.contains('collapsed')) {
                setSidebarState('collapsed');
            } else {
                setSidebarState('expanded');
            }
        });
    }

    // --- PERBAIKAN FINAL: LOGIKA UNTUK MENANDAI LINK AKTIF ---
    // Logika ini lebih kuat dan dapat diandalkan.
    function setActiveLink() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar-item');
        
        // Hapus kelas 'active-link' dari semua link terlebih dahulu
        navLinks.forEach(link => {
            link.classList.remove('active-link');
        });

        navLinks.forEach(link => {
            const linkPath = new URL(link.href).pathname;
            
            // Periksa apakah URL saat ini sama dengan URL link
            // atau jika URL saat ini merupakan sub-path dari URL link
            if (currentPath === linkPath) {
                link.classList.add('active-link');
            } else if (linkPath !== '/' && currentPath.startsWith(linkPath + '/')) {
                link.classList.add('active-link');
            }
        });
    }

    // Jalankan fungsi saat halaman dimuat
    setActiveLink();
    window.addEventListener('popstate', setActiveLink); // Memastikan transisi berjalan saat navigasi kembali
    window.addEventListener('hashchange', setActiveLink); // Memastikan transisi berjalan untuk tab

    // === LOGIKA ALERT PENGHILANG OTOMATIS (FINAL & TERPUSAT) ===
    const alerts = document.querySelectorAll('.alert');
    if (alerts) {
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('fade-out');
                alert.addEventListener('transitionend', () => {
                    alert.remove();
                });
            }, 5000);
        });
    }
    // --- LOGIKA KONFIRMASI DENGAN MODAL KUSTOM ---
const logoutButton = document.querySelector('.logout-button');
const logoutModal = document.getElementById('logoutModal');

if (logoutButton && logoutModal) {
    const confirmLogoutLink = logoutModal.querySelector('#confirmLogoutLink');
    const cancelLogoutBtn = logoutModal.querySelector('#cancelLogout');
    const closeBtn = logoutModal.querySelector('.close-button');

    logoutButton.addEventListener('click', (e) => {
        e.preventDefault();
        confirmLogoutLink.href = logoutButton.href;
        logoutModal.classList.add('active');
    });

    const closeModal = () => {
        logoutModal.classList.remove('active');
    };

    cancelLogoutBtn.addEventListener('click', closeModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    window.addEventListener('click', (e) => {
        if (e.target === logoutModal) {
            closeModal();
        }
    });
}
// --- FUNGSI BARU UNTUK MENANGANI SEMUA TOMBOL BATAL DI MODAL ---
document.querySelectorAll('.btn-cancel[data-dismiss="modal"]').forEach(button => {
    button.addEventListener('click', () => {
        const modal = button.closest('.modal');
        if (modal) {
            modal.classList.remove('active');
        }
    });
});
});