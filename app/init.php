<?php

require_once 'config/config.php';

require_once 'core/Database.php';
require_once 'core/DatabaseSessionHandler.php';
require_once 'core/SecurityHelper.php';
require_once 'core/Controller.php';
require_once 'core/App.php';

// Setup Stateless Database Session Handler untuk Koyeb
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }

    $handler = new DatabaseSessionHandler();
    session_set_save_handler($handler, true);
    session_start();
}
