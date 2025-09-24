document.addEventListener('DOMContentLoaded', () => {
     // === LOGIKA UTAMA UNTUK SIDEBAR TOGGLE (VERSI RESPONSIVE) ===
    const sidebar = document.getElementById('sidebar');
    // Tombol di dalam sidebar
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn'); 
    // Tombol di header konten (mungkin perlu Anda tambahkan di file header PHP)
    const headerToggleBtn = document.querySelector('.main-content .sidebar-toggle-button');

    const toggleSidebar = () => {
        // Di layar besar, gunakan 'collapsed'
        if (window.innerWidth > 992) {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarState', sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded');
        } else {
            // Di layar kecil, gunakan 'active' untuk overlay
            sidebar.classList.toggle('active');
        }
    };

    if (sidebar) {
        // Atur state awal saat memuat halaman
        if (window.innerWidth > 992 && localStorage.getItem('sidebarState') === 'collapsed') {
            sidebar.classList.add('collapsed');
        }
        
        // Tambahkan event listener ke kedua tombol
        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', toggleSidebar);
        }
        if (headerToggleBtn) {
            headerToggleBtn.addEventListener('click', toggleSidebar);
        }
    }
    function setActiveLink() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar-item');
        navLinks.forEach(link => {
            link.classList.remove('active-link');
        });

        navLinks.forEach(link => {
            const linkPath = new URL(link.href).pathname;
            if (currentPath === linkPath) {
                link.classList.add('active-link');
            } else if (linkPath !== '/' && currentPath.startsWith(linkPath + '/')) {
                link.classList.add('active-link');
            }
        });
    }
    setActiveLink();
    window.addEventListener('popstate', setActiveLink); 
    window.addEventListener('hashchange', setActiveLink); 
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
    document.querySelectorAll('.btn-cancel[data-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
            }
        });
    });
});