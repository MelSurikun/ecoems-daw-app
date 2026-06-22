<?php
// ============================================================
//  ECOEMS — auth.php
//  Sesión y helpers de autenticación/roles
// ============================================================

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/config.php';

function usuarioActual(): ?array {
    return $_SESSION['usuario'] ?? null;
}

function estaAutenticado(): bool {
    return usuarioActual() !== null;
}

function tieneRol(string $rol): bool {
    $u = usuarioActual();
    return $u !== null && $u['rol'] === $rol;
}

// Para usar en endpoints JSON: detiene la ejecución con 401/403 si no cumple.
function requiereSesion(): array {
    $u = usuarioActual();
    if ($u === null) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['status' => 'error', 'mensaje' => 'Debes iniciar sesión.']);
        exit;
    }
    return $u;
}

function requiereRol(string $rol): array {
    $u = requiereSesion();
    if ($u['rol'] !== $rol) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(['status' => 'error', 'mensaje' => 'No tienes permiso para esta acción.']);
        exit;
    }
    return $u;
}

// Para usar en páginas PHP normales: redirige si no cumple.
function requiereSesionPagina(string $redirect = 'login.php'): array {
    $u = usuarioActual();
    if ($u === null) {
        header('Location: ' . $redirect);
        exit;
    }
    return $u;
}

function requiereRolPagina(string $rol, string $redirect = 'index.php'): array {
    $u = requiereSesionPagina();
    if ($u['rol'] !== $rol) {
        header('Location: ' . $redirect);
        exit;
    }
    return $u;
}
