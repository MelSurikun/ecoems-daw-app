<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../auth.php';

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$nombre   = trim($input['nombre'] ?? '');
$email    = trim($input['email'] ?? '');
$password = (string)($input['password'] ?? '');

if ($nombre === '' || $email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Nombre, email y contraseña son requeridos.']);
    exit;
}
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'La contraseña debe tener al menos 6 caracteres.']);
    exit;
}

$pdo = getDB();

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'mensaje' => 'Ese email ya está registrado.']);
    exit;
}

// El registro público siempre crea cuentas de aspirante; el rol admin
// se asigna manualmente en la base de datos (ver database/auth.sql).
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES (?, ?, ?, 'aspirante')");
$stmt->execute([$nombre, $email, $hash]);

$_SESSION['usuario'] = [
    'id'     => (int)$pdo->lastInsertId(),
    'nombre' => $nombre,
    'email'  => $email,
    'rol'    => 'aspirante',
];

echo json_encode(['status' => 'ok', 'datos' => $_SESSION['usuario']]);
