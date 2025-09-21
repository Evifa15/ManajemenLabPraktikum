// File: ManajemenLabPraktikum/public/js/siswa-script.js

document.addEventListener('DOMContentLoaded', () => {
    
    console.log('DEBUG: siswa-script.js dimuat.');

    // --- Logika untuk Modal Keranjang ---
    const cartButton = document.querySelector('.floating-cart-button');
    const keranjangModal = document.getElementById('keranjangModal');

    if (cartButton && keranjangModal) {
        const closeBtn = keranjangModal.querySelector('.close-button');
        const closeModal = () => {
            keranjangModal.classList.remove('active');
        };

        cartButton.addEventListener('click', (e) => {
            e.preventDefault();
            keranjangModal.classList.add('active'); 
        });
        
        closeBtn.addEventListener('click', closeModal);
        
        window.addEventListener('click', (e) => {
            if (e.target === keranjangModal) {
                closeModal();
            }
        });
    }

    // --- Logika untuk Modal Pengembalian (jika ada di halaman) ---
    const pengembalianModal = document.getElementById('pengembalianModal');
    if (pengembalianModal) {
        const openModalBtns = document.querySelectorAll('.pengembalian-btn');
        const closeModalBtn = pengembalianModal.querySelector('.close-button');
        const closeModal = () => {
            pengembalianModal.classList.remove('active');
        };

        openModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const peminjamanId = btn.dataset.id;
                
                fetch(`${BASEURL}/siswa/get-peminjaman-by-id/${peminjamanId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data) {
                            document.getElementById('peminjaman_id').value = data.id;
                            document.getElementById('modal_nama_barang').textContent = data.nama_barang;
                            document.getElementById('modal_kode_barang').textContent = data.kode_barang;
                            document.getElementById('modal_jumlah_pinjam').textContent = data.jumlah_pinjam;
                            document.getElementById('modal_wajib_kembali').textContent = new Date(data.tanggal_wajib_kembali).toLocaleDateString('id-ID');
                            
                            const today = new Date().toISOString().split('T')[0];
                            document.getElementById('tanggal_kembali').value = today;

                            pengembalianModal.classList.add('active');
                            pengembalianModal.querySelector('.modal-content').scrollTop = 0;
                        } else {
                            alert('Gagal memuat data peminjaman.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            
                });
        });

        closeModalBtn.addEventListener('click', closeModal);
        window.addEventListener('click', (e) => {
            if (e.target === pengembalianModal) {
                closeModal();
            }
        });
    }
    
    // --- Logika untuk Modal Profil dan Password ---
    const editProfileBtn = document.getElementById('editProfileBtn');
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    const editProfileModal = document.getElementById('editProfileModal');
    const changePasswordModal = document.getElementById('changePasswordModal');

    if (editProfileBtn && editProfileModal) {
        editProfileBtn.addEventListener('click', () => {
            fetch(`${BASEURL}/siswa/get-profile-by-id`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('siswaId').value = data.id;
                        document.getElementById('id_siswa').value = data.id_siswa;
                        document.getElementById('nama_siswa').value = data.nama;
                        document.getElementById('jenis_kelamin_siswa').value = data.jenis_kelamin;
                        document.getElementById('ttl_siswa').value = data.ttl;
                        document.getElementById('agama_siswa').value = data.agama;
                        document.getElementById('no_hp_siswa').value = data.no_hp;
                        document.getElementById('alamat_siswa').value = data.alamat;
                        document.getElementById('email_siswa').value = data.email;
                        document.getElementById('fotoLama').value = data.foto;
                        editProfileModal.classList.add('active');
                    }
                });
        });
        editProfileModal.querySelector('.close-button').addEventListener('click', () => {
            editProfileModal.classList.remove('active');
        });
        window.addEventListener('click', (e) => {
            if (e.target === editProfileModal) {
                editProfileModal.classList.remove('active');
            }
        });
    }

    if (changePasswordBtn && changePasswordModal) {
        changePasswordBtn.addEventListener('click', () => {
            changePasswordModal.classList.add('active');
        });
        changePasswordModal.querySelector('.close-button').addEventListener('click', () => {
            changePasswordModal.classList.remove('active');
        });
        window.addEventListener('click', (e) => {
            if (e.target === changePasswordModal) {
                changePasswordModal.classList.remove('active');
            }
        });
    }

});