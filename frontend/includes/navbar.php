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

        <!-- Left cyan border -->
        <rect x="12" y="12" width="3" height="46" rx="1" fill="#00e5ff"/>

        <!-- Gold star/sparkle -->
        <path d="M44 10 L46 17 L53 19 L46 21 L44 28 L42 21 L35 19 L42 17 Z" fill="url(#goldGrad)"/>

        <!-- Abstract cyan "E" emblem -->
        <rect x="20" y="17" width="7" height="36" rx="1.5" fill="#00e5ff"/>
        <path d="M20 20 Q30 18 52 24 L52 28 Q30 22 20 24 Z" fill="#00e5ff"/>
        <path d="M20 48 L52 48 L52 51 L20 51 Z" fill="#00e5ff"/>

        <!-- Gold right accent line -->
        <line x1="284" y1="14" x2="284" y2="56" stroke="url(#goldGrad)" stroke-width="2" stroke-linecap="round"/>

        <!-- "ECOEMS" wordmark -->
        <text x="72" y="33" font-family="'Sora',Arial,sans-serif" font-size="22" font-weight="800" fill="#00e5ff" letter-spacing="3">ECOEMS</text>

        <!-- "DATOS" subtitle -->
        <text x="72" y="51" font-family="'Sora',Arial,sans-serif" font-size="10" font-weight="600" fill="rgba(255,255,255,.6)" letter-spacing="2.5">DATOS</text>
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
