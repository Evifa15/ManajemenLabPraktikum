<div class="content">
    <div class="main-table-container">
        <?php if (!empty($data['data_kelas_wali'])): ?>
            <div class="tab-links-wrapper">
                <?php foreach ($data['data_kelas_wali'] as $index => $item): ?>
                    <button class="tab-link <?= $index === 0 ? 'active' : '' ?>" data-target="#kelas-<?= $item['kelas']['id'] ?>">
                        <?= htmlspecialchars($item['kelas']['nama_kelas']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="tab-content-wrapper">
                <?php foreach ($data['data_kelas_wali'] as $index => $item): ?>
                    <div class="tab-content <?= $index === 0 ? 'active' : '' ?>" id="kelas-<?= $item['kelas']['id'] ?>">
                        <div class="table-wrapper" style="margin-top: 1.5rem;">
                            <table id="tabel-siswa-<?= $item['kelas']['id'] ?>" class="data-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        <th>ID Siswa (NIS)</th>
                                        <th>Jenis Kelamin</th>
                                        <th>No. HP</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($item['siswa'])): ?>
                                        <?php $no = 1; foreach ($item['siswa'] as $siswa): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($siswa['nama']); ?></td>
                                            <td><?= htmlspecialchars($siswa['id_siswa']); ?></td>
                                            <td><?= htmlspecialchars($siswa['jenis_kelamin']); ?></td>
                                            <td><?= htmlspecialchars($siswa['no_hp'] ?? '-'); ?></td>
                                            <td class="action-buttons">
                                                <a href="<?= BASEURL ?>/guru/detailSiswa/<?= $siswa['id'] ?>" class="view-btn" title="Lihat Detail">
                                                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/></svg>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" style="text-align: center;">Belum ada siswa di kelas ini.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state" style="text-align: center; padding: 2rem;">
                <p>Anda belum menjadi wali kelas manapun.</p>
            </div>
        <?php endif; ?>
    </div>
</div>