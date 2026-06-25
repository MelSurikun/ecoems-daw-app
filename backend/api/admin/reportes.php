<?php
// ============================================================
//  ECOEMS — api/admin/reportes.php
//  Reportes/correcciones de reactivos del simulador, guardados
//  en un archivo JSON (sin tocar la base de datos).
//
//  GET  /api/admin/reportes.php  → cualquier usuario con sesión
//       (el simulador del aspirante los necesita para corregirse)
//  PUT  /api/admin/reportes.php  → solo admin, body: objeto completo
//       de overrides { "12": {oculto, respuesta, nota}, ... }
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../auth.php';

$archivo = __DIR__ . '/../../data/reportes_examen.json';
$metodo  = $_SERVER['REQUEST_METHOD'];

requiereSesion();

if ($metodo === 'GET') {
    $contenido = file_exists($archivo) ? file_get_contents($archivo) : '{}';
    $datos = json_decode($contenido, true);
    echo json_encode(['status' => 'ok', 'datos' => $datos ?: []]);
    exit;
}

if ($metodo === 'PUT') {
    requiereRol('admin');

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'Datos inválidos.']);
        exit;
    }

    $ok = @file_put_contents($archivo, json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    if ($ok === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'mensaje' => 'No se pudo guardar. Verifica permisos de escritura en backend/data/.']);
        exit;
    }

    echo json_encode(['status' => 'ok']);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'mensaje' => 'Método no soportado.']);
