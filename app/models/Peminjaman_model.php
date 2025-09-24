<?php

class Peminjaman_model {
    private $table = 'peminjaman';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Mengambil satu data peminjaman berdasarkan ID-nya.
     */
    public function getPeminjamanById($id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }


    /**
     * Memperbarui status peminjaman (misal: 'Disetujui', 'Ditolak', 'Dikembalikan').
     */
    public function updateStatusPeminjaman($id, $status) {
        $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->bind('status', $status);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * =====================================================================
     * FUNGSI createPeminjamanBatch SETELAH DIPERBAIKI
     * =====================================================================
     * Logika pengurangan stok DIHAPUS dari fungsi ini.
     */
    public function createPeminjamanBatch($dataPeminjaman) {
        if (empty($dataPeminjaman)) {
            return 0;
        }
        $this->db->beginTransaction();
        try {
            $peminjamanQuery = "INSERT INTO " . $this->table . " (user_id, barang_id, jumlah_pinjam, tanggal_pinjam, tanggal_wajib_kembali, status, keperluan, verifikator_id) VALUES (:user_id, :barang_id, :jumlah_pinjam, :tanggal_pinjam, :tanggal_wajib_kembali, :status, :keperluan, :verifikator_id)";
            foreach ($dataPeminjaman as $peminjaman) {
                $this->db->query($peminjamanQuery);
                $this->db->bind('user_id', $peminjaman['user_id']);
                $this->db->bind('barang_id', $peminjaman['barang_id']);
                $this->db->bind('jumlah_pinjam', $peminjaman['jumlah_pinjam']);
                $this->db->bind('tanggal_pinjam', $peminjaman['tanggal_pinjam']);
                $this->db->bind('tanggal_wajib_kembali', $peminjaman['tanggal_kembali_diajukan']);
                $this->db->bind('status', 'Menunggu Verifikasi');
                $this->db->bind('keperluan', $peminjaman['keperluan']);
                $this->db->bind('verifikator_id', $peminjaman['verifikator_id']);
                $this->db->execute();
            }
            $this->db->commit();
            return count($dataPeminjaman);
        } catch (Exception $e) {
            $this->db->rollBack();
            return 0;
        }
    }

    /**
     * Mengambil data peminjaman yang perlu diverifikasi oleh guru spesifik.
     */
   public function getPeminjamanForVerification($verifikator_id, $offset, $limit, $filters = []) {
    $query = "SELECT p.*, s.nama as nama_siswa, s.id_siswa, b.nama_barang, b.kode_barang, b.jumlah as stok_barang 
              FROM " . $this->table . " p 
              JOIN siswa s ON p.user_id = s.user_id 
              JOIN barang b ON p.barang_id = b.id
              WHERE p.verifikator_id = :verifikator_id AND p.status = 'Menunggu Verifikasi'";
    if (!empty($filters['keyword'])) {
        $query .= " AND (s.nama LIKE :keyword OR s.id_siswa LIKE :keyword OR b.nama_barang LIKE :keyword)";
    }
    $query .= " ORDER BY p.tanggal_pinjam ASC LIMIT :offset, :limit";
    $this->db->query($query);
    $this->db->bind('verifikator_id', $verifikator_id);
    if (!empty($filters['keyword'])) {
        $this->db->bind(':keyword', "%" . $filters['keyword'] . "%");
    }
    $this->db->bind('offset', (int)$offset, PDO::PARAM_INT);
    $this->db->bind('limit', (int)$limit, PDO::PARAM_INT);
    return $this->db->resultSet();
}

    
    /**
     * Mengambil riwayat peminjaman untuk satu siswa (dengan paginasi).
     */
   public function getHistoryByUserId($userId, $offset, $limit, $filters = []) {
    $query = "SELECT p.*, b.nama_barang, b.kode_barang
          FROM {$this->table} p
          JOIN barang b ON p.barang_id = b.id
          WHERE p.user_id = :user_id";

    if (!empty($filters['keyword'])) {
        $query .= " AND (b.nama_barang LIKE :keyword OR b.kode_barang LIKE :keyword OR p.keperluan LIKE :keyword)";
    }
    if (!empty($filters['status'])) {
        $query .= " AND p.status = :status_filter";
    }

    // BARU: Logika pengurutan berdasarkan filter waktu
    if (isset($filters['waktu']) && $filters['waktu'] == 'terlama') {
        $query .= " ORDER BY p.tanggal_pinjam ASC";
    } else {
        $query .= " ORDER BY p.tanggal_pinjam DESC";
    }

    $query .= " LIMIT :offset, :limit";

    $this->db->query($query);
    $this->db->bind('user_id', $userId);
    $this->db->bind('offset', (int)$offset, PDO::PARAM_INT);
    $this->db->bind('limit', (int)$limit, PDO::PARAM_INT);

    if (!empty($filters['keyword'])) {
        $this->db->bind(':keyword', "%" . $filters['keyword'] . "%");
    }
    if (!empty($filters['status'])) {
        $this->db->bind(':status_filter', $filters['status']);
    }

    return $this->db->resultSet();
}
    
    /**
     * Menghitung total riwayat peminjaman untuk satu siswa.
     */
    public function countHistoryByUserId($userId, $filters = []) {
    $query = "SELECT COUNT(p.id) as total
              FROM {$this->table} p
              JOIN barang b ON p.barang_id = b.id
              WHERE p.user_id = :user_id";

    // BARU: Logika filter status
    if (!empty($filters['keyword'])) {
        $query .= " AND (b.nama_barang LIKE :keyword OR b.kode_barang LIKE :keyword OR p.keperluan LIKE :keyword)";
    }
    if (!empty($filters['status'])) {
        $query .= " AND p.status = :status_filter";
    }

    $this->db->query($query);
    $this->db->bind('user_id', $userId);

    if (!empty($filters['keyword'])) {
        $this->db->bind(':keyword', "%" . $filters['keyword'] . "%");
    }
    // BARU: Binding parameter filter status
    if (!empty($filters['status'])) {
        $this->db->bind(':status_filter', $filters['status']);
    }

    $result = $this->db->single();
    return $result['total'] ?? 0;
}

    /**
     * =====================================================================
     * FUNGSI getHistoryPaginated dan countAllHistory SETELAH DIPERBAIKI
     * =====================================================================
     */
    public function getHistoryPaginated($offset, $limit, $filters = []) {
        $sql = "SELECT 
                    p.id, p.keterangan, u.username as nama_peminjam,
                    CASE
                        WHEN u.role = 'siswa' THEN s.id_siswa
                        WHEN u.role = 'guru' THEN g.nip
                        ELSE '-'
                    END AS no_id_peminjam,
                    b.nama_barang, p.tanggal_pinjam, p.tanggal_kembali, p.status, 
                    g_verif.nama as nama_verifikator, g_verif.nip as nip_verifikator
                FROM {$this->table} p
                JOIN users u ON p.user_id = u.id
                JOIN barang b ON p.barang_id = b.id
                LEFT JOIN guru g ON u.id = g.user_id AND u.role = 'guru'
                LEFT JOIN siswa s ON u.id = s.user_id AND u.role = 'siswa'
                LEFT JOIN guru g_verif ON p.verifikator_id = g_verif.id
                WHERE 1=1";

        if (!empty($filters['keyword'])) {
            $sql .= " AND (u.username LIKE :keyword OR b.nama_barang LIKE :keyword OR g_verif.nama LIKE :keyword)";
        }
        
        $sql .= " ORDER BY p.tanggal_pinjam DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($sql);

        if (!empty($filters['keyword'])) {
            $this->db->bind(':keyword', '%' . $filters['keyword'] . '%');
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }


    
    /**
     * Menghitung semua riwayat peminjaman (untuk Laporan Admin).
     */
    public function countAllHistory($filters = []) {
        $sql = "SELECT COUNT(p.id) as total
                FROM {$this->table} p
                JOIN users u ON p.user_id = u.id
                JOIN barang b ON p.barang_id = b.id
                LEFT JOIN guru g_verif ON p.verifikator_id = g_verif.id
                WHERE 1=1";

        if (!empty($filters['keyword'])) {
            $sql .= " AND (u.username LIKE :keyword OR b.nama_barang LIKE :keyword OR g_verif.nama LIKE :keyword)";
        }

        $this->db->query($sql);
        
        if (!empty($filters['keyword'])) {
            $this->db->bind(':keyword', '%' . $filters['keyword'] . '%');
        }

        $result = $this->db->single();
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Mengambil semua riwayat untuk diekspor ke CSV.
     */
    

    public function countAllVerificationRequests($verifikator_id, $filters = []) {
    $query = "SELECT COUNT(p.id) as total 
              FROM " . $this->table . " p 
              JOIN siswa s ON p.user_id = s.user_id 
              JOIN barang b ON p.barang_id = b.id
              WHERE p.verifikator_id = :verifikator_id AND p.status = 'Menunggu Verifikasi'";
    if (!empty($filters['keyword'])) {
        $query .= " AND (s.nama LIKE :keyword OR s.id_siswa LIKE :keyword OR b.nama_barang LIKE :keyword)";
    }
    $this->db->query($query);
    $this->db->bind('verifikator_id', $verifikator_id);
    if (!empty($filters['keyword'])) {
        $this->db->bind(':keyword', "%" . $filters['keyword'] . "%");
    }
    $result = $this->db->single();
    return $result ? (int)$result['total'] : 0;
}
/**
     * Memperbarui status dan keterangan peminjaman.
     */
   public function updatePeminjamanStatusAndKeterangan($id, $status, $keterangan = null) {
        $query = "UPDATE {$this->table} SET status = :status, keterangan = :keterangan WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->bind('status', $status);
        $this->db->bind('keterangan', $keterangan);
        $this->db->execute();
        return $this->db->rowCount();
    }

// ==========================================================
    // FUNGSI BARU UNTUK UPDATE PENGEMBALIAN
    // ==========================================================
    public function updatePengembalian($data) {
        $query = "UPDATE {$this->table} SET 
                    tanggal_kembali = :tanggal_kembali,
                    status_pengembalian = :status_pengembalian,
                    status = :status,
                    bukti_kembali = :bukti_kembali
                  WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('id', $data['id']);
        $this->db->bind('tanggal_kembali', $data['tanggal_kembali']);
        $this->db->bind('status_pengembalian', $data['status_pengembalian']);
        $this->db->bind('status', $data['status']);
        $this->db->bind('bukti_kembali', $data['bukti_kembali']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // ==========================================================
    // FUNGSI BARU UNTUK MENGAMBIL DATA PEMINJAMAN SECARA AMAN
    // ==========================================================
    public function getPeminjamanByIdAndUserId($id, $userId) {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id AND user_id = :user_id");
        $this->db->bind('id', $id, PDO::PARAM_INT);
        $this->db->bind('user_id', $userId, PDO::PARAM_INT);
        return $this->db->single();
    }
    /* ==========================================================
     * FUNGSI-FUNGSI BARU UNTUK DASHBOARD SISWA
     * ==========================================================
     */

    /**
     * Menghitung jumlah barang yang sedang aktif dipinjam oleh siswa (status 'Disetujui').
     */
    public function countAktifByUserId($userId) {
        $this->db->query("SELECT COUNT(id) as total FROM {$this->table} WHERE user_id = :user_id AND status = 'Disetujui'");
        $this->db->bind('user_id', $userId);
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }
    /**
     * Menghitung jumlah barang yang sedang menunggu persetujuan guru.
     */
    public function countPendingByUserId($userId) {
        $this->db->query("SELECT COUNT(id) as total FROM {$this->table} WHERE user_id = :user_id AND status = 'Menunggu Verifikasi'");
        $this->db->bind('user_id', $userId);
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }
    
    /**
     * Menghitung total riwayat peminjaman yang sudah selesai.
     */
    public function countSelesaiByUserId($userId) {
        $this->db->query("SELECT COUNT(id) as total FROM {$this->table} WHERE user_id = :user_id AND status = 'Selesai'");
        $this->db->bind('user_id', $userId);
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    /**
     * Mengambil daftar barang yang sedang aktif dipinjam (limit 5 untuk dashboard).
     */
    public function getAktifPeminjamanByUserId($userId, $limit = 5) {
        $query = "SELECT p.*, b.nama_barang, b.kode_barang
                  FROM {$this->table} p 
                  JOIN barang b ON p.barang_id = b.id 
                  WHERE p.user_id = :user_id AND p.status = 'Disetujui'
                  ORDER BY p.tanggal_wajib_kembali ASC 
                  LIMIT :limit";
        $this->db->query($query);
        $this->db->bind('user_id', $userId);
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
    /* ==========================================================
     * FUNGSI-FUNGSI BARU UNTUK RIWAYAT DI SISI GURU
     * ==========================================================
     */

    /**
     * Mengambil riwayat peminjaman dari semua siswa yang diampu oleh seorang wali kelas.
     */
     public function getHistoryForWali($waliKelasId, $offset, $limit, $filters = []) {
        $sql = "SELECT p.*, b.nama_barang, b.kode_barang, s.nama as nama_siswa, s.id_siswa, k.nama_kelas
                FROM {$this->table} p
                JOIN barang b ON p.barang_id = b.id
                JOIN siswa s ON p.user_id = s.user_id
                JOIN kelas k ON s.kelas_id = k.id
                WHERE k.wali_kelas_id = :wali_kelas_id";

        if (!empty($filters['keyword'])) {
            $sql .= " AND (s.nama LIKE :keyword OR s.id_siswa LIKE :keyword OR b.nama_barang LIKE :keyword OR k.nama_kelas LIKE :keyword)";
        }
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
        }
        
        // LOGIKA BARU: Mengatur urutan berdasarkan filter waktu
        if (isset($filters['waktu']) && $filters['waktu'] == 'terlama') {
            $sql .= " ORDER BY p.tanggal_pinjam ASC";
        } else {
            $sql .= " ORDER BY p.tanggal_pinjam DESC";
        }

        $sql .= " LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        $this->db->bind('wali_kelas_id', $waliKelasId);
        if (!empty($filters['keyword'])) {
            $this->db->bind('keyword', '%' . $filters['keyword'] . '%');
        }
        if (!empty($filters['status'])) {
            $this->db->bind('status', $filters['status']);
        }
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }
    /**
     * Menghitung total riwayat peminjaman dari semua siswa yang diampu oleh seorang wali kelas.
     */
    public function countHistoryForWali($waliKelasId, $filters = []) {
    $sql = "SELECT COUNT(p.id) as total
            FROM {$this->table} p
            JOIN barang b ON p.barang_id = b.id
            JOIN siswa s ON p.user_id = s.user_id
            JOIN kelas k ON s.kelas_id = k.id
            WHERE k.wali_kelas_id = :wali_kelas_id";
    
    if (!empty($filters['keyword'])) {
        $sql .= " AND (s.nama LIKE :keyword OR s.id_siswa LIKE :keyword OR b.nama_barang LIKE :keyword OR k.nama_kelas LIKE :keyword)";
    }
    // BARIS BARU: Menambahkan filter berdasarkan status
    if (!empty($filters['status'])) {
        $sql .= " AND p.status = :status";
    }

    $this->db->query($sql);
    $this->db->bind('wali_kelas_id', $waliKelasId);
    if (!empty($filters['keyword'])) {
        $this->db->bind('keyword', '%' . $filters['keyword'] . '%');
    }
    // BARIS BARU: Binding parameter status
    if (!empty($filters['status'])) {
        $this->db->bind('status', $filters['status']);
    }

    $result = $this->db->single();
    return $result['total'] ?? 0;
}
    /* ==========================================================
     * FUNGSI-FUNGSI BARU UNTUK DASHBOARD ADMIN
     * ==========================================================
     */
     
    public function countBarangDipinjam() {
        $this->db->query("SELECT COUNT(id) as total FROM {$this->table} WHERE status = 'Disetujui'");
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    public function getLatestPeminjaman($limit = 5) {
        $this->db->query("SELECT p.*, u.username as nama_peminjam, b.nama_barang, b.kode_barang,
                                 CASE
                                     WHEN u.role = 'siswa' THEN s.id_siswa
                                     WHEN u.role = 'guru' THEN g.nip
                                     ELSE '-'
                                 END AS no_id_peminjam
                         FROM {$this->table} p
                         JOIN users u ON p.user_id = u.id
                         JOIN barang b ON p.barang_id = b.id
                         LEFT JOIN siswa s ON u.id = s.user_id AND u.role = 'siswa'
                         LEFT JOIN guru g ON u.id = g.user_id AND u.role = 'guru'
                         ORDER BY p.tanggal_pinjam DESC, p.id DESC
                         LIMIT :limit");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
     public function getAllHistoryForExport($filters = []) {
        $sql = "SELECT 
                    p.id, 
                    u.username as nama_peminjam, 
                    CASE
                        WHEN u.role = 'admin' THEN a.id_staff
                        WHEN u.role = 'guru' THEN g.nip
                        WHEN u.role = 'siswa' THEN s.id_siswa
                    END AS no_id_peminjam,
                    b.nama_barang, 
                    p.tanggal_pinjam, 
                    p.tanggal_kembali, 
                    p.status
                FROM {$this->table} p
                JOIN users u ON p.user_id = u.id
                JOIN barang b ON p.barang_id = b.id
                LEFT JOIN staff a ON u.id = a.user_id
                LEFT JOIN guru g ON u.id = g.user_id
                LEFT JOIN siswa s ON u.id = s.user_id
                WHERE 1=1";
                
        if (!empty($filters['keyword'])) {
            $sql .= " AND (u.username LIKE :keyword OR 
                            (CASE
                                WHEN u.role = 'admin' THEN a.id_staff
                                WHEN u.role = 'guru' THEN g.nip
                                WHEN u.role = 'siswa' THEN s.id_siswa
                            END) LIKE :keyword 
                            OR b.nama_barang LIKE :keyword)";
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $sql .= " AND p.tanggal_pinjam BETWEEN :start_date AND :end_date";
        }

        $sql .= " ORDER BY p.tanggal_pinjam DESC";
        
        $this->db->query($sql);

        if (!empty($filters['keyword'])) {
            $this->db->bind(':keyword', '%' . $filters['keyword'] . '%');
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $this->db->bind(':start_date', $filters['start_date']);
            $this->db->bind(':end_date', $filters['end_date']);
        }

        return $this->db->resultSet();
    }
    
}