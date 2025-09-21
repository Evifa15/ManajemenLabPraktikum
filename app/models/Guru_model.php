<?php

class Guru_model {
    private $table = 'guru';
    private $db;
    public function __construct() {
        $this->db = new Database;
    }
    /**
     * =================================================================
     * FUNGSI GET SEMUA GURU (TANPA PAGINASI/PENCARIAN)
     * =================================================================
     * Digunakan untuk mengisi dropdown di form Tambah/Ubah Kelas.
     */
    public function getAllGuru() {
        $this->db->query('SELECT id, nip, nama FROM ' . $this->table . ' ORDER BY nama ASC');
        return $this->db->resultSet();
    }
    /**
     * =================================================================
     * FUNGSI TAMBAH GURU & BUAT AKUN
     * =================================================================
     * Membuat akun user dan profil guru dalam satu transaksi database.
     */
    public function createGuruAndUserAccount($data) {
        $this->db->beginTransaction();

        try {
            // Langkah 1: Buat entri di tabel 'users'
            // Gunakan NIP sebagai password default
            $hashed_password = password_hash($data['nip'], PASSWORD_DEFAULT);
            
            $this->db->query('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
            $this->db->bind(':username', $data['nama']);
            $this->db->bind(':password', $hashed_password);
            $this->db->bind(':role', 'guru');
            $this->db->execute();
            
            $userId = $this->db->lastInsertId();

            // Langkah 2: Buat entri di tabel 'guru' dengan user_id yang terhubung
            $query = "INSERT INTO guru (user_id, nip, nama, jenis_kelamin, ttl, agama, alamat, no_hp, email, foto) 
                      VALUES (:user_id, :nip, :nama, :jenis_kelamin, :ttl, :agama, :alamat, :no_hp, :email, :foto)";
            
            $this->db->query($query);
            $this->db->bind('user_id', $userId);
            $this->db->bind('nip', $data['nip']);
            $this->db->bind('nama', $data['nama']);
            $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);
            $this->db->bind('ttl', $data['ttl']);
            $this->db->bind('agama', $data['agama']);
            $this->db->bind('alamat', $data['alamat']);
            $this->db->bind('no_hp', $data['no_hp']);
            $this->db->bind('email', $data['email']);
            $this->db->bind('foto', 'default.png'); // Foto default
            
            $this->db->execute();
            
            $this->db->commit();
            return $this->db->rowCount();

        } catch (Exception $e) {
            $this->db->rollBack();
            return 0;
        }
    }
    /**
     * =================================================================
     * FUNGSI GET GURU (PAGINASI & SEARCH) - DIPERBARUI
     * =================================================================
     * Mengambil data guru dengan paginasi dan pencarian yang lebih fleksibel.
     */
    public function getGuruPaginated($offset, $limit, $keyword = null) {
        $sql = 'SELECT id, nip, nama, jenis_kelamin, no_hp FROM ' . $this->table;
        if (!empty($keyword)) {
            // PERUBAHAN: Menambahkan pencarian fleksibel untuk jenis kelamin
            $sql .= ' WHERE nama LIKE :keyword 
                      OR nip LIKE :keyword 
                      OR jenis_kelamin LIKE :keyword
                      OR REPLACE(jenis_kelamin, "-", " ") LIKE :keyword';
        }
        $sql .= ' ORDER BY nama ASC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        if (!empty($keyword)) {
            $this->db->bind(':keyword', '%' . $keyword . '%');
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
    /**
     * =================================================================
     * FUNGSI HITUNG SEMUA GURU - DIPERBARUI
     * =================================================================
     * Menghitung total guru untuk paginasi dan pencarian yang lebih fleksibel.
     */
    public function countAllGuru($keyword = null) {
        $sql = 'SELECT COUNT(id) as total FROM ' . $this->table;
        if (!empty($keyword)) {
            // PERUBAHAN: Menambahkan pencarian fleksibel untuk jenis kelamin
            $sql .= ' WHERE nama LIKE :keyword 
                      OR nip LIKE :keyword
                      OR jenis_kelamin LIKE :keyword
                      OR REPLACE(jenis_kelamin, "-", " ") LIKE :keyword';
        }
        $this->db->query($sql);
        if (!empty($keyword)) {
            $this->db->bind(':keyword', '%' . $keyword . '%');
        }
        $result = $this->db->single();
        return $result ? (int)$result['total'] : 0;
    }
    /**
     * =================================================================
     * FUNGSI IMPORT GURU BATCH
     * =================================================================
     * Mengimpor data guru secara massal dari array (hasil parse CSV).
     */
    public function tambahGuruBatch($dataGuru) {
        if (empty($dataGuru)) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['Tidak ada data untuk diimpor.']];
        }

        $berhasil = 0;
        $gagal = 0;
        $errors = [];

        foreach ($dataGuru as $index => $guru) {
            // Memanggil kembali method yang sudah ada untuk setiap baris data
            // Ini memastikan setiap data yang diimpor juga dibuatkan akun user-nya
            if ($this->createGuruAndUserAccount($guru) > 0) {
                $berhasil++;
            } else {
                $gagal++;
                $errors[] = "Baris " . ($index + 2) . ": Gagal menyimpan. Kemungkinan NIP '{$guru['nip']}' sudah ada.";
            }
        }
        
        return ['success' => $berhasil, 'failed' => $gagal, 'errors' => $errors];
    }
    /**
     * =================================================================
     * FUNGSI HAPUS GURU
     * =================================================================
     * Menghapus data guru dari tabel 'guru' dan akun terkait dari tabel 'users'.
     */
    public function hapusGuru($id) {
        $guru = $this->getGuruById($id);
        if (!$guru) return 0;

        $this->db->beginTransaction();
        try {
            // 1. Hapus dari tabel guru
            $this->db->query('DELETE FROM ' . $this->table . ' WHERE id = :id');
            $this->db->bind('id', $id);
            $this->db->execute();
            $rowCount = $this->db->rowCount();

            // 2. Hapus dari tabel users jika terhubung
            if ($guru['user_id']) {
                $this->db->query('DELETE FROM users WHERE id = :user_id');
                $this->db->bind('user_id', $guru['user_id']);
                $this->db->execute();
            }

            $this->db->commit();
            return $rowCount;

        } catch (Exception $e) {
            $this->db->rollBack();
            return 0;
        }
    }
    /**
     * =================================================================
     * FUNGSI GET GURU BY ID
     * =================================================================
     * Mengambil seluruh data satu guru berdasarkan ID-nya.
     */
    public function getGuruById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
   /**
     * =================================================================
     * FUNGSI UPDATE GURU (DIPERBAIKI)
     * =================================================================
     * Memperbarui data di tabel 'guru' dan 'users' (hanya username).
     */
    public function updateGuru($data) {
    $this->db->beginTransaction();
    try {
        // Langkah 1: Update data di tabel 'guru'
        $query = "UPDATE " . $this->table . " SET
                    nip = :nip, nama = :nama, jenis_kelamin = :jenis_kelamin,
                    ttl = :ttl, agama = :agama, alamat = :alamat,
                    no_hp = :no_hp, email = :email, foto = :foto 
                  WHERE id = :id"; // <-- Menambahkan 'foto = :foto'
        
        $this->db->query($query);
        $this->db->bind('id', $data['id']);
        $this->db->bind('nip', $data['nip']);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);
        $this->db->bind('ttl', $data['ttl']);
        $this->db->bind('agama', $data['agama']);
        $this->db->bind('alamat', $data['alamat']);
        $this->db->bind('no_hp', $data['no_hp']);
        $this->db->bind('email', $data['email']);
        $this->db->bind('foto', $data['foto']); // <-- Menambahkan binding untuk foto
        $this->db->execute();

        $rowCount = $this->db->rowCount();

        // Langkah 2: Update username di tabel 'users' jika nama berubah
        $guru = $this->getGuruById($data['id']);
        if ($guru && $guru['user_id']) {
            $this->db->query('UPDATE users SET username = :username WHERE id = :user_id');
            $this->db->bind(':username', $data['nama']);
            $this->db->bind(':user_id', $guru['user_id']);
            $this->db->execute();
            $rowCount += $this->db->rowCount();
        }

        $this->db->commit();
        return $rowCount;

    } catch (Exception $e) {
        $this->db->rollBack();
        return -1;
    }
    }
    /**
     * =================================================================
     * FUNGSI HAPUS GURU MASSAL
     * =================================================================
     * Menghapus beberapa data guru dan akun user terkait berdasarkan array ID.
     */
    public function hapusGuruMassal($ids) {
        if (empty($ids)) {
            return 0;
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $this->db->beginTransaction();
        try {
            // 1. Ambil user_id dari semua guru yang akan dihapus
            $this->db->query("SELECT user_id FROM {$this->table} WHERE id IN ({$placeholders}) AND user_id IS NOT NULL");
            foreach ($ids as $k => $id) {
                $this->db->bind($k + 1, $id);
            }
            $users_to_delete = $this->db->resultSet();
            $user_ids = array_column($users_to_delete, 'user_id');

            // 2. Hapus dari tabel guru
            $this->db->query("DELETE FROM {$this->table} WHERE id IN ({$placeholders})");
            foreach ($ids as $k => $id) {
                $this->db->bind($k + 1, $id);
            }
            $this->db->execute();
            $rowCount = $this->db->rowCount();

            // 3. Hapus dari tabel users jika ada user_id yang terhubung
            if (!empty($user_ids)) {
                $userPlaceholders = implode(',', array_fill(0, count($user_ids), '?'));
                $this->db->query("DELETE FROM users WHERE id IN ({$userPlaceholders})");
                 foreach ($user_ids as $k => $uid) {
                    $this->db->bind($k + 1, $uid);
                }
                $this->db->execute();
            }
            
            $this->db->commit();
            return $rowCount;

        } catch (Exception $e) {
            $this->db->rollBack();
            return 0;
        }
    }
    /**
     * Mengambil data guru berdasarkan user_id.
     */
    public function getGuruByUserId($userId) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        return $this->db->single();
    }
}