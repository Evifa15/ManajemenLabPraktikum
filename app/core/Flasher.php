<?php

class Flasher {
    public static function setFlash($pesan, $aksi, $tipe) {
        $_SESSION['flash'] = [
            'pesan' => $pesan,
            'aksi'  => $aksi,
            'tipe'  => $tipe
        ];
    }

    public static function flash() {
        if (isset($_SESSION['flash'])) {
            $tipe = $_SESSION['flash']['tipe'];
            $icon = '';
            $title = htmlspecialchars($_SESSION['flash']['pesan']);
            $message = htmlspecialchars($_SESSION['flash']['aksi']);

            switch ($tipe) {
                case 'success':
                    $icon = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>';
                    break;
                case 'danger':
                    $icon = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/></svg>';
                    break;
            }

            echo '<div class="alert alert-' . $tipe . '" role="alert">
                    <div class="alert-icon">
                        ' . $icon . '
                    </div>
                    <div class="alert-content">
                        ' . (!empty($title) ? '<p class="alert-title">' . $title . '</p>' : '') . '
                        ' . (!empty($message) ? '<p class="alert-message">' . $message . '</p>' : '') . '
                    </div>
                  </div>';
            
            unset($_SESSION['flash']);
        }
    }
}