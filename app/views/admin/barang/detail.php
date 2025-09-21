<?php

require_once '../app/views/layouts/admin_header.php'; ?>

<div class="content">
<?php if ($data['item']): ?>
<div class="detail-card-wrapper">
<div class="detail-actions">
<a href="<?= BASEURL; ?>/admin/barang" class="link-kembali">
<i class="fas fa-chevron-left"></i>
</a>
</div>
<div class="detail-card">

        <div class="profile-sidebar">
            <div class="profile-image-container">
                <?php
                    $gambar_url = (!empty($data['item']['gambar']) && file_exists('img/barang/' . $data['item']['gambar']))
                        ? BASEURL . '/img/barang/' . htmlspecialchars($data['item']['gambar'])
                        : BASEURL . '/img/siswa/images.png';
                ?>
                <img src="<?= $gambar_url; ?>" alt="Foto Barang">
            </div>
            <h3><?= htmlspecialchars($data['item']['nama_barang']); ?></h3>
        </div>

        <div class="info-section">
            <div class="info-grid-container">
                <div class="info-column">
                     <h4>Informasi Barang</h4>
                     <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Kode Barang</span>
                            <span class="detail-value"><?= htmlspecialchars($data['item']['kode_barang']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Jumlah Stok</span>
                            <span class="detail-value"><?= htmlspecialchars($data['item']['jumlah']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status</span>
                            <span class="detail-value"><?= htmlspecialchars($data['item']['status']); ?></span>
                        </div>
                     </div>
                </div>

                <div class="info-column">
                     <h4>Detail Tambahan</h4>
                     <div class="detail-grid">
                         <div class="detail-item">
                            <span class="detail-label">Kondisi</span>
                            <span class="detail-value"><?= htmlspecialchars($data['item']['kondisi']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Lokasi Penyimpanan</span>
                            <span class="detail-value"><?= htmlspecialchars($data['item']['lokasi_penyimpanan'] ?? '-'); ?></span>
                        </div>
                         <div class="detail-item">
                            <span class="detail-label">Tanggal Pembelian</span>
                            <span class="detail-value"><?= !empty($data['item']['tanggal_pembelian']) ? date('d F Y', strtotime($data['item']['tanggal_pembelian'])) : '-'; ?></span>
                        </div>
                     </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php else: ?>
<p style="text-align:center; margin-top: 2rem;">Data barang tidak ditemukan.</p>
<?php endif; ?>

</div>

<?php require_once '../app/views/layouts/admin_footer.php'; ?>
