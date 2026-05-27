<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS — Comparador</title>
  <link rel="stylesheet" href="css/estilos.css">
  <style>
    .compare-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    .option-slot {
      border: 2px dashed var(--borde);
      border-radius: var(--radio-lg);
      padding: 1.2rem;
      display: flex;
      flex-direction: column;
      gap: .6rem;
      min-height: 130px;
      transition: var(--trans);
    }
    .option-slot.filled { border-style: solid; }
    .option-slot.s1 { border-color: var(--bordo); background: rgba(2,48,71,.04); }
    .option-slot.s2 { border-color: var(--oro);   background: rgba(255,183,3,.04); }
    .option-slot.s3 { border-color: var(--acento); background: rgba(251,133,0,.04); }
    .option-slot.s4 { border-color: #1E64C8;        background: rgba(30,100,200,.04); }
    .option-slot.empty { align-items: center; justify-content: center; color: var(--texto-2); font-size:.85rem; cursor:pointer; }
    .option-slot.empty:hover { border-color: var(--bordo); color: var(--bordo); }
    .slot-num {
      font-size: .65rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .1em;
    }
    .s1 .slot-num { color: var(--bordo); }
    .s2 .slot-num { color: #7A5800; }
    .s3 .slot-num { color: var(--acento); }
    .s4 .slot-num { color: #1E64C8; }
    .slot-name { font-family: var(--font-display); font-size: .9rem; font-weight: 600; color: var(--texto); }
    .slot-inst { font-size: .75rem; color: var(--texto-2); }
    .slot-corte { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; }
    .s1 .slot-corte { color: var(--bordo); }
    .s2 .slot-corte { color: #7A5800; }
    .s3 .slot-corte { color: var(--acento); }
    .s4 .slot-corte { color: #1E64C8; }
    .slot-remove { align-self: flex-end; font-size:.8rem; cursor:pointer; color:var(--texto-2); background:none; border:none; font-family:var(--font-body); }
    .slot-remove:hover { color: var(--acento); }

    /* Gráfica comparativa SVG */
    .compare-svg { width:100%; height:280px; }
    .legend-bar { display:flex; gap:1.5rem; flex-wrap:wrap; padding:.8rem 1.5rem; border-bottom:1px solid var(--borde); }
    .legend-item-compare { display:flex; align-items:center; gap:.5rem; font-size:.82rem; }
    .legend-color { width:18px; height:4px; border-radius:2px; }
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
      <li><a href="comparar.php" class="activo">Comparador</a></li>
      <li><a href="mapa.php">Mapa</a></li>
      <li><a href="resumen.php">Resumen</a></li>
      <li><a href="acerca.php">Acerca</a></li>
    </ul>
  </nav>

  <!-- Page header -->
  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Módulo 2</p>
      <h1>Comparador de Opciones</h1>
      <p>Compara hasta 4 opciones educativas en la misma gráfica de puntaje de corte histórico.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">

      <!-- Slots de selección -->
      <h2 class="section-title" style="font-size:1.15rem; margin-bottom:1rem">
        <span>Paso 1 — Selecciona las opciones</span>
        Opciones a comparar (máx. 4)
      </h2>
      <div class="compare-grid">
        <!-- Slot 1 — lleno -->
        <div class="option-slot filled s1">
          <span class="slot-num">Opción 1</span>
          <span class="slot-name">CCH Naucalpan</span>
          <span class="slot-inst">UNAM — Matutino</span>
          <span class="slot-corte">112 pts</span>
          <button class="slot-remove">✕ Quitar</button>
        </div>
        <!-- Slot 2 — lleno -->
        <div class="option-slot filled s2">
          <span class="slot-num">Opción 2</span>
          <span class="slot-name">CECyT 9 "Juan de Dios"</span>
          <span class="slot-inst">IPN — Matutino</span>
          <span class="slot-corte">98 pts</span>
          <button class="slot-remove">✕ Quitar</button>
        </div>
        <!-- Slot 3 — lleno -->
        <div class="option-slot filled s3">
          <span class="slot-num">Opción 3</span>
          <span class="slot-name">Prepa 6 "Antonio Caso"</span>
          <span class="slot-inst">UNAM — ENP Matutino</span>
          <span class="slot-corte">118 pts</span>
          <button class="slot-remove">✕ Quitar</button>
        </div>
        <!-- Slot 4 — vacío -->
        <div class="option-slot empty">
          <span style="font-size:1.5rem">＋</span>
          <span>Agregar opción</span>
        </div>
      </div>

      <!-- Filtro de rango de años -->
      <div class="filters-panel mb-3">
        <div class="filter-group">
          <label>Año inicial</label>
          <select class="filter-select">
            <option>2005</option><option>2010</option><option selected>2015</option><option>2019</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Año final</label>
          <select class="filter-select">
            <option selected>2024</option><option>2023</option><option>2022</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Variable a comparar</label>
          <select class="filter-select">
            <option>Puntaje de corte</option>
            <option>Demanda</option>
            <option>Relación D/O</option>
          </select>
        </div>
        <button class="btn btn-bordo btn-sm">↻ Actualizar gráfica</button>
        <button class="btn btn-sm" style="background:var(--fondo);border:1.5px solid var(--borde)">🖼 Descargar imagen</button>
      </div>

      <!-- Gráfica comparativa -->
      <div class="chart-area mb-3">
        <div class="chart-area-header">
          <h3>📈 Puntaje de corte histórico — Comparación</h3>
          <span class="text-muted" style="font-size:.78rem">2015 – 2024</span>
        </div>
        <!-- Leyenda -->
        <div class="legend-bar">
          <div class="legend-item-compare"><div class="legend-color" style="background:var(--bordo)"></div> CCH Naucalpan</div>
          <div class="legend-item-compare"><div class="legend-color" style="background:var(--oro)"></div> CECyT 9 IPN</div>
          <div class="legend-item-compare"><div class="legend-color" style="background:var(--acento)"></div> Prepa 6 UNAM</div>
        </div>
        <div style="padding:1.5rem">
          <svg class="compare-svg" viewBox="0 0 680 260" xmlns="http://www.w3.org/2000/svg">
            <!-- Grid -->
            <line x1="50" y1="10" x2="50" y2="220" stroke="#ddd" stroke-width="1"/>
            <line x1="50" y1="220" x2="660" y2="220" stroke="#ddd" stroke-width="1"/>
            <line x1="50" y1="50"  x2="660" y2="50"  stroke="#eee" stroke-width="1" stroke-dasharray="4,4"/>
            <line x1="50" y1="90"  x2="660" y2="90"  stroke="#eee" stroke-width="1" stroke-dasharray="4,4"/>
            <line x1="50" y1="130" x2="660" y2="130" stroke="#eee" stroke-width="1" stroke-dasharray="4,4"/>
            <line x1="50" y1="170" x2="660" y2="170" stroke="#eee" stroke-width="1" stroke-dasharray="4,4"/>
            <!-- Y labels -->
            <text x="40" y="53"  text-anchor="end" font-size="11" fill="#888">125</text>
            <text x="40" y="93"  text-anchor="end" font-size="11" fill="#888">118</text>
            <text x="40" y="133" text-anchor="end" font-size="11" fill="#888">110</text>
            <text x="40" y="173" text-anchor="end" font-size="11" fill="#888">100</text>
            <!-- X labels -->
            <text x="90"  y="238" text-anchor="middle" font-size="11" fill="#888">2015</text>
            <text x="200" y="238" text-anchor="middle" font-size="11" fill="#888">2017</text>
            <text x="310" y="238" text-anchor="middle" font-size="11" fill="#888">2019</text>
            <text x="420" y="238" text-anchor="middle" font-size="11" fill="#888">2021</text>
            <text x="530" y="238" text-anchor="middle" font-size="11" fill="#888">2023</text>
            <text x="640" y="238" text-anchor="middle" font-size="11" fill="#888">2024</text>

            <!-- Línea 1: CCH Naucalpan (bordo) -->
            <polyline points="90,160 200,145 310,138 420,130 530,122 640,118"
              stroke="#023047" stroke-width="2.5" fill="none"/>
            <circle cx="90"  cy="160" r="4" fill="#023047"/>
            <circle cx="200" cy="145" r="4" fill="#023047"/>
            <circle cx="310" cy="138" r="4" fill="#023047"/>
            <circle cx="420" cy="130" r="4" fill="#023047"/>
            <circle cx="530" cy="122" r="4" fill="#023047"/>
            <circle cx="640" cy="118" r="5" fill="#023047"/>

            <!-- Línea 2: CECyT 9 (oro) -->
            <polyline points="90,185 200,175 310,170 420,165 530,158 640,148"
              stroke="#ffb703" stroke-width="2.5" fill="none" stroke-dasharray="6,3"/>
            <circle cx="90"  cy="185" r="4" fill="#ffb703"/>
            <circle cx="200" cy="175" r="4" fill="#ffb703"/>
            <circle cx="310" cy="170" r="4" fill="#ffb703"/>
            <circle cx="420" cy="165" r="4" fill="#ffb703"/>
            <circle cx="530" cy="158" r="4" fill="#ffb703"/>
            <circle cx="640" cy="148" r="5" fill="#ffb703"/>

            <!-- Línea 3: Prepa 6 (acento) -->
            <polyline points="90,100 200,90 310,80 420,72 530,65 640,58"
              stroke="#fb8500" stroke-width="2.5" fill="none" stroke-dasharray="2,3"/>
            <circle cx="90"  cy="100" r="4" fill="#fb8500"/>
            <circle cx="200" cy="90"  r="4" fill="#fb8500"/>
            <circle cx="310" cy="80"  r="4" fill="#fb8500"/>
            <circle cx="420" cy="72"  r="4" fill="#fb8500"/>
            <circle cx="530" cy="65"  r="4" fill="#fb8500"/>
            <circle cx="640" cy="58"  r="5" fill="#fb8500"/>
          </svg>
        </div>
      </div>

      <!-- Tabla resumen comparativo -->
      <div class="data-table-wrap">
        <div style="padding:1.2rem 1.5rem; border-bottom:1px solid var(--borde)">
          <h3 style="font-family:var(--font-display);color:var(--bordo)">Resumen comparativo — 2024</h3>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Opción</th>
              <th>Institución</th>
              <th>Corte 2024</th>
              <th>Corte prom. histórico</th>
              <th>Demanda 2024</th>
              <th>Oferta 2024</th>
              <th>Tendencia</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span style="width:10px;height:10px;border-radius:50%;background:var(--bordo);display:inline-block;margin-right:.4rem"></span>CCH Naucalpan Mat.</td>
              <td>UNAM</td><td class="num">112</td><td class="num">106</td><td>14,320</td><td>485</td>
              <td><span class="badge badge-up">▲ Sube</span></td>
            </tr>
            <tr>
              <td><span style="width:10px;height:10px;border-radius:50%;background:var(--oro);display:inline-block;margin-right:.4rem"></span>CECyT 9 Mat.</td>
              <td>IPN</td><td class="num">98</td><td class="num">93</td><td>9,800</td><td>420</td>
              <td><span class="badge badge-up">▲ Sube</span></td>
            </tr>
            <tr>
              <td><span style="width:10px;height:10px;border-radius:50%;background:var(--acento);display:inline-block;margin-right:.4rem"></span>Prepa 6 Mat.</td>
              <td>UNAM — ENP</td><td class="num">118</td><td class="num">112</td><td>16,200</td><td>380</td>
              <td><span class="badge badge-up">▲ Sube</span></td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </section>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>
</body>
</html>
