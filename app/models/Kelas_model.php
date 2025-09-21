<?php

class Kelas_model {
    private $table = 'kelas';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * ==========================================================
     * FUNGSI GET KELAS (PAGINASI & SEARCH)
     * ==========================================================
     */
    public function getKelasPaginated($offset, $limit, $keyword = null) {
        $sql = 'SELECT k.*, g.nama as nama_wali_kelas, g.nip FROM ' . $this->table . ' k LEFT JOIN guru g ON k.wali_kelas_id = g.id';
        if (!empty($keyword)) {
            $sql .= ' WHERE k.nama_kelas LIKE :keyword OR g.nama LIKE :keyword';
        }
        $sql .= ' ORDER BY k.nama_kelas ASC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        if (!empty($keyword)) {
            $this->db->bind(':keyword', '%' . $keyword . '%');
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countAllKelas($keyword = null) {
        $sql = 'SELECT COUNT(k.id) as total FROM ' . $this->table . ' k LEFT JOIN guru g ON k.wali_kelas_id = g.id';
        if (!empty($keyword)) {
            $sql .= ' WHERE k.nama_kelas LIKE :keyword OR g.nama LIKE :keyword';
        }
        $this->db->query($sql);
        if (!empty($keyword)) {
            $this->db->bind(':keyword', '%' . $keyword . '%');
        }
        $result = $this->db->single();
        return $result ? (int)$result['total'] : 0;
    }

    
    public function getKelasById($id) {
        $this->db->query(
            'SELECT k.*, g.nama as nama_wali_kelas, g.nip 
             FROM ' . $this->table . ' k
             LEFT JOIN guru g ON k.wali_kelas_id = g.id
             WHERE k.id = :id'
        );
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function tambahKelas($data) {
        $query = "INSERT INTO " . $this->table . " (nama_kelas, wali_kelas_id) VALUES (:nama_kelas, :wali_kelas_id)";
        $this->db->query($query);
        $this->db->bind('nama_kelas', $data['nama_kelas']);
        $this->db->bind('wali_kelas_id', $data['wali_kelas_id']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateKelas($data) {
        $query = "UPDATE " . $this->table . " SET nama_kelas = :nama_kelas, wali_kelas_id = :wali_kelas_id WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('id', $data['id']);
        $this->db->bind('nama_kelas', $data['nama_kelas']);
        $this->db->bind('wali_kelas_id', $data['wali_kelas_id']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    // ✅ Perbaikan: Menambahkan try-catch untuk menangani error database
    public function hapusKelas($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $this->db->query($query);
            $this->db->bind('id', $id);
            $this->db->execute();
            return $this->db->rowCount();
        } catch (PDOException $e) {
            // Mengembalikan 0 jika terjadi error (misalnya karena foreign key)
            return 0;
        }
    }

    public function tambahKelasBatch($dataKelas) {
        if (empty($dataKelas)) {
            return ['success' => 0, 'failed' => 0];
        }

        $this->db->beginTransaction();
        $berhasil = 0;
        $gagal = 0;

        $query = "INSERT INTO " . $this->table . " (nama_kelas, wali_kelas_id) VALUES (:nama_kelas, :wali_kelas_id)";

        foreach ($dataKelas as $kelas) {
            try {
                $this->db->query($query);
                $this->db->bind('nama_kelas', $kelas['nama_kelas']);
                $this->db->bind('wali_kelas_id', $kelas['wali_kelas_id']);
                $this->db->execute();
                if ($this->db->rowCount() > 0) {
                    $berhasil++;
                } else {
                    $gagal++;
                }
            } catch (Exception $e) {
                // Tangani error, misal duplikat nama kelas
                $gagal++;
            }
        }

        if ($gagal > 0) {
            $this->db->rollBack();
            // Jika ada yang gagal, kita batalkan semua agar konsisten
            return ['success' => 0, 'failed' => count($dataKelas)];
        } else {
            $this->db->commit();
            return ['success' => $berhasil, 'failed' => $gagal];
        }
    }

    /**
     * Menghapus beberapa kelas sekaligus berdasarkan array ID.
     * @param array $ids Array berisi ID kelas yang akan dihapus.
     * @return int Jumlah baris yang berhasil dihapus.
     */
    
    // ================== PERBAIKAN DI SINI ==================
    public function hapusKelasMassal($ids) {
        if (empty($ids)) {
            return 0;
        }
        // Buat placeholder sebanyak jumlah ID, contoh: (?,?,?)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Langsung hapus kelas dari tabel kelas. Baris yang error sudah dihilangkan.
        $this->db->query("DELETE FROM " . $this->table . " WHERE id IN ({$placeholders})");
        foreach ($ids as $k => $id) {
            $this->db->bind($k + 1, $id);
        }
        $this->db->execute();
        
        return $this->db->rowCount();
    }
    // =======================================================

    public function hapusGuruMassal() {
        // Logika ini salah tempat dan seharusnya berada di AdminController.
        // Biarkan seperti ini agar tidak menimbulkan error baru,
        // namun idealnya fungsi ini dihapus dari Model.
        $this->checkAuth();
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
        // Redirect kembali ke tab guru
        header('Location: ' . BASEURL . '/admin/kelas/guru');
        exit;
    }
     /* ==========================================================
     * FUNGSI BARU UNTUK MENGAMBIL KELAS BERDASARKAN WALI
     * ==========================================================
     */
    public function getKelasByWaliId($waliKelasId) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE wali_kelas_id = :wali_kelas_id ORDER BY nama_kelas ASC');
        $this->db->bind('wali_kelas_id', $waliKelasId);
        return $this->db->resultSet();
    }
    /* ==========================================================
     * FUNGSI BARU UNTUK DASHBOARD GURU
     * ==========================================================
     */
    public function countKelasByWaliId($waliKelasId) {
        $this->db->query('SELECT COUNT(id) as total FROM ' . $this->table . ' WHERE wali_kelas_id = :wali_kelas_id');
        $this->db->bind('wali_kelas_id', $waliKelasId);
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }
  }