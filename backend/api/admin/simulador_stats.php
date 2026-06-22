<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../auth.php';

$usuario = requiereRol('admin');

$pdo = getDB();

// Todos los intentos con nombre del usuario
$stmt = $pdo->query("
    SELECT i.id, i.usuario_id, i.fecha, i.aciertos, i.total, i.porcentaje, u.nombre AS usuario_nombre
    FROM intentos_simulador i
    JOIN usuarios u ON u.id = i.usuario_id
    ORDER BY i.fecha ASC
");
$intentos = $stmt->fetchAll();

echo json_encode(['status' => 'ok', 'datos' => ['intentos' => $intentos]]);
