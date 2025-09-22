<?php

class Barang_model {
    private $table = 'barang';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

   
   /**
     * =====================================================================
     * FUNGSI getBarangPaginated SETELAH DIPERBAIKI
     * =====================================================================
     * Logika filter diubah untuk menangani 'Semua Ketersediaan' dengan benar
     * dan dibuat lebih efisien dengan filter langsung pada kolom 'jumlah'.
     */
    public function getBarangPaginated($offset, $limit, $filters = []) {
        $query = "SELECT *, 
                    CASE
                        WHEN jumlah >= 4 THEN 'Tersedia'
                        WHEN jumlah >= 1 AND jumlah <= 3 THEN 'Terbatas'
                        ELSE 'Tidak Tersedia'
                    END AS status_barang
                  FROM {$this->table} WHERE 1=1";

        if (!empty($filters['keyword'])) {
            $query .= " AND (nama_barang LIKE :keyword OR kode_barang LIKE :keyword)";
        }

        // Logika filter status ketersediaan yang baru dan sudah benar
        if (isset($filters['status']) && $filters['status'] !== '') {
            if ($filters['status'] == 'Tersedia') {
                $query .= " AND jumlah >= 4";
            } elseif ($filters['status'] == 'Terbatas') {
                $query .= " AND jumlah BETWEEN 1 AND 3";
            } elseif ($filters['status'] == 'Tidak Tersedia') {
                $query .= " AND jumlah <= 0";
            }
        }

        $query .= " ORDER BY nama_barang ASC LIMIT :offset, :limit";

        $this->db->query($query);

        if (!empty($filters['keyword'])) {
            $this->db->bind(':keyword', "%" . $filters['keyword'] . "%");
        }

        // PERBAIKAN UTAMA: Pastikan LIMIT dan OFFSET diikat sebagai Integer
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }
    public function importBarangBatch($dataBarang) {
    if (empty($dataBarang)) {
        return ['success' => 0, 'failed' => 0, 'errors' => ['Tidak ada data untuk diimpor.']];
    }

    $berhasil = 0;
    $gagal = 0;
    $errors = [];

    $this->db->beginTransaction();

    $query = "INSERT INTO {$this->table} 
              (kode_barang, nama_barang, jumlah, kondisi, status, lokasi_penyimpanan, tanggal_pembelian, gambar) 
              VALUES (:kode_barang, :nama_barang, :jumlah, :kondisi, :status, :lokasi_penyimpanan, :tanggal_pembelian, :gambar)";

    foreach ($dataBarang as $index => $barang) {
        try {
            $this->db->query($query);
            $this->db->bind(':kode_barang', $barang['kode_barang']);
            $this->db->bind(':nama_barang', $barang['nama_barang']);
            $this->db->bind(':jumlah', $barang['jumlah']);
            $this->db->bind(':kondisi', $barang['kondisi']);
            $this->db->bind(':status', $barang['status']);
            $this->db->bind(':lokasi_penyimpanan', $barang['lokasi_penyimpanan']);
            $this->db->bind(':tanggal_pembelian', $barang['tanggal_pembelian']);
            $this->db->bind(':gambar', $barang['gambar']);
            $this->db->execute();

            if ($this->db->rowCount() > 0) {
                $berhasil++;
            } else {
                $gagal++;
            }
        } catch (PDOException $e) {
            $gagal++;
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'Duplicate entry') !== false) {
                $errors[] = "Baris " . ($index + 2) . ": Gagal. Kode Barang '{$barang['kode_barang']}' sudah ada.";
            } else {
                $errors[] = "Baris " . ($index + 2) . ": Gagal. Error: " . substr($errorMessage, 0, 100) . "...";
            }
        }
    }

    if ($gagal > 0) {
        $this->db->rollBack();
    } else {
        $this->db->commit();
    }

    return ['success' => $berhasil, 'failed' => $gagal, 'errors' => $errors];
}
    public function getBarangById($id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }
    public function tambahBarang($data) {
        $query = "INSERT INTO {$this->table} 
                  (kode_barang, nama_barang, jumlah, kondisi, status, lokasi_penyimpanan, tanggal_pembelian, gambar) 
                  VALUES (:kode_barang, :nama_barang, :jumlah, :kondisi, :status, :lokasi_penyimpanan, :tanggal_pembelian, :gambar)";
        $this->db->query($query);
        $this->db->bind(':kode_barang', $data['kode_barang']);
        $this->db->bind(':nama_barang', $data['nama_barang']);
        $this->db->bind(':jumlah', $data['jumlah']);
        $this->db->bind(':kondisi', $data['kondisi']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':lokasi_penyimpanan', $data['lokasi_penyimpanan']);
        $this->db->bind(':tanggal_pembelian', $data['tanggal_pembelian']);
        $this->db->bind(':gambar', $data['gambar']);
        $this->db->execute();
        return $this->db->rowCount();
    }
    public function getBarangByKode($kode) {
        $this->db->query("SELECT * FROM {$this->table} WHERE kode_barang = :kode_barang");
        $this->db->bind(':kode_barang', $kode);
        return $this->db->single();
    }
    /**
     * =====================================================================
     * FUNGSI countAllBarang SETELAH DIPERBAIKI
     * =====================================================================
     * Logika filter disamakan dengan getBarangPaginated agar pagination konsisten.
     */
    public function countAllBarang($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM barang WHERE 1=1";
        
        if (!empty($filters['keyword'])) {
            $sql .= " AND (nama_barang LIKE :keyword OR kode_barang LIKE :keyword)";
        }

        // Logika filter status ketersediaan yang baru dan sudah benar
        if (isset($filters['status']) && $filters['status'] !== '') {
            if ($filters['status'] == 'Tersedia') {
                $sql .= " AND jumlah >= 4";
            } elseif ($filters['status'] == 'Terbatas') {
                $sql .= " AND jumlah BETWEEN 1 AND 3";
            } elseif ($filters['status'] == 'Tidak Tersedia') {
                $sql .= " AND jumlah <= 0";
            }
        }

        $this->db->query($sql);
        
        if (!empty($filters['keyword'])) {
            $this->db->bind(':keyword', "%" . $filters['keyword'] . "%");
        }
        
        $row = $this->db->single();
        return $row['total'] ?? 0;
    }
    // File: ManajemenLabPraktikum/app/models/Barang_model.php

  public function getBarangByIds($ids) {
        if (empty($ids)) {
            return [];
        }

        // PERBAIKAN FINAL:
        // 1. Pastikan array di-indeks ulang untuk keamanan ekstra.
        $ids = array_values($ids);

        // 2. Siapkan placeholder (?,?,?) seperti biasa.
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $query = "SELECT *, 
                    CASE
                        WHEN jumlah >= 4 THEN 'Tersedia'
                        WHEN jumlah >= 1 AND jumlah <= 3 THEN 'Terbatas'
                        ELSE 'Tidak Tersedia'
                    END AS status
                  FROM {$this->table} WHERE id IN ({$placeholders}) ORDER BY nama_barang ASC";
                  
        $this->db->query($query);
        
        // 3. Gunakan counter manual ($i) untuk binding. Ini adalah kunci perbaikannya.
        // Metode ini menjamin parameter akan selalu di-bind ke posisi 1, 2, 3, dst.,
        // tidak peduli seperti apa struktur key dari array $ids.
        $i = 1;
        foreach ($ids as $id) {
            $this->db->bind($i, $id, PDO::PARAM_INT);
            $i++;
        }
        
        return $this->db->resultSet();
    }
    public function updateBarang($data) {
        $jumlah = (int)$data['jumlah'];
        if ($jumlah <= 0) {
            $data['status'] = 'Tidak Tersedia';
        } elseif ($jumlah >= 1 && $jumlah <= 3) {
            $data['status'] = 'Terbatas';
        } else {
            $data['status'] = 'Tersedia';
        }
        $query = "UPDATE {$this->table} 
                  SET kode_barang = :kode_barang,
                      nama_barang = :nama_barang,
                      jumlah = :jumlah,
                      kondisi = :kondisi,
                      status = :status,
                      lokasi_penyimpanan = :lokasi_penyimpanan,
                      tanggal_pembelian = :tanggal_pembelian,
                      gambar = :gambar
                  WHERE id = :id";
        $this->db->query($query);
        $this->db->bind(':kode_barang', $data['kode_barang']);
        $this->db->bind(':nama_barang', $data['nama_barang']);
        $this->db->bind(':jumlah', $data['jumlah']);
        $this->db->bind(':kondisi', $data['kondisi']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':lokasi_penyimpanan', $data['lokasi_penyimpanan']);
        $this->db->bind(':tanggal_pembelian', $data['tanggal_pembelian']);
        $this->db->bind(':gambar', $data['gambar']);
        $this->db->bind(':id', $data['id']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function kurangiStok($id, $jumlah) {
        $this->db->beginTransaction();
        try {
            $query = "UPDATE {$this->table} SET jumlah = jumlah - :jumlah WHERE id = :id";
            $this->db->query($query);
            $this->db->bind(':jumlah', $jumlah);
            $this->db->bind(':id', $id);
            $this->db->execute();
            $barang = $this->getBarangById($id);
            if ($barang) {
                $jumlah_baru = $barang['jumlah'];
                $status_baru = '';
                if ($jumlah_baru <= 0) {
                    $status_baru = 'Tidak Tersedia';
                } elseif ($jumlah_baru >= 1 && $jumlah_baru <= 3) {
                    $status_baru = 'Terbatas';
                } else {
                    $status_baru = 'Tersedia';
                }
                $this->db->query("UPDATE {$this->table} SET status = :status WHERE id = :id");
                $this->db->bind(':status', $status_baru);
                $this->db->bind(':id', $id);
                $this->db->execute();
            }
            $this->db->commit();
            return $this->db->rowCount();
        } catch (Exception $e) {
            $this->db->rollBack();
            return 0;
        }
    }

    /**
     * =====================================================================
     * FUNGSI LAMA YANG KURANG LENGKAP
     * =====================================================================
     * public function tambahStok($id, $jumlah) {
     * $this->db->beginTransaction();
     * try {
     * $query = "UPDATE {$this->table} SET jumlah = jumlah + :jumlah WHERE id = :id";
     * $this->db->query($query);
     * $this->db->bind('id', $id);
     * $this->db->bind('jumlah', $jumlah);
     * $this->db->execute();
     * $this->db->commit();
     * return $this->db->rowCount();
     * } catch (Exception $e) {
     * $this->db->rollBack();
     * return 0;
     * }
     * }
     * =====================================================================
     */

    /**
     * =====================================================================
     * FUNGSI BARU YANG SUDAH DIPERBAIKI DAN LENGKAP
     * =====================================================================
     */
    public function tambahStok($id, $jumlah) {
        $this->db->beginTransaction();
        try {
            // Langkah 1: Tambahkan kembali jumlah stok
            $query = "UPDATE {$this->table} SET jumlah = jumlah + :jumlah WHERE id = :id";
            $this->db->query($query);
            $this->db->bind('id', $id);
            $this->db->bind('jumlah', $jumlah);
            $this->db->execute();

            // Langkah 2: Ambil data barang yang sudah diperbarui
            $barang = $this->getBarangById($id);
            if ($barang) {
                $jumlah_baru = $barang['jumlah'];
                $status_baru = '';
                
                // Langkah 3: Tentukan status baru berdasarkan jumlah stok terkini
                if ($jumlah_baru <= 0) {
                    $status_baru = 'Tidak Tersedia';
                } elseif ($jumlah_baru >= 1 && $jumlah_baru <= 3) {
                    $status_baru = 'Terbatas';
                } else {
                    $status_baru = 'Tersedia';
                }

                // Langkah 4: Perbarui status di database
                $this->db->query("UPDATE {$this->table} SET status = :status WHERE id = :id");
                $this->db->bind(':status', $status_baru);
                $this->db->bind(':id', $id);
                $this->db->execute();
            }

            $this->db->commit();
            return $this->db->rowCount();
        } catch (Exception $e) {
            $this->db->rollBack();
            return 0;
        }
    }
    /**
     * =====================================================================
     * FUNGSI BARU UNTUK MENGHAPUS BARANG
     * =====================================================================
     * Fungsi ini akan menghapus data dari database dan juga file gambarnya.
     */
    public function deleteBarang($id) {
        // Langkah 1: Ambil data barang untuk mendapatkan nama file gambar
        $barang = $this->getBarangById($id);
        if (!$barang) {
            return 0; // Hentikan jika barang tidak ditemukan
        }
        $gambar = $barang['gambar'];

        // Langkah 2: Hapus data dari database
        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();

        $rowCount = $this->db->rowCount();

        // Langkah 3: Jika penghapusan dari DB berhasil, hapus file gambar
        if ($rowCount > 0) {
            $filePath = APP_ROOT . '/public/img/barang/' . $gambar;
            // Pastikan file ada dan bukan gambar default sebelum menghapus
            if ($gambar && $gambar !== 'images.png' && file_exists($filePath)) {
                unlink($filePath);
            }
        }

        return $rowCount;
    }
    /* ==========================================================
     * FUNGSI-FUNGSI BARU UNTUK DASHBOARD ADMIN
     * ==========================================================
     */

    public function countAllBarangSimple() {
        $this->db->query("SELECT COUNT(id) as total FROM {$this->table}");
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    public function getTotalStock() {
        $this->db->query("SELECT SUM(jumlah) as total FROM {$this->table}");
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }
    
    public function getKondisiSummary() {
        $this->db->query("SELECT kondisi, COUNT(id) as jumlah FROM {$this->table} GROUP BY kondisi");
        return $this->db->resultSet();
    }

    public function getAvailabilitySummary() {
        $query = "SELECT
                    CASE
                        WHEN jumlah >= 4 THEN 'Tersedia'
                        WHEN jumlah >= 1 AND jumlah <= 3 THEN 'Terbatas'
                        ELSE 'Tidak Tersedia'
                    END AS status_ketersediaan,
                    COUNT(id) as jumlah
                  FROM {$this->table}
                  GROUP BY status_ketersediaan
                  ORDER BY FIELD(status_ketersediaan, 'Tersedia', 'Terbatas', 'Tidak Tersedia')";
        $this->db->query($query);
        return $this->db->resultSet();
    }
     /**
     * =====================================================================
     * FUNGSI BARU UNTUK MENGHAPUS BARANG SECARA MASSAL
     * =====================================================================
     */
    public function hapusBarangMassal($ids) {
        if (empty($ids)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Langkah 1: Ambil nama file gambar sebelum menghapus data
        $this->db->query("SELECT gambar FROM {$this->table} WHERE id IN ({$placeholders})");
        foreach ($ids as $k => $id) {
            $this->db->bind($k + 1, $id);
        }
        $items_to_delete = $this->db->resultSet();

        // Langkah 2: Hapus data dari database
        $this->db->query("DELETE FROM {$this->table} WHERE id IN ({$placeholders})");
        foreach ($ids as $k => $id) {
            $this->db->bind($k + 1, $id);
        }
        $this->db->execute();
        $rowCount = $this->db->rowCount();

        // Langkah 3: Jika penghapusan dari DB berhasil, hapus file gambar
        if ($rowCount > 0) {
            foreach ($items_to_delete as $item) {
                $gambar = $item['gambar'];
                $filePath = APP_ROOT . '/public/img/barang/' . $gambar;
                // Pastikan file ada dan bukan gambar default sebelum menghapus
                if ($gambar && $gambar !== 'images.png' && file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        return $rowCount;
    }

    
}
