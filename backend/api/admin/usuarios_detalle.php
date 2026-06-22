<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../auth.php';

$usuario = requiereRol('admin');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'ID de usuario inválido.']);
    exit;
}

$pdo = getDB();

// Datos del usuario
$stmt = $pdo->prepare("SELECT id, nombre, email, rol, creado FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'mensaje' => 'Usuario no encontrado.']);
    exit;
}

// Todos sus intentos del simulador
$stmt = $pdo->prepare("SELECT id, fecha, aciertos, total, porcentaje, detalle_json
                        FROM intentos_simulador
                        WHERE usuario_id = ?
                        ORDER BY fecha ASC");
$stmt->execute([$id]);
$intentos = $stmt->fetchAll();

// Parsear detalle_json
foreach ($intentos as &$i) {
    $i['detalle'] = $i['detalle_json'] ? json_decode($i['detalle_json'], true) : null;
    unset($i['detalle_json']);
}

// Agregación por materia (todos los intentos)
$porMateria = [];
foreach ($intentos as $i) {
    if (!$i['detalle']) continue;
    foreach ($i['detalle'] as $materia => $d) {
        if (!isset($porMateria[$materia])) {
            $porMateria[$materia] = ['ok' => 0, 'tot' => 0];
        }
        $porMateria[$materia]['ok'] += $d['ok'];
        $porMateria[$materia]['tot'] += $d['tot'];
    }
}
foreach ($porMateria as $m => &$d) {
    $d['pct'] = $d['tot'] > 0 ? round(($d['ok'] / $d['tot']) * 100, 1) : 0;
}

echo json_encode([
    'status' => 'ok',
    'datos' => [
        'usuario' => $user,
        'intentos' => $intentos,
        'por_materia' => $porMateria,
    ],
]);
