<div class="container">
    <?php if (!empty($data['ubahdata'])) : ?>
    <form id="formUbahUser" action="<?= BASEURL ?>/User/prosesUbah" method="post" autocomplete="off" enctype="multipart/form-data">
        
        <!-- Hidden ID -->
        <input type="hidden" value="<?= e($data['ubahdata']['id_user']) ?>" name="id_user">
        <input type="hidden" value="<?= e($data['ubahdata']['role']) ?>" name="role">

        <div class="row">
            <div class="col-12">
                <div class="form-group mb-3">
                    <label for="nama_user" class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_user" class="form-control" 
                           value="<?= htmlspecialchars($data['ubahdata']['nama_user'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="username" class="form-label">Username (Email)</label>
                    <input type="text" name="username" class="form-control" 
                           value="<?= htmlspecialchars($data['ubahdata']['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="password" class="form-label">Ganti Password</label>
                    <div class="input-group">
                        <input id="passwordInput" type="password" name="password" class="form-control" 
                               placeholder="Kosongkan jika tidak ingin ganti password">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.togglePwdModalUser(this)">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    <small class="text-muted italic">Sistem menggunakan enkripsi SHA-256 otomatis.</small>
                </div>

                <?php if ($_SESSION['role'] == 'Admin') : ?>
                <div class="form-group mb-3">
                    <label for="role" class="form-label">Hak Akses (Role)</label>
                    <select name="role" id="role" class="form-control" required>
                        <option value="Asisten" <?= ($data['ubahdata']['role'] == 'Asisten') ? 'selected' : '' ?>>Asisten</option>
                        <option value="Admin" <?= ($data['ubahdata']['role'] == 'Admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <?php endif; ?>

                <?php 
                    $currPhoto = $data['foto_asisten']['photo_profil'] ?? $data['ubahdata']['photo_profil'] ?? null; 
                    $currTTD = $data['foto_asisten']['photo_path'] ?? $data['ubahdata']['photo_path'] ?? null;
                ?>
                <!-- EDIT FOTO PROFIL -->
                <div class="form-group mb-3">
                    <label for="photo_profil" class="form-label">Foto Profil (Admin/Asisten)</label>
                    <input type="file" class="form-control" name="photo_profil" accept="image/jpeg,image/png,image/gif,image/webp"> 
                    <small class="text-muted d-block mt-1">Format: JPG, PNG, GIF, atau WEBP | Maksimal: 5MB</small>
                    <div class="mt-2">
                        <?php if(!empty($currPhoto) && $currPhoto !== 'default.webp'): ?>
                            <small class="text-muted d-block">File saat ini: <?= basename($currPhoto) ?></small>
                            <img src="<?= BASEURL . '/' . $currPhoto ?>" alt="Profil" class="img-thumbnail mt-1 mb-2" width="80">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="hapus_profil" value="1" id="hapusProfilUser">
                                <label class="form-check-label text-danger" for="hapusProfilUser"><small>Hapus foto profil saat ini</small></label>
                            </div>
                        <?php else: ?>
                            <small class="text-muted d-block">File saat ini: default.webp</small>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- EDIT FOTO TTD -->
                <div class="form-group mb-4">
                    <label for="photo_path" class="form-label">Tanda Tangan Digital (TTD)</label>
                    <input type="file" class="form-control" name="photo_path" accept="image/jpeg,image/png,image/gif,image/webp"> 
                    <small class="text-muted d-block mt-1">Format: JPG, PNG, GIF, atau WEBP | Maksimal: 5MB</small>
                    <div class="mt-2">
                        <?php if(!empty($currTTD)): ?>
                            <small class="text-muted d-block">File saat ini: <?= basename($currTTD) ?></small>
                            <img src="<?= BASEURL . '/' . $currTTD ?>" alt="TTD" class="img-thumbnail mt-1 mb-2" width="120">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="hapus_ttd" value="1" id="hapusTTDUser">
                                <label class="form-check-label text-danger" for="hapusTTDUser"><small>Hapus foto TTD saat ini</small></label>
                            </div>
                        <?php else: ?>
                            <small class="text-muted d-block">File saat ini: Tidak ada</small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center border-top pt-3">
                    <button type="submit" class="btn btn-primary px-5">Simpan Perubahan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </form>
    <?php else: ?>
        <div class="alert alert-danger">Data user tidak ditemukan atau gagal dimuat.</div>
    <?php endif; ?>
</div>

<script>
    window.togglePwdModalUser = function(btn) {
        const pwdInput = document.getElementById('passwordInput');
        const icon = btn.querySelector('i');
        if (pwdInput.type === 'password') {
            pwdInput.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            pwdInput.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    };
</script>