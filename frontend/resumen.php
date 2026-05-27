<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS — Resumen Estadístico</title>
  <link rel="stylesheet" href="css/estilos.css">
  <style>
    .sort-arrow { color: var(--oro); margin-left: .3rem; font-size: .75rem; }
    .trend-spark { font-size: 1.1rem; }
    .num-med { color: #1E64C8; font-family: var(--font-display); font-weight: 700; }
    .num-mean { color: #2E7D32; font-family: var(--font-display); font-weight: 700; }

    /* Mini sparkline SVG */
    .spark { width: 70px; height: 28px; vertical-align: middle; }
    .spark polyline { fill: none; stroke-width: 2; stroke-linecap: round; }

    .ranking-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 26px; height: 26px;
      border-radius: 50%;
      font-size: .78rem;
      font-weight: 700;
      flex-shrink: 0;
    }
    .rank-1 { background: var(--oro);   color: var(--bordo); }
    .rank-2 { background: #c0c0c0;      color: #333; }
    .rank-3 { background: #cd7f32;      color: #fff; }
    .rank-n { background: var(--fondo); color: var(--texto-2); border: 1px solid var(--borde); }

    .tab-bar {
      display: flex;
      gap: .4rem;
      border-bottom: 2px solid var(--borde);
      margin-bottom: 1.5rem;
    }
    .tab-btn {
      padding: .6rem 1.2rem;
      border: none;
      background: none;
      font-family: var(--font-body);
      font-size: .86rem;
      font-weight: 600;
      color: var(--texto-2);
      cursor: pointer;
      border-bottom: 2px solid transparent;
      margin-bottom: -2px;
      transition: var(--trans);
    }
    .tab-btn.active { color: var(--bordo); border-bottom-color: var(--bordo); }
    .tab-btn:hover { color: var(--bordo); }
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
      <li><a href="mapa.php">Mapa</a></li>
      <li><a href="resumen.php" class="activo">Resumen</a></li>
      <li><a href="acerca.php">Acerca</a></li>
    </ul>
  </nav>

  <!-- Page header -->
  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Módulo 4</p>
      <h1>Resumen Estadístico</h1>
      <p>Tabla dinámica de media, mediana, tendencia y ranking de puntajes de corte por institución.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">

      <!-- Filtros -->
      <div class="filters-panel mb-3">
        <div class="filter-group">
          <label>Año</label>
          <select class="filter-select">
            <option selected>2024</option><option>2023</option><option>Promedio 2015-2024</option><option>Promedio histórico</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Institución</label>
          <select class="filter-select">
            <option>Todas</option>
            <option>UNAM</option>
            <option>IPN</option>
            <option>SEP</option>
            <option>CONALEP</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Ordenar por</label>
          <select class="filter-select">
            <option>Mayor puntaje de corte</option>
            <option>Menor puntaje de corte</option>
            <option>Mayor demanda</option>
            <option>Mayor ratio D/O</option>
          </select>
        </div>
        <button class="btn btn-bordo btn-sm">Aplicar</button>
        <button class="btn btn-sm" style="background:var(--fondo);border:1.5px solid var(--borde)">↓ Exportar CSV</button>
      </div>

      <!-- KPIs globales 2024 -->
      <div class="stats-row mb-3">
        <div class="stat-card"><div class="stat-num">107</div><div class="stat-label">Corte prom. ZMCDMX</div></div>
        <div class="stat-card"><div class="stat-num">108</div><div class="stat-label">Mediana de cortes</div></div>
        <div class="stat-card"><div class="stat-num">603</div><div class="stat-label">Opciones disponibles</div></div>
        <div class="stat-card"><div class="stat-num">380K</div><div class="stat-label">Aspirantes 2024</div></div>
        <div class="stat-card"><div class="stat-num">118</div><div class="stat-label">Corte más alto (Prepa 6)</div></div>
        <div class="stat-card"><div class="stat-num">58</div><div class="stat-label">Corte más bajo</div></div>
      </div>

      <!-- Tabs -->
      <div class="tab-bar">
        <button class="tab-btn active">Por institución</button>
        <button class="tab-btn">Por plantel (top 20)</button>
        <button class="tab-btn">Tendencia anual</button>
      </div>

      <!-- Tabla principal -->
      <div class="data-table-wrap">
        <div style="padding:1.2rem 1.5rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--borde)">
          <h3 style="font-family:var(--font-display);color:var(--bordo)">Resumen por institución — 2024</h3>
          <span style="font-size:.78rem;color:var(--texto-2)">Haz clic en los encabezados para ordenar</span>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:40px">#</th>
              <th>Institución</th>
              <th>Planteles</th>
              <th>Corte prom. <span class="sort-arrow">▲</span></th>
              <th>Corte mediana</th>
              <th>Corte más alto</th>
              <th>Corte más bajo</th>
              <th>Demanda total</th>
              <th>Tendencia (5 años)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="ranking-badge rank-1">1</span></td>
              <td><strong>UNAM — ENP</strong></td>
              <td>9</td>
              <td class="num">113</td>
              <td class="num-med">114</td>
              <td class="num">118</td>
              <td>108</td>
              <td>72,400</td>
              <td>
                <svg class="spark" viewBox="0 0 70 28">
                  <polyline points="5,22 18,18 31,15 44,12 57,10 65,8" stroke="#023047"/>
                </svg>
                <span class="badge badge-up">▲ +5</span>
              </td>
            </tr>
            <tr>
              <td><span class="ranking-badge rank-2">2</span></td>
              <td><strong>UNAM — CCH</strong></td>
              <td>5</td>
              <td class="num">110</td>
              <td class="num-med">111</td>
              <td class="num">115</td>
              <td>104</td>
              <td>56,200</td>
              <td>
                <svg class="spark" viewBox="0 0 70 28">
                  <polyline points="5,20 18,17 31,16 44,14 57,13 65,11" stroke="#023047"/>
                </svg>
                <span class="badge badge-up">▲ +3</span>
              </td>
            </tr>
            <tr>
              <td><span class="ranking-badge rank-3">3</span></td>
              <td><strong>UAM</strong></td>
              <td>4</td>
              <td class="num">102</td>
              <td class="num-med">103</td>
              <td class="num">108</td>
              <td>97</td>
              <td>18,100</td>
              <td>
                <svg class="spark" viewBox="0 0 70 28">
                  <polyline points="5,18 18,17 31,16 44,15 57,15 65,14" stroke="#ffb703"/>
                </svg>
                <span class="badge badge-up">▲ +2</span>
              </td>
            </tr>
            <tr>
              <td><span class="ranking-badge rank-n">4</span></td>
              <td><strong>IPN — CECyT</strong></td>
              <td>16</td>
              <td class="num">95</td>
              <td class="num-med">96</td>
              <td class="num">104</td>
              <td>84</td>
              <td>88,000</td>
              <td>
                <svg class="spark" viewBox="0 0 70 28">
                  <polyline points="5,16 18,17 31,15 44,14 57,14 65,12" stroke="#ffb703"/>
                </svg>
                <span class="badge badge-up">▲ +2</span>
              </td>
            </tr>
            <tr>
              <td><span class="ranking-badge rank-n">5</span></td>
              <td><strong>SEP — DGETI (CETIS/CBTIS)</strong></td>
              <td>42</td>
              <td class="num">78</td>
              <td class="num-med">76</td>
              <td class="num">95</td>
              <td>58</td>
              <td>95,000</td>
              <td>
                <svg class="spark" viewBox="0 0 70 28">
                  <polyline points="5,14 18,15 31,16 44,14 57,13 65,13" stroke="#1E64C8"/>
                </svg>
                <span class="badge badge-down">▼ −1</span>
              </td>
            </tr>
            <tr>
              <td><span class="ranking-badge rank-n">6</span></td>
              <td><strong>CONALEP</strong></td>
              <td>28</td>
              <td class="num">70</td>
              <td class="num-med">69</td>
              <td class="num">82</td>
              <td>58</td>
              <td>62,000</td>
              <td>
                <svg class="spark" viewBox="0 0 70 28">
                  <polyline points="5,12 18,14 31,13 44,14 57,15 65,14" stroke="#2E7D32"/>
                </svg>
                <span class="badge badge-down">▼ −1</span>
              </td>
            </tr>
            <tr>
              <td><span class="ranking-badge rank-n">7</span></td>
              <td><strong>SEP — DGB (Preparatorias)</strong></td>
              <td>18</td>
              <td class="num">68</td>
              <td class="num-med">67</td>
              <td class="num">79</td>
              <td>58</td>
              <td>42,000</td>
              <td>
                <svg class="spark" viewBox="0 0 70 28">
                  <polyline points="5,15 18,16 31,17 44,16 57,16 65,17" stroke="#888"/>
                </svg>
                <span class="badge badge-down">▼ −2</span>
              </td>
            </tr>
          </tbody>
        </table>
        <div style="padding:.8rem 1.5rem;background:var(--fondo);font-size:.76rem;color:var(--texto-2)">
          7 instituciones · 122 planteles mostrados · Datos ECOEMS 2024 via XABER A.C.
        </div>
      </div>

    </div>
  </section>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>
</body>
</html>
