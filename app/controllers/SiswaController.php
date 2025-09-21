<?php

class SiswaController {

    private function checkAuth() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'siswa') {
            Flasher::setFlash('Akses ditolak!', 'Anda harus login sebagai siswa.', 'danger');
            header('Location: '. BASEURL . '/login?role=siswa');
            exit;
        }
    }
    
    public function index() {
        $this->checkAuth();

        $peminjamanModel = new Peminjaman_model();
        $profileModel = new Profile_model(); 
        $userId = $_SESSION['user_id'];

        $data = [
            'title' => 'Dashboard',
            'username' => $_SESSION['username'],
            'profile' => $profileModel->getProfileByRoleAndUserId('siswa', $userId),
            'peminjamanAktifCount' => $peminjamanModel->countAktifByUserId($userId),
            'menungguVerifikasiCount' => $peminjamanModel->countPendingByUserId($userId),
            'riwayatSelesaiCount' => $peminjamanModel->countSelesaiByUserId($userId),
            'peminjamanAktifList' => $peminjamanModel->getAktifPeminjamanByUserId($userId, 3)
        ];

        $this->view('siswa/index', $data);
    }

    public function katalogBarang($halaman = 1) {
        $this->checkAuth();
        $barangModel = new Barang_model();
        $siswaModel = new Siswa_model();
        
        $halaman = max(1, (int)$halaman);
        $limit = 15;
        $offset = ($halaman - 1) * $limit;
        
        $filters = [
            'keyword' => $_GET['search'] ?? null,
            'status' => $_GET['filter_ketersediaan'] ?? null
        ];
        
        $items = $barangModel->getBarangPaginated($offset, $limit, $filters);
        $totalBarang = $barangModel->countAllBarang($filters);
        $totalHalaman = ceil($totalBarang / $limit);

        $keranjang_ids = $_SESSION['keranjang'] ?? [];
        $data_keranjang = $barangModel->getBarangByIds($keranjang_ids);
        
        $data_siswa = $siswaModel->getSiswaByUserId($_SESSION['user_id']);

        $data = [
            'title' => 'Katalog Barang',
            'username' => $_SESSION['username'],
            'items' => $items,
            'total_halaman' => $totalHalaman,
            'halaman_aktif' => $halaman,
            'filters' => $filters,
            'jumlah_keranjang' => count($keranjang_ids),
            'data_keranjang' => $data_keranjang,
            'data_siswa' => $data_siswa
        ];
        
        $this->view('siswa/katalog_barang', $data);
    }

    public function tambahKeKeranjang() {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['barang_id'])) {
            $barangModel = new Barang_model();
            $barang_id = $_POST['barang_id'];

            if (!isset($_SESSION['keranjang'])) {
                $_SESSION['keranjang'] = [];
            }

            $barang = $barangModel->getBarangById($barang_id);

            if ($barang && $barang['jumlah'] > 0) {
                if (!in_array($barang_id, $_SESSION['keranjang'])) {
                    $_SESSION['keranjang'][] = $barang_id;
                    Flasher::setFlash('Berhasil!', 'Barang ditambahkan ke dalam list peminjaman.', 'success');
                } else {
                    Flasher::setFlash('Info!', 'Barang sudah ada di dalam list peminjaman.', 'danger');
                }
            } else {
                Flasher::setFlash('Gagal!', 'Stok barang sudah habis atau tidak tersedia.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/siswa/katalog');
        exit;
    }
    
    // File: ManajemenLabPraktikum/app/controllers/SiswaController.php

// File: ManajemenLabPraktikum/app/controllers/SiswaController.php

// File: ManajemenLabPraktikum/app/controllers/SiswaController.php

// File: ManajemenLabPraktikum/app/controllers/SiswaController.php

public function hapusDariKeranjang($id) {
    $this->checkAuth();
    if (isset($_SESSION['keranjang'])) {
        $key = array_search($id, $_SESSION['keranjang']);
        if ($key !== false) {
            unset($_SESSION['keranjang'][$key]);
            // BARIS PERBAIKAN: Urutkan ulang key array agar sekuensial
            $_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
            
            Flasher::setFlash('Berhasil!', 'Item dihapus dari list peminjaman.', 'success');
        }
    }
    header('Location: ' . BASEURL . '/siswa/katalog');
    exit;
}
    public function prosesPeminjaman() {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['barang_id'])) {
            $siswaModel = new Siswa_model();
            $kelasModel = new Kelas_model();
            $peminjamanModel = new Peminjaman_model();
            $barangModel = new Barang_model(); 

            $siswa = $siswaModel->getSiswaByUserId($_SESSION['user_id']);
            if (!$siswa) {
                Flasher::setFlash('Gagal!', 'Data siswa tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/siswa/katalog');
                exit;
            }

            $kelas = $kelasModel->getKelasById($siswa['kelas_id']);
            $waliKelasId = $kelas['wali_kelas_id'] ?? null;

            if (is_null($waliKelasId)) {
                Flasher::setFlash('Gagal!', 'Kelas Anda belum memiliki wali kelas. Hubungi Admin.', 'danger');
                header('Location: ' . BASEURL . '/siswa/katalog');
                exit;
            }

            $dataUntukDisimpan = [];
            $jumlah_pinjam_form = $_POST['jumlah_pinjam'];

            foreach ($_POST['barang_id'] as $barang_id) {
                $jumlah = isset($jumlah_pinjam_form[$barang_id]) ? (int)$jumlah_pinjam_form[$barang_id] : 1;
                $barang = $barangModel->getBarangById($barang_id);
                if (!$barang || $jumlah <= 0 || $jumlah > $barang['jumlah']) {
                    Flasher::setFlash('Gagal!', 'Jumlah peminjaman untuk ' . htmlspecialchars($barang['nama_barang']) . ' tidak valid atau melebihi stok yang tersedia.', 'danger');
                    header('Location: ' . BASEURL . '/siswa/katalog');
                    exit;
                }

                $dataUntukDisimpan[] = [
                    'user_id' => $_SESSION['user_id'],
                    'barang_id' => $barang_id,
                    'jumlah_pinjam' => $jumlah,
                    'tanggal_pinjam' => $_POST['tanggal_pinjam'],
                    'tanggal_kembali_diajukan' => $_POST['tanggal_kembali'],
                    'keperluan' => $_POST['keperluan'],
                    'verifikator_id' => $waliKelasId
                ];
            }

            $hasil = $peminjamanModel->createPeminjamanBatch($dataUntukDisimpan);

            if ($hasil > 0) {
                unset($_SESSION['keranjang']);
                Flasher::setFlash('Berhasil!', 'Pengajuan Anda telah dikirim ke wali kelas untuk verifikasi.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat memproses pengajuan.', 'danger');
            }
        } else {
             Flasher::setFlash('Gagal!', 'permintaan kosong atau permintaan tidak valid.', 'danger');
        }

        header('Location: ' . BASEURL . '/siswa/katalog');
        exit;
    }
    
    public function profile() {
        $this->checkAuth();
        $userModel = new User_model();
        $siswaModel = new Siswa_model();
        $kelasModel = new Kelas_model();

        $data['user'] = $userModel->getUserById($_SESSION['user_id']);
        $data['profile'] = $siswaModel->getSiswaByUserId($_SESSION['user_id']);
        $data['title'] = 'Profil Saya';

        $data['kelas'] = null;
        if (isset($data['profile']['kelas_id'])) {
            $data['kelas'] = $kelasModel->getKelasById($data['profile']['kelas_id']);
        }
        
        $this->view('siswa/profile', $data);
    }
    
    public function ubahProfile() {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $siswaModel = new Siswa_model();
            
            $fotoLama = $_POST['foto_lama'] ?? 'default.png';
            $fotoBaru = $fotoLama;

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['foto'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $namaFotoBaru = uniqid('siswa_') . '.' . $ext;
                $targetDir = APP_ROOT . '/public/img/siswa/';

                if (move_uploaded_file($file['tmp_name'], $targetDir . $namaFotoBaru)) {
                    $fotoBaru = $namaFotoBaru;
                    if ($fotoLama !== 'default.png' && file_exists($targetDir . $fotoLama)) {
                        @unlink($targetDir . $fotoLama);
                    }
                }
            }

            $data_update = [
                'user_id' => $_SESSION['user_id'],
                'jenis_kelamin' => $_POST['jenis_kelamin'],
                'email' => $_POST['email'],
                'no_hp' => $_POST['no_hp'],
                'agama' => $_POST['agama'],
                'ttl' => $_POST['ttl'],
                'alamat' => $_POST['alamat'],
                'foto' => $fotoBaru
            ];

            $result = $siswaModel->updateProfileByUserId($data_update);
            if ($result > 0) {
                Flasher::setFlash('Berhasil!', 'Data profil berhasil diubah.', 'success');
            } else if ($result === 0) {
                Flasher::setFlash('Info', 'Tidak ada perubahan data yang disimpan.', 'danger');
            } else {
                Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat mengubah data.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/siswa/profile');
        exit;
    }

    public function getProfileById() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $siswaModel = new Siswa_model();
        $siswa = $siswaModel->getSiswaByUserId($_SESSION['user_id']);
        echo json_encode($siswa);
        exit();
    }


    public function changePassword() {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userModel = new User_model();
            $userId = $_SESSION['user_id'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Logika verifikasi password lama dihapus sesuai permintaan
            
            if ($newPassword === $confirmPassword) {
                if (strlen($newPassword) >= 6) {
                    // Panggil model dan periksa hasil eksekusi
                    if ($userModel->changePassword($userId, $newPassword) > 0) {
                        Flasher::setFlash('Berhasil!', 'Kata sandi telah diubah.', 'success');
                    } else {
                        Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat mengubah kata sandi.', 'danger');
                    }
                } else {
                    Flasher::setFlash('Gagal!', 'Password baru minimal harus 6 karakter.', 'danger');
                }
            } else {
                Flasher::setFlash('Gagal!', 'Konfirmasi password baru tidak cocok.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/siswa/profile');
        exit;
    }
    
    public function view($view, $data = []) {
        extract($data);
        require_once '../app/views/layouts/siswa_header.php';
        require_once '../app/views/' . $view . '.php';
        require_once '../app/views/layouts/siswa_footer.php';
    }

    public function pengembalianBarang($peminjamanId = null) {
        $this->checkAuth();
        $peminjamanModel = new Peminjaman_model();
        $barangModel = new Barang_model();

        $data_peminjaman = $peminjamanModel->getPeminjamanByIdAndUserId($peminjamanId, $_SESSION['user_id']);
        if (!$data_peminjaman || $data_peminjaman['status'] !== 'Disetujui') {
            Flasher::setFlash('Gagal!', 'Peminjaman tidak valid atau belum disetujui.', 'danger');
            header('Location: ' . BASEURL . '/siswa/riwayat');
            exit;
        }

        $data = [
            'title' => 'Form Pengembalian',
            'username' => $_SESSION['username'],
            'peminjaman' => $data_peminjaman,
            'barang' => $barangModel->getBarangById($data_peminjaman['barang_id'])
        ];

        $this->view('siswa/pengembalian_barang', $data);
    }

    public function riwayatPeminjaman($halaman = 1) {
    $this->checkAuth();
    $peminjamanModel = new Peminjaman_model();

    $halaman = max(1, (int)$halaman);
    $limit = 10;
    $offset = ($halaman - 1) * $limit;
    $userId = $_SESSION['user_id'];
    
    $filters = [
        'keyword' => $_GET['search'] ?? null,
        'status' => $_GET['filter_status'] ?? null,
        'waktu' => $_GET['filter_waktu'] ?? 'terbaru'
    ];

    $totalRiwayat = $peminjamanModel->countHistoryByUserId($userId, $filters);
    $totalHalaman = ceil($totalRiwayat / $limit);

    $data = [
        'title' => 'Riwayat Peminjaman',
        'username' => $_SESSION['username'],
        'history' => $peminjamanModel->getHistoryByUserId($userId, $offset, $limit, $filters),
        'total_halaman' => $totalHalaman,
        'halaman_aktif' => $halaman,
        'filters' => $filters
    ];

    $this->view('siswa/riwayat_peminjaman', $data);
}
    
    public function prosesPengembalian() {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['peminjaman_id']) && isset($_FILES['bukti_kembali'])) {
            $peminjamanModel = new Peminjaman_model();
            $barangModel = new Barang_model();
            
            $namaFoto = null;
            if ($_FILES['bukti_kembali']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['bukti_kembali'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $namaFotoBaru = 'bukti_' . uniqid() . '.' . $ext;
                $targetDir = APP_ROOT . '/public/img/bukti_kembali/';
                if (move_uploaded_file($file['tmp_name'], $targetDir . $namaFotoBaru)) {
                    $namaFoto = $namaFotoBaru;
                }
            }
            
            $peminjaman = $peminjamanModel->getPeminjamanById($_POST['peminjaman_id']);
            $tanggal_wajib_kembali = strtotime($peminjaman['tanggal_wajib_kembali']);
            $tanggal_sekarang = strtotime(date('Y-m-d'));
            
            $status_pengembalian = ($tanggal_sekarang <= $tanggal_wajib_kembali) ? 'Tepat Waktu' : 'Terlambat';
            
            $data = [
                'id' => $_POST['peminjaman_id'],
                'barang_id' => $peminjaman['barang_id'],
                'jumlah_pinjam' => $peminjaman['jumlah_pinjam'],
                'tanggal_kembali' => date('Y-m-d'),
                'bukti_kembali' => $namaFoto,
                'status_pengembalian' => $status_pengembalian,
                'status' => 'Selesai'
            ];
            
            if ($peminjamanModel->updatePengembalian($data) > 0) {
                $barangModel->tambahStok($data['barang_id'], $data['jumlah_pinjam']);
                Flasher::setFlash('Berhasil!', 'Pengembalian barang berhasil dicatat.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat memproses pengembalian.', 'danger');
            }
        } else {
            Flasher::setFlash('Gagal!', 'Permintaan tidak valid.', 'danger');
        }
        header('Location: ' . BASEURL . '/siswa/riwayat');
        exit;
    }

    public function getPeminjamanById($id) {
        header('Content-Type: application/json');
        $this->checkAuth();
        $peminjamanModel = new Peminjaman_model();
        $barangModel = new Barang_model();

        $peminjaman = $peminjamanModel->getPeminjamanByIdAndUserId($id, $_SESSION['user_id']);

        if ($peminjaman) {
            $barang = $barangModel->getBarangById($peminjaman['barang_id']);
            $peminjaman['nama_barang'] = $barang['nama_barang'];
            $peminjaman['kode_barang'] = $barang['kode_barang'];
            echo json_encode($peminjaman);
        } else {
            echo json_encode(null);
        }
        exit;
    }
}