<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS — Mapa de Planteles</title>
  <link rel="stylesheet" href="css/estilos.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    .map-layout {
      display: grid;
      grid-template-columns: 300px 1fr;
      gap: 1.5rem;
      align-items: start;
    }
    #mapa-leaflet {
      height: 520px;
      border-radius: var(--radio-lg);
      border: 1px solid var(--borde);
      z-index: 1;
    }
    .result-list { list-style: none; max-height: 400px; overflow-y: auto; }
    .result-item {
      display: flex; align-items: center; gap: .6rem;
      padding: .5rem .3rem; border-bottom: 1px solid var(--borde);
      font-size: .8rem; cursor: pointer; transition: var(--trans);
    }
    .result-item:hover { background: rgba(2,48,71,.04); border-radius: var(--radio); }
    .result-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .result-item-info { flex: 1; min-width: 0; }
    .result-item-info strong { display: block; color: var(--texto); font-weight: 600; font-size: .82rem; }
    .result-item-info small { color: var(--texto-2); font-size: .7rem; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .result-item-corte { font-family: var(--font-display); font-weight: 700; color: var(--bordo); font-size: .85rem; flex-shrink: 0; }
    .spinner { display:inline-block; width:16px; height:16px; border:2px solid var(--borde);
               border-top-color:var(--bordo); border-radius:50%; animation:spin .7s linear infinite;
               vertical-align:middle; margin-right:.4rem; }
    @keyframes spin { to { transform:rotate(360deg); } }
    .filtros-inst {
      display: flex; flex-wrap: wrap; gap: .35rem;
      padding: .6rem 0; border-bottom: 1px solid var(--borde); margin-bottom: .6rem;
    }
    .filtro-btn {
      font-size: .7rem; padding: .25rem .55rem; border-radius: 20px;
      border: 1.5px solid var(--borde); background: var(--fondo);
      cursor: pointer; font-family: var(--font-body); font-weight: 500;
      transition: var(--trans); color: var(--texto-2);
    }
    .filtro-btn:hover { border-color: var(--bordo); color: var(--bordo); }
    .filtro-btn.activo { background: var(--bordo); color: #fff; border-color: var(--bordo); }
    .mapa-footer {
      display: flex; justify-content: space-between; align-items: center;
      margin-top: .6rem; font-size: .75rem; color: var(--texto-2);
    }
    .mapa-legend-inline {
      display: flex; flex-wrap: wrap; gap: .6rem; align-items: center;
    }
    .mapa-legend-inline .legend-item {
      display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem;
    }
    .mapa-legend-inline .legend-dot { width: 8px; height: 8px; border-radius: 50%; }
  </style>
</head>
<body class="page-wrapper">

  <?php require 'includes/navbar.php'; ?>

  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Módulo 3</p>
      <h1>Mapa de Planteles</h1>
      <p>Planteles ECOEMS en la ZMCDMX con datos reales.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">
      <div class="map-layout">

        <!-- Sidebar -->
        <div>
          <div class="sidebar-card mb-2">
            <h4>🔍 Buscar plantel</h4>
            <div class="filter-group mb-2">
              <input type="text" id="inp-buscar-mapa" class="filter-select"
                     placeholder="Clave o nombre…" style="width:100%">
            </div>
            <button id="btn-buscar-mapa" class="btn btn-bordo btn-sm" style="width:100%">
              Buscar y centrar
            </button>
          </div>

          <div class="sidebar-card">
            <h4 id="lista-titulo">📍 Planteles en el mapa</h4>
            <div class="filtros-inst" id="filtros-inst"></div>
            <ul class="result-list" id="lista-planteles">
              <li style="padding:.8rem;color:var(--texto-2);font-size:.82rem">
                <span class="spinner"></span> Cargando…
              </li>
            </ul>
          </div>
        </div>

        <!-- Mapa -->
        <div>
          <div id="mapa-leaflet"></div>
          <div class="mapa-footer">
            <div class="mapa-legend-inline">
              <span style="font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:var(--texto-2)">Instituciones:</span>
              <span class="legend-item"><span class="legend-dot" style="background:#023047"></span>COLBACH</span>
              <span class="legend-item"><span class="legend-dot" style="background:#fb8500"></span>UNAM</span>
              <span class="legend-item"><span class="legend-dot" style="background:#ffb703"></span>IPN</span>
              <span class="legend-item"><span class="legend-dot" style="background:#1E64C8"></span>DGETI</span>
              <span class="legend-item"><span class="legend-dot" style="background:#2E7D32"></span>CONALEP</span>
              <span class="legend-item"><span class="legend-dot" style="background:#6B3FA0"></span>Otras</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="js/mapa.js"></script>
  <script>
    inicializarMapa('mapa-leaflet');

    let todosPlanteles = [];

    async function cargarMapa(q) {
      const lista = document.getElementById('lista-planteles');
      lista.innerHTML = '<li style="padding:.8rem;color:var(--texto-2);font-size:.82rem"><span class="spinner"></span> Cargando…</li>';

      try {
        if (todosPlanteles.length === 0) {
          const resp = await fetch('../backend/api/planteles.php');
          const json = await resp.json();
          if (json.status !== 'ok') throw new Error('Error al cargar planteles');
          todosPlanteles = json.datos.filter(p => p.latitud && p.longitud);
        }

        const filtro = q ? todosPlanteles.filter(p =>
          p.clave.toLowerCase().includes(q.toLowerCase()) ||
          (p.nombre || '').toLowerCase().includes(q.toLowerCase())
        ) : todosPlanteles;

        if (filtro.length === 0) {
          lista.innerHTML = '<li style="padding:.8rem;color:var(--texto-2);font-size:.82rem">Sin resultados.</li>';
          return;
        }

        document.getElementById('lista-titulo').textContent = `📍 ${filtro.length} planteles`;
        marcadoresLayer.clearLayers();
        lista.innerHTML = '';

        for (const p of filtro) {
          const coords = [parseFloat(p.latitud), parseFloat(p.longitud)];
          const color = colorPorInst(p.clave);

          const li = document.createElement('li');
          li.className = 'result-item';
          li.innerHTML = `
            <span class="result-dot" style="background:${color}"></span>
            <div class="result-item-info">
              <strong>${p.nombre || p.clave}</strong>
              <small>${p.clave} · ${p.subsistema || '—'}${p.municipio ? ' · ' + p.municipio : ''}</small>
            </div>
            <span class="result-item-corte" id="corte-${p.clave}">…</span>
          `;
          lista.appendChild(li);

          // Fetch stats
          let puntaje = '?';
          try {
            const sr = await fetch(`../backend/api/escuela.php?plantel=${encodeURIComponent(p.clave)}`);
            const sj = await sr.json();
            if (sj.status === 'ok' && sj.datos.total_solicitudes) {
              const d = sj.datos;
              puntaje = d.puntaje_corte_prom ? Math.round(parseFloat(d.puntaje_corte_prom)) : '?';
              const el = document.getElementById(`corte-${p.clave}`);
              if (el) el.textContent = puntaje + ' pts';
            }
          } catch (e) {}

          const marker = L.marker(coords, { icon: crearIcono(color, puntaje) });
          marker.bindPopup(`
            <div style="font-family:Sora,sans-serif;min-width:200px">
              <strong style="color:#023047;font-size:.95rem">${p.nombre || p.clave}</strong>
              <div style="font-size:.78rem;color:var(--texto-2);margin:.3rem 0">
                ${p.clave} · ${p.subsistema || '—'}
              </div>
              <hr style="margin:.5rem 0;border-color:#e2eaf0">
              <table style="width:100%;font-size:.82rem">
                <tr><td style="color:#4a6070">Solicitudes</td>
                    <td style="text-align:right;font-weight:700">${puntaje !== '?' ? parseInt(sj?.datos?.total_solicitudes ?? 0).toLocaleString('es-MX') : '—'}</td></tr>
                <tr><td style="color:#4a6070">Corte prom.</td>
                    <td style="text-align:right;font-weight:700;color:#023047">${puntaje} pts</td></tr>
              </table>
              <a href="escuela.php?plantel=${encodeURIComponent(p.clave)}"
                 style="display:block;margin-top:.6rem;background:#023047;color:#fff;
                        text-align:center;padding:.35rem;border-radius:4px;font-size:.78rem;
                        text-decoration:none">Ver detalle →</a>
            </div>
          `);
          marcadoresLayer.addLayer(marker);

          li.addEventListener('click', () => {
            mapaLeaflet.setView(coords, 14);
            marker.openPopup();
          });
        }

        renderFiltros(filtro);

      } catch (err) {
        lista.innerHTML = `<li style="padding:.8rem;color:#c00;font-size:.82rem">❌ Error: ${err.message}</li>`;
        console.error(err);
      }
    }

    function renderFiltros(activos) {
      const insts = {};
      todosPlanteles.forEach(p => { const s = p.subsistema || 'Otras'; insts[s] = (insts[s] || 0) + 1; });
      const div = document.getElementById('filtros-inst');
      div.innerHTML = '<button class="filtro-btn activo" data-inst="">Todas</button>' +
        Object.entries(insts).sort().map(([k]) =>
          `<button class="filtro-btn" data-inst="${k}">${k}</button>`
        ).join('');

      div.querySelectorAll('.filtro-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          div.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
          btn.classList.add('activo');
        });
      });
    }

    document.getElementById('btn-buscar-mapa').addEventListener('click', () => {
      cargarMapa(document.getElementById('inp-buscar-mapa').value.trim());
    });
    document.getElementById('inp-buscar-mapa').addEventListener('keydown', e => {
      if (e.key === 'Enter') cargarMapa(document.getElementById('inp-buscar-mapa').value.trim());
    });

    cargarMapa('');
  </script>
</body>
</html>
