<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?> - Lab Praktikum</title>
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/main-style.css?v=2.0">
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/siswa-style.css?v=2.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="app-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Tutup/Buka Sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor">
                        <path d="M0 0h24v24H0V0z" fill="none"/>
                        <path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/>
                    </svg>
                </button>       
            </div>
            <div class="sidebar-content-wrapper">
                 <nav>
                    <div class="sidebar-section">
                        <a href="<?= BASEURL; ?>/siswa/dashboard" class="sidebar-item" id="nav-dashboard-siswa">
                            <span class="sidebar-icon-wrapper">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m0 0v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            </span>
                            <span>Dashboard</span>
                        </a>
                        <a href="<?= BASEURL; ?>/siswa/katalog" class="sidebar-item" id="nav-katalog-siswa">
                            <span class="sidebar-icon-wrapper">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                            </span>
                            <span>Katalog Barang</span>
                        </a>
                        <a href="<?= BASEURL; ?>/siswa/riwayat" class="sidebar-item" id="nav-riwayat-siswa">
                            <span class="sidebar-icon-wrapper">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </span>
                            <span>Riwayat Peminjaman</span>
                        </a>
                    </div>

                    <div class="sidebar-section">
                        <a href="<?= BASEURL; ?>/siswa/profile" class="sidebar-item" id="nav-profile-siswa">
                            <span class="sidebar-icon-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM12 5c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                            </span>
                            <span>Profil</span>
                        </a>
                    </div>
                </nav>
            </div>
            <div class="logout-button-wrapper">
                <a href="<?= BASEURL; ?>/logout" class="logout-button">
                    <span class="sidebar-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="20" viewBox="0 0 24 24" width="20"><path d="M0 0h24v24H0z" fill="none"/><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                    </span>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <div class="main-content">
            <div class="content-area">