<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS — Resumen Estadístico</title>
  <link rel="stylesheet" href="css/estilos.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    .spark { width: 70px; height: 28px; vertical-align: middle; }
    .spark polyline { fill: none; stroke-width: 2; stroke-linecap: round; }
    .ranking-badge {
      display: inline-flex; align-items: center; justify-content: center;
      width: 26px; height: 26px; border-radius: 50%;
      font-size: .78rem; font-weight: 700; flex-shrink: 0;
    }
    .rank-1 { background: var(--oro); color: var(--bordo); }
    .rank-2 { background: #c0c0c0;    color: #333; }
    .rank-3 { background: #cd7f32;    color: #fff; }
    .rank-n { background: var(--fondo); color: var(--texto-2); border:1px solid var(--borde); }
    .tab-bar { display:flex; gap:.4rem; border-bottom:2px solid var(--borde); margin-bottom:1.5rem; }
    .tab-btn {
      padding:.6rem 1.2rem; border:none; background:none;
      font-family:var(--font-body); font-size:.86rem; font-weight:600;
      color:var(--texto-2); cursor:pointer; border-bottom:2px solid transparent;
      margin-bottom:-2px; transition:var(--trans);
    }
    .tab-btn.active { color:var(--bordo); border-bottom-color:var(--bordo); }
    .tab-btn:hover { color:var(--bordo); }
    .spinner { display:inline-block; width:16px; height:16px; border:2px solid var(--borde);
               border-top-color:var(--bordo); border-radius:50%; animation:spin .7s linear infinite; }
    @keyframes spin { to { transform:rotate(360deg); } }
    .canvas-wrap { padding:1.5rem; }
  </style>
</head>
<body class="page-wrapper">

  <?php require 'includes/navbar.php'; ?>

  <!-- Page header -->
  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Módulo 4</p>
      <h1>Resumen Estadístico</h1>
      <p>Estadísticas globales del proceso COMIPEMS 2024 obtenidas en tiempo real desde la base de datos.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">

      <!-- KPIs globales (se llenan con JS) -->
      <div class="stats-row mb-3" id="kpis-globales">
        <div class="stat-card"><div class="stat-num"><span class="spinner"></span></div><div class="stat-label">Total sustentantes</div></div>
        <div class="stat-card"><div class="stat-num">—</div><div class="stat-label">Presentaron examen</div></div>
        <div class="stat-card"><div class="stat-num">—</div><div class="stat-label">Asignados</div></div>
        <div class="stat-card"><div class="stat-num">—</div><div class="stat-label">Puntaje prom. global</div></div>
        <div class="stat-card"><div class="stat-num">—</div><div class="stat-label">Hombres</div></div>
        <div class="stat-card"><div class="stat-num">—</div><div class="stat-label">Mujeres</div></div>
      </div>

      <!-- Gráficas -->
      <div class="grid-2 mb-3">
        <div class="chart-area">
          <div class="chart-area-header">
            <h3>🏫 Asignados por institución</h3>
          </div>
          <div class="canvas-wrap">
            <canvas id="chart-inst" height="240"></canvas>
          </div>
        </div>
        <div class="chart-area">
          <div class="chart-area-header">
            <h3>📐 Porcentaje por materia (promedio global)</h3>
          </div>
          <div class="canvas-wrap">
            <canvas id="chart-materias" height="240"></canvas>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tab-bar">
        <button class="tab-btn active" id="tab-inst">Por institución</button>
        <button class="tab-btn" id="tab-top">Top 10 planteles más solicitados</button>
      </div>

      <!-- Tabla por institución -->
      <div id="panel-inst">
        <div class="data-table-wrap">
          <div style="padding:1.2rem 1.5rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--borde)">
            <h3 style="font-family:var(--font-display);color:var(--bordo)">Resumen por institución asignada — 2024</h3>
          </div>
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Institución</th>
                <th>Aspirantes</th>
                <th>Asignados</th>
                <th>Prom. aciertos</th>
                <th>Prom. certificado</th>
                <th>Hombres</th>
                <th>Mujeres</th>
              </tr>
            </thead>
            <tbody id="tbody-inst">
              <tr><td colspan="8" style="text-align:center;padding:1.5rem;color:var(--texto-2)">
                <span class="spinner"></span> Cargando desde la base de datos…
              </td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tabla top planteles (oculta al inicio) -->
      <div id="panel-top" style="display:none">
        <div class="data-table-wrap">
          <div style="padding:1.2rem 1.5rem;border-bottom:1px solid var(--borde)">
            <h3 style="font-family:var(--font-display);color:var(--bordo)">Top 10 planteles más solicitados — 2024</h3>
          </div>
          <table class="data-table">
            <thead>
              <tr><th>#</th><th>Clave de plantel</th><th>Solicitudes (opción 1ª)</th><th>Ver detalle</th></tr>
            </thead>
            <tbody id="tbody-top"></tbody>
          </table>
        </div>
      </div>

    </div>
  </section>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>

  <script src="js/graficas.js"></script>
  <script>
    // ── Cargar datos del resumen ────────────────────────────
    async function cargarResumen() {
      try {
        const resp = await fetch('../backend/api/resumen.php');
        const json = await resp.json();
        if (json.status !== 'ok') throw new Error('API error');

        const t  = json.totales;
        const pi = json.por_institucion;
        const tp = json.top_planteles;

        // ── KPIs ───────────────────────────────────────────
        const kpis = document.querySelectorAll('#kpis-globales .stat-num');
        const vals = [
          parseInt(t.total_sustentantes ?? 0).toLocaleString('es-MX'),
          parseInt(t.presentaron_examen ?? 0).toLocaleString('es-MX'),
          parseInt(t.asignados ?? 0).toLocaleString('es-MX'),
          (t.promedio_global ?? '—') + ' pts',
          parseInt(t.hombres ?? 0).toLocaleString('es-MX'),
          parseInt(t.mujeres ?? 0).toLocaleString('es-MX'),
        ];
        kpis.forEach((el, i) => { el.textContent = vals[i] ?? '—'; });

        // ── Gráfica: asignados por institución ─────────────
        const colores = ['#023047','#ffb703','#fb8500','#1E64C8','#2E7D32','#6B3FA0','#e63946','#457b9d'];
        new Chart(document.getElementById('chart-inst'), {
          type: 'bar',
          data: {
            labels:   pi.map(d => d.institucion ?? '—'),
            datasets: [{
              label: 'Asignados',
              data:  pi.map(d => parseInt(d.asignados)),
              backgroundColor: pi.map((_, i) => (colores[i % colores.length]) + 'cc'),
              borderColor:     pi.map((_, i) =>  colores[i % colores.length]),
              borderWidth: 1.5, borderRadius: 4,
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
              y: { beginAtZero: true, ticks: { font: { family: 'Sora', size: 11 } }, grid: { color: '#e2eaf0' } },
              x: { ticks: { font: { family: 'Sora', size: 10 } }, grid: { display: false } }
            }
          }
        });

        // ── Gráfica: radar de materias ──────────────────────
        const materias   = ['Matemáticas','Español','Historia','Biología','Física','Química'];
        const pctCampos  = ['pct_matematicas','pct_espanol','pct_historia','pct_biologia','pct_fisica','pct_quimica'];
        new Chart(document.getElementById('chart-materias'), {
          type: 'radar',
          data: {
            labels: materias,
            datasets: [{
              label: 'Promedio % aciertos',
              data:  pctCampos.map(k => parseFloat(t[k] ?? 0)),
              borderColor: '#023047',
              backgroundColor: 'rgba(2,48,71,0.1)',
              borderWidth: 2,
              pointBackgroundColor: '#ffb703',
              pointRadius: 4,
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { labels: { font: { family: 'Sora' } } } },
            scales: {
              r: {
                beginAtZero: true,
                ticks: { font: { family: 'Sora', size: 10 } },
                pointLabels: { font: { family: 'Sora', size: 11 } }
              }
            }
          }
        });

        // ── Tabla: por institución ─────────────────────────
        document.getElementById('tbody-inst').innerHTML = pi.map((d, i) => {
          const rankClass = i < 3 ? `rank-${i+1}` : 'rank-n';
          return `
            <tr>
              <td><span class="ranking-badge ${rankClass}">${i+1}</span></td>
              <td><strong>${d.institucion ?? '—'}</strong></td>
              <td class="num">${parseInt(d.total_aspirantes ?? 0).toLocaleString('es-MX')}</td>
              <td class="num">${parseInt(d.asignados ?? 0).toLocaleString('es-MX')}</td>
              <td class="num">${d.promedio_aciertos ?? '—'}</td>
              <td class="num">${d.promedio_certificado ?? '—'}</td>
              <td class="num">${parseInt(d.hombres ?? 0).toLocaleString('es-MX')}</td>
              <td class="num">${parseInt(d.mujeres ?? 0).toLocaleString('es-MX')}</td>
            </tr>`;
        }).join('') || '<tr><td colspan="8" style="text-align:center;color:var(--texto-2);padding:1rem">Sin datos disponibles.</td></tr>';

        // ── Tabla: top planteles ───────────────────────────
        document.getElementById('tbody-top').innerHTML = tp.map((d, i) => {
          const rankClass = i < 3 ? `rank-${i+1}` : 'rank-n';
          return `
            <tr>
              <td><span class="ranking-badge ${rankClass}">${i+1}</span></td>
              <td><strong>${d.clave ?? '—'}</strong></td>
              <td class="num">${parseInt(d.solicitudes ?? 0).toLocaleString('es-MX')}</td>
              <td><a href="escuela.php?plantel=${encodeURIComponent(d.clave ?? '')}"
                    class="btn btn-sm btn-bordo" style="font-size:.75rem;padding:.3rem .7rem">Ver →</a></td>
            </tr>`;
        }).join('');

      } catch (err) {
        document.getElementById('tbody-inst').innerHTML =
          `<tr><td colspan="8" style="text-align:center;color:#c00;padding:1rem">
            ❌ Error al conectar con la base de datos. Verifica que el backend esté activo.<br>
            <small>${err.message}</small>
          </td></tr>`;
        console.error(err);
      }
    }

    // ── Tabs ────────────────────────────────────────────────
    document.getElementById('tab-inst').addEventListener('click', () => {
      document.getElementById('panel-inst').style.display = 'block';
      document.getElementById('panel-top').style.display  = 'none';
      document.getElementById('tab-inst').classList.add('active');
      document.getElementById('tab-top').classList.remove('active');
    });
    document.getElementById('tab-top').addEventListener('click', () => {
      document.getElementById('panel-inst').style.display = 'none';
      document.getElementById('panel-top').style.display  = 'block';
      document.getElementById('tab-top').classList.add('active');
      document.getElementById('tab-inst').classList.remove('active');
    });

    // ── Arrancar ────────────────────────────────────────────
    cargarResumen();
  </script>
</body>
</html>
