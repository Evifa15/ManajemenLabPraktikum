<?php
class Staff_model {
    private $table = 'staff';
    private $db;
    public function __construct() {
        $this->db = new Database;
    }
    public function createStaffAndUserAccount($data) {
        $this->db->beginTransaction();
        try {
            $passwordSource = $data['id_staff'];
            $hashed_password = password_hash($passwordSource, PASSWORD_DEFAULT);
            $this->db->query('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
            $this->db->bind(':username', $data['id_staff']);
            $this->db->bind(':password', $hashed_password);
            $this->db->bind(':role', 'admin');
            $this->db->execute(); 
            $userId = $this->db->lastInsertId();
            $query = "INSERT INTO staff (user_id, id_staff, nama, jenis_kelamin, ttl, agama, alamat, no_hp, email, foto) 
                      VALUES (:user_id, :id_staff, :nama, :jenis_kelamin, :ttl, :agama, :alamat, :no_hp, :email, :foto)";
            $this->db->query($query);
            $this->db->bind('user_id', $userId); 
            $this->db->bind('id_staff', $data['id_staff']);
            $this->db->bind('nama', $data['nama']);
            $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);
            $this->db->bind('ttl', $data['ttl']);
            $this->db->bind('agama', $data['agama']);
            $this->db->bind('alamat', $data['alamat']);
            $this->db->bind('no_hp', $data['no_hp']);
            $this->db->bind('email', $data['email']);
            $this->db->bind('foto', 'default.png'); 
            $this->db->execute();
            $this->db->commit();
            return $this->db->rowCount();
        } catch (Exception $e) {
            $this->db->rollBack();
            return 0;
        }
    }
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
        $this->db->query($sql); 
        if (!empty($keyword)) {
            $this->db->bind(':keyword', '%' . $keyword . '%'); 
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT); 
        $this->db->bind(':offset', $offset, PDO::PARAM_INT); 
        return $this->db->resultSet(); 
    }
    public function countAllStaff($keyword = null) {
        $sql = 'SELECT COUNT(id) as total FROM ' . $this->table;
        if (!empty($keyword)) {
            $sql .= ' WHERE nama LIKE :keyword 
                      OR id_staff LIKE :keyword 
                      OR jenis_kelamin LIKE :keyword 
                      OR no_hp LIKE :keyword
                      OR REPLACE(jenis_kelamin, "-", " ") LIKE :keyword';
        }
        $this->db->query($sql); 
        if (!empty($keyword)) {
            $this->db->bind(':keyword', '%' . $keyword . '%'); 
        }
        $result = $this->db->single(); 
        return $result ? (int)$result['total'] : 0;
    }
    public function importStaffBatch($dataStaff) {
        if (empty($dataStaff)) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['Tidak ada data untuk diimpor.']];
        }
        $berhasil = 0;
        $gagal = 0;
        $errors = [];
        foreach ($dataStaff as $index => $staff) {
            if ($this->createStaffAndUserAccount($staff) > 0) {
                $berhasil++;
            } else {
                $gagal++;
                $errors[] = "Baris " . ($index + 2) . ": Gagal menyimpan. Kemungkinan ID Staff '{$staff['id_staff']}' sudah ada.";
            }
        }
        return ['success' => $berhasil, 'failed' => $gagal, 'errors' => $errors];
    }
    public function hapusStaff($id) {
        $this->db->query('SELECT user_id FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        $staff = $this->db->single();
        if (!$staff) return 0; 
        $this->db->beginTransaction();
        try {
            $this->db->query('DELETE FROM ' . $this->table . ' WHERE id = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();
            $rowCount = $this->db->rowCount();
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
    public function getStaffById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
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
    public function hapusStaffMassal($ids) {
        if (empty($ids)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->db->beginTransaction();
        try {
            $this->db->query("SELECT user_id FROM {$this->table} WHERE id IN ({$placeholders}) AND user_id IS NOT NULL");
            foreach ($ids as $k => $id) {
                $this->db->bind($k + 1, $id);
            }
            $users_to_delete = $this->db->resultSet();
            $user_ids = array_column($users_to_delete, 'user_id');
            $this->db->query("DELETE FROM {$this->table} WHERE id IN ({$placeholders})");
            foreach ($ids as $k => $id) {
                $this->db->bind($k + 1, $id);
            }
            $this->db->execute();
            $rowCount = $this->db->rowCount();
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