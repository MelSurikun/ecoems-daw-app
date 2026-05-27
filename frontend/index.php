<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS — Inicio</title>
  <link rel="stylesheet" href="css/estilos.css">
  <style>
    .hero-stats {
      display: flex;
      gap: 2.5rem;
      margin-top: 2.5rem;
      flex-wrap: wrap;
    }
    .hero-stat strong {
      display: block;
      font-family: var(--font-display);
      font-size: 1.9rem;
      color: var(--oro-cl);
      line-height: 1;
    }
    .hero-stat span {
      font-size: .75rem;
      color: rgba(255,255,255,.6);
      text-transform: uppercase;
      letter-spacing: .08em;
    }
    .about-strip {
      background: var(--bordo);
      color: #fff;
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      gap: 1.5rem;
      font-size: .85rem;
    }
    .about-strip strong { color: var(--oro); }
    .news-card {
      background: var(--fondo-card);
      border-radius: var(--radio);
      padding: 1.2rem;
      box-shadow: var(--sombra);
      border-left: 3px solid var(--oro);
    }
    .news-card .tag {
      font-size: .68rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .1em;
      color: var(--acento);
      margin-bottom: .4rem;
    }
    .news-card h4 { font-family: var(--font-display); font-size: .95rem; color: var(--bordo); margin-bottom: .3rem; }
    .news-card p  { font-size: .8rem; color: var(--texto-2); }
    .quick-search-wrap {
      max-width: 860px;
    }
  </style>
</head>
<body class="page-wrapper">

  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-brand">
      <div class="navbar-logo">E</div>
      <div>
        <span>Portal ECOEMS</span>
        <small>Consulta Histórica · IPN-LCD</small>
      </div>
    </div>
    <ul class="navbar-nav">
      <li><a href="index.php" class="activo">Inicio</a></li>
      <li><a href="escuela.php">Por Escuela</a></li>
      <li><a href="comparar.php">Comparador</a></li>
      <li><a href="mapa.php">Mapa</a></li>
      <li><a href="resumen.php">Resumen</a></li>
      <li><a href="acerca.php">Acerca</a></li>
    </ul>
  </nav>

  <!-- Hero -->
  <section class="hero">
    <div class="container">
      <p class="hero-eyebrow">📊 Datos históricos ECOEMS 1996–2024</p>
      <h1 class="hero-title">
        Consulta el historial<br>
        de <em>puntajes de corte</em><br>
        de tu escuela ideal
      </h1>
      <p class="hero-subtitle">
        Analiza tendencias de demanda, oferta y puntajes mínimos de ingreso
        para las opciones de Educación Media Superior en la ZMCDMX,
        respaldado por datos abiertos de XABER.
      </p>

      <!-- Buscador rápido -->
      <div class="search-box quick-search-wrap">
        <div class="form-group">
          <label>Institución</label>
          <select class="form-control">
            <option value="">— Selecciona —</option>
            <option>UNAM — CCH</option>
            <option>UNAM — ENP</option>
            <option>IPN — CECyT</option>
            <option>SEP — CETIS</option>
            <option>SEP — CBTIS</option>
            <option>CONALEP</option>
            <option>UAM</option>
          </select>
        </div>
        <div class="form-group">
          <label>Plantel</label>
          <input type="text" class="form-control" placeholder="Buscar plantel…">
        </div>
        <div class="form-group">
          <label>Año</label>
          <select class="form-control">
            <option>2024</option>
            <option>2023</option>
            <option>2022</option>
            <option>2019</option>
            <option>2015</option>
            <option>2010</option>
            <option>Todos</option>
          </select>
        </div>
        <button class="btn btn-primary">🔍 Buscar</button>
      </div>

      <!-- Mini stats -->
      <div class="hero-stats">
        <div class="hero-stat">
          <strong>~29</strong>
          <span>Años de registros</span>
        </div>
        <div class="hero-stat">
          <strong>16+</strong>
          <span>Instituciones</span>
        </div>
        <div class="hero-stat">
          <strong>600+</strong>
          <span>Planteles</span>
        </div>
        <div class="hero-stat">
          <strong>+9M</strong>
          <span>Registros históricos</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Franja info -->
  <div class="about-strip">
    <span>📂 Fuente de datos:</span>
    <span><strong>XABER A.C.</strong> — Repositorio abierto de evidencia educativa</span>
    <span style="margin-left:auto; opacity:.6">xaber.org.mx/repositorio-de-datos</span>
  </div>

  <!-- Módulos -->
  <section class="section">
    <div class="container">
      <h2 class="section-title">
        <span>Módulos disponibles</span>
        ¿Qué quieres consultar?
      </h2>
      <div class="modules-grid">
        <a href="escuela.php" style="text-decoration:none">
          <div class="module-card">
            <div class="module-icon">🏫</div>
            <h3>Consulta por Escuela</h3>
            <p>Serie histórica de puntaje de corte y demanda para un plantel específico.</p>
          </div>
        </a>
        <a href="comparar.php" style="text-decoration:none">
          <div class="module-card">
            <div class="module-icon">⚖️</div>
            <h3>Comparador</h3>
            <p>Compara hasta 4 opciones educativas en una misma gráfica.</p>
          </div>
        </a>
        <a href="mapa.php" style="text-decoration:none">
          <div class="module-card">
            <div class="module-icon">🗺️</div>
            <h3>Mapa de Planteles</h3>
            <p>Visualiza todos los planteles de la ZMCDMX con sus puntajes de corte.</p>
          </div>
        </a>
        <a href="resumen.php" style="text-decoration:none">
          <div class="module-card">
            <div class="module-icon">📋</div>
            <h3>Resumen Estadístico</h3>
            <p>Tabla comparativa de medias, tendencias y ranking por institución.</p>
          </div>
        </a>
      </div>
    </div>
  </section>

  <!-- Novedades / contexto -->
  <section class="section" style="background: var(--fondo-card); padding-top:3rem; padding-bottom:3rem;">
    <div class="container">
      <h2 class="section-title" style="margin-bottom:1.2rem">
        <span>Contexto e información</span>
        Sobre el concurso ECOEMS
      </h2>
      <div class="grid-2" style="gap:1.2rem">
        <div class="news-card">
          <p class="tag">¿Qué es?</p>
          <h4>Concurso de asignación a EMS</h4>
          <p>El concurso ECOEMS asigna a los aspirantes a una opción de Educación Media Superior en la ZMCDMX. Se realiza anualmente desde 1996 con más de 300 000 participantes por año.</p>
        </div>
        <div class="news-card">
          <p class="tag">Puntaje de corte</p>
          <h4>¿Cómo se determina?</h4>
          <p>El puntaje de corte es el mínimo obtenido por el último aspirante asignado a cada opción. Varía cada año según la demanda y los lugares disponibles.</p>
        </div>
        <div class="news-card">
          <p class="tag">Datos abiertos</p>
          <h4>Fuente: XABER A.C.</h4>
          <p>Los datos que respaldan este portal fueron recopilados y publicados por XABER, una organización civil que promueve evidencia educativa abierta en México.</p>
        </div>
        <div class="news-card">
          <p class="tag">Variables disponibles</p>
          <h4>¿Qué información hay?</h4>
          <p>Puntaje de corte, demanda, oferta, asignados, institución, plantel, turno y datos socioeconómicos de los aspirantes (anonimizados) por año.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; Proyecto escolar DAW — IPN Licenciatura en Ciencia de Datos &nbsp;·&nbsp; Equipo: Héctor · Melanie · Amalia</p>
    <p style="margin-top:.4rem">Datos: <a href="https://xaber.org.mx" target="_blank">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>

</body>
</html>
