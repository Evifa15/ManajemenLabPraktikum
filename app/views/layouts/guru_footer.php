</div>
        </div>
    </div>
    <script>
        const BASEURL = "<?= BASEURL; ?>";
    </script>
    <script src="<?= BASEURL; ?>/js/main-script.js"></script>
    <script src="<?= BASEURL; ?>/js/guru-script.js"></script> 
    <div id="logoutModal" class="modal">
    <div class="modal-content delete-modal">
        <div class="delete-icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 0 24 24" width="48px" fill="#ef4444"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <p class="modal-message">Apakah Anda yakin ingin keluar dari akun?</p>
        <div class="modal-actions">
            <button class="btn-cancel" id="cancelLogout">Batal</button>
            <a href="#" class="btn btn-danger" id="confirmLogoutLink">Ya, Logout</a>
        </div>
    </div>
</div>
</body>
</html>
</body>
</html>