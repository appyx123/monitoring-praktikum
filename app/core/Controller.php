<?php

class Controller{
    protected $db;
    protected $id_asisten;

    public function __construct(){
        if (session_status() === PHP_SESSION_NONE) {
            // Konfigurasi session agar bertahan selama 24 jam (86400 detik)
            ini_set('session.gc_maxlifetime', 86400);
            session_set_cookie_params(86400);
            session_start(); 
        }
        $this->db = new Database();

        // Logika Remember Me (Auto-Login)
        if (!isset($_SESSION['id_user']) && isset($_COOKIE['id_user']) && isset($_COOKIE['key'])) {
            $id_user = $_COOKIE['id_user'];
            $key = $_COOKIE['key'];

            $this->db->query("SELECT * FROM mst_user WHERE id_user = :id");
            $this->db->bind('id', $id_user);
            $user = $this->db->single();

            if ($user && $key === hash('sha256', $user['username'] . $user['password'])) {
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama_user'] = $user['nama_user'];
                $_SESSION['photo_profil'] = $user['photo_profil'] ?? null;
            }
        }

        if (isset($_SESSION['role'])) {
            $this->id_asisten = isset($_SESSION['id_asisten']) ? $_SESSION['id_asisten'] : null;
        } else {
            $this->id_asisten = null;
        }
    }

    public function view($view, $data = []){
        if(!isset($_SESSION['id_user'])){
            require_once 'app/views/login/index.php';
        }else{
            require_once 'app/views/' . $view . '.php';
        }
    }

    public function model($model){
        require_once 'app/models/' . $model . '.php';
        return new $model;
    }

    public function isLogin() {
            if (!isset($_SESSION['id_user'])) {
                header('Location:' . BASEURL . '/login'); 
                exit;
            }
    }

    public function isAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['id_user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
            header('Location: ' . BASEURL . '/login');
            exit;
        }
    }

    public function isAsisten() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['id_user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Asisten') {
            header('Location: ' . BASEURL . '/login');
            exit;
        }
    }

    public function verifyCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            header('HTTP/1.0 403 Forbidden');
            die('403 Invalid CSRF Token');
        }
    }

        public function prosesUpload($inputName, $targetDirDB, $customName = null) {
        $file = $_FILES[$inputName] ?? null;
        
        if (!$file) return ['status' => false, 'pesan' => 'File tidak ditemukan'];
        
        // Jika user tidak upload apa-apa (ini normal saat Edit)
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['status' => true, 'no_upload' => true, 'nama_file' => null];
        }
        
        // Validasi Error PHP Upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'pesan' => 'Error kode: ' . $file['error']];
        }

        // Validasi Ekstensi & Ukuran
        $ekstensiValid = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ekstensiFile = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ekstensiFile, $ekstensiValid)) {
            return ['status' => false, 'pesan' => 'Format file yang diupload harus JPG/PNG/GIF/WEBP'];
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return ['status' => false, 'pesan' => 'Ukuran file maksimal 5MB'];
        }

        // --- SETTING EKSTENSI OUTPUT JADI WEBP ---
        $randomHash = bin2hex(random_bytes(8));
        $namaFileBaru = ($customName ? $customName . '_' . $randomHash : bin2hex(random_bytes(16))) . '.webp';

        // Path System (Naik 2 level ke project root)
        $projectRoot = dirname(dirname(__DIR__));
        $targetDirSystem = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $targetDirDB);

        // Buat folder jika belum ada
        if (!file_exists($targetDirSystem)) mkdir($targetDirSystem, 0755, true);

        $fullPath = $targetDirSystem . DIRECTORY_SEPARATOR . $namaFileBaru;

        $tmpName = $file['tmp_name'];
        
        // --- JIKA SUDAH WEBP, LANGSUNG PINDAH ---
        if ($ekstensiFile === 'webp') {
            if (move_uploaded_file($tmpName, $fullPath)) {
                chmod($fullPath, 0644);
                return [
                    'status' => true, 
                    'nama_file' => str_replace(DIRECTORY_SEPARATOR, '/', $targetDirDB) . '/' . $namaFileBaru
                ];
            }
            return ['status' => false, 'pesan' => 'Gagal mengupload file WebP'];
        }

        // --- PROSES KONVERSI GAMBAR (GD LIBRARY) UNTUK JPG/PNG ---
        $image = null;

        if ($ekstensiFile === 'jpg' || $ekstensiFile === 'jpeg') {
            $image = @imagecreatefromjpeg($tmpName);
        } elseif ($ekstensiFile === 'png') {
            $image = @imagecreatefrompng($tmpName);
            // Handle support transparansi untuk file PNG
            if ($image !== false) {
                imagepalettetotruecolor($image);
                imagealphablending($image, false);
                imagesavealpha($image, true);
            }
        } elseif ($ekstensiFile === 'gif') {
            $image = @imagecreatefromgif($tmpName);
            if ($image !== false) {
                imagepalettetotruecolor($image);
                imagealphablending($image, false);
                imagesavealpha($image, true);
            }
        }

        // Jika berhasil di-load ke memory
        if ($image !== false && $image !== null) {
            if (!function_exists('imagewebp')) {
                // FALLBACK: Simpan sesuai ekstensi asli jika imagewebp tidak ada
                $namaFileBaru = ($customName ? $customName . '_' . $randomHash : bin2hex(random_bytes(16))) . '.' . $ekstensiFile;
                $fullPath = $targetDirSystem . DIRECTORY_SEPARATOR . $namaFileBaru;
                
                $berhasil = false;
                if ($ekstensiFile === 'jpg' || $ekstensiFile === 'jpeg') {
                    $berhasil = imagejpeg($image, $fullPath, 80);
                } elseif ($ekstensiFile === 'png') {
                    $berhasil = imagepng($image, $fullPath, 8); 
                } elseif ($ekstensiFile === 'gif') {
                    $berhasil = imagegif($image, $fullPath);
                }
                
                if ($berhasil) {
                    imagedestroy($image);
                    chmod($fullPath, 0644);
                    return [
                        'status' => true, 
                        'nama_file' => str_replace(DIRECTORY_SEPARATOR, '/', $targetDirDB) . '/' . $namaFileBaru
                    ];
                } else {
                    imagedestroy($image);
                    return ['status' => false, 'pesan' => 'Gagal menyimpan gambar (Fallback)'];
                }
            }
            
            // Konversi dan simpan gambar sebagai file .webp dengan kualitas 80%
            if (imagewebp($image, $fullPath, 80)) {
                imagedestroy($image); // Hapus memori sementara
                chmod($fullPath, 0644);
                
                // Return path output baru (.webp) untuk disimpan ke database
                return [
                    'status' => true, 
                    'nama_file' => str_replace(DIRECTORY_SEPARATOR, '/', $targetDirDB) . '/' . $namaFileBaru
                ];
            }
            imagedestroy($image);
            return ['status' => false, 'pesan' => 'Gagal mengonversi gambar ke WebP'];
        }

        return ['status' => false, 'pesan' => 'File gambar rusak atau tidak valid'];
    }
}
?>
