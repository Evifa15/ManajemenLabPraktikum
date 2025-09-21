
<?php

class DashboardController {
    /**
     * Menampilkan halaman dashboard sesuai dengan peran user.
     * Method ini akan dipanggil setelah user berhasil login.
     */
    public function index() {
        // Cek apakah user sudah login dengan melihat session
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            // Jika belum login, tendang ke halaman awal
            header('Location: ' . BASEURL);
            exit;
        }

        $role = $_SESSION['role'];
        $username = $_SESSION['username'];
        
        $data = [
            'title' => 'Dashboard ' . ucfirst($role),
            'username' => $username
        ];

        // Tampilkan view dashboard berdasarkan peran dari session
        // Ini memastikan user hanya bisa mengakses dashboard perannya sendiri
        $this->view($role . '/index', $data);
    }

    // Helper function untuk memuat view
    public function view($view, $data = []) {
        extract($data);
        require_once '../app/views/layouts/header.php';
        require_once '../app/views/' . $view . '.php';
        require_once '../app/views/layouts/footer.php';
    }
}