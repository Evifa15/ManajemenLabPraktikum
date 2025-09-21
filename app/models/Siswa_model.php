<?php

class Siswa_model {
    private $table = 'siswa';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function updateStatusSiswa($siswaId, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('status', $status);
        $this->db->bind('id', $siswaId);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function createSiswaAndUserAccount($data) {
        $this->db->beginTransaction();

        try {
            $hashed_password = password_hash($data['id_siswa'], PASSWORD_DEFAULT);
            $this->db->query('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
            $this->db->bind(':username', $data['nama']);
            $this->db->bind(':password', $hashed_password);
            $this->db->bind(':role', 'siswa');
            $this->db->execute();
            $userId = $this->db->lastInsertId();

            $data['jenis_kelamin'] = str_replace('-', ' ', $data['jenis_kelamin']);

            $query = "INSERT INTO siswa (user_id, id_siswa, nama, jenis_kelamin, ttl, agama, alamat, no_hp, email, foto, kelas_id) 
                      VALUES (:user_id, :id_siswa, :nama, :jenis_kelamin, :ttl, :agama, :alamat, :no_hp, :email, :foto, NULL)";
            
            $this->db->query($query);
            $this->db->bind('user_id', $userId);
            $this->db->bind('id_siswa', $data['id_siswa']);
            $this->db->bind('nama', $data['nama']);
            $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);
            $this->db->bind('no_hp', $data['no_hp']);
            $this->db->bind('email', $data['email']);
            $this->db->bind('ttl', $data['ttl']);
            $this->db->bind('agama', $data['agama']);
            $this->db->bind('alamat', $data['alamat']);
            
            $this->db->bind('foto', $data['foto']);
            
            $this->db->execute();
            $this->db->commit();
            return $this->db->rowCount();

        } catch (Exception $e) {
            $this->db->rollBack();
            return 0;
        }
    }

    public function getAllSiswaPaginated($offset, $limit, $keyword = null) {
        $sql = 'SELECT id, id_siswa, nama, jenis_kelamin, no_hp FROM ' . $this->table;
        if (!empty($keyword)) {
            $sql .= ' WHERE nama LIKE :keyword OR id_siswa LIKE :keyword OR jenis_kelamin LIKE :keyword OR no_hp LIKE :keyword';
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

    public function countAllSiswa($keyword = null) {
        $sql = 'SELECT COUNT(id) as total FROM ' . $this->table;
        if (!empty($keyword)) {
            $sql .= ' WHERE nama LIKE :keyword OR id_siswa LIKE :keyword OR jenis_kelamin LIKE :keyword OR no_hp LIKE :keyword';
        }
        $this->db->query($sql);
        if (!empty($keyword)) {
            $this->db->bind(':keyword', '%' . $keyword . '%');
        }
        $result = $this->db->single();
        return $result ? (int)$result['total'] : 0;
    }
    
    public function getSiswaById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    public function updateSiswa($data) {
        $this->db->beginTransaction();
        try {
            $data['jenis_kelamin'] = str_replace('-', ' ', $data['jenis_kelamin']);

            $query = "UPDATE " . $this->table . " SET
                        id_siswa = :id_siswa, nama = :nama, jenis_kelamin = :jenis_kelamin,
                        ttl = :ttl, agama = :agama, alamat = :alamat,
                        no_hp = :no_hp, email = :email, foto = :foto
                      WHERE id = :id";
            
            $this->db->query($query);
            $this->db->bind('id', $data['id']);
            $this->db->bind('id_siswa', $data['id_siswa']);
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

            $siswa = $this->getSiswaById($data['id']);
            if ($siswa && $siswa['user_id']) {
                $this->db->query('UPDATE users SET username = :username WHERE id = :user_id');
                $this->db->bind(':username', $data['nama']);
                $this->db->bind(':user_id', $siswa['user_id']);
                $this->db->execute();
            }

            $this->db->commit();
            return $rowCount;

        } catch (Exception $e) {
            $this->db->rollBack();
            return -1;
        }
    }
    
    // Metode baru untuk siswa mengubah profil mereka sendiri
    public function updateProfileByUserId($data) {
        $this->db->beginTransaction();
        try {
            $query = "UPDATE " . $this->table . " SET
                        jenis_kelamin = :jenis_kelamin,
                        ttl = :ttl,
                        agama = :agama,
                        alamat = :alamat,
                        no_hp = :no_hp,
                        email = :email,
                        foto = :foto
                      WHERE user_id = :user_id";
            
            $this->db->query($query);
            $this->db->bind('user_id', $data['user_id']);
            $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);
            $this->db->bind('ttl', $data['ttl']);
            $this->db->bind('agama', $data['agama']);
            $this->db->bind('alamat', $data['alamat']);
            $this->db->bind('no_hp', $data['no_hp']);
            $this->db->bind('email', $data['email']);
            $this->db->bind('foto', $data['foto']);
            $this->db->execute();
            
            $this->db->commit();
            return $this->db->rowCount();
        } catch (Exception $e) {
            $this->db->rollBack();
            return -1;
        }
    }

    public function hapusSiswa($id) {
        $siswa = $this->getSiswaById($id);
        if (!$siswa) return 0;

        $this->db->beginTransaction();
        try {
            $this->db->query('DELETE FROM ' . $this->table . ' WHERE id = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();
            $rowCount = $this->db->rowCount();

            if ($siswa['user_id']) {
                $this->db->query('DELETE FROM users WHERE id = :user_id');
                $this->db->bind(':user_id', $siswa['user_id']);
                $this->db->execute();
            }

            $this->db->commit();
            return $rowCount;

        } catch (Exception $e) {
            $this->db->rollBack();
            return 0;
        }
    }

    public function hapusSiswaMassal($ids) {
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
    
    public function importSiswaBatch($dataSiswa) {
        if (empty($dataSiswa)) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['Tidak ada data untuk diimpor.']];
        }

        $berhasil = 0;
        $gagal = 0;
        $errors = [];

        foreach ($dataSiswa as $index => $siswa) {
            if ($this->createSiswaAndUserAccount($siswa) > 0) {
                $berhasil++;
            } else {
                $gagal++;
                $errors[] = "Baris " . ($index + 2) . ": Gagal menyimpan. Kemungkinan ID Siswa (NIS) '{$siswa['id_siswa']}' sudah ada.";
            }
        }
        
        return ['success' => $berhasil, 'failed' => $gagal, 'errors' => $errors];
    }

    public function getSiswaByKelasIdPaginated($kelasId, $offset, $limit, $keyword = null) {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE kelas_id = :kelas_id';
        if (!empty($keyword)) {
            $sql .= ' AND (nama LIKE :keyword OR id_siswa LIKE :keyword)';
        }
        $sql .= ' ORDER BY nama ASC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        $this->db->bind(':kelas_id', $kelasId, PDO::PARAM_INT);
        if (!empty($keyword)) {
            $this->db->bind(':keyword', '%' . $keyword . '%');
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countSiswaByKelasId($kelasId, $keyword = null) {
        $sql = 'SELECT COUNT(id) as total FROM ' . $this->table . ' WHERE kelas_id = :kelas_id';
        if (!empty($keyword)) {
            $sql .= ' AND (nama LIKE :keyword OR id_siswa LIKE :keyword)';
        }
        $this->db->query($sql);
        $this->db->bind(':kelas_id', $kelasId, PDO::PARAM_INT);
        if (!empty($keyword)) {
            $this->db->bind(':keyword', '%' . $keyword . '%');
        }
        $result = $this->db->single();
        return $result ? (int)$result['total'] : 0;
    }

    public function getUnassignedSiswa() {
        $this->db->query('SELECT id, id_siswa, nama FROM ' . $this->table . ' WHERE kelas_id IS NULL ORDER BY nama ASC');
        return $this->db->resultSet();
    }
    
    public function getSiswaByNisInBatch($nises) {
        if (empty($nises)) return [];
        $placeholders = implode(',', array_fill(0, count($nises), '?'));
        
        $query = "SELECT id, id_siswa FROM " . $this->table . " WHERE id_siswa IN ({$placeholders})";
        $this->db->query($query);
        foreach ($nises as $k => $nis) {
            $this->db->bind($k + 1, $nis);
        }
        return $this->db->resultSet();
    }

    public function assignSiswaBatchToKelas($siswaIds, $kelasId) {
        if (empty($siswaIds) || empty($kelasId)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($siswaIds), '?'));
        
        $query = "UPDATE " . $this->table . " SET kelas_id = ? WHERE id IN ({$placeholders}) AND kelas_id IS NULL";
        
        $this->db->query($query);
        $this->db->bind(1, $kelasId);
        foreach ($siswaIds as $k => $id) {
            $this->db->bind($k + 2, $id);
        }
        
        $this->db->execute();
        return $this->db->rowCount();
    }
    
    public function assignSiswaToKelas($siswaId, $kelasId) {
        $query = "UPDATE " . $this->table . " SET kelas_id = :kelas_id WHERE id = :id AND kelas_id IS NULL";
        $this->db->query($query);
        $this->db->bind('kelas_id', $kelasId);
        $this->db->bind('id', $siswaId);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getUnassignedSiswaByKeyword($keyword = null) {
    $sql = 'SELECT id, id_siswa, nama FROM ' . $this->table . ' WHERE kelas_id IS NULL';
    if (!empty($keyword)) {
        $sql .= ' AND (nama LIKE :keyword OR id_siswa LIKE :keyword)';
    }
    $sql .= ' ORDER BY nama ASC';
    
    $this->db->query($sql);
    
    if (!empty($keyword)) {
        $this->db->bind(':keyword', '%' . $keyword . '%');
    }
    
    return $this->db->resultSet();
}
    public function removeSiswaFromKelas($siswaId) {
        $query = "UPDATE " . $this->table . " SET kelas_id = NULL WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('id', $siswaId);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function removeSiswaFromKelasMassal($ids) {
        if (empty($ids)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $query = "UPDATE " . $this->table . " SET kelas_id = NULL WHERE id IN ({$placeholders})";
        
        $this->db->query($query);
        foreach ($ids as $k => $id) {
            $this->db->bind($k + 1, $id);
        }
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getSiswaByUserId($userId) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        return $this->db->single();
    }
    public function countSiswaByWaliId($waliKelasId) {
        $this->db->query(
            'SELECT COUNT(s.id) as total 
             FROM siswa s
             JOIN kelas k ON s.kelas_id = k.id
             WHERE k.wali_kelas_id = :wali_kelas_id'
        );
        $this->db->bind('wali_kelas_id', $waliKelasId);
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }
}