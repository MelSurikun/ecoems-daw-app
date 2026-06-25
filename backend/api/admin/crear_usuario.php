<?php
// ============================================================
//  ECOEMS — api/admin/crear_usuario.php
//  Alta de cuentas desde el panel de administrador.
//  A diferencia de api/auth/registro.php (público, siempre crea
//  aspirantes), este endpoint requiere rol admin y puede crear
//  cuentas con rol "admin" o "aspirante".
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../auth.php';

requiereRol('admin');

$input    = json_decode(file_get_contents('php://input'), true) ?? [];
$nombre   = trim($input['nombre'] ?? '');
$email    = trim($input['email'] ?? '');
$password = (string)($input['password'] ?? '');
$rol      = $input['rol'] ?? 'aspirante';

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
if (!in_array($rol, ['aspirante', 'admin'], true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Rol inválido.']);
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

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES (?, ?, ?, ?)");
$stmt->execute([$nombre, $email, $hash, $rol]);

echo json_encode(['status' => 'ok', 'datos' => ['id' => (int)$pdo->lastInsertId()]]);
