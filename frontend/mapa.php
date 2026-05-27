<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS — Mapa de Planteles</title>
  <link rel="stylesheet" href="css/estilos.css">
  <style>
    .map-layout {
      display: grid;
      grid-template-columns: 280px 1fr;
      gap: 1.5rem;
      align-items: start;
    }
    /* Marcadores sobre el SVG del mapa */
    .map-svg-wrap {
      position: relative;
      width: 100%;
    }
    /* Popup de plantel */
    .plantel-popup {
      position: absolute;
      top: 80px; right: 40px;
      background: #fff;
      border-radius: var(--radio-lg);
      box-shadow: var(--sombra-lg);
      padding: 1.2rem;
      width: 210px;
      border-top: 4px solid var(--bordo);
      z-index: 20;
    }
    .plantel-popup h4 {
      font-family: var(--font-display);
      font-size: .95rem;
      color: var(--bordo);
      margin-bottom: .2rem;
    }
    .plantel-popup .inst { font-size: .72rem; color: var(--texto-2); margin-bottom: .8rem; }
    .popup-row { display:flex; justify-content:space-between; font-size:.8rem; border-bottom:1px solid var(--borde); padding:.35rem 0; }
    .popup-row:last-of-type { border-bottom: none; }
    .popup-row span { color: var(--texto-2); }
    .popup-row strong { color: var(--bordo); font-family: var(--font-display); }
    .popup-close {
      position: absolute;
      top: .5rem; right: .7rem;
      background: none; border: none;
      font-size: 1rem; color: var(--texto-2);
      cursor: pointer; line-height:1;
    }

    /* Mapa SVG esquemático ZMCDMX */
    .mapa-fondo { fill: #ddd5c8; }
    .mapa-agua  { fill: #b8d4e8; }
    .mapa-vial  { stroke: #c5bbad; stroke-width:2; fill:none; }
    .mapa-vial-ppal { stroke: #b0a498; stroke-width:3.5; fill:none; }
    .mapa-borde { fill: #c9bfb3; stroke:#b5a99c; stroke-width:1; }
    .mapa-verde { fill: #c5d9b8; }

    .result-list { list-style: none; }
    .result-item {
      display: flex;
      align-items: center;
      gap: .8rem;
      padding: .6rem .3rem;
      border-bottom: 1px solid var(--borde);
      font-size: .82rem;
      cursor: pointer;
      transition: var(--trans);
    }
    .result-item:hover { background: rgba(2,48,71,.04); border-radius: var(--radio); }
    .result-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .result-item-info { flex: 1; }
    .result-item-info strong { display: block; color: var(--texto); font-weight: 600; }
    .result-item-info small { color: var(--texto-2); font-size: .72rem; }
    .result-item-corte { font-family: var(--font-display); font-weight: 700; color: var(--bordo); font-size: .95rem; }
  </style>
</head>
<body class="page-wrapper">

  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-brand">
      <div class="navbar-logo">E</div>
      <div><span>Portal ECOEMS</span><small>Consulta Histórica · IPN-LCD</small></div>
    </div>
    <ul class="navbar-nav">
      <li><a href="index.php">Inicio</a></li>
      <li><a href="escuela.php">Por Escuela</a></li>
      <li><a href="comparar.php">Comparador</a></li>
      <li><a href="mapa.php" class="activo">Mapa</a></li>
      <li><a href="resumen.php">Resumen</a></li>
      <li><a href="acerca.php">Acerca</a></li>
    </ul>
  </nav>

  <!-- Page header -->
  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Módulo 3</p>
      <h1>Mapa de Planteles</h1>
      <p>Visualiza todos los planteles ECOEMS en la ZMCDMX. Haz clic en un marcador para ver su detalle.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">
      <div class="map-layout">

        <!-- Sidebar filtros -->
        <div>
          <div class="sidebar-card mb-2">
            <h4>🔍 Filtros</h4>
            <div class="filter-group mb-2">
              <label>Institución</label>
              <select class="filter-select" style="width:100%">
                <option>Todas</option>
                <option>UNAM — CCH</option>
                <option>UNAM — ENP</option>
                <option>IPN — CECyT</option>
                <option>SEP — CETIS</option>
                <option>CONALEP</option>
                <option>UAM</option>
              </select>
            </div>
            <div class="filter-group mb-2">
              <label>Delegación / Municipio</label>
              <select class="filter-select" style="width:100%">
                <option>Todas</option>
                <option>Tlalpan</option>
                <option>Coyoacán</option>
                <option>Iztapalapa</option>
                <option>Gustavo A. Madero</option>
                <option>Naucalpan (EdoMex)</option>
                <option>Ecatepec (EdoMex)</option>
              </select>
            </div>
            <div class="filter-group mb-2">
              <label>Puntaje de corte mínimo</label>
              <input type="range" class="filter-input" min="60" max="130" value="60" style="padding:0;border:none;background:none;accent-color:var(--bordo)">
              <small style="font-size:.72rem;color:var(--texto-2)">Desde: 60 pts</small>
            </div>
            <div class="filter-group mb-2">
              <label>Puntaje de corte máximo</label>
              <input type="range" class="filter-input" min="60" max="130" value="130" style="padding:0;border:none;background:none;accent-color:var(--bordo)">
              <small style="font-size:.72rem;color:var(--texto-2)">Hasta: 130 pts</small>
            </div>
            <button class="btn btn-bordo btn-sm" style="width:100%">Aplicar filtros</button>
          </div>

          <!-- Lista de resultados -->
          <div class="sidebar-card">
            <h4>📍 Planteles visibles (6)</h4>
            <ul class="result-list">
              <li class="result-item">
                <span class="result-dot" style="background:var(--bordo)"></span>
                <div class="result-item-info"><strong>CCH Naucalpan</strong><small>UNAM · Naucalpan</small></div>
                <span class="result-item-corte">112</span>
              </li>
              <li class="result-item">
                <span class="result-dot" style="background:var(--acento)"></span>
                <div class="result-item-info"><strong>Prepa 6 "Antonio Caso"</strong><small>UNAM · Coyoacán</small></div>
                <span class="result-item-corte">118</span>
              </li>
              <li class="result-item">
                <span class="result-dot" style="background:var(--oro)"></span>
                <div class="result-item-info"><strong>CECyT 9 "J. de Dios"</strong><small>IPN · G.A. Madero</small></div>
                <span class="result-item-corte">98</span>
              </li>
              <li class="result-item">
                <span class="result-dot" style="background:#1E64C8"></span>
                <div class="result-item-info"><strong>CETIS 10</strong><small>SEP · Iztapalapa</small></div>
                <span class="result-item-corte">74</span>
              </li>
              <li class="result-item">
                <span class="result-dot" style="background:#2E7D32"></span>
                <div class="result-item-info"><strong>CONALEP Tlalpan</strong><small>CONALEP · Tlalpan</small></div>
                <span class="result-item-corte">68</span>
              </li>
              <li class="result-item">
                <span class="result-dot" style="background:#6B3FA0"></span>
                <div class="result-item-info"><strong>CCH Vallejo</strong><small>UNAM · Azcapotzalco</small></div>
                <span class="result-item-corte">108</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Mapa principal -->
        <div>
          <div class="map-container">
            <span class="map-badge">📍 ZMCDMX — Año 2024</span>

            <!-- Mapa SVG esquemático -->
            <div class="map-inner">
              <div class="map-grid-lines"></div>

              <!-- SVG mapa esquemático ZMCDMX -->
              <svg viewBox="0 0 600 460" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;top:0;left:0">
                <!-- Fondo ZMCDMX contorno aproximado -->
                <polygon points="80,80 200,50 340,45 480,80 540,160 520,300 460,400 340,440 200,420 100,350 60,230 70,140"
                  fill="#e0d8cc" stroke="#c5bbad" stroke-width="1.5"/>
                <!-- Área verde (Ajusco/bosques) -->
                <ellipse cx="200" cy="370" rx="80" ry="50" fill="#c8d9b5" opacity=".7"/>
                <ellipse cx="420" cy="340" rx="60" ry="40" fill="#c8d9b5" opacity=".5"/>
                <!-- Lago Texcoco (referencia) -->
                <ellipse cx="470" cy="180" rx="55" ry="30" fill="#b8d4e8" opacity=".6"/>
                <text x="470" y="185" text-anchor="middle" font-size="9" fill="#6090a8" font-family="Sora,sans-serif">Zona lacustre</text>
                <!-- Vialidades esquemáticas -->
                <!-- Periférico aprox -->
                <ellipse cx="290" cy="230" rx="180" ry="155" fill="none" stroke="#c0b5a8" stroke-width="2.5" stroke-dasharray="8,4"/>
                <!-- Ejes viales principales -->
                <line x1="100" y1="230" x2="520" y2="230" stroke="#b5a99c" stroke-width="2"/>
                <line x1="290" y1="80"  x2="290" y2="420" stroke="#b5a99c" stroke-width="2"/>
                <line x1="150" y1="120" x2="450" y2="360" stroke="#c0b5a8" stroke-width="1.5" stroke-dasharray="5,5"/>
                <line x1="150" y1="360" x2="450" y2="120" stroke="#c0b5a8" stroke-width="1.5" stroke-dasharray="5,5"/>
                <!-- Etiquetas de zonas -->
                <text x="170" y="130" font-size="9" fill="#9a8f84" font-family="Sora,sans-serif">Naucalpan</text>
                <text x="350" y="130" font-size="9" fill="#9a8f84" font-family="Sora,sans-serif">G.A. Madero</text>
                <text x="145" y="320" font-size="9" fill="#9a8f84" font-family="Sora,sans-serif">Coyoacán</text>
                <text x="380" y="310" font-size="9" fill="#9a8f84" font-family="Sora,sans-serif">Iztapalapa</text>
                <text x="230" y="260" font-size="9" fill="#9a8f84" font-family="Sora,sans-serif">Centro</text>
                <text x="200" y="390" font-size="9" fill="#9a8f84" font-family="Sora,sans-serif">Tlalpan</text>

                <!-- ── MARCADORES ───────────────────────────── -->
                <!-- CCH Naucalpan — bordo -->
                <g transform="translate(145,145)">
                  <circle r="10" fill="#023047" stroke="#fff" stroke-width="2"/>
                  <circle r="4"  fill="#fff"/>
                  <text y="22" text-anchor="middle" font-size="9" fill="#023047" font-family="Sora,sans-serif" font-weight="600">112</text>
                </g>
                <!-- Prepa 6 — acento -->
                <g transform="translate(200,290)">
                  <circle r="10" fill="#fb8500" stroke="#fff" stroke-width="2"/>
                  <circle r="4"  fill="#fff"/>
                  <text y="22" text-anchor="middle" font-size="9" fill="#fb8500" font-family="Sora,sans-serif" font-weight="600">118</text>
                </g>
                <!-- CECyT 9 — oro -->
                <g transform="translate(355,140)">
                  <circle r="10" fill="#ffb703" stroke="#fff" stroke-width="2"/>
                  <circle r="4"  fill="#fff"/>
                  <text y="22" text-anchor="middle" font-size="9" fill="#7A5800" font-family="Sora,sans-serif" font-weight="600">98</text>
                </g>
                <!-- CETIS 10 — azul -->
                <g transform="translate(420,265)">
                  <circle r="10" fill="#1E64C8" stroke="#fff" stroke-width="2"/>
                  <circle r="4"  fill="#fff"/>
                  <text y="22" text-anchor="middle" font-size="9" fill="#1E64C8" font-family="Sora,sans-serif" font-weight="600">74</text>
                </g>
                <!-- CONALEP Tlalpan — verde -->
                <g transform="translate(245,368)">
                  <circle r="10" fill="#2E7D32" stroke="#fff" stroke-width="2"/>
                  <circle r="4"  fill="#fff"/>
                  <text y="22" text-anchor="middle" font-size="9" fill="#2E7D32" font-family="Sora,sans-serif" font-weight="600">68</text>
                </g>
                <!-- CCH Vallejo — morado -->
                <g transform="translate(255,155)">
                  <circle r="10" fill="#6B3FA0" stroke="#fff" stroke-width="2"/>
                  <circle r="4"  fill="#fff"/>
                  <text y="22" text-anchor="middle" font-size="9" fill="#6B3FA0" font-family="Sora,sans-serif" font-weight="600">108</text>
                </g>

                <!-- Rosa de los vientos mini -->
                <g transform="translate(535,390)">
                  <text y="-14" text-anchor="middle" font-size="11" fill="#999">N</text>
                  <line x1="0" y1="-10" x2="0" y2="10" stroke="#aaa" stroke-width="1.5"/>
                  <line x1="-10" y1="0" x2="10" y2="0" stroke="#aaa" stroke-width="1.5"/>
                </g>
              </svg>
            </div>

            <!-- Popup del plantel seleccionado -->
            <div class="plantel-popup">
              <button class="popup-close">✕</button>
              <h4>Prepa 6 "Antonio Caso"</h4>
              <p class="inst">UNAM — ENP &nbsp;·&nbsp; Matutino &nbsp;·&nbsp; Coyoacán</p>
              <div class="popup-row"><span>Corte 2024</span><strong>118 pts</strong></div>
              <div class="popup-row"><span>Demanda</span><strong>16,200</strong></div>
              <div class="popup-row"><span>Lugares</span><strong>380</strong></div>
              <div class="popup-row"><span>Rel. D/O</span><strong>42.6x</strong></div>
              <a href="escuela.php" class="btn btn-bordo btn-sm" style="margin-top:.8rem;width:100%;justify-content:center">Ver detalle completo →</a>
            </div>

            <!-- Leyenda -->
            <div class="map-legend">
              <h4>Instituciones</h4>
              <div class="legend-item"><span class="legend-dot" style="background:#023047"></span>UNAM — CCH</div>
              <div class="legend-item"><span class="legend-dot" style="background:#fb8500"></span>UNAM — ENP</div>
              <div class="legend-item"><span class="legend-dot" style="background:#ffb703"></span>IPN — CECyT</div>
              <div class="legend-item"><span class="legend-dot" style="background:#1E64C8"></span>SEP — CETIS/CBTIS</div>
              <div class="legend-item"><span class="legend-dot" style="background:#2E7D32"></span>CONALEP</div>
              <div class="legend-item"><span class="legend-dot" style="background:#6B3FA0"></span>Otros</div>
            </div>
          </div>
          <p style="font-size:.74rem;color:var(--texto-2);margin-top:.6rem;text-align:center">
            * Mapa esquemático para fines del prototipo. La versión funcional usará <strong>Leaflet.js + OpenStreetMap</strong>.
          </p>
        </div>

      </div>
    </div>
  </section>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>
</body>
</html>
