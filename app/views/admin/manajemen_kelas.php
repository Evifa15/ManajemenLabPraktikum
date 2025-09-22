<?php require_once '../app/views/layouts/admin_header.php'; ?>

<div class="content">
    <div class="main-table-container">
            <div class="table-controls-container" style="margin-top: 1.5rem;">
            <button type="submit" form="bulkDeleteKelasForm" class="btn btn-danger" id="bulkDeleteKelasBtn" style="display: none;">Hapus Terpilih</button>
            
            <form id="searchKelasForm" action="<?= BASEURL; ?>/admin/kelas" method="GET" class="search-form-container">
            <div class="search-input-wrapper">
                <input type="text" id="searchKelasInput" name="search_kelas" placeholder="Silahkan Lakukan Pencarian Disini..." value="<?= htmlspecialchars($data['search_term_kelas'] ?? '') ?>">
                <button type="submit" class="search-submit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#555"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                </button>
            </div>
            </form>

            <div class="actions-container">
                <button type="button" class="btn btn-secondary" id="importKelasBtn">Import Kelas</button>
                <button type="button" class="add-button" id="addKelasBtn">+ Tambah Kelas</button>
            </div>
        </div>
    
        <?php Flasher::flash(); ?>

        <form action="<?= BASEURL; ?>/admin/hapus-kelas-massal" method="POST" id="bulkDeleteKelasForm">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllKelas"></th>
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th>Wali Kelas</th>
                        <th>ID Wali Kelas (NIP)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="kelasTableBody">
                    <?php if (!empty($data['kelas'])): 
                        $no = ($data['halaman_aktif'] - 1) * 10 + 1;
                        foreach ($data['kelas'] as $kelas): ?>
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="<?= $kelas['id']; ?>" class="row-checkbox-kelas"></td>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($kelas['nama_kelas']); ?></td>
                            <td><?= htmlspecialchars($kelas['nama_wali_kelas'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($kelas['nip'] ?? '-'); ?></td>
                            <td class="action-buttons">
                                <a href="<?= BASEURL ?>/admin/detailKelas/<?= $kelas['id'] ?>" class="view-btn" title="Lihat Detail"><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/></svg></a>
                                <button type="button" class="edit-kelas-btn" data-id="<?= $kelas['id']; ?>" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" fill="currentColor"/></svg></button>
                                <button type="button" class="delete-kelas-btn" data-id="<?= $kelas['id']; ?>" title="Hapus"><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" fill="currentColor"/></svg></button>
                            </td>
                        </tr>
                        <?php endforeach; 
                    else: ?>
                        <tr><td colspan="6" style="text-align: center;">Tidak ada data kelas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>
</div>

<div id="kelasModal" class="modal">
    <div class="modal-content">
        <form id="kelasForm" method="POST">
            <div class="modal-body">
                <input type="hidden" id="kelasId" name="id">
                <div class="form-group">
                    <label for="nama_kelas">Nama Kelas</label>
                    <input type="text" id="nama_kelas" name="nama_kelas" required>
                </div>
                <div class="form-group">
                    <label for="wali_kelas_id">Wali Kelas</label>
                    <select id="wali_kelas_id" name="wali_kelas_id" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($data['all_guru'] as $guru): ?>
                            <option value="<?= $guru['id'] ?>"><?= htmlspecialchars($guru['nama']) ?> (NIP: <?= $guru['nip'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn-submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="importKelasModal" class="modal">
    <div class="modal-content">
        <form id="importKelasForm" action="<?= BASEURL; ?>/admin/import-kelas" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="import-instructions">
                    <strong>Petunjuk:</strong>
                    <ul>
                        <li>Gunakan file dengan format <strong>.csv</strong>.</li>
                        <li>Pastikan urutan kolom: <strong>Nama Kelas</strong>, <strong>Nama Wali Kelas</strong><strong>NIP Wali Kelas</strong>.</li>
                        <li>Baris pertama (header) akan diabaikan.</li>
                        <li>Pastikan <strong>NIP Wali Kelas</strong> sudah terdaftar di tab Daftar Guru.</li>
                    </ul>
                </div>
                <div class="form-group">
                    <label for="file_import_kelas">Pilih File CSV untuk Diimpor</label>
                    <input type="file" id="file_import_kelas" name="file_import_kelas" accept=".csv" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn-submit">Unggah dan Proses</button>
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
<?php require_once '../app/views/layouts/admin_footer.php'; ?>