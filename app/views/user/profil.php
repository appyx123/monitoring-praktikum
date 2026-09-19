<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>Profil Saya</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <?php Flasher::flash(); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-body">
                            <form action="<?= BASEURL; ?>/user/updateProfil" method="post" enctype="multipart/form-data" class="w-100">
                                <div class="row align-items-center w-100 m-0">
                                    <!-- Bagian Kiri: Foto Profil -->
                                    <div class="col-md-4 text-center border-right">
                                        <?php $foto = !empty($data['user']['photo_profil']) ? BASEURL . '/' . $data['user']['photo_profil'] : BASEURL . '/public/img/user.webp'; ?>
                                        <img class="profile-user-img img-fluid img-circle shadow-sm mb-3"
                                             src="<?= e($foto) ?>"
                                             alt="User profile picture"
                                             id="preview-img"
                                             style="width: 180px; height: 180px; object-fit: cover; border-radius: 50%;">
                                        <h3 class="profile-username" style="font-weight: 600;">
                                            <?= e($data['user']['nama_user'] ?? $_SESSION['nama_user']) ?>
                                        </h3>
                                        <p class="text-muted"><span class="badge badge-primary px-3 py-2" style="font-size: 0.9rem;"><?= e($_SESSION['role']) ?></span></p>
                                        
                                        <!-- Upload button -->
                                        <div class="mt-3">
                                            <label for="photo_profil" class="btn btn-sm btn-outline-primary" style="cursor:pointer;">
                                                <i class="fas fa-camera"></i> Ganti Foto
                                            </label>
                                            <input type="file" id="photo_profil" name="photo_profil" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" onchange="previewImage(this)">
                                            <small class="d-block text-muted mt-1">Maks 5MB (JPG/PNG/WEBP)</small>
                                            <?php if (!empty($data['user']['photo_profil']) && $data['user']['photo_profil'] !== 'public/img/user.webp'): ?>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" name="hapus_profil" value="1" id="hapusProfilCheckSaya">
                                                    <label class="form-check-label text-danger" for="hapusProfilCheckSaya"><small>Hapus foto saat ini</small></label>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Bagian Kanan: Detail & Ganti Password -->
                                    <div class="col-md-8 px-4">
                                        <h4 class="mb-4 text-primary" style="font-weight: 600;"><i class="fas fa-info-circle"></i> Detail Informasi & Keamanan</h4>
                                        
                                        <input type="hidden" name="id_user" value="<?= e($data['user']['id_user'] ?? $_SESSION['id_user']) ?>">
                                        
                                        <div class="form-group row mb-4">
                                            <label class="col-sm-3 col-form-label text-muted">Nama Lengkap</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="nama_user" class="form-control" value="<?= e($data['user']['nama_user'] ?? $_SESSION['nama_user']) ?>" required>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-4">
                                            <label class="col-sm-3 col-form-label text-muted">Email (Username)</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="username" class="form-control" value="<?= e($data['user']['username'] ?? $_SESSION['username']) ?>" required>
                                            </div>
                                        </div>


                                        <hr>
                                        <h5 class="mt-4 mb-3" style="font-weight: 600;">Keamanan</h5>
                                        
                                        <div class="form-group row mb-4">
                                            <label class="col-sm-3 col-form-label text-muted">Password Lama</label>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <input type="password" id="passwordLama" name="password_lama" class="form-control" placeholder="Masukkan password lama">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary togglePassword" data-target="passwordLama">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label text-muted">Password Baru</label>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <input type="password" id="passwordBaru" name="password_baru" class="form-control" placeholder="Masukkan password baru">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary togglePassword" data-target="passwordBaru">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="text-muted mt-1 d-block">Biarkan kosong jika tidak ingin ganti password.</small>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right mt-4 pt-3 border-top">
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="fas fa-save mr-1"></i> Simpan Perubahan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleButtons = document.querySelectorAll('.togglePassword');
        
        toggleButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });
</script><script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
