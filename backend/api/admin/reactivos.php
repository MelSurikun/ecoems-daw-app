<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../auth.php';

$usuario = requiereRol('admin');
$pdo     = getDB();
$metodo  = $_SERVER['REQUEST_METHOD'];

// GET — listar reactivos (con filtros opcionales)
if ($metodo === 'GET') {
    $examen_id = (int)($_GET['examen_id'] ?? 1);
    $materia   = trim($_GET['materia'] ?? '');
    $incluir_inactivos = !empty($_GET['inactivos']);

    $sql = "SELECT r.*, e.nombre AS examen_nombre
            FROM reactivos r
            JOIN examenes e ON e.id = r.examen_id
            WHERE r.examen_id = ?";
    $params = [$examen_id];

    if ($materia !== '') {
        $sql .= " AND r.materia = ?";
        $params[] = $materia;
    }
    if (!$incluir_inactivos) {
        $sql .= " AND r.activo = 1";
    }
    $sql .= " ORDER BY r.numero ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reactivos = $stmt->fetchAll();

    foreach ($reactivos as &$r) {
        $r['opciones'] = json_decode($r['opciones'], true);
    }

    echo json_encode(['status' => 'ok', 'datos' => $reactivos]);
    exit;
}

// POST — crear reactivo
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $examen_id    = (int)($input['examen_id'] ?? 1);
    $numero       = (int)($input['numero'] ?? 0);
    $materia      = trim($input['materia'] ?? '');
    $pregunta     = trim($input['pregunta'] ?? '');
    $opciones     = $input['opciones'] ?? [];
    $respuesta    = strtoupper(trim($input['respuesta'] ?? ''));
    $contexto     = $input['contexto'] ?? null;
    $figura_clave = $input['figura_clave'] ?? null;
    $passage      = $input['passage'] ?? null;
    $activo       = isset($input['activo']) ? (int)$input['activo'] : 1;

    if ($numero <= 0 || $materia === '' || $pregunta === '' || count($opciones) !== 4 || !in_array($respuesta, ['A','B','C','D'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'Datos de reactivo inválidos. Revisa número, materia, pregunta, 4 opciones y respuesta A-D.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO reactivos (examen_id, numero, materia, pregunta, opciones, respuesta, contexto, figura_clave, passage, activo)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $examen_id, $numero, $materia, $pregunta,
        json_encode($opciones, JSON_UNESCAPED_UNICODE),
        $respuesta, $contexto, $figura_clave, $passage, $activo,
    ]);

    echo json_encode(['status' => 'ok', 'datos' => ['id' => (int)$pdo->lastInsertId()]]);
    exit;
}

// PUT — actualizar reactivo
if ($metodo === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'ID de reactivo inválido.']);
        exit;
    }

    $numero       = (int)($input['numero'] ?? 0);
    $materia      = trim($input['materia'] ?? '');
    $pregunta     = trim($input['pregunta'] ?? '');
    $opciones     = $input['opciones'] ?? [];
    $respuesta    = strtoupper(trim($input['respuesta'] ?? ''));
    $contexto     = $input['contexto'] ?? null;
    $figura_clave = $input['figura_clave'] ?? null;
    $passage      = $input['passage'] ?? null;
    $activo       = isset($input['activo']) ? (int)$input['activo'] : 1;

    if ($numero <= 0 || $materia === '' || $pregunta === '' || count($opciones) !== 4 || !in_array($respuesta, ['A','B','C','D'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'Datos de reactivo inválidos.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE reactivos SET numero=?, materia=?, pregunta=?, opciones=?, respuesta=?, contexto=?, figura_clave=?, passage=?, activo=? WHERE id=?");
    $stmt->execute([
        $numero, $materia, $pregunta,
        json_encode($opciones, JSON_UNESCAPED_UNICODE),
        $respuesta, $contexto, $figura_clave, $passage, $activo, $id,
    ]);

    echo json_encode(['status' => 'ok', 'datos' => null]);
    exit;
}

// DELETE — desactivar reactivo (borrado lógico)
if ($metodo === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'ID de reactivo inválido.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE reactivos SET activo = 0 WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['status' => 'ok', 'datos' => null]);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'mensaje' => 'Método no soportado.']);
