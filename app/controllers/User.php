<?php

class User extends Controller {
    
    public function index() {
        $this->isAdmin();
        $data['title'] = 'Data User';
        $data['user'] = $this->model('User_model')->tampil();
        
        $this->view('templates/header', $data);
        $this->view('templates/topbar');
        $this->view('templates/sidebar');
        $this->view('user/index', $data);
        $this->view('templates/footer');
    }
    
    public function modalTambah(){
        $this->isAdmin();
        $this->view('user/tambah_user');
    }

    public function tambah() {
        $this->isAdmin();
        $data = $_POST;
        
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if ($this->model('User_model')->tambah($data) > 0) {
            Flasher::setFlash(' berhasil ditambahkan', '', 'success');
        } else {
            Flasher::setFlash(' tidak berhasil ditambahkan', '', 'danger');
        }
        
        header('Location: ' . BASEURL . '/user');
        exit;
    }

    public function ubahModal() {
        $id = $_POST['id'];
        
        // Cek Izin: Hanya Admin, ATAU Asisten yang mengedit dirinya sendiri
        if ($_SESSION['role'] !== 'Admin' && $_SESSION['id_user'] != $id) {
            header('HTTP/1.0 403 Forbidden');
            exit('Akses ditolak');
        }

        $data['ubahdata'] = $this->model('User_model')->detailUser($id);
        
        // Ambil juga data asisten untuk photo dan ttd (jika dia asisten)
        $data['foto_asisten'] = $this->model('Asisten_model')->getByUserId($id); 

        $this->view('user/ubah_user', $data);
    }
    
        public function prosesUbah() {
        $data = $_POST;
        $id_user = $data['id_user'];
        
        // Cek Izin: Hanya Admin, ATAU Asisten yang mengedit dirinya sendiri
        if ($_SESSION['role'] !== 'Admin' && $_SESSION['id_user'] != $id_user) {
            header('HTTP/1.0 403 Forbidden');
            exit('Akses ditolak');
        }
        
        $userLama = $this->model('User_model')->getUserById($id_user);
        $asistenLama = $this->model('Asisten_model')->getByUserId($id_user); 

        // 1. Password
        if (empty($data['password'])) {
            $data['password'] = $userLama['password'];
        } else {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $uploadErrors = [];
        $uploadSuccess = [];

        // 2. Upload Foto Profil atau Hapus
        if (isset($_POST['hapus_profil']) && $_POST['hapus_profil'] == '1') {
            $data['photo_profil'] = null;
        } elseif (!isset($_FILES['photo_profil']) || $_FILES['photo_profil']['error'] === UPLOAD_ERR_NO_FILE) {
            $data['photo_profil'] = $asistenLama ? ($asistenLama['photo_profil'] ?? null) : ($userLama['photo_profil'] ?? null);
        } else {
            $uploadProfil = $this->prosesUpload('photo_profil', 'public/img/uploads/', 'profil_' . $id_user);
            if ($uploadProfil['status']) {
                $data['photo_profil'] = $uploadProfil['nama_file'];
                $uploadSuccess[] = "Foto Profil (WebP)";
            } else {
                $data['photo_profil'] = $asistenLama ? ($asistenLama['photo_profil'] ?? null) : ($userLama['photo_profil'] ?? null);
                $uploadErrors[] = "Profil: " . $uploadProfil['pesan'];
            }
        }

        // 3. Upload TTD atau Hapus
        if (isset($_POST['hapus_ttd']) && $_POST['hapus_ttd'] == '1') {
            $data['photo_path'] = null;
        } elseif (!isset($_FILES['photo_path']) || $_FILES['photo_path']['error'] === UPLOAD_ERR_NO_FILE) {
            $data['photo_path'] = $asistenLama ? ($asistenLama['photo_path'] ?? null) : ($userLama['photo_path'] ?? null);
        } else {
            $uploadTTD = $this->prosesUpload('photo_path', 'public/img/signature/', 'ttd_' . $id_user);
            if ($uploadTTD['status']) {
                $data['photo_path'] = $uploadTTD['nama_file'];
                $uploadSuccess[] = "TTD (WebP)";
            } else {
                $data['photo_path'] = $asistenLama ? ($asistenLama['photo_path'] ?? null) : ($userLama['photo_path'] ?? null);
                $uploadErrors[] = "TTD: " . $uploadTTD['pesan'];
            }
        }

        // CEK JIKA ADA ERROR UPLOAD GAMBAR
        if (!empty($uploadErrors)) {
            Flasher::setFlash('Gagal Upload Gambar', implode(' | ', $uploadErrors), 'danger');
            $redirectURL = (isset($_SESSION['role']) && $_SESSION['role'] == 'Asisten') ? '/asisten' : '/user';
            header('Location: ' . BASEURL . $redirectURL);
            exit;
        }

        // 4. Update Database (Tabel User)
        $updateUser = $this->model('User_model')->ubahDataUser($data);
        
        // 5. Update Database (Tabel Asisten)
        $updateAsisten = 0;
        if ($asistenLama) {
            // Update foto & ttd ke tabel asisten
            $updateAsisten = $this->model('Asisten_model')->updateFilesByUserId(
                $id_user, 
                $data['photo_profil'], 
                $data['photo_path']
            );
            // Sinkronisasi Nama Asisten
            $this->model('Asisten_model')->updateNamaByUserId($id_user, $data['nama_user']);
        }

        if ($updateUser >= 0 && $updateAsisten >= 0) {
            // Perbarui session agar foto profil langsung berubah di sidebar
            if ($id_user == $_SESSION['id_user']) {
                $_SESSION['photo_profil'] = $data['photo_profil'];
            }
            Flasher::setFlash('Berhasil', 'diperbarui', 'success');
        } else {
            Flasher::setFlash('Gagal', 'memperbarui data', 'danger');
        }

        if (isset($_SESSION['role']) && $_SESSION['role'] == 'Asisten') {
            header('Location: ' . BASEURL . '/asisten');
        } else {
            header('Location: ' . BASEURL . '/user');
        }
        exit;
    }


    public function hapus($id){
        $this->verifyCsrfToken();
        $this->isAdmin();
        if($this->model('User_model')->prosesHapus($id)){
            Flasher::setFlash(' berhasil dihapus', '', 'success');
        }else{
            Flasher::setFlash(' tidak berhasil dihapus', '', 'danger');
        }
        header('Location: '.BASEURL. '/user');
        exit;
    }

    public function profil() {
        if (!isset($_SESSION['id_user'])) {
            header('Location: ' . BASEURL . '/login');
            exit;
        }

        $id_user = $_SESSION['id_user'];
        // Ambil data user dari model
        $data['user'] = $this->model('User_model')->getUserById($id_user);
        $data['title'] = 'Profil Saya';

        // Jika data tidak ditemukan di database, gunakan data session agar tidak error
        if (!$data['user']) {
            $data['user'] = [
                'id_user' => $_SESSION['id_user'],
                'username' => $_SESSION['username'],
                'nama_user' => $_SESSION['nama_user'],
                'role' => $_SESSION['role']
            ];
        }

        $this->view('templates/header', $data);
        $this->view('templates/topbar');
        $this->view('templates/sidebar');
        
        // Hapus pengecekan role Admin agar Asisten juga bisa akses profil
        $this->view('user/profil', $data); 

        $this->view('templates/footer');
    }

    public function updateProfil() {
        $this->isLogin(); // Pastikan user telah terotentikasi

        // ID diambil secara mutlak dari sesi, abaikan input $_POST['id_user']
        $id_user = (int)$_SESSION['id_user'];
        $passwordLama = $_POST['password_lama'] ?? '';
        $passwordBaru = $_POST['password_baru'] ?? '';
        
        $userLama = $this->model('User_model')->getUserById($id_user);
        if (!$userLama) {
            Flasher::setFlash('Gagal', 'Pengguna tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/login');
            exit;
        }

        // Jika ingin mengganti password, verifikasi password lama terlebih dahulu
        $passwordFinal = $userLama['password'];
        if (!empty($passwordBaru)) {
            if (empty($passwordLama) || !password_verify($passwordLama, $userLama['password'])) {
                Flasher::setFlash('Gagal', 'Kata sandi lama salah atau tidak diisi!', 'danger');
                header('Location: ' . BASEURL . '/user/profil');
                exit;
            }
            $passwordFinal = password_hash($passwordBaru, PASSWORD_DEFAULT);
        }

        $dataUpdate = [
            'id_user'   => $id_user,
            'username'  => filter_var(trim($_POST['username'] ?? ''), FILTER_SANITIZE_EMAIL),
            'nama_user' => htmlspecialchars(trim($_POST['nama_user'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'password'  => $passwordFinal,
            'role'      => $userLama['role'] // Cegah manipulasi role sendiri
        ];

        // Proses Upload Foto Profil atau Hapus
        $photoProfilBaru = null;
        $hapusProfil = false;
        if (isset($_POST['hapus_profil']) && $_POST['hapus_profil'] == '1') {
            $photoProfilBaru = null;
            $hapusProfil = true;
            $_SESSION['photo_profil'] = null;
        } elseif (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
                $uploadProfil = $this->prosesUpload('photo_profil', 'public/img/uploads/', 'profil_' . $id_user);
                if ($uploadProfil['status']) {
                    $photoProfilBaru = $uploadProfil['nama_file'];
                    $_SESSION['photo_profil'] = $photoProfilBaru;
                } else {
                    Flasher::setFlash('Gagal', 'Upload foto gagal: ' . $uploadProfil['pesan'], 'danger');
                    header('Location: ' . BASEURL . '/user/profil');
                    exit;
                }
            } else {
                Flasher::setFlash('Gagal', 'Upload foto gagal: Error kode ' . $_FILES['photo_profil']['error'], 'danger');
                header('Location: ' . BASEURL . '/user/profil');
                exit;
            }
        }

        if ($this->model('User_model')->ubahDataUserLengkap($dataUpdate) >= 0) {
            // Perbarui session agar langsung terlihat perubahannya
            $_SESSION['nama_user'] = $dataUpdate['nama_user'];
            $_SESSION['username'] = $dataUpdate['username'];
            
            // Simpan foto ke DB jika ada yang diupload atau dihapus
            $asistenLama = $this->model('Asisten_model')->getByUserId($id_user); 
            
            if ($photoProfilBaru || $hapusProfil) {
                // Selalu update mst_user
                $this->model('User_model')->updateFotoViaUser($id_user, $photoProfilBaru);
                // Jika dia asisten, update mst_asisten juga
                if ($asistenLama) {
                    $this->model('Asisten_model')->updateFotoViaUser($id_user, $photoProfilBaru);
                }
            }
            
            // Sinkronisasi Nama Asisten
            if ($asistenLama) {
                $this->model('Asisten_model')->updateNamaByUserId($id_user, $dataUpdate['nama_user']);
            }
            
            Flasher::setFlash('Berhasil', 'Profil Anda telah berhasil diperbarui', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal memperbarui profil', 'danger');
        }

        header('Location: ' . BASEURL . '/user/profil');
        exit;
    }



}