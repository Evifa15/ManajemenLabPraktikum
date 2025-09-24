<?php
require_once '../config/config.php';
require_once '../app/core/Database.php';
require_once '../app/models/Staff_model.php';

echo "<!DOCTYPE html><html><head><title>Admin Creator</title>";
echo "</head><body>";
echo "<h1>Membuat Akun Admin...</h1>";
try {
    $staffModel = new Staff_model();
    $adminData = [
        'nama'          => 'Administrator',
        'id_staff'      => '001', 
        'jenis_kelamin' => 'Laki-laki',
        'ttl'           => null, 
        'agama'         => null,
        'alamat'        => null,
        'no_hp'         => null,
        'email'         => 'admin@sistem.com'
    ];
    
    $result = $staffModel->createStaffAndUserAccount($adminData);

    if ($result > 0) {
        echo "<p class='success'>SUKSES! Akun admin berhasil dibuat.</p>";
        echo "<p>Silakan login dengan detail berikut:</p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> " . htmlspecialchars($adminData['id_staff']) . "</li>";
        echo "<li><strong>Password:</strong> " . htmlspecialchars($adminData['id_staff']) . "</li>";
        echo "</ul>";
        echo "<p class='error'><strong>PENTING:</strong> Segera HAPUS file 'buat_admin.php' ini dari folder /public/.</p>";
    } else {
        echo "<p class='error'>GAGAL! Akun admin gagal dibuat. Kemungkinan username atau ID Staff 'admin001' sudah ada di database.</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>Terjadi error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";