<?php

class Asisten extends Controller {
    
    public function index(){
        $data['title'] = 'Data Asisten';
        $id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;
        $role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
    
        if ($role == 'Asisten') {
            $data['asisten'] = $this->model('Asisten_model')->getAsistenDetails($id_user);
            $data['user'] = $this->model('User_model')->getUserDetails($id_user);
        } else {
            $data['asisten'] = $this->model('Asisten_model')->tampil();
        }
    
        $this->view('templates/header', $data);
        $this->view('templates/topbar');
        $this->view('templates/sidebar');
        $this->view('asisten/index', $data);
        $this->view('templates/footer');
    }        

    public function modalTambah(){
        $this->isAdmin();
        $data['userOptions'] = $this->model('Asisten_model')->tampilUser();
        $this->view('asisten/tambah_asisten', $data);
    }


    public function tambah()
    {
        $this->isAdmin();
        $data = $_POST;
        
        // 1. Validasi Field Kosong
        $requiredFields = ['username', 'stambuk', 'nama_asisten', 'angkatan', 'status', 'jenis_kelamin'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $_SESSION['old'] = $_POST;
                Flasher::setFlash('Semua field wajib diisi!', 'Gagal', 'danger');
                header('Location: ' . BASEURL . '/asisten');
                exit;
            }
        }
        
        // 2. Cek apakah Username atau Stambuk sudah ada (Pencegahan awal)
        if ($this->model('User_model')->getUserByUsername($data['username'])) {
            $_SESSION['old'] = $_POST;
            Flasher::setFlash('Username/Email sudah terdaftar!', 'Gagal', 'danger');
            header('Location: ' . BASEURL . '/asisten');
            exit;
        }

        if ($this->model('Asisten_model')->cekStambuk($data['stambuk'])) {
            $_SESSION['old'] = $_POST;
            Flasher::setFlash('Stambuk ini sudah ada di sistem!', 'Gagal', 'danger');
            header('Location: ' . BASEURL . '/asisten');
            exit;
        }

        // 3. Persiapan Password & Upload File
        $passwordHash = $this->validateAkun($data['username'], $data['password']);
        $namaFileCustom = preg_replace('/[^A-Za-z0-9]/', '_', $data['nama_asisten']);
        
        $data['photo_profil'] = null;
        if (!empty($_FILES['photo_profil']['name'])) {
            $uploadProfil = $this->prosesUpload('photo_profil', 'public/img/uploads/', $namaFileCustom . '_profil');
            if ($uploadProfil['status']) $data['photo_profil'] = $uploadProfil['nama_file'];
        }

        $data['photo_path'] = null;
        if (!empty($_FILES['photo_path']['name'])) {
            $uploadSignature = $this->prosesUpload('photo_path', 'public/img/signature/', $namaFileCustom . '_ttd');
            if ($uploadSignature['status']) $data['photo_path'] = $uploadSignature['nama_file'];
        }

        // 4. Proses Eksekusi Database
        try {
            // Tambah User Terlebih Dahulu
            $dataUser = [
                'nama_user' => $data['nama_asisten'], 
                'username'  => $data['username'],
                'password'  => $passwordHash,
                'role'      => 'Asisten'
            ];

            $id_user = $this->model('User_model')->tambah($dataUser);

            if ($id_user > 0) {
                // Ambil ID User yang baru dibuat (untuk Foreign Key)
                $newUser = $this->model('User_model')->getUserByUsername($data['username']);
                
                // Pembersihan array untuk dikirim ke model asisten
                $dataFinal = [
                    'stambuk'       => $data['stambuk'],
                    'nama_asisten'  => $data['nama_asisten'],
                    'angkatan'      => $data['angkatan'],
                    'status'        => $data['status'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'id_user'       => $newUser['id_user'],
                    'photo_profil'  => $data['photo_profil'],
                    'photo_path'    => $data['photo_path']
                ];

                if ($this->model('Asisten_model')->tambah($dataFinal) > 0) {
                    $_SESSION['old'] = [];
                    Flasher::setFlash('Data Asisten Berhasil Ditambahkan', '', 'success');
                } else {
                    // Jika asisten gagal, hapus usernya agar tidak jadi data sampah
                    $this->model('User_model')->prosesHapus($newUser['id_user']);
                    Flasher::setFlash('Gagal menyimpan data asisten.', '', 'danger');
                }
            } else {
                Flasher::setFlash('Gagal membuat akun user.', '', 'danger');
            }

        } catch (PDOException $e) {
            // Tangani jika terjadi Duplicate Entry (Kode 1062) saat Double Submit
            if ($e->errorInfo[1] == 1062) {
                Flasher::setFlash('Data sudah ada atau terkirim ganda. Silakan cek daftar asisten.', 'Info', 'warning');
            } else {
                Flasher::setFlash('Error Database: ' . $e->getMessage(), 'Gagal', 'danger');
            }
        }

        header('Location: ' . BASEURL . '/asisten');
        exit;
    }

    public function ubahModal(){
        $this->isAdmin();
        $id = $_POST['id'];
        $data['userOptions'] = $this->model('Asisten_model')->tampilUser();
        $data['ubahdata'] = $this->model('Asisten_model')->getUbahData($id);
        $this->view('asisten/ubah_asisten', $data);
    }

        public function prosesUbah() {

        $this->isAdmin();
        $data = $_POST;
        $id_asisten = $data['id_asisten'];
        $id_user = $data['id_user'];

        // 1. Ambil data lama untuk pengecekan
        $asistenLama = $this->model('Asisten_model')->detailAsisten($id_asisten);
        $userLama = $this->model('User_model')->getUserById($id_user);

        // 2. VALIDASI EMAIL
        if ($data['username'] !== $userLama['username']) {
            $cekEmail = $this->model('User_model')->getUserByUsernameExceptMe($data['username'], $id_user);
            if ($cekEmail) {
                Flasher::setFlash('Gagal', 'Email sudah dipakai orang lain!', 'danger');
                header('Location: ' . BASEURL . '/asisten');
                exit;
            }
        }

        // 3. Sanitasi Nama File & Variabel Notifikasi
        $namaBersih = preg_replace('/[^A-Za-z0-9]/', '_', $data['nama_asisten']); 
        $uploadErrors = [];
        $uploadSuccess = []; // Untuk mencatat upload yang berhasil

        // 4. Logika Password SHA-256
        if (empty($data['password'])) {
            $data['password'] = $userLama['password']; 
        } else {
            $data['password'] = hash('sha256', $data['password']); 
        }

        // 5. Handle Foto Profil
        if (isset($_POST['hapus_profil']) && $_POST['hapus_profil'] == '1') {
            $data['photo_profil'] = null;
        } elseif (!isset($_FILES['photo_profil']) || $_FILES['photo_profil']['error'] === UPLOAD_ERR_NO_FILE) {
            $data['photo_profil'] = $asistenLama['photo_profil'];
        } else {
            $uploadProfil = $this->prosesUpload('photo_profil', 'public/img/uploads/', $namaBersih . '_profil');
            if ($uploadProfil['status']) {
                $data['photo_profil'] = $uploadProfil['nama_file'];
                $uploadSuccess[] = "Foto Profil (WebP)";
            } else {
                $data['photo_profil'] = $asistenLama['photo_profil'];
                $uploadErrors[] = "Profil: " . $uploadProfil['pesan'];
            }
        }

        // 6. Handle Foto TTD
        if (isset($_POST['hapus_ttd']) && $_POST['hapus_ttd'] == '1') {
            $data['photo_path'] = null;
        } elseif (!isset($_FILES['photo_path']) || $_FILES['photo_path']['error'] === UPLOAD_ERR_NO_FILE) {
            $data['photo_path'] = $asistenLama['photo_path'];
        } else {
            $uploadTTD = $this->prosesUpload('photo_path', 'public/img/signature/', $namaBersih . '_ttd');
            if ($uploadTTD['status']) {
                $data['photo_path'] = $uploadTTD['nama_file'];
                $uploadSuccess[] = "TTD (WebP)";
            } else {
                $data['photo_path'] = $asistenLama['photo_path'];
                $uploadErrors[] = "TTD: " . $uploadTTD['pesan'];
            }
        }

        // CEK JIKA ADA ERROR UPLOAD GAMBAR
        // Akan menghentikan proses save dan menampilkan errornya!
        if (!empty($uploadErrors)) {
            Flasher::setFlash('Gagal Upload Gambar', implode(' | ', $uploadErrors), 'danger');
            header('Location: ' . BASEURL . '/asisten');
            exit;
        }

        // 7. Eksekusi ke Database
        $data['nama_user'] = $data['nama_asisten'];
        $updateUser = $this->model('User_model')->ubahDataUser($data);
        $updateAsisten = $this->model('Asisten_model')->ubahData($data);

        if ($updateUser >= 0 && $updateAsisten >= 0) {
            Flasher::setFlash('Data Asisten', 'berhasil diperbarui', 'success');
        } else {
            Flasher::setFlash('Gagal', 'diperbarui', 'danger');
        }

        header('Location: ' . BASEURL . '/asisten');
        exit;
    }

    public function ubah($id) {
        $data['title'] = 'Ubah Data Asisten'; // Agar <title> di header terisi
        
        // Mengambil data lengkap (Asisten + User) dari model
        $data['ubahdata'] = $this->model('Asisten_model')->getUbahData($id);

        // Jika data tidak ditemukan, kembalikan ke index
        if (!$data['ubahdata']) {
            Flasher::setFlash('Data', 'tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/asisten');
            exit;
        }

        // Kirim $data ke setiap view agar tidak 'Undefined Variable'
        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('asisten/ubah_asisten', $data);
        $this->view('templates/footer');
    }

    public function hapus($id){
        $this->verifyCsrfToken();
        $this->isAdmin();
        if($this->model('Asisten_model')->prosesHapus($id)){
            Flasher::setFlash(' berhasil dihapus', '', 'success');
        }else{
            Flasher::setFlash(' tidak berhasil dihapus', '', 'danger');
        }
        header('Location: '.BASEURL. '/asisten');
        exit;
    }

    public function importExcel() {
        $this->isAdmin();
        
        if (isset($_FILES['file_excel']['name']) && $_FILES['file_excel']['name'] != '') {
            if ($_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
                Flasher::setFlash('Gagal mengupload file. Error kode: ' . $_FILES['file_excel']['error'], 'Error', 'danger');
                header('Location: ' . BASEURL . '/asisten');
                exit;
            }

            $allowed_ext = ['csv'];
            $ext = pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION);
            
            if (in_array(strtolower($ext), $allowed_ext)) {
                ini_set('auto_detect_line_endings', TRUE);
                $file = fopen($_FILES['file_excel']['tmp_name'], 'r');
                $successCount = 0;
                $failCount = 0;
                $isFirstRow = true;
                
                while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
                    if ($isFirstRow) {
                        $isFirstRow = false;
                        continue;
                    }
                    
                    $stambuk  = trim(isset($row[0]) ? $row[0] : '');
                    $nama     = trim(isset($row[1]) ? $row[1] : '');
                    $angkatan = trim(isset($row[2]) ? $row[2] : '');
                    $status   = trim(isset($row[3]) ? $row[3] : '');
                    $jk       = trim(isset($row[4]) ? $row[4] : '');
                    $email    = trim(isset($row[5]) ? $row[5] : '');
                    
                    if (empty($email) || empty($stambuk) || empty($nama)) {
                        $failCount++;
                        continue;
                    }
                    
                    if ($this->model('User_model')->getUserByUsername($email) || 
                        $this->model('Asisten_model')->cekStambuk($stambuk)) {
                        $failCount++;
                        continue;
                    }
                    
                    $passwordHash = hash('sha256', 'iclabs-umi');
                    $dataUser = [
                        'nama_user' => $nama, 
                        'username'  => $email,
                        'password'  => $passwordHash,
                        'role'      => 'Asisten'
                    ];
                    
                    $id_user = $this->model('User_model')->tambah($dataUser);
                    if ($id_user > 0) {
                        $newUser = $this->model('User_model')->getUserByUsername($email);
                        $dataAsisten = [
                            'stambuk'       => $stambuk,
                            'nama_asisten'  => $nama,
                            'angkatan'      => $angkatan,
                            'status'        => $status,
                            'jenis_kelamin' => $jk,
                            'id_user'       => $newUser['id_user'],
                            'photo_profil'  => null,
                            'photo_path'    => null
                        ];
                        
                        if ($this->model('Asisten_model')->tambah($dataAsisten) > 0) {
                            $successCount++;
                        } else {
                            $this->model('User_model')->prosesHapus($newUser['id_user']);
                            $failCount++;
                        }
                    } else {
                        $failCount++;
                    }
                }
                fclose($file);
                
                Flasher::setFlash("Import Selesai. $successCount sukses, $failCount gagal/duplikat.", 'Info', 'info');
            } else {
                Flasher::setFlash('Format file harus .csv (Comma Delimited)', 'Error', 'danger');
            }
        } else {
            Flasher::setFlash('Pilih file terlebih dahulu!', 'Error', 'warning');
        }
        
        header('Location: ' . BASEURL . '/asisten');
        exit;
    }

    public function downloadTemplate() {
        $this->isAdmin();
        $filename = "Template_Asisten_" . date('Ymd') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        
        $file = fopen('php://output', 'w');
        fputcsv($file, ['Stambuk', 'Nama Asisten', 'Angkatan', 'Status (Asisten/Calon Asisten)', 'Jenis Kelamin (Pria/Wanita)', 'Username (Email)'], ";");
        fputcsv($file, ['13020210001', 'Fulan', '2021', 'Asisten', 'Pria', 'contoh@student.umi.ac.id'], ";");
        fclose($file);
        exit;
    }

    private function validateAkun($username, $password)
    {
        $username = trim(strtolower($username));

        if ($username === '') {
            throw new Exception('Email wajib diisi!');
        }

        $allowedDomains = [
            '@umi.ac.id',
            '@student.umi.ac.id',
            '@gmail.com'
        ];

        $valid = false;
        foreach ($allowedDomains as $domain) {
            if (substr($username, -strlen($domain)) === $domain) {
                $valid = true;
                break;
            }
        }

        // email khusus
        if ($username === 'iclabs@umi.ac.id') {
            $valid = true;
        }

        if (!$valid) {
            throw new Exception(
                'Email tidak valid! Gunakan domain UMI / Student / Gmail'
            );
        }

        $finalPass = trim($password);
        if ($finalPass === '') {
            $finalPass = 'iclabs-umi';
        }

        return hash('sha256', $finalPass);
    }
}