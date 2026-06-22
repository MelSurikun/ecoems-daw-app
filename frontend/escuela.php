<?php require_once __DIR__ . '/../backend/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS, Consulta por Escuela</title>
  <link rel="stylesheet" href="css/estilos.css?v=2">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
    .result-header-name h2 { font-family: var(--font-display); font-size: 1.3rem; color: var(--bordo); }
    .result-header-name p  { font-size: .82rem; color: var(--texto-2); }
    .corte-big { font-family: var(--font-display); font-size: 2.8rem; font-weight: 700; color: var(--bordo); line-height: 1; }
    .corte-big sub { font-size: .9rem; color: var(--texto-2); }
    .corte-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .1em; color: var(--texto-2); }
    .fab {
      position: fixed; bottom: 2rem; right: 2rem;
      background: var(--bordo); color: #fff; border: none;
      border-radius: 50px; padding: .8rem 1.4rem;
      font-family: var(--font-body); font-size: .85rem; font-weight: 600;
      cursor: pointer; box-shadow: var(--sombra-lg);
      display: flex; align-items: center; gap: .5rem; transition: var(--trans);
    }
    .fab:hover { background: var(--bordo-os); transform: translateY(-2px); }
    .estado-msg {
      padding: 2rem; text-align: center; color: var(--texto-2);
      font-size: .9rem; background: var(--fondo-card);
      border-radius: var(--radio-lg); border: 1px dashed var(--borde);
    }
    .spinner {
      display: inline-block; width: 20px; height: 20px;
      border: 3px solid var(--borde); border-top-color: var(--bordo);
      border-radius: 50%; animation: spin .7s linear infinite;
      vertical-align: middle; margin-right: .5rem;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .canvas-wrap { padding: 1.5rem; position: relative; min-height: 200px; }
  </style>
</head>
<body class="page-wrapper">

  <?php require 'includes/navbar.php'; ?>

  <!-- Page header -->
  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Módulo 1</p>
      <h1>Consulta por Escuela / Plantel</h1>
      <p>Estadísticas de demanda, asignación y puntajes para una opción educativa.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">

      <!-- Filtros -->
      <div class="filters-panel mb-3">
        <div class="filter-group" style="flex:2">
          <label>Clave de plantel (ej. U70001, I10009)</label>
          <input type="text" id="inp-plantel" class="filter-select"
                 placeholder="Escribe la clave o busca…"
                 style="min-width:180px">
          <!-- Sugerencias autocomplete -->
          <div id="autocomplete-list" style="
            position:absolute; background:#fff; border:1px solid var(--borde);
            border-radius:var(--radio); box-shadow:var(--sombra);
            z-index:200; max-height:200px; overflow-y:auto; width:260px; display:none;
          "></div>
        </div>
        <div class="filter-group" style="position:relative">
          <!-- El campo plantel es suficiente para la segunda entrega; año viene en entrega 3 -->
        </div>
        <button id="btn-buscar" class="btn btn-bordo btn-sm">Consultar</button>
      </div>

      <!-- Área de resultados -->
      <div id="resultados">
        <div class="estado-msg">
          Ingresa una clave de plantel y presiona <strong>Consultar</strong>.
        </div>
      </div>

    </div>
  </section>

  <!-- FAB -->
  <button class="fab" id="btn-fab" style="display:none">Agregar a comparación</button>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>

  <script src="js/graficas.js"></script>
  <script>
    // ── Plantilla HTML para resultados ──────────────────────
    function plantillaResultado(clave, d) {
      const nombreEsc = d.nombre || clave;
      if (!d.tiene_datos) {
        return `
          <div class="result-header">
            <div class="result-header-name">
              <h2>${nombreEsc}</h2>
              <p>Clave: <code>${clave}</code></p>
            </div>
          </div>
          <div class="estado-msg">
            Aun no tenemos un analisis de esta escuela.
          </div>
        `;
      }

      const totalSol   = parseInt(d.total_solicitudes ?? 0).toLocaleString('es-MX');
      const asignados  = parseInt(d.asignados ?? 0).toLocaleString('es-MX');
      const corteMin   = d.puntaje_corte_min ?? '—';
      const corteMax   = d.puntaje_corte_max ?? '—';
      const corteProm  = d.puntaje_corte_prom ?? '—';
      const promCert   = d.promedio_cert ?? '—';
      const hombres    = parseInt(d.hombres ?? 0).toLocaleString('es-MX');
      const mujeres    = parseInt(d.mujeres ?? 0).toLocaleString('es-MX');

      return `
        <div class="result-header">
          <div class="result-header-name">
            <h2>${nombreEsc}</h2>
            <p>Clave: <code>${clave}</code> &nbsp;·&nbsp; Datos del proceso COMIPEMS 2024 &nbsp;·&nbsp; Fuente: XABER A.C.</p>
          </div>
          <div style="text-align:right">
            <p class="corte-label">Puntaje de corte promedio</p>
            <p class="corte-big">${corteProm} <sub>pts</sub></p>
          </div>
        </div>

        <div class="stats-row mb-3">
          <div class="stat-card"><div class="stat-num">${corteMin}</div><div class="stat-label">Corte minimo</div></div>
          <div class="stat-card"><div class="stat-num">${corteProm}</div><div class="stat-label">Corte promedio</div></div>
          <div class="stat-card"><div class="stat-num">${corteMax}</div><div class="stat-label">Corte maximo</div></div>
          <div class="stat-card"><div class="stat-num">${totalSol}</div><div class="stat-label">Solicitudes</div></div>
          <div class="stat-card"><div class="stat-num">${asignados}</div><div class="stat-label">Asignados</div></div>
        </div>

        <div class="grid-2 mb-3">
          <div class="chart-area">
            <div class="chart-area-header">
              <h3>Distribucion por sexo (asignados)</h3>
            </div>
            <div class="canvas-wrap">
              <canvas id="chart-sexo" height="220"></canvas>
            </div>
          </div>

          <div class="chart-area">
            <div class="chart-area-header">
              <h3>Resumen del plantel</h3>
            </div>
            <div style="padding:1.5rem">
              <table class="data-table">
                <tbody>
                  <tr><td>Hombres solicitantes</td><td class="num">${hombres}</td></tr>
                  <tr><td>Mujeres solicitantes</td><td class="num">${mujeres}</td></tr>
                  <tr><td>Promedio certificado (asignados)</td><td class="num">${promCert}</td></tr>
                  <tr><td>Puntaje de corte min.</td><td class="num">${corteMin} pts</td></tr>
                  <tr><td>Puntaje de corte max.</td><td class="num">${corteMax} pts</td></tr>
                  <tr><td>Puntaje de corte prom.</td><td class="num" style="color:var(--bordo);font-weight:700">${corteProm} pts</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      `;
    }

    // ── Lógica de búsqueda ──────────────────────────────────
    async function buscarPlantel() {
      const clave = document.getElementById('inp-plantel').value.trim();
      if (!clave) {
        alert('Ingresa una clave de plantel primero.');
        return;
      }

      const div = document.getElementById('resultados');
      div.innerHTML = `<div class="estado-msg"><span class="spinner"></span> Consultando datos…</div>`;

      try {
        const base = '../backend';
        const resp = await fetch(`${base}/api/escuela.php?plantel=${encodeURIComponent(clave)}`);
        const json = await resp.json();

        if (json.status !== 'ok' || !json.datos) {
          div.innerHTML = `<div class="estado-msg">No se encontraron datos para la clave <strong>${clave}</strong>. Verifica que el plantel exista en la base de datos.</div>`;
          return;
        }

        const d = json.datos;
        div.innerHTML = plantillaResultado(clave, d);

        // Renderizar gráfica de sexo
        const canvas = document.getElementById('chart-sexo');
        if (canvas && window.Chart) {
          new Chart(canvas, {
            type: 'doughnut',
            data: {
              labels: ['Hombres', 'Mujeres'],
              datasets: [{
                data: [parseInt(d.hombres ?? 0), parseInt(d.mujeres ?? 0)],
                backgroundColor: ['#023047cc', '#fb8500cc'],
                borderColor:     ['#023047',   '#fb8500'],
                borderWidth: 2,
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: { position: 'bottom', labels: { font: { family: 'Sora' } } },
                tooltip: {
                  callbacks: {
                    label: ctx => {
                      const total = parseInt(d.hombres ?? 0) + parseInt(d.mujeres ?? 0);
                      const pct = total ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                      return ` ${ctx.label}: ${ctx.parsed.toLocaleString('es-MX')} (${pct}%)`;
                    }
                  }
                }
              }
            }
          });
        }

        // Mostrar FAB
        document.getElementById('btn-fab').style.display = 'flex';
        document.getElementById('btn-fab').onclick = () => {
          let opcs;
          try { opcs = JSON.parse(sessionStorage.getItem('comparar_opciones') || 'null'); } catch(e) { opcs = null; }
          if (!Array.isArray(opcs)) opcs = [null, null, null, null];
          const idx = opcs.indexOf(null);
          if (idx !== -1 && !opcs.includes(clave)) opcs[idx] = clave;
          sessionStorage.setItem('comparar_opciones', JSON.stringify(opcs));
          window.location.href = 'comparar.php';
        };

      } catch (err) {
        div.innerHTML = `<div class="estado-msg">Error de conexión con el servidor. Verifica que el backend esté activo.<br><small>${err.message}</small></div>`;
        console.error(err);
      }
    }

    // ── Autocomplete ────────────────────────────────────────
    const inpPlantel   = document.getElementById('inp-plantel');
    const autocomplete = document.getElementById('autocomplete-list');
    let debounceTimer;

    inpPlantel.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const q = inpPlantel.value.trim();
      if (q.length < 2) { autocomplete.style.display = 'none'; return; }

      debounceTimer = setTimeout(async () => {
        try {
          const resp = await fetch(`../backend/api/escuela.php?q=${encodeURIComponent(q)}`);
          const json = await resp.json();
          if (json.status !== 'ok' || !json.datos?.length) {
            autocomplete.style.display = 'none'; return;
          }
          autocomplete.innerHTML = json.datos.map(item => `
            <div style="padding:.5rem .8rem; cursor:pointer; font-size:.84rem;
                        border-bottom:1px solid var(--borde); font-family:Sora,sans-serif"
                 onmouseover="this.style.background='#f0f8ff'"
                 onmouseout="this.style.background=''"
                 onclick="document.getElementById('inp-plantel').value='${item.clave_plantel}';
                          document.getElementById('autocomplete-list').style.display='none'">
              <strong>${item.clave_plantel}</strong>
              <span style="color:#4a6070;margin-left:.5rem">${item.nombre ? item.nombre.substring(0,50) + ' · ' : ''}${item.solicitudes.toLocaleString()} solicitudes</span>
            </div>
          `).join('');
          autocomplete.style.display = 'block';
        } catch (e) { autocomplete.style.display = 'none'; }
      }, 350);
    });

    document.addEventListener('click', e => {
      if (!autocomplete.contains(e.target) && e.target !== inpPlantel)
        autocomplete.style.display = 'none';
    });

    // ── Botón buscar y Enter ────────────────────────────────
    document.getElementById('btn-buscar').addEventListener('click', buscarPlantel);
    inpPlantel.addEventListener('keydown', e => { if (e.key === 'Enter') buscarPlantel(); });

    // ── Auto-buscar si viene por URL ?plantel=XXX ───────────
    const params = new URLSearchParams(window.location.search);
    if (params.get('plantel')) {
      inpPlantel.value = params.get('plantel');
      buscarPlantel();
    }
  </script>
</body>
</html>
