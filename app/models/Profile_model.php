<?php

class Profile_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Mengambil data profil lengkap berdasarkan peran dan user_id.
     * @param string $role Peran pengguna (admin, guru, siswa).
     * @param int $userId ID dari tabel 'users'.
     * @return mixed Array data profil jika ditemukan, false jika tidak.
     */
     public function getProfileByRoleAndUserId($role, $userId) {
        // Tentukan tabel mana yang akan di-query berdasarkan peran
        $table = '';
        if ($role === 'guru') {
            $table = 'guru';
        } elseif ($role === 'siswa') {
            $table = 'siswa';
        } else {
            return false; // Peran tidak valid atau tidak memerlukan profil spesifik
        }

        $this->db->query("SELECT * FROM {$table} WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        
        return $this->db->single();
    }
}
