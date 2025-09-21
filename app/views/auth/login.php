<div class="login-container">
    <h1>Login <?= ucfirst($role); ?></h1> 
    <p>Silakan masuk menggunakan akun Anda Untuk Melanjutkan.</p>

    <?php Flasher::flash(); ?>

    <form action="<?= BASEURL; ?>/process-login" method="POST">
        <input type="hidden" name="role" value="<?= $role; ?>">
        
        <div class="input-group">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <input type="text" name="username" placeholder="Nama Pengguna" required>
        </div>
        
        <div class="input-group">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <input type="password" name="password" id="password-input" placeholder="Kata Sandi" required>
            <span id="show-password-btn" class="show-password-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </span>
        </div>

        <button type="submit">Login</button>
    </form>

    <div class="back-link">
        <a href="<?= BASEURL; ?>">Kembali pilih peran</a>
    </div>
</div>