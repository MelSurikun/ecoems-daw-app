<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../auth.php';

$usuario = requiereRol('admin');

$pdo = getDB();

// Todos los intentos con nombre del usuario
$stmt = $pdo->query("
    SELECT i.id, i.usuario_id, i.fecha, i.aciertos, i.total, i.porcentaje, i.detalle_json, u.nombre AS usuario_nombre
    FROM intentos_simulador i
    JOIN usuarios u ON u.id = i.usuario_id
    ORDER BY i.fecha ASC
");
$intentos = $stmt->fetchAll();

foreach ($intentos as &$i) {
    $i['detalle'] = $i['detalle_json'] ? json_decode($i['detalle_json'], true) : null;
    unset($i['detalle_json']);
}

echo json_encode(['status' => 'ok', 'datos' => ['intentos' => $intentos]]);
