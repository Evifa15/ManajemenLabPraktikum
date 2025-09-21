<div class="content">
    <div class="main-table-container">
        <div class="table-controls-container">
            <form id="searchForm" action="<?= BASEURL; ?>/siswa/riwayat" method="GET" class="search-form-container">
                <select name="filter_status" class="filter-dropdown" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Menunggu Verifikasi" <?= (isset($data['filters']['status']) && $data['filters']['status'] == 'Menunggu Verifikasi') ? 'selected' : ''; ?>>Diperiksa</option>
                    <option value="Disetujui" <?= (isset($data['filters']['status']) && $data['filters']['status'] == 'Disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="Selesai" <?= (isset($data['filters']['status']) && $data['filters']['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                    <option value="Ditolak" <?= (isset($data['filters']['status']) && $data['filters']['status'] == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                </select> 
                <select name="filter_waktu" class="filter-dropdown" onchange="this.form.submit()">
                    <option value="terbaru" <?= (isset($data['filters']['waktu']) && $data['filters']['waktu'] == 'terbaru') ? 'selected' : ''; ?>>Terbaru</option>
                    <option value="terlama" <?= (isset($data['filters']['waktu']) && $data['filters']['waktu'] == 'terlama') ? 'selected' : ''; ?>>Terlama</option>
                </select>  
                
                <div class="search-input-wrapper">
                    <input type="text" id="searchInput" name="search" placeholder="Cari barang atau keperluan..." value="<?= htmlspecialchars($data['filters']['keyword'] ?? '') ?>">
                    <button type="submit" class="search-submit-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#555"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    </button>
                </div>
            </form>
        </div>
        <div class="table-wrapper" style="margin-top: 0px;">
            <?php Flasher::flash(); ?>
            <table>
                <thead>
                    <tr>
                        <th>Barang yang Dipinjam</th>
                        <th>Jumlah</th>
                        <th>Keperluan</th>
                        <th>Ket</th>
                        <th>Tanggal Pinjam</th>
                        <th>Rencana Kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['history'])): ?>
                        <?php 
                            $no = ($data['halaman_aktif'] - 1) * 10 + 1;
                            foreach ($data['history'] as $item): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($item['nama_barang']); ?><br>
                                    <small style="color: #666;"><?= htmlspecialchars($item['kode_barang']); ?></small>
                                </td>
                                <td><?= htmlspecialchars($item['jumlah_pinjam']); ?></td>
                                <td><?= htmlspecialchars($item['keperluan']); ?></td>
                                <td><?= htmlspecialchars($item['keterangan'] ?? '-'); ?></td>
                                <td><?= date('d/m/Y', strtotime($item['tanggal_pinjam'])); ?></td>
                                <td><?= date('d/m/Y', strtotime($item['tanggal_wajib_kembali'])); ?></td>
                                <td>
                                    <?php
                                        $status_class = strtolower(str_replace(' ', '-', $item['status']));
                                        $status_display = htmlspecialchars($item['status']);
                                    ?>
                                    <span class="status-badge status-<?= $status_class; ?>">
                                        <?php
                                            if ($item['status'] === 'Menunggu Verifikasi') {
                                                echo 'Diperiksa'; // Ganti teks sesuai keinginan Anda
                                            } else {
                                                echo htmlspecialchars($item['status']);
                                            }
                                        ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <?php if ($item['status'] === 'Disetujui'): ?>
                                        <button type="button" class="btn pengembalian-btn" data-id="<?= $item['id']; ?>">
                                            Kembalikan
                                        </button>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">Tidak ada riwayat peminjaman saat ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (isset($data['total_halaman']) && $data['total_halaman'] > 1): ?>
        <div class="pagination-container">
            <?php $queryParams = http_build_query(['search' => $data['keyword'] ?? '']); ?>
            <a href="<?= BASEURL ?>/siswa/riwayat/<?= max(1, $data['halaman_aktif'] - 1) ?>?<?= $queryParams ?>" class="pagination-btn <?= ($data['halaman_aktif'] <= 1) ? 'disabled' : '' ?>">Sebelumnya</a>
            <div class="page-numbers">
                <?php for ($i = 1; $i <= $data['total_halaman']; $i++): ?>
                    <a href="<?= BASEURL ?>/siswa/riwayat/<?= $i ?>?<?= $queryParams ?>" class="page-link <?= ($i == $data['halaman_aktif']) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <a href="<?= BASEURL ?>/siswa/riwayat/<?= min($data['total_halaman'], $data['halaman_aktif'] + 1) ?>?<?= $queryParams ?>" class="pagination-btn <?= ($data['halaman_aktif'] >= $data['total_halaman']) ? 'disabled' : '' ?>">Berikutnya</a>
        </div>
        <?php endif; ?>
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
