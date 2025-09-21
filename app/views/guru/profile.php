<div class="content">
    <?php Flasher::flash(); ?>
    <div class="profile-layout-container">

        <div class="profile-main-card-wrapper">
            <div class="profile-main-card">
                <form id="editProfileForm" method="POST" action="<?= BASEURL; ?>/guru/updateProfile" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($data['profile']['id']); ?>">
                    <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($data['profile']['foto']); ?>">
                    <input type="hidden" name="nip" value="<?= htmlspecialchars($data['profile']['nip'] ?? ''); ?>">

                    <div class="profile-info-header" style="margin-bottom: 2rem;">
                        <h4>Informasi Pribadi</h4>
                    </div>

                    <div class="profile-info-grid">
                        <div class="profile-info-group">
                            <label for="nama_guru">Nama Lengkap</label>
                            <input type="text" id="nama_guru" name="nama" value="<?= htmlspecialchars($data['profile']['nama'] ?? ''); ?>" readonly>
                        </div>
                        <div class="profile-info-group">
                            <label for="jenis_kelamin_guru">Jenis Kelamin</label>
                            <select id="jenis_kelamin_guru" name="jenis_kelamin">
                                <option value="Laki-laki" <?= ($data['profile']['jenis_kelamin'] ?? '') == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?= ($data['profile']['jenis_kelamin'] ?? '') == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="profile-info-group">
                            <label for="email_guru">Email</label>
                            <input type="email" id="email_guru" name="email" value="<?= htmlspecialchars($data['profile']['email'] ?? ''); ?>">
                        </div>
                        <div class="profile-info-group">
                            <label for="no_hp_guru">No. HP</label>
                            <input type="text" id="no_hp_guru" name="no_hp" value="<?= htmlspecialchars($data['profile']['no_hp'] ?? ''); ?>">
                        </div>
                        <div class="profile-info-group">
                            <label for="agama_guru">Agama</label>
                            <input type="text" id="agama_guru" name="agama" value="<?= htmlspecialchars($data['profile']['agama'] ?? ''); ?>">
                        </div>
                         <div class="profile-info-group">
                            <label for="ttl_guru">Tempat, Tanggal Lahir</label>
                            <input type="text" id="ttl_guru" name="ttl" value="<?= htmlspecialchars($data['profile']['ttl'] ?? ''); ?>">
                        </div>
                        <div class="profile-info-group full-width">
                            <label for="alamat_guru">Alamat</label>
                            <textarea id="alamat_guru" name="alamat"><?= htmlspecialchars($data['profile']['alamat'] ?? ''); ?></textarea>
                        </div>
                        <div class="profile-info-group full-width">
                            <label for="foto_guru">Ubah Foto (Opsional)</label>
                            <input type="file" id="foto_guru" name="foto" accept="image/*">
                        </div>
                    </div>

                    <div class="profile-actions-wrapper">
                        <button type="submit" class="btn btn-primary btn-save">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="profile-info-container">
            <div class="profile-avatar-card">
                <?php
                    $foto = $data['profile']['foto'] ?? 'default.png';
                    $fotoPath = 'img/guru/' . htmlspecialchars($foto);
                    if (!file_exists($fotoPath) || empty($foto)) {
                        $fotoUrl = BASEURL . '/img/siswa/default.png';
                    } else {
                        $fotoUrl = BASEURL . '/' . $fotoPath;
                    }
                ?>
                <img src="<?= $fotoUrl . '?v=' . time(); ?>" alt="Foto Profil" class="profile-avatar-large">
                <h3 class="profile-title"><?= htmlspecialchars($data['profile']['nama'] ?? 'Profil Guru'); ?></h3>
            </div>
            
            <div class="profile-info-card-standalone">
                <div class="profile-info-header">
                    <h4>Informasi Akun</h4>
                </div>
                <ul class="profile-info-list">
                    <li>
                        <span>Nama Pengguna</span>
                        <strong><?= htmlspecialchars($data['user']['username'] ?? '-'); ?></strong>
                    </li>
                    <li>
                        <span>Peran</span>
                        <strong><?= htmlspecialchars(ucfirst($data['user']['role'] ?? '-')); ?></strong>
                    </li>
                    <li>
                        <span>ID Pengguna (NIP)</span>
                        <strong><?= htmlspecialchars($data['profile']['nip'] ?? '-'); ?></strong>
                    </li>
                </ul>
                <div class="divider"></div> 
                <div class="button-action-wrapper"> 
                    <button type="button" class="btn btn-secondary btn-small" id="changePasswordBtn">Ubah Kata Sandi</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="changePasswordModal" class="modal">
    <div class="modal-content" style="max-width: 500px; padding: 0;">
        <form id="changePasswordForm" method="POST" action="<?= BASEURL; ?>/guru/change-password">
            <div class="modal-body">
                <div class="form-group">
                    <label for="new_password">Kata Sandi Baru</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn-submit">Ubah Kata Sandi</button>
            </div>
        </form>
    </div>
</div>