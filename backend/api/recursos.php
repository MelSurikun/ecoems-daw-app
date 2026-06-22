<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../auth.php';

$pdo    = getDB();
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $materia = trim($_GET['materia'] ?? '');
    if ($materia !== '') {
        $stmt = $pdo->prepare("SELECT * FROM recursos WHERE materia = ? ORDER BY creado DESC");
        $stmt->execute([$materia]);
    } else {
        $stmt = $pdo->query("SELECT * FROM recursos ORDER BY creado DESC");
    }
    echo json_encode(['status' => 'ok', 'datos' => $stmt->fetchAll()]);
    exit;
}

// Crear, editar y borrar recursos: solo administradores.
$usuario = requiereRol('admin');

if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $titulo      = trim($input['titulo'] ?? '');
    $materia     = trim($input['materia'] ?? '');
    $descripcion = trim($input['descripcion'] ?? '');
    $tipo        = in_array($input['tipo'] ?? '', ['enlace', 'pdf']) ? $input['tipo'] : 'enlace';
    $url         = trim($input['url'] ?? '');

    if ($titulo === '' || $materia === '' || $url === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'Título, materia y URL son requeridos.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO recursos (titulo, materia, descripcion, tipo, url, creado_por)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titulo, $materia, $descripcion, $tipo, $url, $usuario['id']]);
    echo json_encode(['status' => 'ok', 'datos' => ['id' => (int)$pdo->lastInsertId()]]);
    exit;
}

if ($metodo === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'ID de recurso inválido.']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE recursos SET titulo=?, materia=?, descripcion=?, tipo=?, url=? WHERE id=?");
    $stmt->execute([
        trim($input['titulo'] ?? ''),
        trim($input['materia'] ?? ''),
        trim($input['descripcion'] ?? ''),
        in_array($input['tipo'] ?? '', ['enlace', 'pdf']) ? $input['tipo'] : 'enlace',
        trim($input['url'] ?? ''),
        $id,
    ]);
    echo json_encode(['status' => 'ok', 'datos' => null]);
    exit;
}

if ($metodo === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'ID de recurso inválido.']);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM recursos WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'ok', 'datos' => null]);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'mensaje' => 'Método no soportado.']);
