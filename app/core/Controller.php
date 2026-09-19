<?php

class Controller{
    protected $db;
    protected $id_asisten;

    public function __construct(){
        if (session_status() === PHP_SESSION_NONE) {
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                     || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            ini_set('session.gc_maxlifetime', 86400);
            session_set_cookie_params([
                'lifetime' => 86400,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start(); 
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->db = Database::getInstance();

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

        // Nama file unik
        $randomHash = bin2hex(random_bytes(8));
        $namaFileBaru = ($customName ? $customName . '_' . $randomHash : bin2hex(random_bytes(16))) . '.' . $ekstensiFile;
        $cleanDir = trim(str_replace(['public/', '\\'], ['', '/'], $targetDirDB), '/');
        $s3Key = ($cleanDir !== '' ? $cleanDir . '/' : '') . $namaFileBaru;

        try {
            $s3 = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => defined('B2_REGION') && B2_REGION ? B2_REGION : 'us-east-005',
                'endpoint' => defined('B2_ENDPOINT') && B2_ENDPOINT ? B2_ENDPOINT : 'https://s3.us-east-005.backblazeb2.com',
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => defined('B2_KEY_ID') ? B2_KEY_ID : '',
                    'secret' => defined('B2_APPLICATION_KEY') ? B2_APPLICATION_KEY : '',
                ],
            ]);

            $bucket = defined('B2_BUCKET_NAME') ? B2_BUCKET_NAME : '';
            $mimeType = mime_content_type($file['tmp_name']) ?: 'application/octet-stream';

            // Stream resource to keep RAM footprint O(1) in Docker container
            $stream = fopen($file['tmp_name'], 'r');

            $result = $s3->putObject([
                'Bucket'      => $bucket,
                'Key'         => $s3Key,
                'Body'        => $stream,
                'ContentType' => $mimeType,
                'ACL'         => 'public-read'
            ]);

            if (is_resource($stream)) {
                fclose($stream);
            }

            // Tentukan URL publik Backblaze B2
            if (defined('B2_PUBLIC_URL') && !empty(B2_PUBLIC_URL)) {
                $publicUrl = rtrim(B2_PUBLIC_URL, '/') . '/' . $s3Key;
            } else {
                $region = defined('B2_REGION') && B2_REGION ? B2_REGION : 'us-east-005';
                $publicUrl = $result['ObjectURL'] ?? "https://{$bucket}.s3.{$region}.backblazeb2.com/{$s3Key}";
            }

            return [
                'status'    => true,
                'nama_file' => $publicUrl
            ];
        } catch (\Aws\Exception\AwsException $e) {
            error_log('Backblaze B2 Upload AWS Exception: ' . $e->getMessage());
            return ['status' => false, 'pesan' => 'Gagal upload ke Backblaze B2: ' . $e->getAwsErrorMessage()];
        } catch (\Exception $e) {
            error_log('Backblaze B2 Upload Error: ' . $e->getMessage());
            return ['status' => false, 'pesan' => 'Gagal upload: ' . $e->getMessage()];
        }
    }
}
?>
