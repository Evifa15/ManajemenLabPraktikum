<div class="content-katalog">
    <div class="katalog-main-card">
        <div class="katalog-controls">
            <form action="<?= BASEURL; ?>/siswa/katalog" method="GET" class="search-filter-form" id="filterForm">
                <div class="search-input-container">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24" width="24px" fill="#888"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    <input type="text" name="search" placeholder="Silahkan Lakukan Pencarian Disini..." value="<?= htmlspecialchars($data['filters']['keyword'] ?? '') ?>">
                </div>
                <select name="filter_ketersediaan" class="filter-dropdown" onchange="this.form.submit()">
                    <option value="">Semua Ketersediaan</option>
                    <option value="Tersedia" <?= ($data['filters']['status'] ?? '') == 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                    <option value="Terbatas" <?= ($data['filters']['status'] ?? '') == 'Terbatas' ? 'selected' : '' ?>>Terbatas</option>
                    <option value="Tidak Tersedia" <?= ($data['filters']['status'] ?? '') == 'Tidak Tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option>
                </select>
            </form>
            <button class="floating-cart-button" onclick="document.getElementById('keranjangModal').classList.add('active');">
                <span>Formulir Peminjaman</span>
                <div class="cart-icon-wrapper">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="cart-badge"><?= $data['jumlah_keranjang'] ?? 0 ?></span>
                </div>
            </button>
        </div>
        
        <?php Flasher::flash(); ?> 
        <div class="katalog-grid">
            <?php if (!empty($data['items'])): ?>
                <?php foreach ($data['items'] as $item): ?>
                    <div class="katalog-card">
                        <div class="card-top-section">
                            <div class="card-image">
                                <?php
                                    $gambar_url = (!empty($item['gambar']) && file_exists('img/barang/' . $item['gambar']))
                                        ? BASEURL . '/img/barang/' . htmlspecialchars($item['gambar'])
                                        : BASEURL . '/img/siswa/images.png';
                                ?>
                                <img src="<?= $gambar_url; ?>" alt="<?= htmlspecialchars($item['nama_barang']); ?>">
                            </div>
                            <div class="card-info">
                                <span class="item-status-label <?= strtolower(str_replace(' ', '-', $item['status_barang'])); ?>"><?= htmlspecialchars($item['status_barang']); ?></span>
                                <h4 class="katalog-title"><?= htmlspecialchars($item['nama_barang']); ?></h4>
                                <p class="katalog-code"><?= htmlspecialchars($item['kode_barang']); ?></p> 
                                <p class="katalog-stock">Stok: <strong><?= htmlspecialchars($item['jumlah']); ?></strong></p>
                            </div>
                        </div>
                        <div class="card-form-action">
                            <form action="<?= BASEURL; ?>/siswa/tambah-ke-keranjang" method="post">
                                <input type="hidden" name="barang_id" value="<?= $item['id']; ?>">
                                <button type="submit" class="btn-keranjang" <?= ($item['jumlah'] <= 0) ? 'disabled' : ''; ?>>
                                    Ajukan Pinjam
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center" style="width: 100%;">Tidak ada barang yang ditemukan.</p>
            <?php endif; ?>
        </div>
        
        <?php if (isset($data['total_halaman']) && $data['total_halaman'] > 1): ?>
        <div class="pagination-container">
            <?php
                $queryParams = http_build_query([
                    'search' => $data['filters']['keyword'] ?? '',
                    'filter_ketersediaan' => $data['filters']['status'] ?? ''
                ]);
            ?>
            <a href="<?= BASEURL ?>/siswa/katalog/<?= max(1, $data['halaman_aktif'] - 1) ?>?<?= $queryParams ?>" class="pagination-btn <?= ($data['halaman_aktif'] <= 1) ? 'disabled' : '' ?>">Sebelumnya</a>
            <div class="page-numbers">
                <?php for ($i = 1; $i <= $data['total_halaman']; $i++): ?>
                    <a href="<?= BASEURL ?>/siswa/katalog/<?= $i ?>?<?= $queryParams ?>" class="page-link <?= ($i == $data['halaman_aktif']) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <a href="<?= BASEURL ?>/siswa/katalog/<?= min($data['total_halaman'], $data['halaman_aktif'] + 1) ?>?<?= $queryParams ?>" class="pagination-btn <?= ($data['halaman_aktif'] >= $data['total_halaman']) ? 'disabled' : '' ?>">Berikutnya</a>
        </div>
        <?php endif; ?>
    </div>
    
    <div id="keranjangModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-body">
            <?php if (!empty($data['data_keranjang'])): ?>
                
                <form id="peminjamanForm" action="<?= BASEURL; ?>/siswa/proses-peminjaman" method="post">
                    <h4 class="form-section-title">Daftar Barang</h4>
                    <div class="keranjang-list">
                        <?php foreach ($data['data_keranjang'] as $item): ?>
                            <div class="keranjang-item-card">
                                <div class="keranjang-item-info">
                                    <img src="<?= BASEURL . '/img/barang/' . htmlspecialchars($item['gambar'] ?? 'images.png'); ?>" alt="<?= htmlspecialchars($item['nama_barang']); ?>">
                                    <div class="item-details">
                                        <h5><?= htmlspecialchars($item['nama_barang']); ?></h5>
                                        <span class="item-code"><?= htmlspecialchars($item['kode_barang']); ?></span>
                                    </div>
                                </div>
                                <div class="keranjang-item-controls">
                                    <span class="item-status-label small <?= strtolower(str_replace(' ', '-', $item['status'])); ?>"><?= htmlspecialchars($item['status']); ?></span>
                                    <input type="number" name="jumlah_pinjam[<?= $item['id']; ?>]" value="1" min="1" max="<?= $item['jumlah']; ?>" class="input-jumlah">
                                    <a href="<?= BASEURL; ?>/siswa/hapus-dari-keranjang/<?= $item['id']; ?>" class="keranjang-item-remove" title="Hapus item">&times;</a>
                                </div>
                                <input type="hidden" name="barang_id[]" value="<?= $item['id']; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <h4 class="form-section-title">Informasi Peminjam</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_peminjam">Nama Peminjam</label>
                            <input type="text" id="nama_peminjam" name="nama_peminjam" value="<?= htmlspecialchars($data['data_siswa']['nama'] ?? ''); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="no_id_peminjam">No. ID (NIS)</label>
                            <input type="text" id="no_id_peminjam" name="no_id_peminjam" value="<?= htmlspecialchars($data['data_siswa']['id_siswa'] ?? ''); ?>" readonly>
                        </div>
                    </div>

                    <h4 class="form-section-title">Detail Peminjaman</h4>
                    <div class="form-row">
                         <div class="form-group">
                            <label for="tanggal_pinjam">Tanggal Pinjam</label>
                            <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_kembali">Tanggal Kembali</label>
                            <input type="date" id="tanggal_kembali" name="tanggal_kembali" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="keperluan">Keperluan Peminjaman</label>
                        <textarea id="keperluan" name="keperluan" rows="3" placeholder="Contoh: Untuk Praktikum Fisika kelas 12 IPA 1" required></textarea>
                    </div>
                </form>

            <?php else: ?>
                <p style="text-align:center; color: #888; padding: 2rem 0;">Keranjang Anda kosong. Silahkan pilih barang yang ingin dipinjam terlebih dahulu.</p>
            <?php endif; ?>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" data-dismiss="modal">Tutup</button>
            
            <?php if (!empty($data['data_keranjang'])): ?>
                <button type="submit" class="btn-submit" form="peminjamanForm">Ajukan Peminjaman</button>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
</div>
</div>