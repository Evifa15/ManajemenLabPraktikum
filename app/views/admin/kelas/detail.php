<?php require_once '../app/views/layouts/admin_header.php'; ?>

<div class="content">
<?php if ($data['kelas']): ?>
<div class="detail-layout">
    
    <div class="info-card-container">
        <div class="info-card">
            <div class="info-card-header">
                <h3>Informasi Kelas</h3>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Nama Kelas</span>
                        <span class="info-value"><?= htmlspecialchars($data['kelas']['nama_kelas']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Wali Kelas</span>
                        <span class="info-value"><?= htmlspecialchars($data['kelas']['nama_wali_kelas'] ?? '-'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">NIP Wali Kelas</span>
                        <span class="info-value"><?= htmlspecialchars($data['kelas']['nip'] ?? '-'); ?></span>
                    </div>
                </div>
            </div>

            <div class="info-card-footer">
                <a href="<?= BASEURL; ?>/admin/kelas" class="link-kembali-kelas">Kembali</a>
            </div>
        </div>
    </div>

    <div class="main-table-container">  
        <div class="table-controls-container">
            <button type="submit" form="bulkDeleteSiswaForm" class="btn btn-danger" id="bulkDeleteSiswaBtn" style="display: none;">Keluarkan Terpilih</button>

            <form id="searchSiswaForm" action="<?= BASEURL ?>/admin/detailKelas/<?= $data['kelas']['id'] ?>" method="get" class="search-form-container">
                <div class="search-input-wrapper">
                    <input type="text" id="searchSiswaInput" name="search" placeholder="Silahkan Lakukan Pencarian Disini..." value="<?= htmlspecialchars($data['search_term'] ?? '') ?>">
                    <button type="submit" class="search-submit-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#555"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    </button>
                </div>
            </form>

            <div class="actions-container">
                <button type="button" class="btn btn-secondary" id="importSiswaBtn">Import Siswa</button>
                <button type="button" class="add-button" id="addSiswaBtn">+ Tambah Siswa</button>
            </div>
        </div>

        <?php Flasher::flash(); ?>

        <form action="<?= BASEURL ?>/admin/remove-siswa-massal" method="POST" id="bulkDeleteSiswaForm">
            <input type="hidden" name="kelas_id" value="<?= $data['kelas']['id']; ?>">

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllSiswa"></th>
                            <th>No</th>
                            <th>Nama Siswa</th> <th>Jenis Kelamin</th>
                            <th>No. HP</th>
                            <th>Aksi</th> </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['siswa'])):
                            $no = ($data['halaman_aktif'] - 1) * 10 + 1;
                            foreach ($data['siswa'] as $siswa): ?>
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="<?= $siswa['id']; ?>" class="row-checkbox-siswa"></td>
                                <td><?= $no++; ?></td>
                                
                                <td>
                                    <?= htmlspecialchars($siswa['nama']); ?><br>
                                    <small style="color: #666;">ID: <?= htmlspecialchars($siswa['id_siswa']); ?></small>
                                </td>

                                <td><?= htmlspecialchars($siswa['jenis_kelamin']); ?></td>
                                <td><?= htmlspecialchars($siswa['no_hp'] ?? '-'); ?></td>

                                <td class="action-buttons">
                                    <a href="<?= BASEURL ?>/admin/detailSiswa/<?= $siswa['id'] ?>?origin=kelas&kelas_id=<?= $data['kelas']['id'] ?>" class="view-btn" title="Lihat Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/></svg>
                                    </a>
                                    <button type="button" class="delete-btn delete-siswa-btn" data-id="<?= $siswa['id']; ?>" onclick="showDeleteModal(this)" title="Keluarkan dari Kelas">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" fill="currentColor"/></svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach;
                        else: ?>
                            <tr><td colspan="7" style="text-align:center;">Tidak ada data siswa di kelas ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>

        <?php if (isset($data['total_halaman']) && $data['total_halaman'] > 1):
            $searchQuery = isset($data['search_term']) ? '?search=' . urlencode($data['search_term']) : '';
        ?>
            <div class="pagination-container">
                <a href="<?= BASEURL ?>/admin/detailKelas/<?= $data['kelas']['id'] ?>/<?= max(1, $data['halaman_aktif'] - 1) . $searchQuery ?>" class="pagination-btn <?= ($data['halaman_aktif'] <= 1) ? 'disabled' : '' ?>">Sebelumnya</a>
                <div class="page-numbers">
                    <?php for ($i = 1; $i <= $data['total_halaman']; $i++): ?>
                        <a href="<?= BASEURL ?>/admin/detailKelas/<?= $data['kelas']['id'] ?>/<?= $i . $searchQuery ?>" class="page-link <?= ($i == $data['halaman_aktif']) ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
                <a href="<?= BASEURL ?>/admin/detailKelas/<?= $data['kelas']['id'] ?>/<?= min($data['total_halaman'], $data['halaman_aktif'] + 1) . $searchQuery ?>" class="pagination-btn <?= ($data['halaman_aktif'] >= $data['total_halaman']) ? 'disabled' : '' ?>">Berikutnya</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
    <p style="text-align:center; margin-top: 2rem;">Data kelas tidak ditemukan.</p>
<?php endif; ?>
</div>

<div id="assignSiswaModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="assignSiswaModalTitle">Tambah Siswa ke Kelas Ini</h3>
            <span class="close-button">&times;</span>
        </div>
        <form id="assignSiswaForm" action="<?= BASEURL; ?>/admin/assignSiswaToKelas" method="POST">
            <div class="modal-body">
                <input type="hidden" name="kelas_id" value="<?= $data['kelas']['id']; ?>">
                <div class="form-group">
                    <label for="searchUnassignedSiswaInput">Cari Siswa</label>
                    <input type="text" id="searchUnassignedSiswaInput" placeholder="Cari nama atau NIS siswa...">
                </div>
                <div class="form-group">
                    <label for="siswa_id_select">Pilih Siswa</label>
                    <select id="siswa_id_select" name="siswa_id" required>
                        <option value="">-- Cari dan Pilih Siswa --</option>
                        <?php if (isset($data['unassigned_siswa']) && !empty($data['unassigned_siswa'])): ?>
                            <?php foreach ($data['unassigned_siswa'] as $siswa): ?>
                                <option value="<?= $siswa['id']; ?>"><?= htmlspecialchars($siswa['nama']); ?> (NIS: <?= htmlspecialchars($siswa['id_siswa']); ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
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

<div id="importSiswaModal" class="modal">
    <div class="modal-content">
        <form id="importSiswaForm" action="<?= BASEURL; ?>/admin/importSiswaKeKelas" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="kelas_id" value="<?= $data['kelas']['id']; ?>">
                <div class="import-instructions">
                    <strong>Petunjuk:</strong>
                    <ul>
                        <li>Gunakan file dengan format CSV</li>
                        <li>Pastikan hanya terdapat satu kolom berisi ID Siswa (NIS). </li>
                        <li>Baris pertama (header) akan diabaikan.</li>
                        <li>Siswa yang diimpor akan otomatis dimasukkan ke kelas ini.</li>
                    </ul>
                </div>
                <div class="form-group">
                    <label for="file_import_siswa">Pilih File CSV untuk Diimpor</label>
                    <input type="file" id="file_import_siswa" name="file_import_siswa" accept=".csv" required>
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
        <p class="modal-message">Apakah Anda yakin ingin menghapus data ini? </p>
        <div class="modal-actions">
            <button class="btn-cancel" id="cancelDelete">Batal</button>
            <a href="#" class="btn btn-danger" id="confirmDeleteLink">Ya, Hapus</a>
        </div>
    </div>
</div>

<div id="editSiswaStatusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="editSiswaStatusModalTitle">Ubah Status Siswa</h3>
            <span class="close-button">&times;</span>
        </div>
        <form id="editSiswaStatusForm" method="POST">
            <div class="modal-body">
                <input type="hidden" id="editSiswaStatusId" name="id">
                <div class="form-group">
                    <label for="status-siswa">Status</label>
                    <select id="status-siswa" name="status">
                        <option value="Aktif">Aktif</option>
                        <option value="Non-Aktif">Non-Aktif</option>
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

<?php require_once '../app/views/layouts/admin_footer.php'; ?>