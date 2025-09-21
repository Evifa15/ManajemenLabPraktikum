<div class="card-container">
    <h1>Pilih Peran Anda</h1>
    <p>Silakan pilih peran Anda untuk melanjutkan.</p>
    
    <form id="roleForm" action="<?= BASEURL; ?>/login" method="GET">
        <input type="hidden" name="role" id="selected-role">

        <div class="roles">
            <div class="role-card" data-role="guru">
                <div class="role-icon">
                    <img src="https://img.icons8.com/ios/50/administrator-male.png" alt="ikon guru"/>
                </div>
                <div class="role-name">Guru</div>
                <div class="role-description">Masuk sebagai pengajar dan fasilitator kelas.</div>
                <div class="custom-radio"></div>
            </div>

            <div class="role-card" data-role="siswa">
                <div class="role-icon">
                    <img src="https://img.icons8.com/ios/50/guest-male.png" alt="ikon siswa"/>
                </div>
                <div class="role-name">Siswa</div>
                <div class="role-description">Masuk sebagai peserta didik dan anggota kelas.</div>
                <div class="custom-radio"></div>
            </div>
            
            <div class="role-card" data-role="admin">
                <div class="role-icon">
                    <img src="https://img.icons8.com/ios/50/admin-settings-male.png" alt="ikon admin"/>
                </div>
                <div class="role-name">Administrator</div>
                <div class="role-description">Kelola sistem, pengguna, dan data aplikasi.</div>
                <div class="custom-radio"></div>
            </div>
        </div>

        <button type="submit" class="continue-btn" disabled>Lanjutkan</button>
    </form>
</div>