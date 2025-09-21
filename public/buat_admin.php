<?php
// Skrip untuk membuat akun admin pertama kali.
// Simpan file ini di dalam folder /public/ lalu akses melalui browser.

// Memuat file-file inti yang dibutuhkan
require_once '../config/config.php';
require_once '../app/core/Database.php';
require_once '../app/models/Staff_model.php'; // Kita gunakan Staff_model karena ia membuat user dengan role 'admin'

echo "<!DOCTYPE html><html><head><title>Admin Creator</title>";
echo "<style>body{font-family: sans-serif; padding: 20px; line-height: 1.6;} .success{color: green; font-weight: bold;} .error{color: red; font-weight: bold;}</style>";
echo "</head><body>";
echo "<h1>Membuat Akun Admin...</h1>";

try {
    // Inisialisasi model
    $staffModel = new Staff_model();

    // Data untuk admin baru.
    // Password akan sama dengan ID Staff, yaitu 'admin001'
    $adminData = [
        'nama'          => 'admin',
        'id_staff'      => 'admin001',
        'jenis_kelamin' => 'Laki-laki',
        'ttl'           => null,
        'agama'         => null,
        'alamat'        => null,
        'no_hp'         => null,
        'email'         => 'admin@sistem.com'
    ];
    
    // Panggil fungsi untuk membuat user dan staff sekaligus
    $result = $staffModel->createStaffAndUserAccount($adminData);

    if ($result > 0) {
        echo "<p class='success'>SUKSES! Akun admin berhasil dibuat.</p>";
        echo "<p>Silakan login dengan detail berikut:</p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> admin</li>";
        echo "<li><strong>Password:</strong> admin001</li>";
        echo "</ul>";
        echo "<p class='error'><strong>PENTING:</strong> Setelah berhasil login, segera HAPUS file 'buat_admin.php' ini dari folder /public/ Anda demi keamanan.</p>";
    } else {
        echo "<p class='error'>GAGAL! Akun admin gagal dibuat. Kemungkinan username 'admin' atau ID Staff 'admin001' sudah ada di database. Silakan periksa tabel 'users' dan 'staff'.</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>Terjadi error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";