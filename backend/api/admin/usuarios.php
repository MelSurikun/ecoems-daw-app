<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../auth.php';

$usuario = requiereRol('admin');

$pdo = getDB();
$stmt = $pdo->query("SELECT id, nombre, email, rol, creado FROM usuarios ORDER BY creado DESC");
echo json_encode(['status' => 'ok', 'datos' => $stmt->fetchAll()]);
