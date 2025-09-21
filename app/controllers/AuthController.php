<?php
// Memuat file-file yang dibutuhkan sekali saja
require_once '../app/core/Flasher.php';
require_once '../app/models/User_model.php';

class AuthController {

    public function showPilihPeran() {
        $data = ['title' => 'Pilih Peran'];
        $this->view('auth/pilih-peran', $data);
    }

    public function showLogin() {
        if (!isset($_GET['role']) || empty($_GET['role'])) {
            header('Location: ' . BASEURL);
            exit;
        }
        $role = htmlspecialchars($_GET['role']);
        $allowed_roles = ['admin', 'guru', 'siswa'];
        if (!in_array($role, $allowed_roles)) {
            header('Location: ' . BASEURL);
            exit;
        }
        $data = [
            'title' => 'Login ' . ucfirst($role),
            'role' => $role
        ];
        $this->view('auth/login', $data);
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL); exit;
        }

        $username = trim(htmlspecialchars($_POST['username']));
        $password = $_POST['password'];
        $role = htmlspecialchars($_POST['role']);

        $userModel = new User_model();
        $user = $userModel->findUserByUsernameAndRole($username, $role);

        if ($user && password_verify($password, $user['password'])) {
            if (isset($_SESSION['login_attempts'][$username])) {
                unset($_SESSION['login_attempts'][$username]);
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // --- REVISI DI SINI ---
            // PENGALIHAN DINAMIS BERDASARKAN PERAN PENGGUNA
            header('Location: ' . BASEURL . '/' . $user['role'] . '/dashboard');
            exit;
        } else {
            if (!isset($_SESSION['login_attempts'][$username])) {
                $_SESSION['login_attempts'][$username] = 1;
            } else {
                $_SESSION['login_attempts'][$username]++;
            }
            $attempts = $_SESSION['login_attempts'][$username];
            if ($attempts < 4) {
                Flasher::setFlash('Nama Pengguna atau Kata Sandi Salah.', '', 'danger');
            } else {
                Flasher::setFlash('Silahkan Hubungi admin untuk Konfirmasi.', '', 'danger');
            }
            header('Location: ' . BASEURL . '/login?role=' . $role);
            exit;
        }
    }

    /**
     * Menghapus semua data session dan mengarahkan ke halaman utama.
     */
    public function logout() {
        session_start();
        $_SESSION = array();
        session_destroy();
        header('location: ' . BASEURL);
        exit;
    }

    public function view($view, $data = []) {
        extract($data);
        require_once '../app/views/layouts/header.php';
        require_once '../app/views/' . $view . '.php'; 
        require_once '../app/views/layouts/footer.php';
    }
}

