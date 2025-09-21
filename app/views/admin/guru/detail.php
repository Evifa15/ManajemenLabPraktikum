<?php

require_once '../app/views/layouts/admin_header.php'; ?>

<div class="content">
<?php if ($data['guru']): ?>
<div class="detail-card-wrapper">
<div class="detail-actions">
<a href="<?= BASEURL; ?>/admin/pengguna/guru" class="link-kembali">
<i class="fas fa-chevron-left"></i>
</a>
</div>

    <div class="detail-card">
        <div class="profile-sidebar">
            <div class="profile-image-container">
                <?php
                    $foto = $data['guru']['foto'] ?? 'default.png';
                    $fotoUrl = BASEURL . '/img/guru/' . htmlspecialchars($foto);
                    if (!file_exists('img/guru/' . $foto) || empty($foto)) {
                        $fotoUrl = BASEURL . '/img/siswa/default.png';
                    }
                ?>
                <img src="<?= $fotoUrl; ?>" alt="Foto Guru">
            </div>
            <h3><?= htmlspecialchars($data['guru']['nama']); ?></h3>
        </div>

        <div class="info-section">
            <div class="info-grid-container">
                <div class="info-column">
                    <h4>Informasi Pribadi</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">ID Guru (NIP)</span>
                            <span class="detail-value"><?= htmlspecialchars($data['guru']['nip']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Jenis Kelamin</span>
                            <span class="detail-value"><?= htmlspecialchars($data['guru']['jenis_kelamin']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Tempat, Tanggal Lahir</span>
                            <span class="detail-value"><?= htmlspecialchars($data['guru']['ttl'] ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Agama</span>
                            <span class="detail-value"><?= htmlspecialchars($data['guru']['agama'] ?? '-'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="info-column">
                    <h4>Informasi Kontak</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">No. HP</span>
                            <span class="detail-value"><?= htmlspecialchars($data['guru']['no_hp'] ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email</span>
                            <span class="detail-value"><?= htmlspecialchars($data['guru']['email'] ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Alamat</span>
                            <span class="detail-value"><?= htmlspecialchars($data['guru']['alamat'] ?? '-'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
    <p style="text-align:center; margin-top: 2rem;">Data guru tidak ditemukan.</p>
<?php endif; ?>

</div>

<?php require_once '../app/views/layouts/admin_footer.php'; ?>
