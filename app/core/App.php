<?php
require_once 'app/core/Flasher.php';

class App {

    protected $controller = 'Home'; 
    protected $method = 'index';  
    protected $params = [];     

    public function __construct() {
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

        // --- GLOBAL CSRF PROTECTION ON POST ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            $sessionToken = $_SESSION['csrf_token'] ?? '';

            if (empty($sessionToken) || !hash_equals($sessionToken, $token)) {
                http_response_code(403);
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => false, 'pesan' => 'Invalid or expired CSRF token.']);
                } else {
                    echo "<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'><h1>403 Forbidden</h1><p>Invalid or missing CSRF token.</p><p><a href='javascript:history.back()'>Kembali</a></p></body></html>";
                }
                exit;
            }
        }

        $url = $this->parseURL();

        // 1. Ambil nama Controller
        if (isset($url[0])) {
            $this->controller = $url[0];
            unset($url[0]);
        }
        
        // 2. Ambil nama Method
        if (isset($url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        // --- DEV OPS STRICT ROUTING CHECK ---
        require_once 'app/config/routes.php';
        $isRouteValid = false;
        
        // Cek apakah controller dan method ada di whitelist routes.php
        if (isset($routes[$this->controller]) && in_array($this->method, $routes[$this->controller])) {
            if (file_exists('app/controllers/' . $this->controller . '.php')) {
                require_once 'app/controllers/' . $this->controller . '.php';
                if (method_exists($this->controller, $this->method)) {
                    $isRouteValid = true;
                }
            }
        }

        // Jika rute tidak terdaftar di routes.php ATAU file/method tidak ada
        if (!$isRouteValid) {
            $this->controller = 'ErrorPage';
            $this->method = 'notFound';
            require_once 'app/controllers/ErrorPage.php';
        }

        // 3. Instansiasi Controller
        $this->controller = new $this->controller;

        // 4. Ambil parameter (id, dll)
        if (!empty($url)) {
            $this->params = array_values($url);
        }

        // 5. Eksekusi
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseURL() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            
            if (isset($url[0]) && is_string($url[0])) {
                $url[0] = ucfirst($url[0]);
            }

            return $url;
        }
        
        return [];
    }
}