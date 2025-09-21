<div class="content">
<?php if ($data['siswa']): ?>
<div class="detail-card-wrapper">

    <div class="detail-actions">
        <a href="<?= BASEURL; ?>/guru/siswa" class="link-kembali">
             <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/></svg>
        </a>
    </div>

    <div class="detail-card">
        
        <div class="profile-sidebar">
            <div class="profile-image-container">
                <?php
                    $foto = $data['siswa']['foto'] ?? 'default.png';
                    $fotoUrl = BASEURL . '/img/siswa/' . htmlspecialchars($foto);
                    if (!file_exists('img/siswa/' . $foto) || empty($foto)) {
                        $fotoUrl = BASEURL . '/img/siswa/default.png';
                    }
                ?>
                <img src="<?= $fotoUrl; ?>" alt="Foto Siswa">
            </div>
            <h3><?= htmlspecialchars($data['siswa']['nama']); ?></h3>
        </div>

        <div class="info-section">
            <div class="info-grid-container">
                <div class="info-column">
                    <h4>Informasi Pribadi</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">ID Siswa (NIS)</span>
                            <span class="detail-value"><?= htmlspecialchars($data['siswa']['id_siswa']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Jenis Kelamin</span>
                            <span class="detail-value"><?= htmlspecialchars($data['siswa']['jenis_kelamin']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Tempat, Tanggal Lahir</span>
                            <span class="detail-value"><?= htmlspecialchars($data['siswa']['ttl'] ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Agama</span>
                            <span class="detail-value"><?= htmlspecialchars($data['siswa']['agama'] ?? '-'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="info-column">
                    <h4>Informasi Kontak</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">No. HP</span>
                            <span class="detail-value"><?= htmlspecialchars($data['siswa']['no_hp'] ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email</span>
                            <span class="detail-value"><?= htmlspecialchars($data['siswa']['email'] ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Alamat</span>
                            <span class="detail-value"><?= htmlspecialchars($data['siswa']['alamat'] ?? '-'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php else: ?>
    <p style="text-align:center; margin-top: 2rem;">Data siswa tidak ditemukan.</p>
<?php endif; ?>

</div>