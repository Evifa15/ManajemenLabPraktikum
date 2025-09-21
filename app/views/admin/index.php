<div class="content">
    <div class="dashboard-container">
        <div class="dashboard-layout">
            <div class="main-column">
                <div class="overview-container">
                    <div class="overview-card card-blue">
                        <div class="card-icon-wrapper"><img src="https://img.icons8.com/ios-filled/50/FFFFFF/conference-call.png" alt="pengguna"/></div>
                        <span>Total Pengguna</span>
                        <p><?= htmlspecialchars($data['totalPengguna']); ?></p>
                    </div>
                    <div class="overview-card card-orange">
                        <div class="card-icon-wrapper"><img src="https://img.icons8.com/ios-filled/50/FFFFFF/holding-box.png" alt="jenis barang"/></div>
                        <span>Total Jenis Barang</span>
                        <p><?= htmlspecialchars($data['totalJenisBarang']); ?></p>
                    </div>
                    <div class="overview-card card-dark-blue">
                        <div class="card-icon-wrapper"><img width="24" height="24" src="https://img.icons8.com/external-kmg-design-glyph-kmg-design/32/FFFFFF/external-increase-payment-2-kmg-design-glyph-kmg-design.png" alt="external-increase-payment-2-kmg-design-glyph-kmg-design"/></div>
                        <span>Total Stok Barang</span>
                        <p><?= htmlspecialchars($data['totalStok']); ?></p>
                    </div>
                    <div class="overview-card card-red">
                        <div class="card-icon-wrapper"><img width="24" height="24" src="https://img.icons8.com/ios-filled/50/FFFFFF/open-box.png" alt="open-box"/></div>
                        <span>Barang Dipinjam</span>
                        <p><?= htmlspecialchars($data['barangDipinjam']); ?></p>
                    </div>
                </div>

                <div class="latest-loans-container">
                    <div class="peminjaman-header">
                        <h4>Peminjaman Terbaru</h4>
                        <a href="<?= BASEURL; ?>/admin/laporan" class="btn-lihat-semua">Lihat Semua ></a>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Peminjam</th>
                                    <th>Nama Barang</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data['peminjamanTerbaru'])): ?>
                                    <?php foreach ($data['peminjamanTerbaru'] as $item): ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($item['nama_peminjam']); ?><br>
                                                <small>ID: <?= htmlspecialchars($item['no_id_peminjam'] ?? '-'); ?></small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($item['nama_barang']); ?><br>
                                                <small>ID: <?= htmlspecialchars($item['kode_barang']); ?></small>
                                            </td>
                                            <td><?= date('d M Y', strtotime($item['tanggal_pinjam'])); ?></td>
                                            <td style="text-align: center;">
                                                <?php
                                                    $status_class = strtolower(str_replace(' ', '-', $item['status']));
                                                ?>
                                                <span class="status-badge status-<?= $status_class; ?>">
                                                    <?php
                                                        if ($item['status'] === 'Menunggu Verifikasi') {
                                                            echo 'Diperiksa';
                                                        } else {
                                                            echo htmlspecialchars($item['status']);
                                                        }
                                                    ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align: center;">Belum ada riwayat peminjaman.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="side-column">
                <div class="dashboard-banner">
                    <div class="banner-text">
                        <h3>Hai, <?= htmlspecialchars(explode(' ', $data['username'])[0]); ?>!</h3>
                        <p>Selamat Datang kembali.</p>
                    </div>
                    <a href="<?= BASEURL; ?>/admin/profile" class="banner-profile-pic">
                        <?php 
                            $foto = $data['profile']['foto'] ?? 'default.png';
                            
                            // Cek jika nama file foto adalah default
                            if ($foto === 'default.png') {
                                $fotoUrl = BASEURL . '/img/siswa/default.png'; // Arahkan ke folder siswa
                            } else {
                                // Cek keberadaan file foto di folder staff
                                $fotoPath = APP_ROOT . '/public/img/staff/' . htmlspecialchars($foto);
                                $fotoUrl = (file_exists($fotoPath))
                                            ? BASEURL . '/img/staff/' . htmlspecialchars($foto)
                                            : BASEURL . '/img/siswa/default.png'; // Cadangan jika foto tidak ditemukan
                            }
                        ?>
                        <img src="<?= $fotoUrl . '?v=' . time(); ?>" alt="Foto Profil">
                    </a>
                </div>

                <div class="chart-container">
                    <h4>Status Ketersediaan Barang</h4>
                    <div class="chart-wrapper">
                        <canvas id="kondisiChart"></canvas>
                    </div>
                    <div class="chart-legend">
                        <?php if (!empty($data['chartDetails'])): ?>
                            <ul>
                            <?php foreach ($data['chartDetails'] as $detail): ?>
                                <li>
                                    <span class="color-box color-<?= $detail['color']; ?>"></span>
                                    <span class="label-text"><?= htmlspecialchars($detail['label']); ?> (<?= htmlspecialchars($detail['percentage']); ?>%)</span>
                                    <span class="label-value"><?= htmlspecialchars($detail['value']); ?></span>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>