<?php require_once '../app/views/layouts/admin_header.php'; ?>

<div class="content">
    <div class="main-table-container">
    
        <?php Flasher::flash(); ?> <div class="table-controls-container">
            <button type="submit" form="bulkDeleteBarangForm" class="btn btn-danger" id="bulkDeleteBarangBtn" style="display: none;">Hapus Terpilih</button>

            <form action="<?= BASEURL ?>/admin/barang" method="GET" class="search-and-filter-form">
                <div class="search-container">
                    <input type="text" name="search" id="searchInput" placeholder="Cari nama atau kode barang..." value="<?= htmlspecialchars($data['filters']['keyword'] ?? '') ?>">
                    <button type="submit" class="search-submit-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#4CAF50"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    </button>
                </div>

                <div class="filter-container">
                    <select name="filter_status" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="Tersedia" <?= ($data['filters']['status'] ?? '') == 'Tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="Terbatas" <?= ($data['filters']['status'] ?? '') == 'Terbatas' ? 'selected' : ''; ?>>Terbatas</option>
                        <option value="Tidak Tersedia" <?= ($data['filters']['status'] ?? '') == 'Tidak Tersedia' ? 'selected' : ''; ?>>Tidak Tersedia</option>
                    </select>
                </div>
            </form>

            <div class="actions-container">
                <button type="button" class="btn btn-secondary" id="importItemBtn">Import Barang</button>
                <button class="add-button" id="addItemBtn">+ Tambah Barang</button>
            </div>
        </div>
        
        <form action="<?= BASEURL; ?>/admin/hapus-barang-massal" method="POST" id="bulkDeleteBarangForm">
            <div class="table-wrapper">
                <table id="itemTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllBarang"></th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Jumlah Stok</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($data['items']) && !empty($data['items'])):
                            foreach ($data['items'] as $item): ?>
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="<?= $item['id']; ?>" class="row-checkbox-barang"></td>
                                <td><?= htmlspecialchars($item['kode_barang']); ?></td>
                                <td><?= htmlspecialchars($item['nama_barang']); ?></td>
                                <td><?= htmlspecialchars($item['jumlah']); ?></td>
                                <td><?= ucfirst(htmlspecialchars($item['kondisi'])); ?></td>
                                <td><?= htmlspecialchars($item['status']); ?></td>
                                <td class="action-buttons">
                                    <button type="button" class="view-btn" data-id="<?= $item['id']; ?>" title="Lihat Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/></svg>
                                    </button>
                                    <button type="button" class="edit-btn" data-id="<?= $item['id']; ?>" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" fill="currentColor"/></svg>
                                    </button>
                                    <button type="button" class="delete-btn" data-id="<?= $item['id']; ?>" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" fill="currentColor"/></svg>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach;
                        else: ?>
                            <tr><td colspan="7" style="text-align:center;">Tidak ada data barang yang cocok.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
        
        <?php if (isset($data['total_halaman']) && $data['total_halaman'] > 1): ?>
        <div class="pagination-container">
            <?php
                $queryParams = http_build_query([
                    'search' => $data['filters']['keyword'] ?? '',
                    'filter_kondisi' => $data['filters']['kondisi'] ?? '',
                    'filter_status' => $data['filters']['status'] ?? ''
                ]);
            ?>
            <a href="<?= BASEURL ?>/admin/barang/<?= max(1, $data['halaman_aktif'] - 1) ?>?<?= $queryParams ?>" class="pagination-btn <?= ($data['halaman_aktif'] <= 1) ? 'disabled' : '' ?>">Sebelumnya</a>
            <div class="page-numbers">
                <?php for ($i = 1; $i <= $data['total_halaman']; $i++): ?>
                    <a href="<?= BASEURL ?>/admin/barang/<?= $i ?>?<?= $queryParams ?>" class="page-link <?= ($i == $data['halaman_aktif']) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <a href="<?= BASEURL ?>/admin/barang/<?= min($data['total_halaman'], $data['halaman_aktif'] + 1) ?>?<?= $queryParams ?>" class="pagination-btn <?= ($data['halaman_aktif'] >= $data['total_halaman']) ? 'disabled' : '' ?>">Berikutnya</a>
        </div>
        <?php endif; ?>
    </div>
    
    <div id="itemModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <form id="itemForm" action="<?= BASEURL; ?>/admin/tambah-barang" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="itemId" name="id">
                    <input type="hidden" id="gambarLama" name="gambar_lama">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_barang">Nama Barang</label>
                            <input type="text" id="nama_barang" name="nama_barang" required>
                        </div>
                        <div class="form-group">
                            <label for="kode_barang">Kode Barang</label>
                            <input type="text" id="kode_barang" name="kode_barang" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="jumlah">Jumlah Stok</label>
                            <input type="number" id="jumlah" name="jumlah" required min="0">
                        </div>
                        <div class="form-group">
                            <label for="lokasi_penyimpanan">Lokasi Penyimpanan</label>
                            <input type="text" id="lokasi_penyimpanan" name="lokasi_penyimpanan">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tanggal_pembelian">Tanggal Pembelian</label>
                            <input type="date" id="tanggal_pembelian" name="tanggal_pembelian">
                        </div>
                         <div class="form-group">
                            <label for="gambar">Gambar (Opsional)</label>
                            <input type="file" id="gambar" name="gambar" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="deleteModal" class="modal">
        <div class="modal-content delete-modal">
            <div class="delete-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 0 24 24" width="48px" fill="#ef4444"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            </div>
            <p class="modal-message">Apakah Anda yakin ingin menghapus data ini?</p>
            <div class="modal-actions">
                <button class="btn-cancel" id="cancelDelete">Batal</button>
                <a href="#" class="btn btn-danger" id="confirmDeleteLink">Ya, Hapus</a>
            </div>
        </div>
    </div>

    <div id="importItemModal" class="modal">
        <div class="modal-content">
            <form id="importItemForm" action="<?= BASEURL; ?>/admin/import-barang" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="import-instructions">
                        <strong>Petunjuk:</strong>
                        <ul>
                            <li>Gunakan file dengan format <strong>.csv</strong>.</li>
                            <li>Pastikan urutan kolom: <br><strong>Kode Barang, Nama Barang, Jumlah, Kondisi, Status, Lokasi, Tgl Pembelian (YYYY-MM-DD)</strong>.</li>
                            <li>Baris pertama (header) akan diabaikan.</li>
                        </ul>
                    </div>
                    <div class="form-group">
                        <label for="file_import_barang">Pilih File CSV untuk Diimpor</label>
                        <input type="file" id="file_import_barang" name="file_import_barang" accept=".csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-submit">Unggah dan Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/admin_footer.php'; ?>