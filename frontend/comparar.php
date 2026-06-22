<?php require_once __DIR__ . '/../backend/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS — Comparador</title>
  <link rel="stylesheet" href="css/estilos.css?v=2">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
    .option-slot.s4 { border-color: #1E64C8;       background: rgba(30,100,200,.04); }
    .option-slot.empty { align-items: center; justify-content: center; color: var(--texto-2); font-size:.85rem; cursor:pointer; }
    .option-slot.empty:hover { border-color: var(--bordo); color: var(--bordo); }
    .slot-num  { font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em; }
    .s1 .slot-num { color: var(--bordo); }
    .s2 .slot-num { color: #7A5800; }
    .s3 .slot-num { color: var(--acento); }
    .s4 .slot-num { color: #1E64C8; }
    .slot-name   { font-family:var(--font-display); font-size:.9rem; font-weight:600; color:var(--texto); }
    .slot-inst   { font-size:.75rem; color:var(--texto-2); }
    .slot-corte  { font-family:var(--font-display); font-size:1.5rem; font-weight:700; }
    .s1 .slot-corte { color: var(--bordo); }
    .s2 .slot-corte { color: #7A5800; }
    .s3 .slot-corte { color: var(--acento); }
    .s4 .slot-corte { color: #1E64C8; }
    .slot-remove { align-self:flex-end; font-size:.8rem; cursor:pointer; color:var(--texto-2); background:none; border:none; font-family:var(--font-body); }
    .slot-remove:hover { color: var(--acento); }
    .legend-bar { display:flex; gap:1.5rem; flex-wrap:wrap; padding:.8rem 1.5rem; border-bottom:1px solid var(--borde); }
    .legend-item-compare { display:flex; align-items:center; gap:.5rem; font-size:.82rem; }
    .legend-color { width:18px; height:4px; border-radius:2px; }
    .spinner { display:inline-block; width:16px; height:16px; border:2px solid var(--borde); border-top-color:var(--bordo); border-radius:50%; animation:spin .7s linear infinite; vertical-align:middle; margin-right:.4rem; }
    @keyframes spin { to { transform:rotate(360deg); } }
    .modal-overlay {
      display:none; position:fixed; inset:0; background:rgba(0,0,0,.4);
      z-index:500; align-items:center; justify-content:center;
    }
    .modal-overlay.open { display:flex; }
    .modal {
      background:#fff; border-radius:var(--radio-lg); padding:1.5rem;
      width:340px; box-shadow:var(--sombra-lg); font-family:var(--font-body);
    }
    .modal h3 { font-family:var(--font-display); color:var(--bordo); margin-bottom:1rem; }
    .canvas-wrap { padding:1.5rem; position:relative; min-height:260px; }
  </style>
</head>
<body class="page-wrapper">

  <?php require 'includes/navbar.php'; ?>

  <!-- Page header -->
  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Módulo 2</p>
      <h1>Comparador de Opciones</h1>
      <p>Compara hasta 4 opciones educativas con datos reales de la BD.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">

      <!-- Slots -->
      <h2 class="section-title" style="font-size:1.15rem;margin-bottom:1rem">
        <span>Paso 1 — Selecciona las opciones</span>
        Opciones a comparar (máx. 4)
      </h2>
      <div class="compare-grid" id="slots-grid"></div>

      <!-- Botón actualizar -->
      <div class="filters-panel mb-3">
        <button id="btn-comparar" class="btn btn-bordo btn-sm">📊 Comparar opciones</button>
        <span id="msg-comparar" style="font-size:.82rem;color:var(--texto-2)"></span>
      </div>

      <!-- Gráfica comparativa -->
      <div class="chart-area mb-3" id="area-grafica" style="display:none">
        <div class="chart-area-header">
          <h3>📊 Estadísticas comparadas — datos 2024</h3>
        </div>
        <div id="leyenda-comparar" class="legend-bar"></div>
        <div class="canvas-wrap">
          <canvas id="chart-comparar" height="260"></canvas>
        </div>
      </div>

      <!-- Tabla comparativa -->
      <div class="data-table-wrap" id="area-tabla" style="display:none">
        <div style="padding:1.2rem 1.5rem;border-bottom:1px solid var(--borde)">
          <h3 style="font-family:var(--font-display);color:var(--bordo)">Resumen comparativo</h3>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Plantel</th>
              <th>Solicitudes</th>
              <th>Asignados</th>
              <th>Corte mín.</th>
              <th>Corte máx.</th>
              <th>Corte prom.</th>
              <th>% Mat.</th>
              <th>% Esp.</th>
            </tr>
          </thead>
          <tbody id="tabla-cuerpo"></tbody>
        </table>
      </div>

    </div>
  </section>

  <!-- Modal para agregar clave -->
  <div class="modal-overlay" id="modal-overlay">
    <div class="modal">
      <h3>Agregar opción</h3>
      <div class="filter-group" style="margin-bottom:1rem">
        <label>Clave de plantel</label>
        <input type="text" id="modal-inp" class="filter-select"
               placeholder="Ej. U70001, I10009…" style="width:100%">
      </div>
      <div style="display:flex;gap:.6rem;justify-content:flex-end">
        <button class="btn btn-sm" onclick="cerrarModal()" style="background:var(--fondo);border:1.5px solid var(--borde)">Cancelar</button>
        <button class="btn btn-bordo btn-sm" onclick="agregarDesdeModal()">Agregar</button>
      </div>
    </div>
  </div>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>

  <script src="js/graficas.js"></script>
  <script>
    // ── Colores de los 4 slots ──────────────────────────────
    const SLOT_COLORES  = ['#023047', '#ffb703', '#fb8500', '#1E64C8'];
    const SLOT_CLASES   = ['s1', 's2', 's3', 's4'];
    const SLOT_NOMBRES  = ['Opción 1', 'Opción 2', 'Opción 3', 'Opción 4'];
    let opciones = (() => {
      try {
        const saved = sessionStorage.getItem('comparar_opciones');
        if (saved) {
          const parsed = JSON.parse(saved);
          if (Array.isArray(parsed) && parsed.length === 4) return parsed;
        }
      } catch (e) {}
      return [null, null, null, null];
    })();

    function guardarOpciones() {
      sessionStorage.setItem('comparar_opciones', JSON.stringify(opciones));
    }

    let slotNombres   = [null, null, null, null];
    let slotActivo    = -1;

    // ── Render de los 4 slots ───────────────────────────────
    function renderSlots() {
      const grid = document.getElementById('slots-grid');
      grid.innerHTML = opciones.map((clave, i) => {
        if (!clave) return `
          <div class="option-slot empty" onclick="abrirModal(${i})">
            <span style="font-size:1.5rem">＋</span>
            <span>${SLOT_NOMBRES[i]}</span>
          </div>`;
        const nombre = slotNombres[i] || clave;
        return `
          <div class="option-slot filled ${SLOT_CLASES[i]}">
            <span class="slot-num">${SLOT_NOMBRES[i]}</span>
            <span class="slot-name">${nombre}</span>
            <span class="slot-inst" id="slot-inst-${i}">cargando…</span>
            <span class="slot-corte" id="slot-corte-${i}">…</span>
            <button class="slot-remove" onclick="quitarOpcion(${i})">✕ Quitar</button>
          </div>`;
      }).join('');
      opciones.forEach((clave, i) => { if (clave) cargarMiniDato(clave, i); });
    }

    async function cargarMiniDato(clave, idx) {
      try {
        const resp = await fetch(`../backend/api/escuela.php?plantel=${encodeURIComponent(clave)}`);
        const json = await resp.json();
        if (json.status === 'ok') {
          const d = json.datos;
          if (d.nombre) { slotNombres[idx] = d.nombre; document.querySelectorAll('.slot-name')[idx].textContent = d.nombre; }
          document.getElementById(`slot-corte-${idx}`).textContent =
            (d.puntaje_corte_prom ?? '—') + ' pts';
          document.getElementById(`slot-inst-${idx}`).textContent =
            d.tiene_datos
              ? `Solicitudes: ${parseInt(d.total_solicitudes ?? 0).toLocaleString('es-MX')}`
              : 'Sin datos disponibles';
        }
      } catch (e) {}
    }

    // ── Modal ───────────────────────────────────────────────
    function abrirModal(idx) {
      slotActivo = idx;
      document.getElementById('modal-inp').value = '';
      document.getElementById('modal-overlay').classList.add('open');
      setTimeout(() => document.getElementById('modal-inp').focus(), 100);
    }
    function cerrarModal() {
      document.getElementById('modal-overlay').classList.remove('open');
    }
    function agregarDesdeModal() {
      const clave = document.getElementById('modal-inp').value.trim();
      if (!clave) { alert('Escribe una clave de plantel.'); return; }
      if (opciones.includes(clave)) { alert('Esa clave ya está en la comparación.'); return; }
      opciones[slotActivo] = clave;
      guardarOpciones();
      cerrarModal();
      renderSlots();
    }
    document.getElementById('modal-inp').addEventListener('keydown', e => {
      if (e.key === 'Enter') agregarDesdeModal();
    });
    document.getElementById('modal-overlay').addEventListener('click', e => {
      if (e.target === e.currentTarget) cerrarModal();
    });

    function quitarOpcion(idx) {
      opciones[idx] = null;
      guardarOpciones();
      renderSlots();
      document.getElementById('area-grafica').style.display = 'none';
      document.getElementById('area-tabla').style.display   = 'none';
    }

    // ── Comparar: llamar a la API y renderizar ───────────────
    document.getElementById('btn-comparar').addEventListener('click', async () => {
      const claves = opciones.filter(Boolean);
      if (claves.length < 2) {
        document.getElementById('msg-comparar').textContent = '⚠️ Agrega al menos 2 opciones.';
        return;
      }
      document.getElementById('msg-comparar').innerHTML = '<span class="spinner"></span> Consultando…';

      try {
        const qs    = claves.map(c => `claves[]=${encodeURIComponent(c)}`).join('&');
        const resp  = await fetch(`../backend/api/comparar.php?${qs}`);
        const json  = await resp.json();

        if (json.status !== 'ok') {
          document.getElementById('msg-comparar').textContent = '❌ Error al obtener datos.';
          return;
        }

        document.getElementById('msg-comparar').textContent = '';
        renderGraficaComparacion(json.datos, claves);
        renderTablaComparacion(json.datos);

      } catch (err) {
        document.getElementById('msg-comparar').textContent = '❌ Error de conexión.';
        console.error(err);
      }
    });

    function nombreSlot(i) {
      const opc = opciones.filter(Boolean);
      const idx = opc.indexOf(opciones[i]);
      return slotNombres[i] || opciones[i] || ('Opción ' + (i + 1));
    }

    // ── Gráfica de barras agrupadas ─────────────────────────
    function renderGraficaComparacion(datos, claves) {
      const areaGrafica = document.getElementById('area-grafica');
      areaGrafica.style.display = 'block';

      const indices = claves.map(c => opciones.indexOf(c));
      document.getElementById('leyenda-comparar').innerHTML = datos.map((d, i) => `
        <div class="legend-item-compare">
          <div class="legend-color" style="background:${SLOT_COLORES[claves.indexOf(d.clave_plantel ?? claves[i])]}"></div>
          ${d.clave_plantel ?? claves[i]}
        </div>`).join('');

      const labels   = ['Total solicitudes', 'Asignados', 'Corte min.', 'Corte prom.', 'Corte max.'];
      const datasets = datos.map((d, i) => ({
        label: (i < indices.length ? nombreSlot(indices[i]) : (d.clave_plantel ?? claves[i])),
        data: [
          parseInt(d.total_solicitudes ?? 0),
          parseInt(d.asignados ?? 0),
          parseFloat(d.puntaje_min ?? 0),
          parseFloat(d.puntaje_prom ?? 0),
          parseFloat(d.puntaje_max ?? 0),
        ],
        backgroundColor: SLOT_COLORES[i] + 'bb',
        borderColor:     SLOT_COLORES[i],
        borderWidth: 1.5,
        borderRadius: 4,
      }));

      const canvas = document.getElementById('chart-comparar');
      if (window._chartComp) window._chartComp.destroy();
      window._chartComp = new Chart(canvas, {
        type: 'bar',
        data: { labels, datasets },
        options: {
          responsive: true,
          plugins: {
            legend: { labels: { font: { family: 'Sora', size: 12 } } },
            tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('es-MX')}` } }
          },
          scales: {
            y: { beginAtZero: true, ticks: { font: { family: 'Sora', size: 11 } }, grid: { color: '#e2eaf0' } },
            x: { ticks: { font: { family: 'Sora', size: 11 } }, grid: { display: false } }
          }
        }
      });
    }

    // ── Tabla comparativa ───────────────────────────────────
    function renderTablaComparacion(datos) {
      document.getElementById('area-tabla').style.display = 'block';
      const indices = opciones.map((o, i) => ({o, i})).filter(x => x.o).map(x => x.i);
      document.getElementById('tabla-cuerpo').innerHTML = datos.map((d, i) => {
        const idx = i < indices.length ? indices[i] : -1;
        const nombre = idx >= 0 ? nombreSlot(idx) : (d.clave_plantel ?? '—');
        return `
        <tr>
          <td><span style="width:10px;height:10px;border-radius:50%;background:${SLOT_COLORES[i]};display:inline-block;margin-right:.4rem"></span>
              <a href="escuela.php?plantel=${encodeURIComponent(d.clave_plantel ?? '')}" style="color:var(--bordo);font-weight:600">
                ${nombre}
              </a></td>
          <td class="num">${parseInt(d.total_solicitudes ?? 0).toLocaleString('es-MX')}</td>
          <td class="num">${parseInt(d.asignados ?? 0).toLocaleString('es-MX')}</td>
          <td class="num">${d.puntaje_min ?? '—'}</td>
          <td class="num">${d.puntaje_max ?? '—'}</td>
          <td class="num" style="font-weight:700;color:var(--bordo)">${d.puntaje_prom ?? '—'}</td>
          <td class="num">${d.pct_mat ?? '—'}%</td>
          <td class="num">${d.pct_esp ?? '—'}%</td>
        </tr>`;}).join('');
    }

    // ── Al cargar: revisar si escuela.php redirigio con una clave ──
    const clavePendiente = sessionStorage.getItem('comparar_agregar');
    if (clavePendiente) {
      const idx = opciones.indexOf(null);
      if (idx !== -1 && !opciones.includes(clavePendiente)) {
        opciones[idx] = clavePendiente;
        guardarOpciones();
      }
      sessionStorage.removeItem('comparar_agregar');
    }
    renderSlots();
  </script>
</body>
</html>
