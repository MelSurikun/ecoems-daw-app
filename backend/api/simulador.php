<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../auth.php';

$pdo    = getDB();
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'POST') {
    // Guardar un intento. Requiere sesión iniciada.
    $usuario = requiereSesion();

    $input    = json_decode(file_get_contents('php://input'), true) ?? [];
    $aciertos = (int)($input['aciertos'] ?? -1);
    $total    = (int)($input['total'] ?? 0);
    $detalle  = $input['detalle'] ?? null; // { "Español": 10, "Matemáticas": 8, ... }

    if ($aciertos < 0 || $total <= 0 || $aciertos > $total) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'Datos de calificación inválidos.']);
        exit;
    }

    $porcentaje = round(($aciertos / $total) * 100, 2);
    $detalleJson = $detalle ? json_encode($detalle, JSON_UNESCAPED_UNICODE) : null;

    $stmt = $pdo->prepare("INSERT INTO intentos_simulador (usuario_id, aciertos, total, porcentaje, detalle_json)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$usuario['id'], $aciertos, $total, $porcentaje, $detalleJson]);

    echo json_encode(['status' => 'ok', 'datos' => ['id' => (int)$pdo->lastInsertId(), 'porcentaje' => $porcentaje]]);
    exit;
}

if ($metodo === 'GET') {
    // Historial del usuario en sesión (para el dashboard "Mi panel").
    $usuario = requiereSesion();

    $stmt = $pdo->prepare("SELECT id, fecha, aciertos, total, porcentaje, detalle_json
                            FROM intentos_simulador
                            WHERE usuario_id = ?
                            ORDER BY fecha ASC");
    $stmt->execute([$usuario['id']]);
    $intentos = $stmt->fetchAll();

    foreach ($intentos as &$i) {
        $i['detalle'] = $i['detalle_json'] ? json_decode($i['detalle_json'], true) : null;
        unset($i['detalle_json']);
    }

    echo json_encode(['status' => 'ok', 'datos' => $intentos]);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'mensaje' => 'Método no soportado.']);
