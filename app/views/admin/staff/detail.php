<?php
// File: ManajemenLabPraktikum/app/views/admin/staff/detail.php

require_once '../app/views/layouts/admin_header.php'; ?>

<div class="content">
    <?php if ($data['staff']): ?>
    <div class="detail-card-wrapper">
        <div class="detail-actions">
            <a href="<?= BASEURL; ?>/admin/pengguna/staff" class="link-kembali">
                <i class="fas fa-chevron-left"></i>
            </a>
        </div>

        <div class="detail-card">
            
            <div class="profile-sidebar">
                <div class="profile-image-container">
                    <?php
                        // Memastikan baris ini berada di dalam tag PHP yang benar
                        $foto = $data['staff']['foto'] ?? 'default.png';
                        // Memeriksa keberadaan file di folder 'staff'
                        $fotoPath = 'img/staff/' . htmlspecialchars($foto);
                        $fotoUrl = BASEURL . '/' . $fotoPath;
                        if (!file_exists($fotoPath) || empty($foto)) {
                            $fotoUrl = BASEURL . '/img/siswa/default.png';
                        }
                    ?>
                    <img src="<?= $fotoUrl; ?>" alt="Foto Staff">
                </div>
                <h3><?= htmlspecialchars($data['staff']['nama']); ?></h3>
            </div>

            <div class="info-section">
                <div class="info-grid-container">
                    <div class="info-column">
                        <h4>Informasi Pribadi</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">ID Staff</span>
                                <span class="detail-value"><?= htmlspecialchars($data['staff']['id_staff']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Jenis Kelamin</span>
                                <span class="detail-value"><?= htmlspecialchars($data['staff']['jenis_kelamin']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Tempat, Tanggal Lahir</span>
                                <span class="detail-value"><?= htmlspecialchars($data['staff']['ttl'] ?? '-'); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Agama</span>
                                <span class="detail-value"><?= htmlspecialchars($data['staff']['agama'] ?? '-'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="info-column">
                        <h4>Informasi Kontak</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">No. HP</span>
                                <span class="detail-value"><?= htmlspecialchars($data['staff']['no_hp'] ?? '-'); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?= htmlspecialchars($data['staff']['email'] ?? '-'); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Alamat</span>
                                <span class="detail-value"><?= htmlspecialchars($data['staff']['alamat'] ?? '-'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
        <p style="text-align:center; margin-top: 2rem;">Data staff tidak ditemukan.</p>
    <?php endif; ?>

</div>

<?php require_once '../app/views/layouts/admin_footer.php'; ?>