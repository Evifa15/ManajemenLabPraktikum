<?php

class GuruController {

    private function checkAuth() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'guru') {
            Flasher::setFlash('Akses ditolak!', 'Anda harus login sebagai guru.', 'danger');
            header('Location: ' . BASEURL . '/login?role=guru');
            exit;
        }
    }
    public function profile() {
        $this->checkAuth();
        $userModel = new User_model();
        $profileModel = new Profile_model();

        $data['user'] = $userModel->getUserById($_SESSION['user_id']);
        $data['profile'] = $profileModel->getProfileByRoleAndUserId($_SESSION['role'], $_SESSION['user_id']);
        $data['title'] = 'Profil Saya';
        
        extract($data);
        require_once '../app/views/layouts/guru_header.php';
        // Menggunakan view profile guru yang baru
        require_once '../app/views/guru/profile.php'; 
        require_once '../app/views/layouts/guru_footer.php';
    }
    public function updateProfile() {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $guruModel = new Guru_model();
            
            // Siapkan data dari form
            $data = $_POST;
            
            // Logika untuk menangani upload foto
            $fotoLama = $_POST['foto_lama'] ?? 'default.png';
            $data['foto'] = $fotoLama;

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['foto'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $namaFotoBaru = 'guru_' . uniqid() . '.' . $ext;
                $targetDir = APP_ROOT . '/public/img/guru/'; // Pastikan folder img/guru ada

                // Buat folder jika belum ada
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                if (move_uploaded_file($file['tmp_name'], $targetDir . $namaFotoBaru)) {
                    $data['foto'] = $namaFotoBaru;
                    // Hapus foto lama jika bukan default
                    if ($fotoLama !== 'default.png' && file_exists($targetDir . $fotoLama)) {
                        @unlink($targetDir . $fotoLama);
                    }
                }
            }

            // Panggil model untuk update data di database
            $result = $guruModel->updateGuru($data);

            if ($result >= 0) { // Berhasil jika 0 (tidak ada perubahan) atau > 0 (ada perubahan)
                Flasher::setFlash('Berhasil!', 'Data profil berhasil diperbarui.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat memperbarui data.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/guru/profile');
        exit;
    }
    public function index() {
        $this->checkAuth();
        
        // Memuat semua model yang dibutuhkan
        $guruModel = new Guru_model();
        $peminjamanModel = new Peminjaman_model();
        $kelasModel = new Kelas_model();
        $siswaModel = new Siswa_model();
        $profileModel = new Profile_model(); // Model baru untuk profil

        // Mengambil data guru yang sedang login
        $guru = $guruModel->getGuruByUserId($_SESSION['user_id']);
        
        $verifikasiCount = 0;
        $kelasCount = 0;
        $siswaCount = 0;
        $requests = [];

        if ($guru && isset($guru['id'])) {
            // Data untuk kartu ringkasan
            $verifikasiCount = $peminjamanModel->countAllVerificationRequests($guru['id']);
            $kelasCount = $kelasModel->countKelasByWaliId($guru['id']);
            $siswaCount = $siswaModel->countSiswaByWaliId($guru['id']);
            // Data untuk daftar di bawah banner (5 permintaan terbaru)
            $requests = $peminjamanModel->getPeminjamanForVerification($guru['id'], 0, 3);
        }
        
        // Mengambil data profil untuk foto di banner
        $profile = $profileModel->getProfileByRoleAndUserId('guru', $_SESSION['user_id']);

        $data = [ 
            'title' => 'Dashboard Guru', 
            'username' => $_SESSION['username'],
            'profile' => $profile, // Data profil untuk foto
            'verifikasiCount' => $verifikasiCount,
            'kelasCount' => $kelasCount,
            'siswaCount' => $siswaCount,
            'requests' => $requests
        ];

        // Memuat view baru
        $this->view('guru/index', $data);
    }

    public function verifikasiPeminjaman($halaman = 1) {
    $this->checkAuth();
    $peminjamanModel = new Peminjaman_model();
    $guruModel = new Guru_model();

    $halaman = max(1, (int)$halaman);
    $limit = 10;
    $offset = ($halaman - 1) * $limit;

    // Menggunakan struktur $filters
    $filters = [
        'keyword' => $_GET['search'] ?? null
    ];

    $guru = $guruModel->getGuruByUserId($_SESSION['user_id']);
    $requests = [];
    $totalHalaman = 1;

    if ($guru && isset($guru['id'])) {
        $requests = $peminjamanModel->getPeminjamanForVerification($guru['id'], $offset, $limit, $filters);
        $totalRequests = $peminjamanModel->countAllVerificationRequests($guru['id'], $filters);
        $totalHalaman = ceil($totalRequests / $limit);
    } else {
        Flasher::setFlash('Peringatan!', 'Data profil guru Anda tidak lengkap. Silakan hubungi Administrator.', 'danger');
    }

    $data = [ 
        'title' => 'Verifikasi Peminjaman', 
        'username' => $_SESSION['username'],
        'requests' => $requests,
        'halaman_aktif' => $halaman,
        'total_halaman' => $totalHalaman,
        'filters' => $filters // Mengirim $filters ke view
    ];

    $this->view('guru/verifikasi_peminjaman', $data);
}
    
    public function daftarSiswaWali() {
        $this->checkAuth();

        $guruModel = new Guru_model();
        $kelasModel = new Kelas_model();
        $siswaModel = new Siswa_model();

        $guru = $guruModel->getGuruByUserId($_SESSION['user_id']);
        
        $data_kelas_wali = [];
        $limit = 10; 

        if ($guru) {
            $kelas_list = $kelasModel->getKelasByWaliId($guru['id']);

            foreach ($kelas_list as $kelas) {
                $siswa_di_kelas = $siswaModel->getSiswaByKelasIdPaginated($kelas['id'], 0, $limit); 
                $total_siswa = $siswaModel->countSiswaByKelasId($kelas['id']);
                
                $data_kelas_wali[] = [
                    'kelas' => $kelas,
                    'siswa' => $siswa_di_kelas,
                    'total_halaman' => ceil($total_siswa / $limit),
                    'halaman_aktif' => 1
                ];
            }
        }

        $data = [ 
            'title' => 'Daftar Siswa Wali', 
            'username' => $_SESSION['username'],
            'data_kelas_wali' => $data_kelas_wali
        ];

        $this->view('guru/daftar_siswa_wali', $data);
    }

    public function getSiswaPage($kelasId = 0, $halaman = 1) {
        $this->checkAuth();
        header('Content-Type: application/json');

        $siswaModel = new Siswa_model();
        $limit = 10;
        $halaman = max(1, (int)$halaman);
        $offset = ($halaman - 1) * $limit;

        $siswa_list = $siswaModel->getSiswaByKelasIdPaginated((int)$kelasId, $offset, $limit);
        $total_siswa = $siswaModel->countSiswaByKelasId((int)$kelasId);
        $total_halaman = ceil($total_siswa / $limit);

        $tableBodyHtml = '';
        if (!empty($siswa_list)) {
            $no = $offset + 1;
            foreach ($siswa_list as $siswa) {
                $tableBodyHtml .= '<tr>';
                $tableBodyHtml .= '<td>' . $no++ . '</td>';
                $tableBodyHtml .= '<td>' . htmlspecialchars($siswa['nama']) . '</td>';
                $tableBodyHtml .= '<td>' . htmlspecialchars($siswa['id_siswa']) . '</td>';
                $tableBodyHtml .= '<td>' . htmlspecialchars($siswa['jenis_kelamin']) . '</td>';
                $tableBodyHtml .= '<td>' . htmlspecialchars($siswa['no_hp'] ?? '-') . '</td>';
                $tableBodyHtml .= '</tr>';
            }
        } else {
            $tableBodyHtml = '<tr><td colspan="5" style="text-align:center;">Tidak ada siswa di halaman ini.</td></tr>';
        }

        $paginationHtml = '';
        if ($total_halaman > 1) {
            $paginationHtml .= '<a href="#" class="pagination-link pagination-btn ' . ($halaman <= 1 ? 'disabled' : '') . '" data-page="' . max(1, $halaman - 1) . '" data-kelasid="' . $kelasId . '">Sebelumnya</a>';
            $paginationHtml .= '<div class="page-numbers">';
            for ($i = 1; $i <= $total_halaman; $i++) {
                $paginationHtml .= '<a href="#" class="pagination-link page-link ' . ($i == $halaman ? 'active' : '') . '" data-page="' . $i . '" data-kelasid="' . $kelasId . '">' . $i . '</a>';
            }
            $paginationHtml .= '</div>';
            $paginationHtml .= '<a href="#" class="pagination-link pagination-btn ' . ($halaman >= $total_halaman ? 'disabled' : '') . '" data-page="' . min($total_halaman, $halaman + 1) . '" data-kelasid="' . $kelasId . '">Berikutnya</a>';
        }
        
        echo json_encode([
            'tableBody' => $tableBodyHtml,
            'pagination' => $paginationHtml
        ]);
        exit;
    }

    public function riwayatPeminjaman($halaman = 1) {
        $this->checkAuth();
        
        $peminjamanModel = new Peminjaman_model();
        $guruModel = new Guru_model();

        $halaman = max(1, (int)$halaman);
        $limit = 10;
        $offset = ($halaman - 1) * $limit;
        
        // FILTER DIPERBARUI: Menambahkan 'waktu'
        $filters = [
            'keyword' => $_GET['search'] ?? null,
            'status' => $_GET['filter_status'] ?? null,
            'waktu' => $_GET['filter_waktu'] ?? 'terbaru'
        ];
        
        $guru = $guruModel->getGuruByUserId($_SESSION['user_id']);
        $history = [];
        $totalHalaman = 1;

        if ($guru) {
            $history = $peminjamanModel->getHistoryForWali($guru['id'], $offset, $limit, $filters);
            $totalRiwayat = $peminjamanModel->countHistoryForWali($guru['id'], $filters);
            $totalHalaman = ceil($totalRiwayat / $limit);
        }
        
        $data = [ 
            'title' => 'Riwayat Peminjaman', 
            'username' => $_SESSION['username'],
            'history' => $history,
            'halaman_aktif' => $halaman,
            'total_halaman' => $totalHalaman,
            'filters' => $filters
        ];
        
        $this->view('guru/riwayat_peminjaman', $data);
    }

    public function changePassword() {
    $this->checkAuth();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $userModel = new User_model();

        if ($_POST['new_password'] === $_POST['confirm_password']) {
            if (strlen($_POST['new_password']) >= 6) {
                $userModel->changePassword($_SESSION['user_id'], $_POST['new_password']);
                Flasher::setFlash('Berhasil!', 'Kata sandi telah diubah.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Password baru minimal harus 6 karakter.', 'danger');
            }
        } else {
            Flasher::setFlash('Gagal!', 'Konfirmasi password baru tidak cocok.', 'danger');
        }
    }
    header('Location: ' . BASEURL . '/guru/profile');
    exit;
}

    public function view($view, $data = []) {
        extract($data);
        require_once '../app/views/layouts/guru_header.php';
        require_once '../app/views/' . $view . '.php';
        require_once '../app/views/layouts/guru_footer.php';
    }
    public function prosesVerifikasi() {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['peminjaman_id']) && isset($_POST['status'])) {
            $peminjamanModel = new Peminjaman_model();
            $barangModel = new Barang_model();
            $peminjamanId = $_POST['peminjaman_id'];
            $status = $_POST['status'];
            $keterangan = $_POST['keterangan'] ?? null;
            
            $peminjaman = $peminjamanModel->getPeminjamanById($peminjamanId);

            if ($peminjaman && $peminjaman['status'] == 'Menunggu Verifikasi') {
                if ($status === 'Ditolak' && empty($keterangan)) {
                    Flasher::setFlash('Gagal!', 'Alasan penolakan tidak boleh kosong.', 'danger');
                } else {
                    $result = $peminjamanModel->updatePeminjamanStatusAndKeterangan($peminjamanId, $status, $keterangan);
                    
                    if ($result > 0) {
                        if ($status === 'Disetujui') {
                            $barangModel->kurangiStok($peminjaman['barang_id'], $peminjaman['jumlah_pinjam']);
                            Flasher::setFlash('Berhasil!', 'Peminjaman berhasil disetujui dan stok barang telah dikurangi.', 'success');
                        } else if ($status === 'Ditolak') {
                            Flasher::setFlash('Berhasil!', 'Peminjaman berhasil ditolak.', 'success');
                        }
                    } else {
                        Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat memperbarui status.', 'danger');
                    }
                }
            } else {
                 Flasher::setFlash('Gagal!', 'Permintaan peminjaman tidak valid atau sudah diproses.', 'danger');
            }
        } else {
            Flasher::setFlash('Gagal!', 'Permintaan tidak valid.', 'danger');
        }
        // Redirect ke halaman dashboard guru setelah proses
        header('Location: ' . BASEURL . '/guru/dashboard');
        exit;
    }
   
    public function detailSiswa($id) {
        $this->checkAuth();
        $siswaModel = new Siswa_model();
        
        $data = [
            'title' => 'Detail Siswa',
            'username' => $_SESSION['username'],
            'siswa' => $siswaModel->getSiswaById($id)
        ];

        if (!$data['siswa']) {
            header('Location: ' . BASEURL . '/guru/siswa');
            exit;
        }

        $this->view('guru/detail_siswa', $data);
    }
    
}