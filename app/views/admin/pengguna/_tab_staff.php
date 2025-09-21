<div class="table-controls-container">
    <button type="submit" form="bulkDeleteStaffForm" class="btn btn-danger" id="bulkDeleteStaffBtn" style="display: none;">Hapus Terpilih</button>
    
    <form id="searchStaffForm" action="<?= BASEURL; ?>/admin/pengguna/staff" method="GET" class="search-form-container">
    <div class="search-input-wrapper">
        <input type="text" id="searchStaffInput" name="search_staff" placeholder="Silahkan Lakukan Pencarian Disini..." value="<?= htmlspecialchars($data['search_term_staff'] ?? '') ?>">
        <button type="submit" class="search-submit-icon">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#555"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
        </button>
    </div>
    </form>

    <div class="actions-container">
        <button type="button" class="btn btn-secondary" id="importStaffBtn">Import Staff</button>
        <button type="button" class="add-button" id="addStaffBtn">+ Tambah Staff</button>
    </div>
</div>

<?php Flasher::flash(); ?>

<form action="<?= BASEURL; ?>/admin/hapus-staff-massal" method="POST" id="bulkDeleteStaffForm">
    <table>
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAllStaff"></th>
                <th>No</th>
                <th>Nama Staff</th>
                <th>ID Staff</th>
                <th>Jenis Kelamin</th>
                <th>No. HP</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="staffTableBody">
            <?php if (!empty($data['staff'])):
                $no = ($data['halaman_aktif'] - 1) * $data['limit'] + 1;
                foreach ($data['staff'] as $staff): ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= $staff['id']; ?>" class="row-checkbox-staff"></td>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($staff['nama']); ?></td>
                    <td><?= htmlspecialchars($staff['id_staff']); ?></td>
                    <td><?= htmlspecialchars($staff['jenis_kelamin']); ?></td>
                    <td><?= htmlspecialchars($staff['no_hp']); ?></td>
                    
                    <td class="action-buttons">
                        <a href="<?= BASEURL ?>/admin/detailStaff/<?= $staff['id'] ?>" class="view-btn" title="Lihat Detail">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/></svg>
                        </a>
                        <button type="button" class="edit-staff-btn" data-id="<?= $staff['id']; ?>" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" fill="currentColor"/></svg>
                        </button>
                        <button type="button" class="delete-staff-btn" data-id="<?= $staff['id']; ?>" title="Hapus">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" fill="currentColor"/></svg>
                        </button>
                    </td>
                </tr>
                <?php endforeach;
            else: ?>
                <tr><td colspan="7" style="text-align: center;">Tidak ada data staff.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</form>

<?php if (isset($data['total_halaman']) && $data['total_halaman'] > 1): ?>
<div class="pagination-container">
    <?php $queryParams = http_build_query(['search_staff' => $data['search_term_staff'] ?? '']); ?>
    <a href="<?= BASEURL ?>/admin/pengguna/staff/<?= max(1, $data['halaman_aktif'] - 1) ?>?<?= $queryParams ?>" class="pagination-btn <?= ($data['halaman_aktif'] <= 1) ? 'disabled' : '' ?>">Sebelumnya</a>
    <div class="page-numbers">
        <?php for ($i = 1; $i <= $data['total_halaman']; $i++): ?>
            <a href="<?= BASEURL ?>/admin/pengguna/staff/<?= $i ?>?<?= $queryParams ?>" class="page-link <?= ($i == $data['halaman_aktif']) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <a href="<?= BASEURL ?>/admin/pengguna/staff/<?= min($data['total_halaman'], $data['halaman_aktif'] + 1) ?>?<?= $queryParams ?>" class="pagination-btn <?= ($data['halaman_aktif'] >= $data['total_halaman']) ? 'disabled' : '' ?>">Berikutnya</a>
</div>
<?php endif; ?>