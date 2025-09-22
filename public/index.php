<?php
// =================================================================
// INITIALIZER & BOOTSTRAP
// =================================================================
if (!session_id()) { 
    session_start(); 
}

require_once '../config/config.php';
require_once '../app/core/Database.php';
require_once '../app/core/Flasher.php';
require_once '../app/controllers/AuthController.php';
require_once '../app/controllers/AdminController.php';
require_once '../app/controllers/GuruController.php';
require_once '../app/controllers/SiswaController.php';
require_once '../app/models/User_model.php';
require_once '../app/models/Barang_model.php';
require_once '../app/models/Guru_model.php';
require_once '../app/models/Kelas_model.php';
require_once '../app/models/Siswa_model.php';
require_once '../app/models/Peminjaman_model.php';
require_once '../app/models/Profile_model.php';
require_once '../app/models/Staff_model.php';

// =================================================================
// PARSING URL
// =================================================================
$authController = new AuthController();
$adminController = new AdminController();
$guruController = new GuruController();
$siswaController = new SiswaController();

$url = isset($_GET['url']) ? $_GET['url'] : '';
$url_parts = explode('/', filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));

$controller = $url_parts[0] ?? '';
$method = $url_parts[1] ?? '';
$param1 = $url_parts[2] ?? '';
$param2 = $url_parts[3] ?? '';

// =================================================================
// RUTE OTENTIKASI
// =================================================================
if (empty($controller)) {
    $authController->showPilihPeran();
} elseif ($controller === 'login') {
    $authController->showLogin();
} elseif ($controller === 'process-login') {
    $authController->processLogin();
} elseif ($controller === 'logout') {
    $authController->logout();
} 

// =================================================================
// RUTE UNTUK "ADMIN"
// =================================================================
elseif ($controller === 'admin') {
    if ($method === 'dashboard' || empty($method)) {
        $adminController->index();
    }
    elseif ($method === 'updateProfile') { 
        $adminController->updateProfile(); 
    }
    elseif ($method === 'change-password') {
    $adminController->changePassword();
    } 
    elseif ($method === 'pengguna') { $adminController->manajemenPengguna($param1, $param2); }
    elseif ($method === 'tambah-staff') { $adminController->tambahStaff(); }
    elseif ($method === 'import-staff') { $adminController->importStaff(); }
    elseif ($method === 'searchStaff') { $adminController->searchStaff(); }
    elseif ($method === 'hapus-staff' && !empty($param1)) { $adminController->hapusStaff($param1); }
    elseif ($method === 'get-staff-by-id' && !empty($param1)) { $adminController->getStaffById($param1); }
    elseif ($method === 'ubah-staff') { $adminController->ubahStaff(); }
    elseif ($method === 'hapus-staff-massal') { $adminController->hapusStaffMassal(); }
    elseif ($method === 'import-guru') { $adminController->importGuru(); }
    elseif ($method === 'searchGuru') { $adminController->searchGuru(); }
    elseif ($method === 'hapus-guru' && !empty($param1)) { $adminController->hapusGuru($param1); }
    elseif ($method === 'get-guru-by-id' && !empty($param1)) { $adminController->getGuruById($param1); }
    elseif ($method === 'ubah-guru') { $adminController->ubahGuru(); }
    elseif ($method === 'detailGuru' && !empty($param1)) { $adminController->detailGuru($param1); }
    elseif ($method === 'hapus-guru-massal') { $adminController->hapusGuruMassal(); }
    elseif ($method === 'detailStaff' && !empty($param1)) { $adminController->detailStaff($param1); }
    elseif ($method === 'tambah-siswa') { $adminController->tambahSiswa(); }
    elseif ($method === 'ubah-siswa') { $adminController->ubahSiswa(); }
    elseif ($method === 'hapus-siswa' && !empty($param1)) { $adminController->hapusSiswa($param1); }
    elseif ($method === 'get-siswa-by-id' && !empty($param1)) { $adminController->getSiswaById($param1); }
    elseif ($method === 'hapus-siswa-massal') { $adminController->hapusSiswaMassal(); }
    elseif ($method === 'import-siswa') { $adminController->importSiswa(); }
    elseif ($method === 'detailSiswa' && !empty($param1)) { $adminController->detailSiswa($param1); }
    elseif ($method === 'barang') { $halaman = $param1 ?? 1; $adminController->manajemenBarang($halaman); }
    elseif ($method === 'tambah-barang') { $adminController->tambahBarang(); }
    elseif ($method === 'import-barang') { $adminController->importBarang(); }
    elseif ($method === 'ubah-barang') { $adminController->ubahBarang(); }
    elseif ($method === 'hapus-barang' && !empty($param1)) { $adminController->hapusBarang($param1); }
    elseif ($method === 'get-barang-by-id' && !empty($param1)) { $adminController->getBarangById($param1); } 
    elseif ($method === 'detailBarang' && !empty($param1)) { $adminController->detailBarang($param1); }
    elseif ($method === 'hapus-barang-massal') { $adminController->hapusBarangMassal(); }
    elseif ($method === 'import-barang') { $adminController->importBarang(); } 
    elseif ($method === 'import-kelas') { $adminController->importKelas(); }
    elseif ($method === 'hapus-kelas-massal') { $adminController->hapusKelasMassal(); }
    elseif ($method === 'kelas') { $halaman = $param1 ?: 1; $adminController->manajemenKelas($halaman); }
    elseif ($method === 'tambah-kelas') { $adminController->tambahKelas(); }
    elseif ($method === 'get-kelas-by-id' && !empty($param1)) { $adminController->getKelasById($param1); }
    elseif ($method === 'ubah-kelas') { $adminController->ubahKelas(); }
    elseif ($method === 'hapus-kelas' && !empty($param1)) { $adminController->hapusKelas($param1); }
    elseif ($method === 'detailKelas' && !empty($param1)) { $halaman = $param2 ?? 1; $adminController->detailKelas($param1, $halaman); }
    elseif ($method === 'ubah-password-akun') { $adminController->ubahPasswordAkun(); }
    elseif ($method === 'laporan') { $halaman = $param1 ?? 1; $adminController->laporanRiwayat($halaman); } 
    elseif ($method === 'unduh-laporan') { $adminController->unduhLaporan(); }
    elseif ($method === 'profile') { $adminController->profile(); } 
    elseif ($method === 'change-password') { $adminController->changePassword(); }
    elseif ($method === 'searchUnassignedSiswa') { $adminController->searchUnassignedSiswa(); } // Rute yang hilang
    elseif ($method === 'importSiswaKeKelas') { $adminController->importSiswaKeKelas(); }
    elseif ($method === 'assignSiswaToKelas') { $adminController->assignSiswaToKelas(); }
    elseif ($method === 'remove-siswa-massal') { $adminController->removeSiswaDariKelasMassal(); }
    elseif ($method === 'hapusSiswaDariKelas' && !empty($param1)) { $adminController->hapusSiswaDariKelas($param1); }
    else {
        http_response_code(404);
        echo "<h1>404 Not Found</h1> Halaman yang Anda cari tidak ditemukan di dalam Admin.";
    }
}

/* =====================================================================
 * BLOK ROUTING GURU YANG SUDAH DIRAPIKAN
 * =====================================================================
 */
elseif ($controller === 'guru') {
    if ($method === 'dashboard' || empty($method)) {
        $guruController->index();
    } elseif ($method === 'verifikasi') {
        $guruController->verifikasiPeminjaman($param1 ?? 1);
    } elseif ($method === 'proses-verifikasi') {
        $guruController->prosesVerifikasi();
    } elseif ($method === 'siswa') {
        $guruController->daftarSiswaWali();
    } elseif ($method === 'riwayat') {
        $guruController->riwayatPeminjaman($param1 ?? 1);
    } elseif ($method === 'getSiswaPage' && !empty($param1)) {
        $guruController->getSiswaPage($param1, $param2 ?? 1);
    } elseif ($method === 'detailSiswa' && !empty($param1)) {
        $guruController->detailSiswa($param1);
    } elseif ($method === 'profile') {
        $guruController->profile();
    } elseif ($method === 'updateProfile') { // <--- TAMBAHKAN BARIS INI
        $guruController->updateProfile();   // <--- TAMBAH BARIS INI
    } elseif ($method === 'change-password') {
        $guruController->changePassword();
    } else {
        http_response_code(404);
        echo "<h1>404 Not Found</h1> Halaman yang Anda cari tidak ditemukan di dalam Guru.";
    }
}
// =================================================================
// RUTE UNTUK "SISWA"
// =================================================================
elseif ($controller === 'siswa') {
    if ($method === 'dashboard' || empty($method)) {
        $siswaController->index();
    } elseif ($method === 'katalog') {
        $halaman = $param1 ?? 1;
        $siswaController->katalogBarang($halaman);
    } elseif ($method === 'riwayat') {
        $halaman = $param1 ?? 1;
        $siswaController->riwayatPeminjaman($halaman);
    } elseif ($method === 'tambah-ke-keranjang') {
        $siswaController->tambahKeKeranjang();
    } elseif ($method === 'hapus-dari-keranjang' && !empty($param1)) {
        $siswaController->hapusDariKeranjang($param1);
    } elseif ($method === 'proses-peminjaman') {
        $siswaController->prosesPeminjaman();
    } elseif ($method === 'profile') {
        $siswaController->profile();
    } elseif ($method === 'ubah-profile') { $siswaController->ubahProfile(); }
    elseif ($method === 'get-profile-by-id') { $siswaController->getProfileById(); }
    // Perbaikan utama ada di baris ini
    elseif ($method === 'changePassword') { $siswaController->changePassword(); }
    elseif ($method === 'proses-pengembalian') {
        $siswaController->prosesPengembalian();
    } elseif ($method === 'get-peminjaman-by-id' && !empty($param1)) {
        $siswaController->getPeminjamanById($param1);
    } else {
        http_response_code(404);
        echo "<h1>404 Not Found</h1> Halaman yang Anda cari tidak ditemukan di dalam Siswa.";
    }
}  

// =================================================================
// PENANGANAN JIKA CONTROLLER TIDAK DITEMUKAN
// =================================================================
else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1> Halaman yang Anda cari tidak ditemukan.";
}