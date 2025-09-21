<div class="content">
    <div class="main-table-container">
        <div class="table-controls-container" style="justify-content: space-between; align-items: center;">
           
            <form id="searchForm" action="<?= BASEURL; ?>/guru/riwayat" method="GET" class="search-form-container">
                <select name="filter_status" class="filter-dropdown" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Menunggu Verifikasi" <?= ($data['filters']['status'] ?? '') == 'Menunggu Verifikasi' ? 'selected' : '' ?>>Diperiksa</option>
                    <option value="Disetujui" <?= ($data['filters']['status'] ?? '') == 'Disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="Ditolak" <?= ($data['filters']['status'] ?? '') == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    <option value="Selesai" <?= ($data['filters']['status'] ?? '') == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
                <select name="filter_waktu" class="filter-dropdown" onchange="this.form.submit()">
                    <option value="terbaru" <?= ($data['filters']['waktu'] ?? '') == 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
                    <option value="terlama" <?= ($data['filters']['waktu'] ?? '') == 'terlama' ? 'selected' : '' ?>>Terlama</option>
                </select>
                <div class="search-input-wrapper">
                    <input type="text" id="searchInput" name="search" placeholder="Silahkan Lakukan Pencarian Disini..." value="<?= htmlspecialchars($data['filters']['keyword'] ?? '') ?>">
                    <button type="submit" class="search-submit-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#555"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Barang yang Dipinjam</th>
                        <th>Jumlah</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['history'])): ?>
                        <?php $no = ($data['halaman_aktif'] - 1) * 10 + 1;
                        foreach ($data['history'] as $item): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <?= htmlspecialchars($item['nama_siswa']); ?><br>
                                <small style="color: #666;"><?= htmlspecialchars($item['id_siswa']); ?></small>
                            </td>
                            <td><?= htmlspecialchars($item['nama_kelas']); ?></td>
                            <td>
                                <?= htmlspecialchars($item['nama_barang']); ?><br>
                                <small style="color: #666;"><?= htmlspecialchars($item['kode_barang']); ?></small>
                            </td>
                            <td><?= htmlspecialchars($item['jumlah_pinjam']); ?></td>
                            <td><?= date('d/m/Y', strtotime($item['tanggal_pinjam'])); ?></td>
                            <td><?= $item['tanggal_kembali'] ? date('d/m/Y', strtotime($item['tanggal_kembali'])) : '-'; ?></td>
                            <td>
                                <?php
                                    $status_class = strtolower(str_replace(' ', '-', $item['status']));
                                ?>
                                <span class="status-badge status-<?= $status_class; ?>">
                                    <?php
                                        if ($item['status'] === 'Menunggu Verifikasi') {
                                            echo 'Diperiksa';
                                        } else {
                                            echo htmlspecialchars($item['status']);
                                        }
                                    ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">Tidak ada riwayat peminjaman dari siswa wali Anda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($data['total_halaman']) && $data['total_halaman'] > 1): ?>
        <div class="pagination-container">
            <?php
                $queryParams = http_build_query($data['filters']);
            ?>
            <a href="<?= BASEURL ?>/guru/riwayat/<?= max(1, $data['halaman_aktif'] - 1) ?>?<?= $queryParams ?>" class="pagination-btn <?= ($data['halaman_aktif'] <= 1) ? 'disabled' : '' ?>">Sebelumnya</a>
            <div class="page-numbers">
                <?php for ($i = 1; $i <= $data['total_halaman']; $i++): ?>
                    <a href="<?= BASEURL ?>/guru/riwayat/<?= $i ?>?<?= $queryParams ?>" class="page-link <?= ($i == $data['halaman_aktif']) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <a href="<?= BASEURL ?>/guru/riwayat/<?= min($data['total_halaman'], $data['halaman_aktif'] + 1) ?>?<?= $queryParams ?>" class="pagination-btn <?= ($data['halaman_aktif'] >= $data['total_halaman']) ? 'disabled' : '' ?>">Berikutnya</a>
        </div>
        <?php endif; ?>
    </div>
</div>