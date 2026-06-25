<?php
// Rutas relativas configurables: por defecto asume que se incluye desde
// frontend/admin/*.php (un nivel bajo frontend/). Si se incluye desde
// frontend/*.php directamente (p. ej. biblioteca.php compartida con el
// aspirante), el archivo que hace el require debe definir estas variables
// ANTES del include: $navAdminRoot = ''; $navAdminDir = 'admin/';
$navAdminRoot = $navAdminRoot ?? '../';   // prefijo para llegar a frontend/
$navAdminDir  = $navAdminDir  ?? '';      // prefijo para llegar a frontend/admin/
$navBackend   = $navAdminRoot . '../';    // prefijo para llegar a la raíz del proyecto (backend/)
require __DIR__ . '/loader.php';
?>
<nav class="navbar navbar-admin">
  <div class="navbar-brand">
    <a href="<?= $navAdminRoot ?>index.php" class="navbar-logo" aria-label="Ir al inicio">
      <svg viewBox="0 0 300 70" width="210" height="49" aria-label="ECOEMS">
        <defs>
          <linearGradient id="goldGradAdmin" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#FFD700"/>
            <stop offset="100%" stop-color="#DAA520"/>
          </linearGradient>
        </defs>
        <rect width="300" height="70" rx="8" fill="#023047"/>
        <polygon points="40,6 62,13 40,20 18,13" fill="url(#goldGradAdmin)"/>
        <rect x="26" y="18" width="28" height="5" rx="2" fill="url(#goldGradAdmin)"/>
        <path d="M55,20 Q58,25 55,33" stroke="url(#goldGradAdmin)" stroke-width="2" fill="none" stroke-linecap="round"/>
        <path d="M53,31 L57,31 Q55,35 55,38" stroke="url(#goldGradAdmin)" stroke-width="2.5" fill="none" stroke-linecap="round"/>
        <line x1="284" y1="14" x2="284" y2="56" stroke="url(#goldGradAdmin)" stroke-width="2" stroke-linecap="round"/>
        <text x="72" y="33" font-family="'Sora',Arial,sans-serif" font-size="22" font-weight="800" fill="#00e5ff" letter-spacing="3">ECOEMS</text>
        <text x="72" y="51" font-family="'Sora',Arial,sans-serif" font-size="10" font-weight="600" fill="rgba(255,255,255,.6)" letter-spacing="2.5">PORTAL</text>
      </svg>
    </a>
    <span class="navbar-badge">ADMIN</span>
  </div>
  <div class="navbar-nav-group">
    <ul class="navbar-nav">
      <li><a href="<?= $navAdminDir ?>dashboard.php"<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? ' class="activo"' : '' ?>>Panel</a></li>
      <li><a href="<?= $navAdminDir ?>usuarios.php"<?= basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? ' class="activo"' : '' ?>>Usuarios</a></li>
      <li><a href="<?= $navAdminRoot ?>biblioteca.php"<?= basename($_SERVER['PHP_SELF']) === 'biblioteca.php' ? ' class="activo"' : '' ?>>Biblioteca</a></li>
      <li><a href="<?= $navAdminDir ?>examen.php"<?= basename($_SERVER['PHP_SELF']) === 'examen.php' ? ' class="activo"' : '' ?>>Examen</a></li>
      <li class="navbar-divider"></li>
      <li><a href="<?= $navAdminRoot ?>index.php" class="nav-link-public">← Portal público</a></li>
    </ul>
    <ul class="navbar-nav navbar-session">
      <li class="navbar-username"><?= htmlspecialchars(usuarioActual()['nombre']) ?></li>
      <li><a href="#" id="btn-logout">Salir</a></li>
    </ul>
  </div>
</nav>
<script>
  document.getElementById('btn-logout').addEventListener('click', async (e) => {
    e.preventDefault();
    await fetch('<?= $navBackend ?>backend/api/auth/logout.php', { method: 'POST' });
    window.location.href = '<?= $navAdminRoot ?>index.php';
  });
</script>
