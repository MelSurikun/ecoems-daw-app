<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';

$pdo   = getDB();
$q     = trim($_GET['q'] ?? '');
$clave = trim($_GET['clave'] ?? '');

if ($clave) {
    $stmt = $pdo->prepare("SELECT * FROM planteles WHERE clave = ?");
    $stmt->execute([$clave]);
    $row = $stmt->fetch();
    echo json_encode(['status' => 'ok', 'datos' => $row ?: null]);
} elseif ($q) {
    $stmt = $pdo->prepare("
        SELECT clave, nombre, especialidad, subsistema, municipio, estado, direccion
        FROM planteles
        WHERE clave LIKE :q OR nombre LIKE :q2 OR subsistema LIKE :q2
        ORDER BY clave
        LIMIT 30
    ");
    $like = $q . '%';
    $like2 = '%' . $q . '%';
    $stmt->execute([':q' => $like, ':q2' => $like2]);
    echo json_encode(['status' => 'ok', 'datos' => $stmt->fetchAll()]);
} else {
    $stmt = $pdo->query("SELECT * FROM planteles ORDER BY subsistema, nombre");
    echo json_encode(['status' => 'ok', 'datos' => $stmt->fetchAll()]);
}
