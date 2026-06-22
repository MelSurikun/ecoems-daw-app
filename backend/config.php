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

// Si una petición web (no CLI) truena con una excepción no capturada —por
// ejemplo una tabla que aún no existe en la base de datos— responder con
// JSON válido en vez de dejar la salida vacía o una página de error en HTML.
// Sin esto, fetch().json() en el navegador falla con
// "Unexpected end of JSON input" aunque el verdadero problema sea otro.
if (PHP_SAPI !== 'cli') {
    set_exception_handler(function (Throwable $e) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }

        $detalle = $e->getMessage();
        $mensaje = 'Error interno del servidor.';

        // Caso muy común durante el desarrollo: una tabla nueva (usuarios,
        // recursos, intentos_simulador) que existe en la BD local pero no
        // se ha migrado todavía en este servidor. Lo señalamos explícito
        // para no tener que ir a la pestaña Network a buscar el detalle.
        if (preg_match("/Table '[^']+\\.([a-zA-Z_]+)' doesn't exist/", $detalle, $m)
            || str_contains($detalle, '1146')) {
            $tabla = $m[1] ?? '?';
            $mensaje = "Falta la tabla \"$tabla\" en esta base de datos. "
                     . "¿Ya corriste auth.sql / recursos.sql / simulador.sql en ESTE servidor "
                     . "(no solo en tu base local)?";
        }

        echo json_encode([
            'status'  => 'error',
            'mensaje' => $mensaje,
            'detalle' => $detalle,
        ]);
        exit;
    });
}
