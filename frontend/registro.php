<?php
// Crear cuenta ahora vive en login.php (panel deslizante "Crear cuenta" / "Iniciar sesión").
// Este archivo se conserva solo para no romper enlaces existentes.
require_once __DIR__ . '/../backend/auth.php';

$qs = ['modo' => 'registro'];
if (!empty($_GET['next'])) {
    $qs['next'] = $_GET['next'];
}

header('Location: login.php?' . http_build_query($qs));
exit;
