<nav class="navbar">
  <div class="navbar-brand">
    <a href="index.php" class="navbar-logo" aria-label="Ir al inicio">
      <svg viewBox="0 0 300 70" width="210" height="49" aria-label="ECOEMS">
        <defs>
          <linearGradient id="goldGrad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#FFD700"/>
            <stop offset="100%" stop-color="#DAA520"/>
          </linearGradient>
        </defs>
        <rect width="300" height="70" rx="8" fill="#023047"/>

        <!-- Gold graduation cap (birrete) -->
        <polygon points="40,6 62,13 40,20 18,13" fill="url(#goldGrad)"/>
        <rect x="26" y="18" width="28" height="5" rx="2" fill="url(#goldGrad)"/>
        <path d="M55,20 Q58,25 55,33" stroke="url(#goldGrad)" stroke-width="2" fill="none" stroke-linecap="round"/>
        <path d="M53,31 L57,31 Q55,35 55,38" stroke="url(#goldGrad)" stroke-width="2.5" fill="none" stroke-linecap="round"/>

        <!-- Gold right accent line -->
        <line x1="284" y1="14" x2="284" y2="56" stroke="url(#goldGrad)" stroke-width="2" stroke-linecap="round"/>

        <!-- "ECOEMS" wordmark -->
        <text x="72" y="33" font-family="'Sora',Arial,sans-serif" font-size="22" font-weight="800" fill="#00e5ff" letter-spacing="3">ECOEMS</text>

        <!-- "DATOS" subtitle -->
        <text x="72" y="51" font-family="'Sora',Arial,sans-serif" font-size="10" font-weight="600" fill="rgba(255,255,255,.6)" letter-spacing="2.5">PORTAL</text>
      </svg>
    </a>
  </div>
  <ul class="navbar-nav">
    <li><a href="index.php"<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? ' class="activo"' : '' ?>>Inicio</a></li>
    <li><a href="escuela.php"<?= basename($_SERVER['PHP_SELF']) === 'escuela.php' ? ' class="activo"' : '' ?>>Por Escuela</a></li>
    <li><a href="comparar.php"<?= basename($_SERVER['PHP_SELF']) === 'comparar.php' ? ' class="activo"' : '' ?>>Comparador</a></li>
    <li><a href="mapa.php"<?= basename($_SERVER['PHP_SELF']) === 'mapa.php' ? ' class="activo"' : '' ?>>Mapa</a></li>
    <li><a href="resumen.php"<?= basename($_SERVER['PHP_SELF']) === 'resumen.php' ? ' class="activo"' : '' ?>>Resumen</a></li>
    <li><a href="planteles.php"<?= basename($_SERVER['PHP_SELF']) === 'planteles.php' ? ' class="activo"' : '' ?>>Planteles</a></li>
    <li><a href="acerca.php"<?= basename($_SERVER['PHP_SELF']) === 'acerca.php' ? ' class="activo"' : '' ?>>Acerca</a></li>
  </ul>
</nav>
