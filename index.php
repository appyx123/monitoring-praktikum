<?php
ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('error_log', 'php://stderr');
error_reporting(E_ALL);

require_once 'app/config/config.php';

if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === true) {
    http_response_code(503);
    require_once 'app/views/errors/503.php';
    exit;
}

// Global Error Handlers for 500 Internal Server Error (Logged to Container stderr)
function customExceptionHandler($e) {
    if (ob_get_level()) ob_end_clean();
    $msg = sprintf(
        "[%s] FATAL EXCEPTION: %s in %s:%d\nStack Trace:\n%s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    file_put_contents('php://stderr', $msg);
    http_response_code(500);
    require_once 'app/views/errors/500.php';
    exit;
}

function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (error_reporting() === 0) return false;
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

function customShutdownHandler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        $msg = sprintf(
            "[%s] FATAL SHUTDOWN: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );
        file_put_contents('php://stderr', $msg);
        if (ob_get_level()) ob_end_clean();
        http_response_code(500);
        require_once 'app/views/errors/500.php';
    }
}

set_exception_handler('customExceptionHandler');
// set_error_handler('customErrorHandler');
register_shutdown_function('customShutdownHandler');

require_once 'app/init.php';

$app = new App;
    