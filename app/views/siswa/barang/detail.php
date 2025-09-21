<div class="content">
    <div class="dashboard-main-card">
        <div class="dashboard-card-header">
            <button class="sidebar-toggle-button" id="toggle-btn">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
            </button>
        </div>

        <div class="dashboard-top-grid">
            <div class="overview-container">
                <div class="overview-card card-yellow">
                    <div class="card-icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    <span>Peminjaman Aktif</span>
                    <p><?= htmlspecialchars($data['peminjamanAktifCount']); ?></p>
                </div>
                <div class="overview-card card-green">
                    <div class="card-icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg></div>
                    <span>Menunggu Persetujuan</span>
                    <p><?= htmlspecialchars($data['menungguVerifikasiCount']); ?></p>
                </div>
                <div class="overview-card card-dark">
                    <div class="card-icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    <span>Riwayat Selesai</span>
                    <p><?= htmlspecialchars($data['riwayatSelesaiCount']); ?></p>
                </div>
            </div>

            <div class="dashboard-banner">
                <div class="banner-text">
                    <h3>Hai, <?= htmlspecialchars(explode(' ', $data['username'])[0]); ?>!</h3>
                    <p>Selamat Datang kembali.</p>
                    <a href="<?= BASEURL; ?>/siswa/katalog" class="btn-banner">Ajukan Pinjam</a>
                </div>
                
                <a href="<?= BASEURL; ?>/siswa/profile" class="banner-profile-pic">
                    <?php 
                        $foto = $data['profile']['foto'] ?? 'default.png';
                        if (!file_exists('img/siswa/' . $foto) || empty($foto)) {
                            $foto = 'default.png';
                        }
                    ?>
                    <img src="<?= BASEURL . '/img/siswa/' . htmlspecialchars($foto); ?>" alt="Foto Profil">
                </a>
            </div>
        </div>

        <div class="peminjaman-list-container">
            <div class="peminjaman-header">
                <h4 class="section-title">Barang yang Sedang Dipinjam</h4>
                <?php if ($data['peminjamanAktifCount'] > 2): ?>
                    <a href="<?= BASEURL; ?>/siswa/riwayat" class="btn-lihat-semua">Lihat Semua ></a>
                <?php endif; ?>
            </div>

            <?php if (!empty($data['peminjamanAktifList'])): ?>
                <?php foreach ($data['peminjamanAktifList'] as $item): ?>
                    <div class="peminjaman-item-card">
                        <div class="item-info">
                            <h5><?= htmlspecialchars($item['nama_barang']); ?></h5>
                            <?php
                                $tglWajibKembali = strtotime($item['tanggal_wajib_kembali']);
                                $hariIni = strtotime(date('Y-m-d'));
                                $terlambat = $tglWajibKembali < $hariIni;
                            ?>
                            <span class="due-date <?= $terlambat ? 'late' : '' ?>">
                                Wajib Kembali: <?= date('d F Y', $tglWajibKembali); ?>
                            </span>
                        </div>
                        <button type="button" class="btn pengembalian-btn" data-id="<?= $item['id']; ?>">
                            Kembalikan
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="peminjaman-item-card empty">
                    <p>Tidak ada barang yang sedang Anda pinjam saat ini.</p>
                    <a href="<?= BASEURL; ?>/siswa/katalog" class="btn">Pinjam Barang Sekarang</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="pengembalianModal" class="modal">
    </div>