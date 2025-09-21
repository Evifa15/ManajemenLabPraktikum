<div class="content">
    <div class="dashboard-container">
        <div class="dashboard-top-grid">
            <div class="overview-container">
                <div class="overview-card card-yellow">
                    <div class="card-icon-wrapper"><img width="22" height="22" src="https://img.icons8.com/glyph-neue/64/FFFFFF/home--v1.png" alt="home--v1"/>
                    </div>
                    <span>Jumlah Kelas</span>
                    <p><?= htmlspecialchars($data['kelasCount']); ?></p>
                </div>
                <div class="overview-card card-green">
                    <div class="card-icon-wrapper">
                        <img width="23" height="23" src="https://img.icons8.com/fluency-systems-filled/48/FFFFFF/present.png" alt="present"/>
                    </div>
                    <span>Permintaan Verifikasi</span>
                    <p><?= htmlspecialchars($data['verifikasiCount']); ?></p>
                </div>
                <div class="overview-card card-dark">
                    <div class="card-icon-wrapper">
                       <img width="23" height="23" src="https://img.icons8.com/ios-filled/50/FFFFFF/conference-call.png" alt="conference-call"/>
                    </div>
                    <span>Jumlah Siswa</span>
                    <p><?= htmlspecialchars($data['siswaCount']); ?></p>
                </div>
            </div>

            <div class="dashboard-banner">
                <div class="banner-text">
                    <h3>Hai, <?= htmlspecialchars(explode(' ', $data['username'])[0]); ?>!</h3>
                    <p>Selamat Datang kembali.</p>
                    <a href="<?= BASEURL; ?>/guru/riwayat" class="btn-banner">Lihat Riwayat</a>
                </div>
                <a href="<?= BASEURL; ?>/guru/profile" class="banner-profile-pic">
                    <?php 
                        $foto = $data['profile']['foto'] ?? 'default.png';
                        // Menggunakan path yang benar untuk foto guru
                        $fotoPath = 'img/guru/' . htmlspecialchars($foto);
                        if (!file_exists($fotoPath) || empty($foto) || !isset($data['profile']['foto'])) {
                            // Jika foto spesifik tidak ada, gunakan foto default
                            $fotoUrl = BASEURL . '/img/siswa/default.png';
                        } else {
                            $fotoUrl = BASEURL . '/' . $fotoPath;
                        }
                    ?>
                    <img src="<?= $fotoUrl; ?>" alt="Foto Profil">
                </a>
            </div>
        </div>

        <div class="peminjaman-list-container">
            <div class="peminjaman-header">
                <h4 class="section-title">Verifikasi Peminjaman Terbaru</h4>
                <?php if ($data['verifikasiCount'] > 3): ?>
                    <a href="<?= BASEURL; ?>/guru/verifikasi" class="btn-lihat-semua">Lihat Semua ></a>
                <?php endif; ?>
            </div>
            
            <?php Flasher::flash(); ?>

            <?php if (!empty($data['requests'])): ?>
                <?php foreach ($data['requests'] as $req): ?>
                    <div class="peminjaman-item-card">
                        <div class="item-info">
                            <h5><?= htmlspecialchars($req['nama_barang']); ?> (<?= htmlspecialchars($req['jumlah_pinjam']); ?>)</h5>
                            <span class="due-date">
                                Diajukan oleh: <strong><?= htmlspecialchars($req['nama_siswa']); ?></strong>
                            </span>
                        </div>
                        <div class="item-actions">
                             <form action="<?= BASEURL; ?>/guru/proses-verifikasi" method="post" style="display:inline;">
                                <input type="hidden" name="peminjaman_id" value="<?= $req['id']; ?>">
                                <button type="submit" name="status" value="Disetujui" class="btn btn-success">Setujui</button>
                            </form>
                            <button type="button" class="btn btn-danger open-modal-tolak-btn" data-id="<?= $req['id']; ?>">Tolak</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="peminjaman-item-card empty">
                    <p>Tidak ada permintaan verifikasi baru saat ini. Pekerjaan bagus!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="tolakModal" class="modal">
    <div class="modal-content" style="max-width: 500px; padding: 0;">
        <form id="tolakForm" method="POST" action="<?= BASEURL; ?>/guru/proses-verifikasi">
            <div class="modal-body">
                <input type="hidden" id="peminjamanIdTolak" name="peminjaman_id">
                <input type="hidden" name="status" value="Ditolak">
                <div class="form-group">
                    <label for="alasan-tolak">Alasan Penolakan</label>
                    <textarea id="alasan-tolak" name="keterangan" rows="4" placeholder="Siswa akan melihat alasan ini. Pastikan alasan jelas dan informatif." required></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak Permintaan</button>
            </div>
        </form>
    </div>
</div>