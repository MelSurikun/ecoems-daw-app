<?php
require_once __DIR__ . '/../../backend/auth.php';
if (tieneRol('admin') && strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    require __DIR__ . '/navbar_admin.php';
    return;
}
$pagina = basename($_SERVER['PHP_SELF']);
?>
<header class="main-header">
  <a href="index.php" class="logo" aria-label="Ir al inicio">
    <svg viewBox="0 0 300 70" width="160" height="37" aria-label="ECOEMS">
      <defs>
        <linearGradient id="goldGrad" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stop-color="#FFD700"/>
          <stop offset="100%" stop-color="#DAA520"/>
        </linearGradient>
      </defs>
      <rect width="300" height="70" rx="8" fill="#023047"/>
      <polygon points="40,6 62,13 40,20 18,13" fill="url(#goldGrad)"/>
      <rect x="26" y="18" width="28" height="5" rx="2" fill="url(#goldGrad)"/>
      <path d="M55,20 Q58,25 55,33" stroke="url(#goldGrad)" stroke-width="2" fill="none" stroke-linecap="round"/>
      <path d="M53,31 L57,31 Q55,35 55,38" stroke="url(#goldGrad)" stroke-width="2.5" fill="none" stroke-linecap="round"/>
      <line x1="284" y1="14" x2="284" y2="56" stroke="url(#goldGrad)" stroke-width="2" stroke-linecap="round"/>
      <text x="72" y="33" font-family="'Sora',Arial,sans-serif" font-size="22" font-weight="800" fill="#00e5ff" letter-spacing="3">ECOEMS</text>
      <text x="72" y="51" font-family="'Sora',Arial,sans-serif" font-size="10" font-weight="600" fill="rgba(255,255,255,.6)" letter-spacing="2.5">PORTAL</text>
    </svg>
  </a>

  <input type="checkbox" id="btn-nav">
  <label for="btn-nav" class="btn-nav" aria-label="Abrir menú"><span></span></label>
  <label for="btn-nav" class="sidebar-overlay" aria-hidden="true"></label>

  <nav class="sidebar" aria-label="Menú principal">
    <ul class="navigation">
      <li><a href="index.php"<?= $pagina === 'index.php' ? ' class="activo"' : '' ?>>Inicio</a></li>
      <li><a href="escuela.php"<?= $pagina === 'escuela.php' ? ' class="activo"' : '' ?>>Por Escuela</a></li>
      <li><a href="comparar.php"<?= $pagina === 'comparar.php' ? ' class="activo"' : '' ?>>Comparador</a></li>
      <li><a href="mapa.php"<?= $pagina === 'mapa.php' ? ' class="activo"' : '' ?>>Mapa</a></li>
      <li><a href="resumen.php"<?= $pagina === 'resumen.php' ? ' class="activo"' : '' ?>>Resumen</a></li>
      <li><a href="planteles.php"<?= $pagina === 'planteles.php' ? ' class="activo"' : '' ?>>Planteles</a></li>
      <li><a href="biblioteca.php"<?= $pagina === 'biblioteca.php' ? ' class="activo"' : '' ?>>Biblioteca</a></li>
      <li><a href="simulador.php"<?= $pagina === 'simulador.php' ? ' class="activo"' : '' ?>>Simulador</a></li>
      <li><a href="acerca.php"<?= $pagina === 'acerca.php' ? ' class="activo"' : '' ?>>Acerca</a></li>
    </ul>
    <ul class="navigation navigation-session">
      <?php if ($u = usuarioActual()): ?>
        <li class="sidebar-user">👤 <?= htmlspecialchars($u['nombre']) ?></li>
        <?php if ($u['rol'] === 'admin'): ?>
          <li><a href="admin/dashboard.php">⚙ Panel de administración</a></li>
        <?php endif; ?>
        <li><a href="dashboard.php"<?= $pagina === 'dashboard.php' ? ' class="activo"' : '' ?>>Mi panel</a></li>
        <li><a href="#" id="btn-logout" class="sidebar-logout">Salir</a></li>
      <?php else: ?>
        <li><a href="login.php">Iniciar sesión</a></li>
        <li><a href="login.php?modo=registro" class="activo">Crear cuenta</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<?php if (usuarioActual()): ?>
<script>
  document.getElementById('btn-logout').addEventListener('click', async (e) => {
    e.preventDefault();
    await fetch('../backend/api/auth/logout.php', { method: 'POST' });
    window.location.href = 'index.php';
  });
</script>
<?php endif; ?>
