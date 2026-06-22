<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../auth.php';

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email    = trim($input['email'] ?? '');
$password = (string)($input['password'] ?? '');

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Email y contraseña son requeridos.']);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT id, nombre, email, password_hash, rol FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$row = $stmt->fetch();

if (!$row || !password_verify($password, $row['password_hash'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'mensaje' => 'Email o contraseña incorrectos.']);
    exit;
}

$_SESSION['usuario'] = [
    'id'     => $row['id'],
    'nombre' => $row['nombre'],
    'email'  => $row['email'],
    'rol'    => $row['rol'],
];

echo json_encode(['status' => 'ok', 'datos' => $_SESSION['usuario']]);
