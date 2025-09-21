<?php

class Staff_model {
    private $table = 'staff';
    private $db;

    public function __construct() {
        // Mengaktifkan kembali koneksi ke database
        $this->db = new Database;
    }

    /**
     * Membuat akun user dan profil staff dalam satu transaksi.
     * @param array $data Data dari form POST
     * @return int Jumlah baris yang berhasil ditambahkan (1 jika sukses, 0 jika gagal)
     */
    public function createStaffAndUserAccount($data) {
        // Memulai transaksi untuk memastikan kedua query berhasil
        $this->db->beginTransaction();

        try {
            // Langkah 1: Buat entri di tabel 'users'
            // Kata sandi diambil dari ID Staff dan di-hash untuk keamanan
            $hashed_password = password_hash($data['id_staff'], PASSWORD_DEFAULT);
            
            $this->db->query('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
            $this->db->bind(':username', $data['nama']);
            $this->db->bind(':password', $hashed_password);
            $this->db->bind(':role', 'admin'); // Role sudah pasti 'admin' untuk staff
            $this->db->execute();
            
            // Ambil ID dari user yang baru saja dibuat untuk dihubungkan ke tabel staff
            $userId = $this->db->lastInsertId();

            // Langkah 2: Buat entri di tabel 'staff' dengan user_id yang sudah didapat
            $query = "INSERT INTO staff (user_id, id_staff, nama, jenis_kelamin, ttl, agama, alamat, no_hp, email, foto) 
                      VALUES (:user_id, :id_staff, :nama, :jenis_kelamin, :ttl, :agama, :alamat, :no_hp, :email, :foto)";
            
            $this->db->query($query);
            $this->db->bind('user_id', $userId); // Ini adalah penghubungnya
            $this->db->bind('id_staff', $data['id_staff']);
            $this->db->bind('nama', $data['nama']);
            $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);
            $this->db->bind('ttl', $data['ttl']);
            $this->db->bind('agama', $data['agama']);
            $this->db->bind('alamat', $data['alamat']);
            $this->db->bind('no_hp', $data['no_hp']);
            $this->db->bind('email', $data['email']);
            $this->db->bind('foto', 'default.png'); // Menggunakan foto default
            
            $this->db->execute();
            
            // Jika semua query berhasil, konfirmasi transaksi
            $this->db->commit();
            return $this->db->rowCount();

        } catch (Exception $e) {
            // Jika ada satu saja yang gagal, batalkan semua perubahan
            $this->db->rollBack();
            // Anda bisa mencatat error $e->getMessage() jika perlu untuk debug
            return 0;
        }
    }
    
    /**
     * =================================================================
     * FUNGSI GET STAFF PAGINATED (DIPERBAIKI)
     * =================================================================
     * Mengambil data staff dengan pencarian yang lebih fleksibel untuk jenis kelamin.
     */
    public function getStaffPaginated($offset, $limit, $keyword = null) {
        $sql = 'SELECT id, id_staff, nama, jenis_kelamin, no_hp FROM ' . $this->table;
        if (!empty($keyword)) {
            $sql .= ' WHERE nama LIKE :keyword 
                      OR id_staff LIKE :keyword 
                      OR jenis_kelamin LIKE :keyword 
                      OR no_hp LIKE :keyword
                      OR REPLACE(jenis_kelamin, "-", " ") LIKE :keyword';
        }
        $sql .= ' ORDER BY nama ASC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql); // PERBAIKAN: Menggunakan -> bukan .
        if (!empty($keyword)) {
            $this->db->bind(':keyword', '%' . $keyword . '%'); // PERBAIKAN: Menggunakan -> bukan .
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT); // PERBAIKAN: Menggunakan -> bukan .
        $this->db->bind(':offset', $offset, PDO::PARAM_INT); // PERBAIKAN: Menggunakan -> bukan .
        return $this->db->resultSet(); // PERBAIKAN: Menggunakan -> bukan .
    }

    /**
     * =================================================================
     * FUNGSI COUNT ALL STAFF (DIPERBAIKI)
     * =================================================================
     * Menghitung total staff dengan pencarian yang lebih fleksibel untuk jenis kelamin.
     */
    public function countAllStaff($keyword = null) {
        $sql = 'SELECT COUNT(id) as total FROM ' . $this->table;
        if (!empty($keyword)) {
            $sql .= ' WHERE nama LIKE :keyword 
                      OR id_staff LIKE :keyword 
                      OR jenis_kelamin LIKE :keyword 
                      OR no_hp LIKE :keyword
                      OR REPLACE(jenis_kelamin, "-", " ") LIKE :keyword';
        }
        $this->db->query($sql); // PERBAIKAN: Menggunakan -> bukan .
        if (!empty($keyword)) {
            $this->db->bind(':keyword', '%' . $keyword . '%'); // PERBAIKAN: Menggunakan -> bukan .
        }
        $result = $this->db->single(); // PERBAIKAN: Menggunakan -> bukan .
        return $result ? (int)$result['total'] : 0;
    }

    // Letakkan method ini di dalam class Staff_model

    /**
     * Mengimpor data staff secara massal dari array (hasil parse CSV).
     * @param array $dataStaff Array berisi data staff
     * @return array Hasil import ['success' => int, 'failed' => int, 'errors' => array]
     */
    public function importStaffBatch($dataStaff) {
        if (empty($dataStaff)) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['Tidak ada data untuk diimpor.']];
        }

        $berhasil = 0;
        $gagal = 0;
        $errors = [];

        foreach ($dataStaff as $index => $staff) {
            // Memanggil kembali method yang sudah ada untuk setiap baris data
            // Ini memastikan setiap data yang diimpor juga dibuatkan akun user-nya
            if ($this->createStaffAndUserAccount($staff) > 0) {
                $berhasil++;
            } else {
                $gagal++;
                $errors[] = "Baris " . ($index + 2) . ": Gagal menyimpan. Kemungkinan ID Staff '{$staff['id_staff']}' sudah ada.";
            }
        }
        
        return ['success' => $berhasil, 'failed' => $gagal, 'errors' => $errors];
    }


    /**
     * =================================================================
     * FUNGSI HAPUS STAFF
     * =================================================================
     * Menghapus data staff dari tabel 'staff' dan akun terkait dari tabel 'users'.
     */
    public function hapusStaff($id) {
        // Ambil dulu data staff untuk mendapatkan user_id
        $this->db->query('SELECT user_id FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        $staff = $this->db->single();

        if (!$staff) return 0; // Hentikan jika staff tidak ditemukan

        $this->db->beginTransaction();

        try {
            // 1. Hapus dari tabel staff
            $this->db->query('DELETE FROM ' . $this->table . ' WHERE id = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();
            $rowCount = $this->db->rowCount();

            // 2. Hapus dari tabel users jika ada user_id yang terhubung
            if ($staff['user_id']) {
                $this->db->query('DELETE FROM users WHERE id = :user_id');
                $this->db->bind('user_id', $staff['user_id']);
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
     * FUNGSI GET STAFF BY ID
     * =================================================================
     * Mengambil seluruh data satu staff berdasarkan ID-nya.
     */
    public function getStaffById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * =================================================================
     * FUNGSI UPDATE STAFF
     * =================================================================
     * Memperbarui data di tabel 'staff' dan 'users' (hanya username).
     */
    // File: ManajemenLabPraktikum/app/models/Staff_model.php

public function updateStaff($data) {
    $this->db->beginTransaction();
    try {
        $query = "UPDATE " . $this->table . " SET
                    id_staff = :id_staff,
                    nama = :nama,
                    jenis_kelamin = :jenis_kelamin,
                    ttl = :ttl,
                    agama = :agama,
                    alamat = :alamat,
                    no_hp = :no_hp,
                    email = :email,
                    foto = :foto
                  WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('id', $data['id']);
        $this->db->bind('id_staff', $data['id_staff']);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);
        $this->db->bind('ttl', $data['ttl']);
        $this->db->bind('agama', $data['agama']);
        $this->db->bind('alamat', $data['alamat']);
        $this->db->bind('no_hp', $data['no_hp']);
        $this->db->bind('email', $data['email']);
        $this->db->bind('foto', $data['foto']);
        $this->db->execute();

        $rowCount = $this->db->rowCount();

        $staff = $this->getStaffById($data['id']);
        if ($staff && $staff['user_id']) {
            $this->db->query('UPDATE users SET username = :username WHERE id = :user_id');
            $this->db->bind(':username', $data['nama']);
            $this->db->bind(':user_id', $staff['user_id']);
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
     * FUNGSI HAPUS STAFF MASSAL
     * =================================================================
     * Menghapus beberapa data staff dan akun user terkait berdasarkan array ID.
     */
    public function hapusStaffMassal($ids) {
        if (empty($ids)) {
            return 0;
        }
        
        // Membuat placeholder sebanyak jumlah ID, contoh: (?,?,?)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $this->db->beginTransaction();
        try {
            // 1. Ambil dulu user_id dari semua staff yang akan dihapus
            $this->db->query("SELECT user_id FROM {$this->table} WHERE id IN ({$placeholders}) AND user_id IS NOT NULL");
            foreach ($ids as $k => $id) {
                $this->db->bind($k + 1, $id);
            }
            $users_to_delete = $this->db->resultSet();
            $user_ids = array_column($users_to_delete, 'user_id');

            // 2. Hapus dari tabel staff
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
    public function getStaffByUserId($userId) {
    $this->db->query('SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id');
    $this->db->bind(':user_id', $userId);
    return $this->db->single();
}
}