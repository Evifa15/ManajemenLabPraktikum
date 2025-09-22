<?php

class AdminController
{
    private function checkAuth()
    {
        if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
            Flasher::setFlash('Akses ditolak!', 'Anda harus login sebagai admin.', 'danger');
            header('Location: ' . BASEURL . '/login?role=admin');
            exit;
        }
    }

    public function index()
{
    $this->checkAuth();
    $userModel = new User_model();
    $staffModel = new Staff_model();
    $barangModel = new Barang_model();
    $peminjamanModel = new Peminjaman_model();

    // Mengambil data profil admin dengan benar menggunakan Staff_model
    $user = $userModel->getUserById($_SESSION['user_id']);
    $profile = $staffModel->getStaffByUserId($user['id']);

    $totalPengguna = $userModel->countAllUsersSimple();
    $totalJenisBarang = $barangModel->countAllBarangSimple();
    $totalStok = $barangModel->getTotalStock();
    $barangDipinjam = $peminjamanModel->countBarangDipinjam();

    $availabilitySummary = $barangModel->getAvailabilitySummary();
    $chartLabels = json_encode(array_column($availabilitySummary, 'status_ketersediaan'));
    $chartData = json_encode(array_column($availabilitySummary, 'jumlah'));

    $totalItemsForChart = array_sum(array_column($availabilitySummary, 'jumlah'));
    $chartDetails = [];
    $chartColors = [];
    $colorMap = [
        'Tersedia' => ['name' => 'green', 'rgb' => 'rgb(22, 163, 74)'],
        'Terbatas' => ['name' => 'orange', 'rgb' => 'rgb(245, 158, 11)'],
        'Tidak Tersedia' => ['name' => 'red', 'rgb' => 'rgb(220, 38, 38)']
    ];

    foreach ($availabilitySummary as $item) {
        $status = $item['status_ketersediaan'];
        $percentage = ($totalItemsForChart > 0) ? round(($item['jumlah'] / $totalItemsForChart) * 100) : 0;
        $chartDetails[] = [
            'label' => ucfirst($status),
            'value' => $item['jumlah'],
            'percentage' => $percentage,
            'color' => $colorMap[$status]['name'] ?? 'grey'
        ];
        $chartColors[] = $colorMap[$status]['rgb'] ?? 'rgb(128, 128, 128)';
    }

    $peminjamanTerbaru = $peminjamanModel->getLatestPeminjaman(3);

    $data = [
        'title' => 'Dashboard Admin',
        'username' => $_SESSION['username'],
        'profile' => $profile,
        'totalPengguna' => $totalPengguna,
        'totalJenisBarang' => $totalJenisBarang,
        'totalStok' => $totalStok,
        'barangDipinjam' => $barangDipinjam,
        'chartLabels' => $chartLabels,
        'chartData' => $chartData,
        'chartColors' => json_encode($chartColors),
        'chartDetails' => $chartDetails,
        'peminjamanTerbaru' => $peminjamanTerbaru
    ];

    $this->view('admin/index', $data);
}
    
    // --- METHOD BARU DITAMBAHKAN DI SINI ---
    public function profile()
{
    $this->checkAuth();
    $userModel = new User_model();
    $staffModel = new Staff_model();

    $data['user'] = $userModel->getUserById($_SESSION['user_id']);
    $data['profile'] = $staffModel->getStaffByUserId($data['user']['id']); // <-- BARIS INI SUDAH BENAR
    $data['title'] = 'Profil Admin';
    $this->view('admin/profile', $data);
}


    public function updateProfile()
{
    $this->checkAuth();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $staffModel = new Staff_model();

        $data = $_POST;
        $fotoLama = $_POST['foto_lama'] ?? 'default.png';
        $data['foto'] = $fotoLama;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $namaFotoBaru = 'staff_' . uniqid() . '.' . $ext;
            $targetDir = APP_ROOT . '/public/img/staff/';

            // Pastikan folder img/staff ada dan dapat ditulisi
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $targetDir . $namaFotoBaru)) {
                $data['foto'] = $namaFotoBaru;
                // Hapus foto lama jika bukan foto default
                if ($fotoLama !== 'default.png' && file_exists($targetDir . $fotoLama)) {
                    @unlink($targetDir . $fotoLama);
                }
            }
        }

        $result = $staffModel->updateStaff($data);
        if ($result > 0) {
            Flasher::setFlash('Berhasil!', 'Data profil berhasil diubah.', 'success');
        } else if ($result === 0) {
            Flasher::setFlash('Info', 'Tidak ada perubahan data yang disimpan.', 'danger');
        } else {
            Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat mengubah data.', 'danger');
        }
    }
    header('Location: ' . BASEURL . '/admin/profile');
    exit;
}

    public function manajemenBarang($halaman = 1)
    {
        $barangModel = new Barang_model();

        $halaman = max(1, (int) $halaman);
        $limit = 10;
        $offset = ($halaman - 1) * $limit;

        $filters = [
            'keyword' => $_GET['search'] ?? null,
            'kondisi' => $_GET['filter_kondisi'] ?? null,
            'status' => $_GET['filter_status'] ?? null
        ];

        $items = $barangModel->getBarangPaginated($offset, $limit, $filters);
        $totalBarang = $barangModel->countAllBarang($filters);
        $totalHalaman = ceil($totalBarang / $limit);

        $data = [
            'title' => 'Manajemen Barang',
            'items' => $items,
            'total_halaman' => $totalHalaman,
            'halaman_aktif' => $halaman,
            'filters' => $filters
        ];

        $this->view('admin/manajemen_barang', $data); 
    }
// Inside class AdminController
// Inside class AdminController
public function manajemenPengguna($tab = 'staff', $halaman = 1)
{
    $this->checkAuth();
    $halaman = max(1, (int) $halaman);
    $limit = 10;
    $offset = ($halaman - 1) * $limit;

    $data = [
        'title' => 'Manajemen Pengguna',
        'active_tab' => $tab,
        'limit' => $limit,
        'halaman_aktif' => $halaman,
    ];

    $keyword = null;
    $totalUsers = 0;
    $users = [];
    $totalHalaman = 1;

    switch ($tab) {
        case 'staff':
            $staffModel = new Staff_model();
            $keyword = $_GET['search_staff'] ?? null;
            $users = $staffModel->getStaffPaginated($offset, $limit, $keyword);
            $totalUsers = $staffModel->countAllStaff($keyword);
            $data['staff'] = $users;
            $data['search_term_staff'] = $keyword;
            break;
        case 'guru':
            $guruModel = new Guru_model();
            $keyword = $_GET['search_guru'] ?? null;
            $users = $guruModel->getGuruPaginated($offset, $limit, $keyword);
            $totalUsers = $guruModel->countAllGuru($keyword);
            $data['guru'] = $users;
            $data['search_term_guru'] = $keyword;
            break;
        case 'siswa':
            $siswaModel = new Siswa_model();
            $keyword = $_GET['search_siswa'] ?? null;
            $users = $siswaModel->getAllSiswaPaginated($offset, $limit, $keyword);
            $totalUsers = $siswaModel->countAllSiswa($keyword);
            $data['siswa'] = $users;
            $data['search_term_siswa'] = $keyword;
            break;
        case 'akun':
            $userModel = new User_model();
            $filters = [
                'keyword' => $_GET['search'] ?? null,
                'role' => $_GET['filter_role'] ?? null,
            ];
            $users = $userModel->getUsersPaginated($offset, $limit, $filters);
            $totalUsers = $userModel->countAllUsers($filters);
            $data['users'] = $users;
            $data['filters'] = $filters;
            break;
        default:
            // Jika tab tidak valid, default ke 'staff'
            $tab = 'staff';
            $staffModel = new Staff_model();
            $keyword = $_GET['search_staff'] ?? null;
            $users = $staffModel->getStaffPaginated($offset, $limit, $keyword);
            $totalUsers = $staffModel->countAllStaff($keyword);
            $data['staff'] = $users;
            $data['search_term_staff'] = $keyword;
            $data['active_tab'] = $tab;
    }

    $totalHalaman = ceil($totalUsers / $limit);
    $data['total_halaman'] = $totalHalaman;
    $this->view('admin/manajemen_pengguna', $data);
}

    public function ubahBarang()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $barangModel = new Barang_model();
            $data = $_POST;
            $data['gambar'] = $_POST['gambar_lama'];

            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['gambar'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $namaGambarBaru = uniqid() . '.' . $ext;
                $targetDir = APP_ROOT . '/public/img/barang/';

                if (move_uploaded_file($file['tmp_name'], $targetDir . $namaGambarBaru)) {
                    $data['gambar'] = $namaGambarBaru;
                    if ($data['gambar_lama'] && $data['gambar_lama'] !== 'images.png') {
                        @unlink($targetDir . $data['gambar_lama']);
                    }
                }
            }

            $jumlah = (int) $data['jumlah'];
            if ($jumlah <= 0) {
                $status = 'Tidak Tersedia';
            } elseif ($jumlah >= 1 && $jumlah <= 3) {
                $status = 'Terbatas';
            } else {
                $status = 'Tersedia';
            }
            $data['status'] = $status;

            $result = $barangModel->updateBarang($data);
            if ($result > 0) {
                Flasher::setFlash('Berhasil!', 'Data barang berhasil diubah.', 'success');
            } else if ($result === 0) {
                Flasher::setFlash('Info', 'Tidak ada perubahan data yang disimpan.', 'danger');
            } else {
                Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat mengubah data.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/admin/barang');
        exit;
    }

    public function hapusBarang($id)
    {
        $barangModel = new Barang_model();
        if ($barangModel->deleteBarang($id) > 0) {
            Flasher::setFlash('Berhasil!', 'Data barang berhasil dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Gagal menghapus data barang.', 'danger');
        }
          header('Location: ' . BASEURL . '/admin/barang');
        exit;
    }

    public function getBarangById($id)
    {
        header('Content-Type: application/json');
        $barangModel = new Barang_model();
        $barang = $barangModel->getBarangById($id);
        echo json_encode($barang);
        exit();
    }

    public function detailBarang($id)
    {
        $barangModel = new Barang_model();
        $data = [
            'title' => 'Detail Barang',
            'item' => $barangModel->getBarangById($id)
        ];
        $this->view('admin/barang/detail', $data);
    }

    public function manajemenKelas($halaman = 1)
    {
        $halaman = max(1, (int) $halaman);
        $limit = 10;
        $offset = ($halaman - 1) * $limit;
        $keyword = $_GET['search_kelas'] ?? null;

        $kelasModel = new Kelas_model();
        $guruModel = new Guru_model();

        $data['title'] = 'Manajemen Kelas';
        $data['all_guru'] = $guruModel->getAllGuru();
        $data['kelas'] = $kelasModel->getKelasPaginated($offset, $limit, $keyword);
        $data['total_kelas'] = $kelasModel->countAllKelas($keyword);
        $data['total_halaman'] = ceil($data['total_kelas'] / $limit);
        $data['halaman_aktif'] = $halaman;
        $data['search_term_kelas'] = $keyword;

        $this->view('admin/manajemen_kelas', $data);
    }

    public function tambahKelas()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $kelasModel = new Kelas_model();
            if (empty($_POST['wali_kelas_id'])) {
                Flasher::setFlash('Gagal!', 'Wali kelas tidak boleh kosong.', 'danger');
            } elseif ($kelasModel->tambahKelas($_POST) > 0) {
                Flasher::setFlash('Berhasil!', 'Data kelas berhasil ditambahkan.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Gagal menambahkan kelas.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/admin/kelas');
        exit;
    }

    public function importKelas()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_import_kelas'])) {
            $file = $_FILES['file_import_kelas'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                Flasher::setFlash('Gagal!', 'Terjadi error saat mengunggah file.', 'danger');
            } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) != 'csv') {
                Flasher::setFlash('Gagal!', 'Hanya file format .csv yang didukung.', 'danger');
            } else {
                $fileHandle = fopen($file['tmp_name'], 'r');
                fgetcsv($fileHandle);
                $guruModel = new Guru_model();
                $allGuru = $guruModel->getAllGuru();
                $guruMap = array_column($allGuru, 'id', 'nip');
                $dataUntukImport = [];
                $errors = [];
                $line = 2;
                while (($row = fgetcsv($fileHandle)) !== FALSE) {
                    $nama_kelas = trim($row[0]);
                    $nip_wali = trim($row[1]);
                    if (empty($nama_kelas)) {
                        $errors[] = "Baris {$line}: Nama kelas kosong.";
                    } elseif (empty($nip_wali) || !isset($guruMap[$nip_wali])) {
                        $errors[] = "Baris {$line}: NIP Wali Kelas '{$nip_wali}' tidak ditemukan.";
                    } else {
                        $dataUntukImport[] = ['nama_kelas' => $nama_kelas, 'wali_kelas_id' => $guruMap[$nip_wali]];
                    }
                    $line++;
                }
                fclose($fileHandle);
                if (empty($errors)) {
                    $kelasModel = new Kelas_model();
                    $hasil = $kelasModel->tambahKelasBatch($dataUntukImport);
                    if ($hasil['failed'] > 0) {
                        Flasher::setFlash('Gagal!', "{$hasil['failed']} data kelas gagal diimpor.", 'danger');
                    } else {
                        Flasher::setFlash('Berhasil!', "{$hasil['success']} data kelas berhasil diimpor.", 'success');
                    }
                } else {
                    $pesan = "Gagal mengimpor data kelas. <br> Detail: <br>" . implode('<br>', $errors);
                    Flasher::setFlash('Proses Gagal', $pesan, 'danger');
                }
            }
        } else {
            Flasher::setFlash('Gagal!', 'Tidak ada file yang diunggah.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/kelas');
        exit;
    }

    public function importBarang() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_import_barang'])) {
        $file = $_FILES['file_import_barang'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Flasher::setFlash('Gagal!', 'Terjadi error saat mengunggah file.', 'danger');
        } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) != 'csv') {
            Flasher::setFlash('Gagal!', 'Hanya file format .csv yang didukung.', 'danger');
        } else {
            $fileHandle = fopen($file['tmp_name'], 'r');
            fgetcsv($fileHandle); // Lewati header

            $dataUntukImport = [];
            while (($row = fgetcsv($fileHandle)) !== FALSE) {
                if (count($row) < 7 || empty(trim($row[0]))) continue; // Lewati baris kosong

                $dataUntukImport[] = [
                    'kode_barang'        => trim($row[0]),
                    'nama_barang'        => trim($row[1]),
                    'jumlah'             => (int)trim($row[2]),
                    'kondisi'            => trim($row[3]),
                    'status'             => trim($row[4]),
                    'lokasi_penyimpanan' => trim($row[5]),
                    'tanggal_pembelian'  => !empty(trim($row[6])) ? trim($row[6]) : null,
                    'gambar'             => 'images.png' // Gambar default
                ];
            }
            fclose($fileHandle);

            if (!empty($dataUntukImport)) {
                $barangModel = new Barang_model();
                $hasil = $barangModel->importBarangBatch($dataUntukImport);

                if ($hasil['failed'] > 0) {
                    $pesan = "{$hasil['success']} data berhasil diimpor, {$hasil['failed']} data gagal. <br> Detail: <br>" . implode('<br>', $hasil['errors']);
                    Flasher::setFlash('Proses Selesai dengan Error', $pesan, 'danger');
                } else {
                    Flasher::setFlash('Berhasil!', "{$hasil['success']} data barang berhasil diimpor.", 'success');
                }
            } else {
                Flasher::setFlash('Gagal!', 'File CSV kosong atau formatnya salah.', 'danger');
            }
        }
    } else {
        Flasher::setFlash('Gagal!', 'Tidak ada file yang diunggah.', 'danger');
    }
    header('Location: ' . BASEURL . '/admin/barang');
    exit;
}

    public function hapusKelas($id)
    {
        $kelasModel = new Kelas_model();
        $result = $kelasModel->hapusKelas($id);
        if ($result > 0) {
            Flasher::setFlash('Berhasil!', 'Data kelas berhasil dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Gagal menghapus kelas. Pastikan tidak ada siswa yang terdaftar di kelas ini.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/kelas');
        exit;
    }
     public function unduhLaporan() {
        $peminjamanModel = new Peminjaman_model();
        
        $filters = [
            'keyword' => $_GET['search'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null
        ];
        
        $history = $peminjamanModel->getAllHistoryForExport($filters);

        if (empty($history)) {
            Flasher::setFlash('Gagal!', 'Tidak ada data yang dapat diunduh.', 'danger');
            header('Location: ' . BASEURL . '/admin/laporan');
            exit;
        }

        $filename = 'Laporan_Peminjaman_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $output = fopen('php://output', 'w');
        
        // Header CSV
        fputcsv($output, ['ID Peminjaman', 'Nama Peminjam', 'ID Peminjam', 'Nama Barang', 'Tgl Pinjam', 'Tgl Kembali', 'Status']);

        // Data
        foreach ($history as $row) {
            fputcsv($output, [
                $row['id'],
                $row['nama_peminjam'],
                $row['no_id_peminjam'],
                $row['nama_barang'],
                $row['tanggal_pinjam'],
                $row['tanggal_kembali'],
                $row['status']
            ]);
        }
        fclose($output);
        exit;
    }

    public function laporanRiwayat($halaman = 1)
    {
        $peminjamanModel = new Peminjaman_model();
        $halaman = max(1, (int) $halaman);
        $limit = 10;
        $offset = ($halaman - 1) * $limit;
        $filters = ['keyword' => $_GET['search'] ?? null];
        $history = $peminjamanModel->getHistoryPaginated($offset, $limit, $filters);
        $totalHistory = $peminjamanModel->countAllHistory($filters);
        $totalHalaman = ceil($totalHistory / $limit);
        $data = [
            'title' => 'Laporan & Riwayat',
            'history' => $history,
            'halaman_aktif' => $halaman,
            'total_halaman' => $totalHalaman,
            'filters' => $filters
        ];
        $this->view('admin/laporan_riwayat', $data);
    }

    public function tambahStaff()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $staffModel = new Staff_model();
            if ($staffModel->createStaffAndUserAccount($_POST) > 0) {
                Flasher::setFlash('Berhasil!', 'Data staff berhasil ditambahkan.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Gagal menambahkan staff. Pastikan ID Staff unik.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/admin/pengguna/staff');
        exit;
    }

    public function hapusStaff($id)
    {
        $staffModel = new Staff_model();
        if ($staffModel->hapusStaff($id) > 0) {
            Flasher::setFlash('Berhasil!', 'Data staff berhasil dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Gagal menghapus data staff.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/pengguna/staff');
        exit;
    }

    public function view($view, $data = [])
    {
        extract($data);
        require_once '../app/views/layouts/admin_header.php';
        require_once '../app/views/' . $view . '.php';
        require_once '../app/views/layouts/admin_footer.php';
    }

    public function importStaff()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_import_staff'])) {
            $file = $_FILES['file_import_staff'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                Flasher::setFlash('Gagal!', 'Terjadi error saat mengunggah file.', 'danger');
            } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) != 'csv') {
                Flasher::setFlash('Gagal!', 'Hanya file format .csv yang didukung.', 'danger');
            } else {
                $fileHandle = fopen($file['tmp_name'], 'r');
                fgetcsv($fileHandle);
                $dataUntukImport = [];
                while (($row = fgetcsv($fileHandle)) !== FALSE) {
                    if (!empty($row[0]) && !empty($row[1])) {
                        $dataUntukImport[] = [
                            'nama'          => trim($row[0]),
                            'id_staff'      => trim($row[1]),
                            'jenis_kelamin' => trim($row[2] ?? 'Laki-laki'),
                            'no_hp'         => trim($row[3] ?? null),
                            'email'         => trim($row[4] ?? null),
                            'ttl'           => null,
                            'agama'         => null,
                            'alamat'        => null
                        ];
                    }
                }
                fclose($fileHandle);
                if (!empty($dataUntukImport)) {
                    $staffModel = new Staff_model();
                    $hasil = $staffModel->importStaffBatch($dataUntukImport);
                    if ($hasil['failed'] > 0) {
                        $pesan = "{$hasil['success']} data berhasil diimpor, {$hasil['failed']} data gagal. <br> Detail Error: <br>" . implode('<br>', $hasil['errors']);
                        Flasher::setFlash('Proses Selesai dengan Error', $pesan, 'danger');
                    } else {
                        Flasher::setFlash('Berhasil!', "{$hasil['success']} data staff berhasil diimpor.", 'success');
                    }
                } else {
                    Flasher::setFlash('Gagal!', 'File CSV kosong atau formatnya salah.', 'danger');
                }
            }
        } else {
            Flasher::setFlash('Gagal!', 'Tidak ada file yang diunggah.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/pengguna/staff');
        exit;
    }

    public function searchStaff()
    {
        header('Content-Type: application/json');
        $keyword = $_GET['keyword'] ?? '';
        $staffModel = new Staff_model();
        $staffData = $staffModel->getStaffPaginated(0, 999, $keyword);
        echo json_encode($staffData);
        exit();
    }

    public function getStaffById($id)
    {
        header('Content-Type: application/json');
        $staffModel = new Staff_model();
        $staff = $staffModel->getStaffById($id);
        echo json_encode($staff);
        exit();
    }

    public function ubahStaff()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $staffModel = new Staff_model();
            if ($staffModel->updateStaff($_POST) >= 0) {
                Flasher::setFlash('Berhasil!', 'Data staff berhasil diubah.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Gagal mengubah data staff.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/admin/pengguna/staff');
        exit;
    }

    public function hapusStaffMassal()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['ids'])) {
            $ids = $_POST['ids'];
            $staffModel = new Staff_model();
            $rowCount = $staffModel->hapusStaffMassal($ids);
            if ($rowCount > 0) {
                Flasher::setFlash('Berhasil!', "{$rowCount} data staff berhasil dihapus.", 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Tidak ada data staff yang dihapus.', 'danger');
            }
        } else {
            Flasher::setFlash('Gagal!', 'Tidak ada data yang dipilih untuk dihapus.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/pengguna/staff');
        exit;
    }

    public function tambahGuru()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $guruModel = new Guru_model();
            if ($guruModel->createGuruAndUserAccount($_POST) > 0) {
                Flasher::setFlash('Berhasil!', 'Data guru berhasil ditambahkan.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Gagal menambahkan guru. Pastikan NIP unik.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/admin/pengguna/guru');
        exit;
    }

    public function importGuru()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_import_guru'])) {
            $file = $_FILES['file_import_guru'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                Flasher::setFlash('Gagal!', 'Terjadi error saat mengunggah file.', 'danger');
            } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) != 'csv') {
                Flasher::setFlash('Gagal!', 'Hanya file format .csv yang didukung.', 'danger');
            } else {
                $fileHandle = fopen($file['tmp_name'], 'r');
                fgetcsv($fileHandle);
                $dataUntukImport = [];
                while (($row = fgetcsv($fileHandle)) !== FALSE) {
                    if (!empty($row[0]) && !empty($row[1])) {
                        $dataUntukImport[] = [
                            'nama'          => trim($row[0]),
                            'nip'           => trim($row[1]),
                            'jenis_kelamin' => trim($row[2] ?? 'Laki-laki'),
                            'no_hp'         => trim($row[3] ?? null),
                            'email'         => trim($row[4] ?? null),
                            'ttl'           => null, 'agama' => null, 'alamat' => null
                        ];
                    }
                }
                fclose($fileHandle);
                if (!empty($dataUntukImport)) {
                    $guruModel = new Guru_model();
                    $hasil = $guruModel->tambahGuruBatch($dataUntukImport);
                    if ($hasil['failed'] > 0) {
                        $pesan = "{$hasil['success']} data berhasil diimpor, {$hasil['failed']} data gagal. <br> Detail: <br>" . implode('<br>', $hasil['errors']);
                        Flasher::setFlash('Proses Selesai dengan Error', $pesan, 'danger');
                    } else {
                        Flasher::setFlash('Berhasil!', "{$hasil['success']} data guru berhasil diimpor.", 'success');
                    }
                } else {
                    Flasher::setFlash('Gagal!', 'File CSV kosong atau formatnya salah.', 'danger');
                }
            }
        } else {
            Flasher::setFlash('Gagal!', 'Tidak ada file yang diunggah.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/pengguna/guru');
        exit;
    }

    public function searchGuru()
    {
        header('Content-Type: application/json');
        $keyword = $_GET['keyword'] ?? '';
        $guruModel = new Guru_model();
        $guruData = $guruModel->getGuruPaginated(0, 999, $keyword);
        echo json_encode($guruData);
        exit();
    }

    public function hapusGuru($id)
    {
        $guruModel = new Guru_model();
        if ($guruModel->hapusGuru($id) > 0) {
            Flasher::setFlash('Berhasil!', 'Data guru berhasil dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Gagal menghapus data guru.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/pengguna/guru');
        exit;
    }

    public function getGuruById($id)
    {
        header('Content-Type: application/json');
        $guruModel = new Guru_model();
        $guru = $guruModel->getGuruById($id);
        echo json_encode($guru);
        exit();
    }

    public function ubahGuru()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $guruModel = new Guru_model();
            $result = $guruModel->updateGuru($_POST);
            if ($result > 0) {
                Flasher::setFlash('Berhasil!', 'Data guru berhasil diubah.', 'success');
            } else if ($result === 0) {
                Flasher::setFlash('Info', 'Tidak ada perubahan data yang disimpan.', 'danger');
            } else {
                Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat mengubah data.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/admin/pengguna/guru');
        exit;
    }

    public function detailGuru($id)
    {
        $guruModel = new Guru_model();
        $data = [
            'title' => 'Detail Guru',
            'guru' => $guruModel->getGuruById($id)
        ];
        $this->view('admin/guru/detail', $data);
    }

    public function hapusGuruMassal()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['ids'])) {
            $ids = $_POST['ids'];
            $guruModel = new Guru_model();
            $rowCount = $guruModel->hapusGuruMassal($ids);
            if ($rowCount > 0) {
                Flasher::setFlash('Berhasil!', "{$rowCount} data guru berhasil dihapus.", 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Tidak ada data guru yang dihapus.', 'danger');
            }
        } else {
            Flasher::setFlash('Gagal!', 'Tidak ada data yang dipilih untuk dihapus.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/pengguna/guru');
        exit;
    }

    public function detailStaff($id)
    {
        $staffModel = new Staff_model();
        $data = [
            'title' => 'Detail Staff',
            'staff' => $staffModel->getStaffById($id)
        ];
        $this->view('admin/staff/detail', $data);
    }

    public function tambahSiswa()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $namaFoto = 'default.png';
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['foto'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $namaFotoBaru = uniqid('siswa_') . '.' . $ext;
                $targetDir = APP_ROOT . '/public/img/siswa/';
                if (move_uploaded_file($file['tmp_name'], $targetDir . $namaFotoBaru)) {
                    $namaFoto = $namaFotoBaru;
                }
            }
            $data = $_POST;
            $data['foto'] = $namaFoto;
            $siswaModel = new Siswa_model();
            if ($siswaModel->createSiswaAndUserAccount($data) > 0) {
                Flasher::setFlash('Berhasil!', 'Data siswa berhasil ditambahkan.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Gagal menambahkan siswa. Pastikan ID Siswa (NIS) unik.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/admin/pengguna/siswa');
        exit;
    }

    public function hapusSiswa($id)
    {
        $siswaModel = new Siswa_model();
        if ($siswaModel->hapusSiswa($id) > 0) {
            Flasher::setFlash('Berhasil!', 'Data siswa berhasil dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Gagal menghapus data siswa.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/pengguna/siswa');
        exit;
    }

    public function detailSiswa($id)
{
    $siswaModel = new Siswa_model();
    $data = [
        'title' => 'Detail Siswa',
        'siswa' => $siswaModel->getSiswaById($id),
        'origin' => $_GET['origin'] ?? null,
        'kelas_id' => $_GET['kelas_id'] ?? null
    ];
    $this->view('admin/siswa/detail', $data);
}

    public function ubahSiswa()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $siswaModel = new Siswa_model();
            $data = $_POST;
            $data['foto'] = $_POST['foto_lama'];
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['foto'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $namaFotoBaru = uniqid('siswa_') . '.' . $ext;
                $targetDir = APP_ROOT . '/public/img/siswa/';
                if (move_uploaded_file($file['tmp_name'], $targetDir . $namaFotoBaru)) {
                    $data['foto'] = $namaFotoBaru;
                    if ($data['foto_lama'] && $data['foto_lama'] !== 'default.png') {
                        @unlink($targetDir . $data['foto_lama']);
                    }
                }
            }
            $result = $siswaModel->updateSiswa($data);
            if ($result > 0) {
                Flasher::setFlash('Berhasil!', 'Data siswa berhasil diubah.', 'success');
            } else if ($result === 0) {
                Flasher::setFlash('Info', 'Tidak ada perubahan data yang disimpan.', 'danger');
            } else {
                Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat mengubah data.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/admin/pengguna/siswa');
        exit;
    }

    public function hapusSiswaMassal()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['ids'])) {
            $ids = $_POST['ids'];
            $siswaModel = new Siswa_model();
            $rowCount = $siswaModel->hapusSiswaMassal($ids);
            if ($rowCount > 0) {
                Flasher::setFlash('Berhasil!', "{$rowCount} data siswa berhasil dihapus.", 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Tidak ada data siswa yang dihapus.', 'danger');
            }
        } else {
            Flasher::setFlash('Gagal!', 'Tidak ada data yang dipilih untuk dihapus.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/pengguna/siswa');
        exit;
    }

    public function importSiswa()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_import_siswa'])) {
            $file = $_FILES['file_import_siswa'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                Flasher::setFlash('Gagal!', 'Terjadi error saat mengunggah file.', 'danger');
            } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) != 'csv') {
                Flasher::setFlash('Gagal!', 'Hanya file format .csv yang didukung.', 'danger');
            } else {
                $fileHandle = fopen($file['tmp_name'], 'r');
                fgetcsv($fileHandle);
                $dataUntukImport = [];
                while (($row = fgetcsv($fileHandle)) !== FALSE) {
                    if (!empty($row[0]) && !empty($row[1])) {
                        $dataUntukImport[] = [
                            'nama'          => trim($row[0]),
                            'id_siswa'      => trim($row[1]),
                            'jenis_kelamin' => trim($row[2] ?? 'Laki-laki'),
                            'no_hp'         => trim($row[3] ?? null),
                            'email'         => trim($row[4] ?? null),
                            'ttl'           => null,
                            'agama'         => null,
                            'alamat'        => null,
                            'foto'          => 'default.png'
                        ];
                    }
                }
                fclose($fileHandle);
                if (!empty($dataUntukImport)) {
                    $siswaModel = new Siswa_model();
                    $hasil = $siswaModel->importSiswaBatch($dataUntukImport);
                    if ($hasil['failed'] > 0) {
                        $pesan = "{$hasil['success']} data berhasil diimpor, {$hasil['failed']} data gagal. <br> Detail Error: <br>" . implode('<br>', $hasil['errors']);
                        Flasher::setFlash('Proses Selesai dengan Error', $pesan, 'danger');
                    } else {
                        Flasher::setFlash('Berhasil!', "{$hasil['success']} data siswa berhasil diimpor.", 'success');
                    }
                } else {
                    Flasher::setFlash('Gagal!', 'File CSV kosong atau formatnya salah.', 'danger');
                }
            }
        } else {
            Flasher::setFlash('Gagal!', 'Tidak ada file yang diunggah.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/pengguna/siswa');
        exit;
    }

    public function ubahPasswordAkun()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['new_password'])) {
            $id = $_POST['id'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            if ($newPassword !== $confirmPassword) {
                Flasher::setFlash('Gagal!', 'Konfirmasi kata sandi tidak cocok.', 'danger');
            } elseif (strlen($newPassword) < 6) {
                Flasher::setFlash('Gagal!', 'Kata sandi baru minimal harus 6 karakter.', 'danger');
            } else {
                $userModel = new User_model();
                if ($userModel->changePassword($id, $newPassword) > 0) {
                    Flasher::setFlash('Berhasil!', 'Kata sandi berhasil diubah.', 'success');
                } else {
                    Flasher::setFlash('Gagal!', 'Gagal mengubah kata sandi.', 'danger');
                }
            }
        } else {
            Flasher::setFlash('Gagal!', 'Permintaan tidak valid.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/pengguna/akun');
        exit;
    }

    public function ubahKelas()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $kelasModel = new Kelas_model();
            if ($kelasModel->updateKelas($_POST) > 0) {
                Flasher::setFlash('Berhasil!', 'Data kelas berhasil diubah.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Gagal mengubah data kelas.', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/admin/kelas');
        exit;
    }

    public function getKelasById($id)
    {
        header('Content-Type: application/json');
        $kelasModel = new Kelas_model();
        $kelas = $kelasModel->getKelasById($id);
        echo json_encode($kelas);
        exit;
    }

    public function hapusKelasMassal()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['ids'])) {
            $ids = $_POST['ids'];
            $kelasModel = new Kelas_model();
            $rowCount = $kelasModel->hapusKelasMassal($ids);
            if ($rowCount > 0) {
                Flasher::setFlash('Berhasil!', "{$rowCount} data kelas berhasil dihapus.", 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Tidak ada data kelas yang dihapus.', 'danger');
            }
        } else {
            Flasher::setFlash('Gagal!', 'Tidak ada data yang dipilih untuk dihapus.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/kelas');
        exit;
    }

    public function detailKelas($kelasId, $halaman = 1)
    {
        $kelasModel = new Kelas_model();
        $siswaModel = new Siswa_model();
        $kelas = $kelasModel->getKelasById($kelasId);
        $limit = 10;
        $halaman = max(1, (int) $halaman);
        $offset = ($halaman - 1) * $limit;
        $siswa = [];
        $totalSiswa = 0;
        $totalHalaman = 1;
        $searchTerm = null;
        if ($kelas) {
            $searchTerm = $_GET['search'] ?? null;
            $siswa = $siswaModel->getSiswaByKelasIdPaginated($kelas['id'], $offset, $limit, $searchTerm);
            $totalSiswa = $siswaModel->countSiswaByKelasId($kelas['id'], $searchTerm);
            $totalHalaman = ceil($totalSiswa / $limit);
        }
        $data = [
            'title' => 'Detail Kelas',
            'kelas' => $kelas,
            'siswa' => $siswa,
            'total_halaman' => $totalHalaman,
            'halaman_aktif' => (int) $halaman,
            'search_term' => $searchTerm,
            'unassigned_siswa' => $siswaModel->getUnassignedSiswa()
        ];
        $this->view('admin/kelas/detail', $data);
    }

    public function searchUnassignedSiswa()
    {
        header('Content-Type: application/json');
        $keyword = $_GET['keyword'] ?? null;
        $siswaModel = new Siswa_model();
        $siswaData = $siswaModel->getUnassignedSiswaByKeyword($keyword);
        echo json_encode($siswaData);
        exit();
    }

    public function assignSiswaToKelas()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['siswa_id']) && isset($_POST['kelas_id'])) {
            $siswaModel = new Siswa_model();
            $siswaId = $_POST['siswa_id'];
            $kelasId = $_POST['kelas_id'];
            $rowCount = $siswaModel->assignSiswaToKelas($siswaId, $kelasId);
            if ($rowCount > 0) {
                Flasher::setFlash('Berhasil!', 'Siswa berhasil ditambahkan ke kelas.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Tidak ada siswa yang ditambahkan ke kelas. Mungkin siswa sudah memiliki kelas.', 'danger');
            }
        } else {
            Flasher::setFlash('Gagal!', 'Permintaan tidak valid.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/detailKelas/' . $_POST['kelas_id']);
        exit;
    }

    public function importSiswaKeKelas()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_import_siswa']) && isset($_POST['kelas_id'])) {
            $file = $_FILES['file_import_siswa'];
            $kelasId = $_POST['kelas_id'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                Flasher::setFlash('Gagal!', 'Terjadi error saat mengunggah file.', 'danger');
            } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) != 'csv') {
                Flasher::setFlash('Gagal!', 'Hanya file format .csv yang didukung.', 'danger');
            } else {
                $fileHandle = fopen($file['tmp_name'], 'r');
                fgetcsv($fileHandle);
                $nises_to_find = [];
                $line = 2;
                while (($row = fgetcsv($fileHandle)) !== FALSE) {
                    $nis = trim($row[0]);
                    if (!empty($nis)) {
                        $nises_to_find[] = $nis;
                    }
                    $line++;
                }
                fclose($fileHandle);
                if (!empty($nises_to_find)) {
                    $siswaModel = new Siswa_model();
                    $siswa_valid = $siswaModel->getSiswaByNisInBatch($nises_to_find);
                    $found_nises = array_column($siswa_valid, 'id_siswa');
                    $siswa_to_assign = array_column($siswa_valid, 'id');
                    $rowCount = $siswaModel->assignSiswaBatchToKelas($siswa_to_assign, $kelasId);
                    $not_found_nises = array_diff($nises_to_find, $found_nises);
                    $not_found_message = '';
                    if (!empty($not_found_nises)) {
                        $not_found_message = 'Berikut adalah ID Siswa (NIS) yang tidak ditemukan: ' . implode(', ', $not_found_nises);
                    }
                    if ($rowCount > 0) {
                        $message = "{$rowCount} siswa berhasil ditambahkan ke kelas.";
                        if (!empty($not_found_message)) {
                            $message .= "<br>" . $not_found_message;
                        }
                        Flasher::setFlash('Berhasil!', $message, 'success');
                    } else if (!empty($not_found_message)) {
                        Flasher::setFlash('Proses Gagal!', "Tidak ada siswa yang berhasil ditambahkan. <br>" . $not_found_message, 'danger');
                    } else {
                        Flasher::setFlash('Gagal!', 'File CSV kosong atau tidak memiliki data yang valid.', 'danger');
                    }
                } else {
                    Flasher::setFlash('Gagal!', 'File CSV kosong atau tidak memiliki data yang valid.', 'danger');
                }
            }
        } else {
            Flasher::setFlash('Gagal!', 'Permintaan tidak valid.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/detailKelas/' . $_POST['kelas_id']);
        exit;
    }

    public function hapusSiswaDariKelas($siswaId)
    {
        $siswaModel = new Siswa_model();
        $siswa = $siswaModel->getSiswaById($siswaId);
        if (!$siswa) {
            Flasher::setFlash('Gagal!', 'Siswa tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/kelas');
            exit;
        }
        $kelasId = $siswa['kelas_id'];
        if ($siswaModel->removeSiswaFromKelas($siswaId) > 0) {
            Flasher::setFlash('Berhasil!', 'Siswa berhasil dikeluarkan dari kelas.', 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Gagal mengeluarkan siswa dari kelas.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/detailKelas/' . $kelasId);
        exit;
    }

    public function removeSiswaDariKelasMassal()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['ids']) && !empty($_POST['kelas_id'])) {
            $ids = $_POST['ids'];
            $kelasId = $_POST['kelas_id'];
            $siswaModel = new Siswa_model();
            $rowCount = $siswaModel->removeSiswaFromKelasMassal($ids);
            if ($rowCount > 0) {
                Flasher::setFlash('Berhasil!', "{$rowCount} siswa berhasil dikeluarkan dari kelas.", 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Tidak ada siswa yang dikeluarkan dari kelas.', 'danger');
            }
            header('Location: ' . BASEURL . '/admin/detailKelas/' . $kelasId);
            exit;
        } else {
            Flasher::setFlash('Gagal!', 'Permintaan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/kelas');
            exit;
        }
    }

    public function getSiswaById($id)
    {
        header('Content-Type: application/json');
        ob_clean();
        $siswaModel = new Siswa_model();
        $siswa = $siswaModel->getSiswaById($id);
        echo json_encode($siswa);
        exit();
    }

    public function changePassword()
{
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
    header('Location: ' . BASEURL . '/admin/profile');
    exit;
}
public function tambahBarang()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $barangModel = new Barang_model();
        $data = $_POST;

        // Logika untuk menentukan status berdasarkan jumlah
        $jumlah = (int) $data['jumlah'];
        if ($jumlah <= 0) {
            $status = 'Tidak Tersedia';
        } elseif ($jumlah >= 1 && $jumlah <= 3) {
            $status = 'Terbatas';
        } else {
            $status = 'Tersedia';
        }
        $data['status'] = $status;

        // Logika untuk upload gambar (jika ada)
        $namaGambar = 'images.png'; // default
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['gambar'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $namaGambarBaru = uniqid() . '.' . $ext;
            $targetDir = APP_ROOT . '/public/img/barang/';
            if (move_uploaded_file($file['tmp_name'], $targetDir . $namaGambarBaru)) {
                $namaGambar = $namaGambarBaru;
            }
        }
        $data['gambar'] = $namaGambar;

        if ($barangModel->tambahBarang($data) > 0) {
            Flasher::setFlash('Berhasil!', 'Data barang berhasil ditambahkan.', 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Gagal menambahkan barang.', 'danger');
        }
    }
    header('Location: ' . BASEURL . '/admin/barang');
    exit;
}
 public function hapusBarangMassal()
    {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['ids'])) {
            $ids = $_POST['ids'];
            $barangModel = new Barang_model();
            $rowCount = $barangModel->hapusBarangMassal($ids);

            if ($rowCount > 0) {
                Flasher::setFlash('Berhasil!', "{$rowCount} data barang berhasil dihapus.", 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Tidak ada data barang yang dihapus.', 'danger');
            }
        } else {
            Flasher::setFlash('Gagal!', 'Tidak ada data yang dipilih untuk dihapus.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/barang');
        exit;
    }
}