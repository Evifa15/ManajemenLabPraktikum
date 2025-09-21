<?php require_once '../app/views/layouts/admin_header.php'; ?>
<?php $animationClass = (isset($_GET['search']) && !empty($_GET['search'])) ? ' no-animation' : ''; ?>
<div class="content">
     <div class="main-table-container<?= $animationClass; ?>">
        <div class="tab-links-wrapper">
            <a href="<?= BASEURL; ?>/admin/pengguna/staff" class="tab-link <?= (isset($data['active_tab']) && $data['active_tab'] == 'staff') ? 'active' : '' ?>">Data Staff</a>
            <a href="<?= BASEURL; ?>/admin/pengguna/guru" class="tab-link <?= (isset($data['active_tab']) && $data['active_tab'] == 'guru') ? 'active' : '' ?>">Data Guru</a>
            <a href="<?= BASEURL; ?>/admin/pengguna/siswa" class="tab-link <?= (isset($data['active_tab']) && $data['active_tab'] == 'siswa') ? 'active' : '' ?>">Data Siswa</a>
            <a href="<?= BASEURL; ?>/admin/pengguna/akun" class="tab-link <?= (isset($data['active_tab']) && $data['active_tab'] == 'akun') ? 'active' : '' ?>">Data Akun</a>
        </div>

        <div class="tab-content active">
    <?php
    if (isset($data['active_tab']) && !empty($data['active_tab'])) {
        $tabView = '../app/views/admin/pengguna/_tab_' . $data['active_tab'] . '.php';
        if (file_exists($tabView)) {
            require_once $tabView;
        } else {
            echo "<p>Tampilan untuk tab '{$data['active_tab']}' tidak ditemukan.</p>";
        }
    }
    ?>
</div>
    </div>
</div>

<div id="staffModal" class="modal">
    <div class="modal-content">
        <form id="staffForm" method="POST" action="<?= BASEURL; ?>/admin/tambah-staff" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" id="staffId" name="id">
                <div class="form-row">
                    <div class="form-group"><label for="nama">Nama Staff</label><input type="text" id="nama" name="nama" required></div>
                    <div class="form-group"><label for="id_staff">ID Staff</label><input type="text" id="id_staff" name="id_staff" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label for="jenis_kelamin_staff">Jenis Kelamin</label><select id="jenis_kelamin_staff" name="jenis_kelamin" required><option value="Laki-laki">Laki-laki</option><option value="Perempuan">Perempuan</option></select></div>
                    <div class="form-group"><label for="ttl_staff">Tempat, Tanggal Lahir</label><input type="text" id="ttl_staff" name="ttl"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label for="agama_staff">Agama</label><input type="text" id="agama_staff" name="agama"></div>
                    <div class="form-group"><label for="no_hp_staff">No. HP</label><input type="text" id="no_hp_staff" name="no_hp"></div>
                </div>
                <div class="form-group"><label for="alamat_staff">Alamat</label><textarea id="alamat_staff" name="alamat" rows="2"></textarea></div>
                <div class="form-group"><label for="email_staff">Email</label><input type="email" id="email_staff" name="email"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn-submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="importStaffModal" class="modal">
    <div class="modal-content">
        <form id="importStaffForm" action="<?= BASEURL; ?>/admin/import-staff" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="import-instructions">
                    <strong>Petunjuk:</strong>
                    <ul>
                        <li>Gunakan file dengan format <strong>.csv</strong>.</li>
                        <li>Pastikan urutan kolom: <br><strong>Nama, ID Staff, Jenis Kelamin, No. HP, Email</strong>.</li>
                        <li>Baris pertama (header) akan diabaikan.</li>
                        <li>Akun login akan dibuat otomatis dengan <strong>Nama</strong> sebagai username dan <strong>ID Staff</strong> sebagai password awal.</li>
                    </ul>
                </div>
                <div class="form-group">
                    <label for="file_import_staff">Pilih File CSV untuk Diimpor</label>
                    <input type="file" id="file_import_staff" name="file_import_staff" accept=".csv" required>
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

<div id="guruModal" class="modal">
    <div class="modal-content">
        <form id="guruForm" method="POST" action="<?= BASEURL; ?>/admin/tambah-guru" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" id="guruId" name="id">
                <div class="form-row">
                    <div class="form-group"><label for="nama_guru">Nama Guru</label><input type="text" id="nama_guru" name="nama" required></div>
                    <div class="form-group"><label for="nip_guru">ID Guru (NIP)</label><input type="text" id="nip_guru" name="nip" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label for="jenis_kelamin_guru">Jenis Kelamin</label><select id="jenis_kelamin_guru" name="jenis_kelamin" required><option value="Laki laki">Laki-laki</option> <option value="Perempuan">Perempuan</option></select></div>
                    <div class="form-group"><label for="ttl_guru">Tempat, Tanggal Lahir</label><input type="text" id="ttl_guru" name="ttl"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label for="agama_guru">Agama</label><input type="text" id="agama_guru" name="agama"></div>
                    <div class="form-group"><label for="no_hp_guru">No. HP</label><input type="text" id="no_hp_guru" name="no_hp"></div>
                </div>
                <div class="form-group"><label for="alamat_guru">Alamat</label><textarea id="alamat_guru" name="alamat" rows="2"></textarea></div>
                <div class="form-group"><label for="email_guru">Email</label><input type="email" id="email_guru" name="email"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn-submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="importGuruModal" class="modal">
    <div class="modal-content">
        <form id="importGuruForm" action="<?= BASEURL; ?>/admin/import-guru" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="import-instructions">
                    <strong>Petunjuk:</strong>
                    <ul>
                        <li>Gunakan file dengan format <strong>.csv</strong>.</li>
                        <li>Pastikan urutan kolom: <strong>Nama, NIP, Jenis Kelamin, No. HP, Email</strong>.</li>
                        <li>Baris pertama (header) akan diabaikan.</li>
                        <li>Akun login akan dibuat otomatis dengan <strong>Nama</strong> sebagai username dan <strong>NIP</strong> sebagai password awal.</li>
                    </ul>
                </div>
                <div class="form-group">
                    <label for="file_import_guru">Pilih File CSV untuk Diimpor</label>
                    <input type="file" id="file_import_guru" name="file_import_guru" accept=".csv" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn-submit">Unggah dan Proses</button>
            </div>
        </form>
    </div>
</div>
<div id="importSiswaModal" class="modal">
    <div class="modal-content">
        <form id="importSiswaForm" action="<?= BASEURL; ?>/admin/import-siswa" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="import-instructions">
                    <strong>Petunjuk:</strong>
                    <ul>
                        <li>Gunakan file dengan format <strong>.csv</strong>.</li>
                        <li>Pastikan urutan kolom: <strong>Nama, ID Siswa (NIS), Jenis Kelamin, No. HP, Email</strong>.</li>
                        <li>Baris pertama (header) akan diabaikan.</li>
                        <li>Akun login akan dibuat otomatis dengan <strong>Nama</strong> sebagai username dan <strong>ID Siswa</strong> sebagai password awal.</li>
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
<div id="ubahPasswordModal" class="modal">
    <div class="modal-content">
        <form id="ubahPasswordForm" method="POST" action="<?= BASEURL; ?>/admin/ubah-password-akun">
            <div class="modal-body">
                <p>Mengubah kata sandi untuk akun <strong id="username-akun"></strong>.</p>
                <input type="hidden" id="akunId" name="id">
                <div class="form-group">
                    <label for="password-baru">Kata Sandi Baru</label>
                    <input type="password" id="password-baru" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="konfirmasi-password">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" id="konfirmasi-password" name="confirm_password" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn-submit">Simpan Kata Sandi Baru</button>
            </div>
        </form>
    </div>
</div>
<?php require_once '../app/views/layouts/admin_footer.php'; ?>