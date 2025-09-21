<?php

class User_model {
    private $table = 'users';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // --- FUNGSI DIPERBAIKI ---
    public function getUsersPaginated($offset, $limit, $filters = []) {
        $query = "SELECT 
                    u.id, u.username, u.role, u.password,
                    CASE 
                        WHEN u.role = 'admin' THEN a.id_staff
                        WHEN u.role = 'guru' THEN g.nip
                        WHEN u.role = 'siswa' THEN s.id_siswa
                    END AS id_pengguna,
                    CASE 
                        WHEN u.role = 'admin' THEN a.email
                        WHEN u.role = 'guru' THEN g.email
                        WHEN u.role = 'siswa' THEN s.email
                    END AS email
                FROM 
                    {$this->table} u
                LEFT JOIN staff a ON u.id = a.user_id AND u.role = 'admin'
                LEFT JOIN guru g ON u.id = g.user_id AND u.role = 'guru'
                LEFT JOIN siswa s ON u.id = s.user_id AND u.role = 'siswa'
                WHERE 1=1"; 

        if (!empty($filters['keyword'])) {
            $query .= " AND (u.username LIKE :keyword OR 
                              (CASE 
                                  WHEN u.role = 'admin' THEN a.id_staff
                                  WHEN u.role = 'guru' THEN g.nip
                                  WHEN u.role = 'siswa' THEN s.id_siswa
                              END) LIKE :keyword)";
        }

        if (!empty($filters['role'])) {
            $query .= " AND u.role = :role";
        }

        $query .= " ORDER BY u.id ASC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        
        if (!empty($filters['keyword'])) {
            $this->db->bind(':keyword', '%' . $filters['keyword'] . '%');
        }
        if (!empty($filters['role'])) {
            $this->db->bind(':role', $filters['role']);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    // --- FUNGSI DIPERBAIKI ---
    public function countAllUsers($filters = []) {
        $query = "SELECT COUNT(u.id) AS total FROM {$this->table} u
                LEFT JOIN staff a ON u.id = a.user_id AND u.role = 'admin'
                LEFT JOIN guru g ON u.id = g.user_id AND u.role = 'guru'
                LEFT JOIN siswa s ON u.id = s.user_id AND u.role = 'siswa'
                WHERE 1=1";

        if (!empty($filters['keyword'])) {
            $query .= " AND (u.username LIKE :keyword OR 
                              (CASE 
                                  WHEN u.role = 'admin' THEN a.id_staff
                                  WHEN u.role = 'guru' THEN g.nip
                                  WHEN u.role = 'siswa' THEN s.id_siswa
                              END) LIKE :keyword)";
        }

        if (!empty($filters['role'])) {
            $query .= " AND u.role = :role";
        }

        $this->db->query($query);

        if (!empty($filters['keyword'])) {
            $this->db->bind(':keyword', '%' . $filters['keyword'] . '%');
        }
        if (!empty($filters['role'])) {
            $this->db->bind(':role', $filters['role']);
        }

        $result = $this->db->single();
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * REVISI FINAL: Memperbaiki logika binding parameter di dalam switch case.
     */
    public function createUserWithRole($data) {
        $this->db->beginTransaction();

        try {
            // Langkah 1: Masukkan data ke tabel 'users'
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $this->db->query('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
            $this->db->bind(':username', $data['username']);
            $this->db->bind(':password', $hashed_password);
            $this->db->bind(':role', $data['role']);
            $this->db->execute();
            
            $userId = $this->db->lastInsertId();

            // Langkah 2: Masukkan data ke tabel peran
            $role = $data['role'];
            $idPengguna = $data['id_pengguna'];
            $email = $data['email'];
            $nama = $data['username'];

            switch ($role) {
                case 'admin':
                    $this->db->query('INSERT INTO admin (user_id, id_admin, nama, email) VALUES (:user_id, :id_pengguna, :nama, :email)');
                    $this->db->bind(':id_pengguna', $idPengguna);
                    break;
                case 'guru':
                    $this->db->query('INSERT INTO guru (user_id, nip, nama, email) VALUES (:user_id, :id_pengguna, :nama, :email)');
                    $this->db->bind(':id_pengguna', $idPengguna);
                    break;
                case 'siswa':
                    $this->db->query('INSERT INTO siswa (user_id, id_siswa, nama, email) VALUES (:user_id, :id_pengguna, :nama, :email)');
                    $this->db->bind(':id_pengguna', $idPengguna);
                    break;
                default:
                    throw new Exception("Peran tidak valid.");
            }

            // PERBAIKAN UTAMA: Pindahkan binding ke sini, setelah query disiapkan
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':nama', $nama);
            $this->db->bind(':email', $email);
            $this->db->execute();

            $this->db->commit();
            return 1;

        } catch (Exception $e) {
            $this->db->rollBack();
            // die('Error: ' . $e->getMessage()); 
            return 0;
        }
    }
    
    /**
     * REVISI FINAL: Memperbaiki logika binding parameter untuk update.
     */
    public function updateUser($data) {
        $currentUser = $this->getUserById($data['id']);
        if (!$currentUser) return 0;
        
        $this->db->beginTransaction();

        try {
            // Langkah 1: Perbarui tabel 'users'
            if (empty($data['password'])) {
                $this->db->query('UPDATE users SET username = :username, role = :role WHERE id = :id');
            } else {
                $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
                $this->db->query('UPDATE users SET username = :username, password = :password, role = :role WHERE id = :id');
                $this->db->bind(':password', $hashed_password);
            }
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':username', $data['username']);
            $this->db->bind(':role', $data['role']);
            $this->db->execute();

            // Langkah 2: Perbarui tabel peran
            $role = $currentUser['role'];
            $idPengguna = $data['id_pengguna'];
            $email = $data['email'];
            $nama = $data['username'];

            switch ($role) {
                case 'admin':
                    $this->db->query('UPDATE admin SET id_admin = :id_pengguna, nama = :nama, email = :email WHERE user_id = :user_id');
                    $this->db->bind(':id_pengguna', $idPengguna);
                    break;
                case 'guru':
                    $this->db->query('UPDATE guru SET nip = :id_pengguna, nama = :nama, email = :email WHERE user_id = :user_id');
                    $this->db->bind(':id_pengguna', $idPengguna);
                    break;
                case 'siswa':
                    $this->db->query('UPDATE siswa SET id_siswa = :id_pengguna, nama = :nama, email = :email WHERE user_id = :user_id');
                    $this->db->bind(':id_pengguna', $idPengguna);
                    break;
            }

            // PERBAIKAN UTAMA: Pindahkan binding ke sini
            $this->db->bind(':user_id', $data['id']);
            $this->db->bind(':nama', $nama);
            $this->db->bind(':email', $email);
            $this->db->execute();

            $this->db->commit();
            return 1;

        } catch (Exception $e) {
            $this->db->rollBack();
            // die('Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    // --- Sisa fungsi lain (Biarkan Apa Adanya) ---
    public function findUserByUsernameAndRole($username, $role) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE username = :username AND role = :role');
        $this->db->bind(':username', $username);
        $this->db->bind(':role', $role);
        return $this->db->single();
    }
    
    public function findUserByUsername($username) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE username = :username');
        $this->db->bind(':username', $username);
        return $this->db->single();
    }
    
    public function hapusUser($id) {
        $this->db->query('DELETE FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
    
    public function getUserById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    // --- FUNGSI DIPERBAIKI ---
    public function getUserDetailById($id) {
        $query = "SELECT 
                    u.id, u.username, u.role,
                    CASE 
                        WHEN u.role = 'admin' THEN a.id_staff
                        WHEN u.role = 'guru' THEN g.nip
                        WHEN u.role = 'siswa' THEN s.id_siswa
                    END AS id_pengguna,
                    CASE 
                        WHEN u.role = 'admin' THEN a.email
                        WHEN u.role = 'guru' THEN g.email
                        WHEN u.role = 'siswa' THEN s.email
                    END AS email
                FROM 
                    {$this->table} u
                LEFT JOIN staff a ON u.id = a.user_id AND u.role = 'admin'
                LEFT JOIN guru g ON u.id = g.user_id AND u.role = 'guru'
                LEFT JOIN siswa s ON u.id = s.user_id AND u.role = 'siswa'
                WHERE u.id = :id";
        
        $this->db->query($query);
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function changePassword($id, $newPassword) {
        $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = 'UPDATE ' . $this->table . ' SET password = :password WHERE id = :id';
        $this->db->query($query);
        $this->db->bind(':id', $id);
        $this->db->bind(':password', $hashed_password);
        $this->db->execute();
        return $this->db->rowCount();
    }
    /* ==========================================================
     * FUNGSI BARU UNTUK DASHBOARD ADMIN
     * ==========================================================
     */
    public function countAllUsersSimple() {
        $this->db->query("SELECT COUNT(id) as total FROM {$this->table}");
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }
}