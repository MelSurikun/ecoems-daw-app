<?php
require_once __DIR__ . '/../../backend/auth.php';
$usuario = requiereRolPagina('admin', '../index.php');
header('Location: examen.php');
exit;
