document.addEventListener('DOMContentLoaded', () => {
    console.log('guru-script.js dimuat.');

    // --- FUNGSI UNTUK MENANDAI LINK SIDEBAR AKTIF ---
    function setActiveLink() {
        const currentPath = window.location.pathname;
        const navLinksConfig = {
            'dashboard': 'nav-dashboard-guru',
            'verifikasi': 'nav-verifikasi-guru',
            'siswa': 'nav-siswa-guru',
            'riwayat': 'nav-riwayat-guru',
            'profile': 'nav-profile-guru'
        };

        for (const key in navLinksConfig) {
            const element = document.getElementById(navLinksConfig[key]);
            if (element && currentPath.includes(`/guru/${key}`)) {
                element.classList.add('active-link');
            }
        }
    }
    setActiveLink();

    // --- LOGIKA UNTUK TAB DAN PAGINATION DI HALAMAN SISWA WALI ---
    const tabContainer = document.querySelector('.tab-links-wrapper');
    if (tabContainer) {
        const tabLinks = tabContainer.querySelectorAll('.tab-link');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabContainer.addEventListener('click', (event) => {
            const clickedTab = event.target.closest('.tab-link');
            if (!clickedTab) return;

            tabLinks.forEach(link => link.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            clickedTab.classList.add('active');
            
            const targetContent = document.querySelector(clickedTab.dataset.target);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });

        const tabContentWrapper = document.querySelector('.tab-content-wrapper');
        tabContentWrapper.addEventListener('click', function(event) {
            const paginationLink = event.target.closest('.pagination-link');
            if (!paginationLink || paginationLink.classList.contains('disabled')) {
                return;
            }
            event.preventDefault();

            const kelasId = paginationLink.dataset.kelasid;
            const page = paginationLink.dataset.page;
            const tableBody = document.querySelector(`#tabel-siswa-${kelasId} tbody`);
            const paginationContainer = document.getElementById(`pagination-container-${kelasId}`);

            fetch(`${BASEURL}/guru/getSiswaPage/${kelasId}/${page}`)
                .then(response => response.json())
                .then(data => {
                    if (tableBody) {
                        tableBody.innerHTML = data.tableBody;
                    }
                    if (paginationContainer) {
                        paginationContainer.innerHTML = data.pagination;
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    }

    // --- LOGIKA UNTUK MODAL PENOLAKAN ---
    const tolakModal = document.getElementById('tolakModal');
    if (tolakModal) {
        const openModalBtns = document.querySelectorAll('.open-modal-tolak-btn');
        const closeBtn = tolakModal.querySelector('.close-button');
        const peminjamanIdInput = document.getElementById('peminjamanIdTolak');
        
        openModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                if(peminjamanIdInput) {
                    peminjamanIdInput.value = btn.dataset.id;
                }
                tolakModal.classList.add('active');
            });
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                tolakModal.classList.remove('active');
            });
        }

        window.addEventListener('click', (e) => {
            if (e.target === tolakModal) {
                tolakModal.classList.remove('active');
            }
        });
    }
    // --- LOGIKA UNTUK MODAL UBAH KATA SANDI DI HALAMAN PROFIL ---
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    const changePasswordModal = document.getElementById('changePasswordModal');

    if (changePasswordBtn && changePasswordModal) {
        const closeBtn = changePasswordModal.querySelector('.close-button');

        // Tampilkan modal saat tombol diklik
        changePasswordBtn.addEventListener('click', () => {
            changePasswordModal.classList.add('active');
        });
    

        // Sembunyikan modal saat tombol close (x) diklik
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                changePasswordModal.classList.remove('active');
            });
        }

        // Sembunyikan modal saat area di luar modal diklik
        window.addEventListener('click', (e) => {
            if (e.target === changePasswordModal) {
                changePasswordModal.classList.remove('active');
            }
        });
    }
});