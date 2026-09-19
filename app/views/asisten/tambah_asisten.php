<form id="formTambahDataAsisten" action="<?= BASEURL ?>/asisten/tambah" method="post" autocomplete="off" enctype="multipart/form-data">
    <div class="row">
        <div class="col-12">            
            
            <!-- Data User (Login) -->
            <div class="alert alert-info py-2 mb-3"><small><strong>Data Login Akun</strong></small></div>
            
            <div class="form-group mb-3">
                <label for="usernameInput" class="form-label">Username (Email)</label>
                <input type="email" name="username" id="usernameInput" class="form-control" placeholder="Contoh: nama@student.umi.ac.id" value="<?= htmlspecialchars($_SESSION['old']['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                <!-- Helper Text -->
                <small class="text-muted d-block mt-1">
                    Akhiran yang diizinkan: 
                    <span class="badge badge-light text-dark border">.iclabs@umi.ac.id</span>
                    <span class="badge badge-light text-dark border">@student.umi.ac.id</span>
                    <span class="badge badge-light text-dark border">@umi.ac.id</span>
                    <span class="badge badge-light text-dark border">@gmail.com</span>
                </small>
                <!-- Tempat Pesan Error muncul -->
                <small id="emailError" class="text-danger font-italic" style="display:none;"></small>
            </div>
            
            <!-- INPUT PASSWORD DENGAN FITUR SHOW/HIDE -->
            <div class="form-group mb-3">
                <label for="passwordInput" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="(Opsional) Default: iclabs-umi">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Data Profil Asisten -->
            <div class="alert alert-info py-2 mb-3"><small><strong>Data Profil Asisten</strong></small></div>

            <div class="form-group mb-3">
                <label for="stambuk" class="form-label">Stambuk</label>
                <input type="text" name="stambuk" class="form-control" placeholder="Masukkan Stambuk" required>
            </div>
            <div class="form-group mb-3">
                <label for="nama_asisten" class="form-label">Nama Asisten</label>
                <input type="text" name="nama_asisten" class="form-control" placeholder="Masukkan Nama Lengkap" required>
            </div>
            <div class="form-group mb-3">
                <label for="angkatan" class="form-label">Angkatan</label>
                <input type="text" name="angkatan" class="form-control" placeholder="Masukkan Angkatan" required>
            </div>
            <div class="form-group mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="">Pilih Status</option>
                        <option value="Asisten">Asisten</option>
                        <option value="Calon Asisten">Calon Asisten</option>
                    </select>
            </div>
            <div class="form-group mb-3">
                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Pria">Pria</option>
                        <option value="Wanita">Wanita</option>
                    </select>
            </div>

            <div class="form-group mb-3">
                <label for="formFile" class="form-label">Masukkan Foto Profil</label>
                <input class="form-control" type="file" name="photo_profil" accept="image/jpeg,image/png,image/gif,image/webp">
                <small class="text-muted d-block mt-1">
                    Format: JPG, PNG, GIF, atau WEBP | Maksimal: 5MB
                </small>
            </div>
            <div class="form-group mb-3">
                <label for="formFile" class="form-label">Masukkan Foto TTD</label>
                <input class="form-control" type="file" name="photo_path" accept="image/jpeg,image/png,image/gif,image/webp">
                <small class="text-muted d-block mt-1">
                    Format: JPG, PNG, GIF, atau WEBP | Maksimal: 5MB
                </small>
            </div><br>
        </div>
        </div>
        <div class="col-12 text-center mt-3">
            <button type="submit" class="btn btn-primary">Tambah Data</button>
            <button type="button" class="btn btn-secondary ml-2" data-bs-dismiss="modal">Batal</button>
        </div>
    </div>
</form>
<?php unset($_SESSION['old']); ?>

<script>
// Validasi file upload real-time
function validateImageFile(input) {
    const allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    const fieldName = input.name === 'photo_profil' ? 'Foto Profil' : 'Foto TTD';
    
    if (input.files.length === 0) return true;
    
    const file = input.files[0];
    const ext = file.name.split('.').pop().toLowerCase();
    
    // Cek ukuran
    if (file.size > maxSize) {
        alert(`${fieldName}: Ukuran file terlalu besar! Maksimal 5MB (ukuran: ${(file.size / 1024 / 1024).toFixed(2)}MB)`);
        input.value = '';
        return false;
    }
    
    // Cek Ekstensi dan MIME type
    if (!allowedExts.includes(ext) && !file.type.startsWith('image/')) {
        alert(`${fieldName}: Format file tidak valid!\n\nFormat yang diizinkan:\n- JPG / JPEG\n- PNG\n- GIF\n- WEBP`);
        input.value = '';
        return false;
    }
    
    return true;
}

// Event listener untuk field upload
document.querySelector('input[name="photo_profil"]').addEventListener('change', function() {
    validateImageFile(this);
});

document.querySelector('input[name="photo_path"]').addEventListener('change', function() {
    validateImageFile(this);
});

// Validasi form submit
document.getElementById('formTambahDataAsisten').addEventListener('submit', function(e) {
    const form = this;
    
    // Cek select fields tidak boleh kosong
    const statusSelect = document.querySelector('select[name="status"]');
    const jenisKelaminSelect = document.querySelector('select[name="jenis_kelamin"]');
    
    if (statusSelect.value === '') {
        alert('Status harus dipilih!');
        statusSelect.focus();
        e.preventDefault();
        return false;
    }
    
    if (jenisKelaminSelect.value === '') {
        alert('Jenis Kelamin harus dipilih!');
        jenisKelaminSelect.focus();
        e.preventDefault();
        return false;
    }
    
    const photoProfile = document.querySelector('input[name="photo_profil"]');
    const photoPath = document.querySelector('input[name="photo_path"]');
    
    // Validasi file jika ada
    if (photoProfile.files.length > 0 && !validateImageFile(photoProfile)) {
        e.preventDefault();
        return false;
    }
    
    if (photoPath.files.length > 0 && !validateImageFile(photoPath)) {
        e.preventDefault();
        return false;
    }
    
    return true;
});
</script>
                