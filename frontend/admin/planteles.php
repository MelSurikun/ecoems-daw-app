<?php
require_once __DIR__ . '/../../backend/auth.php';
$usuario = requiereRolPagina('admin', '../index.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ECOEMS, Gestión de planteles</title>
  <link rel="stylesheet" href="../css/estilos.css?v=2">
  <style>
    .estado-msg { padding: 2rem; text-align: center; color: var(--texto-2); font-size: .9rem; }
  </style>
</head>
<body class="page-wrapper">
  <?php require '../includes/navbar.php'; ?>
  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Administración</p>
      <h1>Planteles</h1>
      <p>Catálogo completo de opciones educativas COMIPECS.</p>
    </div>
  </div>
  <section class="section">
    <div class="container">
      <div class="card" style="padding:2rem">
        <div class="estado-msg">
          Módulo en construcción.<br>
          Por ahora el catálogo se administra via SQL/ETL.
          <div style="margin-top:1rem">
            <a href="../planteles.php" class="btn btn-bordo btn-sm">Ver catálogo público →</a>
          </div>
        </div>
      </div>
    </div>
  </section>
  <footer class="site-footer">
    <p>Panel de Administración <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; 2026</p>
  </footer>
</body>
</html>
