<?php require_once '../app/views/layouts/admin_header.php'; ?>

<div class="content">
    <div class="main-table-container">
        <div class="table-controls-container">
            <div class="laporan-controls-row">
                <form action="<?= BASEURL ?>/admin/laporan" method="get" class="search-form-container">
                    <div class="search-input-wrapper">
                        <input type="text" id="search" name="search" placeholder="Silahkan Lakukan Pencarian Disini..." value="<?= htmlspecialchars($data['filters']['keyword'] ?? '') ?>">
                        <button type="submit" class="search-submit-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#555"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                        </button>
                    </div>
                </form>
                <div class="actions-container">
                    <a href="<?= BASEURL; ?>/admin/unduh-laporan?<?= http_build_query($data['filters']); ?>" class="btn btn-download">Unduh Laporan (.xlsx)</a>
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Peminjam</th>
                        <th>Barang Dipinjam</th>
                        <th>Verifikator</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Ket</th> <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['history'])):
                        // ... (kode foreach)
                        foreach($data['history'] as $item): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <?= htmlspecialchars($item['nama_peminjam']); ?><br>
                                <small style="color:#666">ID: <?= htmlspecialchars($item['no_id_peminjam']); ?></small>
                            </td>
                            <td><?= htmlspecialchars($item['nama_barang']); ?></td>
                            <td>
                                <?= htmlspecialchars($item['nama_verifikator'] ?? '-'); ?><br>
                                <small style="color:#666">NIP: <?= htmlspecialchars($item['nip_verifikator'] ?? '-'); ?></small>
                            </td>
                            <td><?= date('d/m/Y', strtotime($item['tanggal_pinjam'])); ?></td>
                            <td><?= $item['tanggal_kembali'] ? date('d/m/Y', strtotime($item['tanggal_kembali'])) : '-'; ?></td>
                            <td><?= htmlspecialchars($item['keterangan'] ?? '-'); ?></td> <td>
                                <?php
                                    $status_class = strtolower(str_replace(' ', '-', $item['status']));
                                    $status_display = ($item['status'] === 'Menunggu Verifikasi') ? 'Diperiksa' : htmlspecialchars($item['status']);
                                ?>
                                <span class='status-badge status-<?= $status_class ?>'><?= $status_display ?></span>
                            </td>
                        </tr>
                    <?php endforeach;
                    else: ?>
                        <tr><td colspan="8" style="text-align: center;">Tidak ada data riwayat yang cocok.</td></tr> <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($data['total_halaman']) && $data['total_halaman'] > 1): ?>
        <div class="pagination-container">
            <?php $queryParams = http_build_query($data['filters']); ?>
            <a href="<?= BASEURL ?>/admin/laporan/<?= max(1, $data['halaman_aktif'] - 1) ?>?<?= $queryParams ?>" class="pagination-btn <?= ($data['halaman_aktif'] <= 1) ? 'disabled' : '' ?>">Sebelumnya</a>
            <div class="page-numbers">
                <?php for ($i = 1; $i <= $data['total_halaman']; $i++): ?>
                    <a href="<?= BASEURL ?>/admin/laporan/<?= $i ?>?<?= $queryParams ?>" class="page-link <?= ($i == $data['halaman_aktif']) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <a href="<?= BASEURL ?>/admin/laporan/<?= min($data['total_halaman'], $data['halaman_aktif'] + 1) ?>?<?= $queryParams ?>" class="pagination-btn <?= ($data['halaman_aktif'] >= $data['total_halaman']) ? 'disabled' : '' ?>">Berikutnya</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/layouts/admin_footer.php'; ?>