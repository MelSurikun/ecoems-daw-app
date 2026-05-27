<?php
// ============================================================
//  ECOEMS — config.php
//  Configuración de conexión a MariaDB
//  TODO (Segunda entrega): ajustar credenciales del servidor
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'ecoems_db');
define('DB_USER', 'ecoems_user');
define('DB_PASS', 'password');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
