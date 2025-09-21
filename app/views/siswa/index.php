<div class="content">
    <div class="dashboard-container">
        <div class="dashboard-top-grid">
            <div class="overview-container">
                <div class="overview-card card-yellow">
                    <div class="card-icon-wrapper">
                        <img width="28" height="28" src="https://img.icons8.com/ios-filled/50/FFFFFF/holding-box.png" alt="holding-box"/>
                    </div>
                    <span>Peminjaman Aktif</span>
                    <p><?= htmlspecialchars($data['peminjamanAktifCount']); ?></p>
                </div>
                <div class="overview-card card-green">
                    <div class="card-icon-wrapper">
                        <img width="20" height="20" src="https://img.icons8.com/glyph-neue/64/FFFFFF/data-pending.png" alt="data-pending"/>
                    </div>
                    <span>Menunggu Persetujuan</span>
                    <p><?= htmlspecialchars($data['menungguVerifikasiCount']); ?></p>
                </div>
                <div class="overview-card card-dark">
                    <div class="card-icon-wrapper">
                       <img width="24" height="24" src="https://img.icons8.com/glyph-neue/64/FFFFFF/check-all.png" alt="check-all"/>
                    </div>
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
    <div class="modal-content modal-lg">
        <form action="<?= BASEURL; ?>/siswa/proses-pengembalian" method="post" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="peminjaman_id" id="peminjaman_id">

                <h4 class="form-section-title">Informasi Peminjaman</h4>
                <div class="info-peminjaman-box">
                    <div class="info-item">
                        <div class="info-label">Nama Barang</div>
                        <div class="info-value"><span id="modal_nama_barang"></span></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kode Barang</div>
                        <div class="info-value"><span id="modal_kode_barang"></span></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Jumlah Dipinjam</div>
                        <div class="info-value"><span id="modal_jumlah_pinjam"></span></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Wajib Kembali</div>
                        <div class="info-value"><span id="modal_wajib_kembali"></span></div>
                    </div>
                </div>

                <h4 class="form-section-title">Detail Pengembalian</h4>
                <div class="form-group">
                    <label for="tanggal_kembali">Tanggal Kembali (Otomatis)</label>
                    <input type="date" id="tanggal_kembali" name="tanggal_kembali" readonly>
                </div>
                <div class="form-group">
                    <label for="bukti_kembali">Foto Bukti Pengembalian</label>
                    <input type="file" id="bukti_kembali" name="bukti_kembali" accept="image/*" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn-submit">Konfirmasi Pengembalian</button>
            </div>
        </form>
    </div>
</div>
</div>