<?php
$rows = $data['restore'] ?? [];
$title = $data['judul'] ?? 'Restore Data';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-undo"></i> <?= htmlspecialchars($title) ?>
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Flash Message -->
            <div class="row">
                <div class="col-12">
                    <?php Flasher::flash(); ?>
                </div>
            </div>

            <?php if (!empty($rows)): ?>
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-trash-alt"></i> Daftar Data yang Dihapus
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-warning"><?= count($rows) ?> data ditemukan</span>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover m-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th style="width: 25%">Identitas Data</th>
                                    <th style="width: 15%">Jenis Data</th>
                                    <th style="width: 18%">Waktu Hapus</th>
                                    <th style="width: 19%; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach ($rows as $r): ?>
                                    <?php 
                                        // Sesuaikan dengan nama kolom di tabel trs_restore[cite: 1]
                                        $isi = json_decode($r['data_json'], true); 
                                        $id = $r['id_restore']; // Gunakan id_restore sesuai DB[cite: 1]
                                    ?>
                                    <tr>
                                        <td><strong><?= $no++ ?></strong></td>
                                        <td>
                                            <?php
                                                // Tampilkan identitas berdasarkan jenis data
                                                switch ($r['jenis_data']) {
                                                    case 'mst_asisten':
                                                        echo '<strong class="text-primary">' . htmlspecialchars($isi['nama_asisten'] ?? '-') . '</strong>';
                                                        echo '<br><small class="text-muted">Stambuk: ' . htmlspecialchars($isi['stambuk'] ?? '-') . '</small>';
                                                        break;
                                                    case 'mst_dosen':
                                                        echo '<strong class="text-primary">' . htmlspecialchars($isi['nama_dosen'] ?? '-') . '</strong>';
                                                        echo '<br><small class="text-muted">NIP: ' . htmlspecialchars($isi['nip'] ?? '-') . '</small>';
                                                        break;
                                                    case 'mst_ruangan':
                                                        echo '<strong class="text-primary">' . htmlspecialchars($isi['nama_ruangan'] ?? '-') . '</strong>';
                                                        break;
                                                    case 'mst_jurusan':
                                                        echo '<strong class="text-primary">' . htmlspecialchars($isi['jurusan'] ?? '-') . '</strong>';
                                                        echo '<br><small class="text-muted">Singkatan: ' . htmlspecialchars($isi['singkatan_jurusan'] ?? '-') . '</small>';
                                                        break;
                                                    case 'mst_kelas':
                                                        echo '<strong class="text-primary">' . htmlspecialchars($isi['kelas'] ?? '-') . '</strong>';
                                                        echo '<br><small class="text-muted">Angkatan: ' . htmlspecialchars($isi['angkatan'] ?? '-') . '</small>';
                                                        break;
                                                    case 'mst_matakuliah':
                                                        echo '<strong class="text-primary">' . htmlspecialchars($isi['nama_matkul'] ?? '-') . '</strong>';
                                                        echo '<br><small class="text-muted">SKS: ' . htmlspecialchars($isi['sks'] ?? '-') . '</small>';
                                                        break;
                                                    case 'mst_user':
                                                        echo '<strong class="text-primary">' . htmlspecialchars($isi['nama_user'] ?? '-') . '</strong>';
                                                        echo '<br><small class="text-muted">Username: ' . htmlspecialchars($isi['username'] ?? '-') . '</small>';
                                                        break;
                                                    default:
                                                        echo '<span class="text-muted">Data Tidak Dikenal</span>';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?= htmlspecialchars($r['jenis_data']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?= date('d/m/Y H:i', strtotime($r['deleted_at'])) ?>
                                            </small>
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="btn-group btn-group-sm">
                                                <!-- Tombol Lihat Detail -->
                                                <button type="button" class="btn btn-info" 
                                                        data-bs-toggle="modal" data-bs-target="#modal-detail-<?= e($id) ?>">
                                                    <i class="fas fa-eye"></i> Detail
                                                </button>
                                                <!-- Tombol Restore (Redirect ke controller method kembalikan) -->
                                                <form action="<?= BASEURL; ?>/restore/kembalikan/<?= e($id) ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                    <button type="submit" class="btn btn-success" onclick="return confirm('Kembalikan data ini?')">
                                                        <i class="fas fa-redo-alt"></i> Restore
                                                    </button>
                                                </form>
                                                <!-- Tombol Hapus (Redirect ke controller method hapusPermanen) -->
                                                <form action="<?= BASEURL; ?>/restore/hapusPermanen/<?= e($id) ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Hapus permanen dari sistem?')">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>


                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Tidak ada data di tempat sampah.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Rendering Modals Outside Table untuk Menjaga Struktur HTML -->
<?php if (!empty($rows)): ?>
    <?php foreach ($rows as $r): ?>
        <?php 
            $isi = json_decode($r['data_json'], true); 
            $id = $r['id_restore']; 
        ?>
        <!-- Modal Detail -->
        <div class="modal fade" id="modal-detail-<?= e($id) ?>" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white">Detail Data JSON</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Raw Data JSON:</strong></p>
                        <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto; font-size: 12px;"><?= htmlspecialchars(json_encode($isi, JSON_PRETTY_PRINT)) ?></pre>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>