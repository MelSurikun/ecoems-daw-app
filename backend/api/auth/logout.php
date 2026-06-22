<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../auth.php';

unset($_SESSION['usuario']);
session_destroy();

echo json_encode(['status' => 'ok', 'datos' => null]);
