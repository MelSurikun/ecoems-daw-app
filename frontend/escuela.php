<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS — Consulta por Escuela</title>
  <link rel="stylesheet" href="css/estilos.css">
  <style>
    .result-header {
      background: linear-gradient(135deg, rgba(2,48,71,.06) 0%, transparent 100%);
      border: 1px solid var(--borde);
      border-radius: var(--radio-lg);
      padding: 1.2rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    .result-header-name h2 {
      font-family: var(--font-display);
      font-size: 1.3rem;
      color: var(--bordo);
    }
    .result-header-name p { font-size: .82rem; color: var(--texto-2); }
    .result-header-badge {
      text-align: right;
    }
    .corte-big {
      font-family: var(--font-display);
      font-size: 2.8rem;
      font-weight: 700;
      color: var(--bordo);
      line-height: 1;
    }
    .corte-big sub { font-size: .9rem; color: var(--texto-2); }
    .corte-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .1em; color: var(--texto-2); }

    /* SVG line chart */
    .line-chart-svg polyline { fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
    .line-chart-svg .area { fill-opacity: .12; stroke: none; }
    .line-chart-svg .axis-line { stroke: #ddd; stroke-width: 1; }
    .line-chart-svg text { font-family: 'DM Sans', sans-serif; font-size: 11px; fill: #888; }

    /* Demanda/oferta bars */
    .bar-chart-row { display: flex; gap: 1rem; margin-bottom: .7rem; align-items: center; }
    .bar-chart-label { width: 50px; font-size: .75rem; color: var(--texto-2); text-align: right; flex-shrink: 0; }
    .bar-track { flex: 1; height: 20px; background: var(--fondo); border-radius: 4px; overflow: hidden; }
    .bar-fill { height: 100%; border-radius: 4px; transition: var(--trans); display: flex; align-items: center; padding-left: 8px; }
    .bar-fill span { font-size: .72rem; font-weight: 700; color: #fff; }
    .bar-fill-dem { background: var(--bordo); }
    .bar-fill-ofe { background: var(--oro); }
    .bar-fill-asig { background: #2E7D32; }

    .fab {
      position: fixed;
      bottom: 2rem; right: 2rem;
      background: var(--bordo);
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: .8rem 1.4rem;
      font-family: var(--font-body);
      font-size: .85rem;
      font-weight: 600;
      cursor: pointer;
      box-shadow: var(--sombra-lg);
      display: flex;
      align-items: center;
      gap: .5rem;
      transition: var(--trans);
    }
    .fab:hover { background: var(--bordo-os); transform: translateY(-2px); }
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
      <li><a href="index.php">Inicio</a></li>
      <li><a href="escuela.php" class="activo">Por Escuela</a></li>
      <li><a href="comparar.php">Comparador</a></li>
      <li><a href="mapa.php">Mapa</a></li>
      <li><a href="resumen.php">Resumen</a></li>
      <li><a href="acerca.php">Acerca</a></li>
    </ul>
  </nav>

  <!-- Page header -->
  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Módulo 1</p>
      <h1>Consulta por Escuela / Plantel</h1>
      <p>Serie histórica de puntaje de corte, demanda y oferta para una opción educativa.</p>
    </div>
  </div>

  <!-- Contenido principal -->
  <section class="section">
    <div class="container">

      <!-- Filtros -->
      <div class="filters-panel mb-3">
        <div class="filter-group">
          <label>Institución</label>
          <select class="filter-select">
            <option>UNAM — CCH</option>
            <option>UNAM — ENP</option>
            <option>IPN — CECyT</option>
            <option>SEP — CETIS</option>
            <option>CONALEP</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Plantel</label>
          <select class="filter-select">
            <option>CCH Naucalpan</option>
            <option>CCH Vallejo</option>
            <option>CCH Oriente</option>
            <option>CCH Sur</option>
            <option>CCH Azcapotzalco</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Turno</label>
          <select class="filter-select">
            <option>Matutino</option>
            <option>Vespertino</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Año inicial</label>
          <select class="filter-select">
            <option>2000</option><option>2005</option><option>2010</option><option>2015</option><option>2019</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Año final</label>
          <select class="filter-select">
            <option selected>2024</option><option>2023</option><option>2022</option>
          </select>
        </div>
        <button class="btn btn-bordo btn-sm">🔍 Consultar</button>
        <button class="btn btn-sm" style="background:var(--fondo); border:1.5px solid var(--borde)">↓ Exportar CSV</button>
      </div>

      <!-- Encabezado resultado -->
      <div class="result-header">
        <div class="result-header-name">
          <h2>CCH Naucalpan — Matutino</h2>
          <p>UNAM &nbsp;·&nbsp; Opción 4 &nbsp;·&nbsp; Naucalpan de Juárez, Estado de México</p>
        </div>
        <div class="result-header-badge">
          <p class="corte-label">Puntaje de corte 2024</p>
          <p class="corte-big">112 <sub>pts</sub></p>
          <span class="badge badge-down">▼ 3 pts vs 2023</span>
        </div>
      </div>

      <!-- Stats rápidas -->
      <div class="stats-row mb-3">
        <div class="stat-card"><div class="stat-num">112</div><div class="stat-label">Corte 2024</div></div>
        <div class="stat-card"><div class="stat-num">109</div><div class="stat-label">Prom. Histórico</div></div>
        <div class="stat-card"><div class="stat-num">14,320</div><div class="stat-label">Demanda 2024</div></div>
        <div class="stat-card"><div class="stat-num">485</div><div class="stat-label">Lugares 2024</div></div>
        <div class="stat-card"><div class="stat-num">29.5x</div><div class="stat-label">Demanda/Oferta</div></div>
      </div>

      <!-- Gráficas -->
      <div class="grid-2 mb-3">

        <!-- Línea: puntaje de corte -->
        <div class="chart-area">
          <div class="chart-area-header">
            <h3>📈 Puntaje de corte histórico</h3>
            <span class="text-muted" style="font-size:.78rem">2010 – 2024</span>
          </div>
          <div style="padding:1.5rem 1.5rem 1rem">
            <svg class="line-chart-svg" viewBox="0 0 500 220" xmlns="http://www.w3.org/2000/svg">
              <!-- Grid lines -->
              <line x1="40" y1="10" x2="40" y2="190" class="axis-line"/>
              <line x1="40" y1="190" x2="490" y2="190" class="axis-line"/>
              <line x1="40" y1="50"  x2="490" y2="50"  stroke="#eee" stroke-width="1" stroke-dasharray="4,4"/>
              <line x1="40" y1="90"  x2="490" y2="90"  stroke="#eee" stroke-width="1" stroke-dasharray="4,4"/>
              <line x1="40" y1="130" x2="490" y2="130" stroke="#eee" stroke-width="1" stroke-dasharray="4,4"/>
              <line x1="40" y1="170" x2="490" y2="170" stroke="#eee" stroke-width="1" stroke-dasharray="4,4"/>
              <!-- Y axis labels -->
              <text x="30" y="53"  text-anchor="end">120</text>
              <text x="30" y="93"  text-anchor="end">115</text>
              <text x="30" y="133" text-anchor="end">110</text>
              <text x="30" y="173" text-anchor="end">105</text>
              <!-- X axis labels -->
              <text x="60"  y="205" text-anchor="middle">2010</text>
              <text x="120" y="205" text-anchor="middle">2012</text>
              <text x="180" y="205" text-anchor="middle">2014</text>
              <text x="240" y="205" text-anchor="middle">2016</text>
              <text x="300" y="205" text-anchor="middle">2018</text>
              <text x="360" y="205" text-anchor="middle">2020</text>
              <text x="420" y="205" text-anchor="middle">2022</text>
              <text x="470" y="205" text-anchor="middle">2024</text>
              <!-- Area fill -->
              <polygon points="60,155 120,140 180,130 240,120 300,115 360,125 420,110 470,100 470,190 60,190"
                fill="#023047" fill-opacity=".1"/>
              <!-- Line -->
              <polyline points="60,155 120,140 180,130 240,120 300,115 360,125 420,110 470,100"
                stroke="#023047" stroke-width="2.5" fill="none"/>
              <!-- Points -->
              <circle cx="60"  cy="155" r="4" fill="#023047"/>
              <circle cx="120" cy="140" r="4" fill="#023047"/>
              <circle cx="180" cy="130" r="4" fill="#023047"/>
              <circle cx="240" cy="120" r="4" fill="#023047"/>
              <circle cx="300" cy="115" r="4" fill="#023047"/>
              <circle cx="360" cy="125" r="4" fill="#023047"/>
              <circle cx="420" cy="110" r="4" fill="#023047"/>
              <circle cx="470" cy="100" r="5" fill="#fb8500"/>
              <!-- Last point label -->
              <text x="470" y="93" text-anchor="middle" fill="#fb8500" font-weight="600">112</text>
            </svg>
          </div>
        </div>

        <!-- Barras: demanda vs oferta -->
        <div class="chart-area">
          <div class="chart-area-header">
            <h3>📊 Demanda vs. Oferta</h3>
            <span class="text-muted" style="font-size:.78rem">Últimos 6 años</span>
          </div>
          <div style="padding:1.5rem">
            <div style="display:flex;gap:1.2rem;margin-bottom:1rem;font-size:.75rem;">
              <span style="display:flex;align-items:center;gap:.4rem"><span style="width:12px;height:12px;border-radius:2px;background:var(--bordo);display:inline-block"></span>Demanda</span>
              <span style="display:flex;align-items:center;gap:.4rem"><span style="width:12px;height:12px;border-radius:2px;background:var(--oro);display:inline-block"></span>Oferta</span>
              <span style="display:flex;align-items:center;gap:.4rem"><span style="width:12px;height:12px;border-radius:2px;background:#2E7D32;display:inline-block"></span>Asignados</span>
            </div>
            <!-- 2024 -->
            <p style="font-size:.75rem;font-weight:700;color:var(--texto-2);margin-bottom:.4rem">2024</p>
            <div class="bar-chart-row"><div class="bar-chart-label">Dem.</div><div class="bar-track"><div class="bar-fill bar-fill-dem" style="width:95%"><span>14,320</span></div></div></div>
            <div class="bar-chart-row"><div class="bar-chart-label">Ofe.</div><div class="bar-track"><div class="bar-fill bar-fill-ofe" style="width:3%"><span>485</span></div></div></div>
            <div class="bar-chart-row"><div class="bar-chart-label">Asig.</div><div class="bar-track"><div class="bar-fill bar-fill-asig" style="width:3%"><span>485</span></div></div></div>
            <hr class="divider" style="margin:1rem 0 .8rem">
            <!-- 2022 -->
            <p style="font-size:.75rem;font-weight:700;color:var(--texto-2);margin-bottom:.4rem">2022</p>
            <div class="bar-chart-row"><div class="bar-chart-label">Dem.</div><div class="bar-track"><div class="bar-fill bar-fill-dem" style="width:88%"><span>13,100</span></div></div></div>
            <div class="bar-chart-row"><div class="bar-chart-label">Ofe.</div><div class="bar-track"><div class="bar-fill bar-fill-ofe" style="width:3.2%"><span>480</span></div></div></div>
            <div class="bar-chart-row"><div class="bar-chart-label">Asig.</div><div class="bar-track"><div class="bar-fill bar-fill-asig" style="width:3.2%"><span>480</span></div></div></div>
          </div>
        </div>
      </div>

      <!-- Tabla de datos -->
      <div class="data-table-wrap">
        <div style="padding:1.2rem 1.5rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--borde)">
          <h3 style="font-family:var(--font-display);color:var(--bordo)">📄 Datos por año</h3>
          <button class="btn btn-sm btn-bordo">↓ Descargar CSV</button>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Año</th>
              <th>Puntaje de corte</th>
              <th>Demanda</th>
              <th>Oferta</th>
              <th>Asignados</th>
              <th>Relación D/O</th>
              <th>Variación</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>2024</td><td class="num">112</td><td>14,320</td><td>485</td><td>485</td><td class="num">29.5x</td><td><span class="badge badge-down">▼ 3</span></td></tr>
            <tr><td>2023</td><td class="num">115</td><td>13,980</td><td>485</td><td>485</td><td class="num">28.8x</td><td><span class="badge badge-up">▲ 2</span></td></tr>
            <tr><td>2022</td><td class="num">113</td><td>13,100</td><td>480</td><td>480</td><td class="num">27.3x</td><td><span class="badge badge-up">▲ 5</span></td></tr>
            <tr><td>2019</td><td class="num">108</td><td>12,450</td><td>480</td><td>480</td><td class="num">25.9x</td><td><span class="badge badge-down">▼ 1</span></td></tr>
            <tr><td>2015</td><td class="num">105</td><td>10,200</td><td>460</td><td>460</td><td class="num">22.2x</td><td><span class="badge badge-up">▲ 3</span></td></tr>
            <tr><td>2010</td><td class="num">98</td><td>8,900</td><td>440</td><td>440</td><td class="num">20.2x</td><td>—</td></tr>
          </tbody>
        </table>
      </div>

    </div>
  </section>

  <!-- FAB -->
  <button class="fab">⚖️ Agregar a comparación</button>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>

</body>
</html>
