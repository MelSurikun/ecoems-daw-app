<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';

$pdo = getDB();
$examen_id = (int)($_GET['examen_id'] ?? 1);

// Cargar reactivos activos del examen
$stmt = $pdo->prepare("SELECT id, numero, materia, pregunta, opciones, respuesta, contexto, figura_clave, passage
                        FROM reactivos
                        WHERE examen_id = ? AND activo = 1
                        ORDER BY numero ASC");
$stmt->execute([$examen_id]);
$reactivos = $stmt->fetchAll();

foreach ($reactivos as &$r) {
    $r['opciones'] = json_decode($r['opciones'], true);
}

// Cargar figuras referenciadas
$claves = array_filter(array_column($reactivos, 'figura_clave'));
$figuras = [];
if (!empty($claves)) {
    $placeholders = implode(',', array_fill(0, count($claves), '?'));
    $stmt = $pdo->prepare("SELECT clave, datos FROM figuras WHERE clave IN ($placeholders)");
    $stmt->execute(array_values($claves));
    foreach ($stmt->fetchAll() as $f) {
        $figuras[$f['clave']] = $f['datos'];
    }
}

echo json_encode([
    'status' => 'ok',
    'datos' => [
        'reactivos' => $reactivos,
        'figuras' => $figuras,
    ],
]);
