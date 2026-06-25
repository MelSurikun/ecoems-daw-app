<?php require_once __DIR__ . '/../backend/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS, Planteles</title>
  <link rel="stylesheet" href="css/estilos.css?v=3">
  <style>
    .search-bar {
      display: flex;
      gap: .75rem;
      align-items: flex-end;
      flex-wrap: wrap;
    }
    .search-bar .filter-group { flex: 1; min-width: 220px; }
    .plantel-card {
      background: var(--fondo-card);
      border-radius: var(--radio-lg);
      padding: 1rem 1.4rem;
      box-shadow: var(--sombra);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      transition: var(--trans);
      border-left: 4px solid var(--bordo);
    }
    .plantel-card:hover { transform: translateY(-2px); box-shadow: var(--sombra-lg); }
    .plantel-card .info { flex: 1; min-width: 0; }
    .plantel-card .clave { font-family: var(--font-display); font-size: 1rem; font-weight: 700; color: var(--bordo); }
    .plantel-card .nombre { font-size: .88rem; color: var(--texto); margin-top: .1rem; }
    .plantel-card .meta { font-size: .76rem; color: var(--texto-2); margin-top: .3rem; display: flex; gap: .6rem; flex-wrap: wrap; }
    .plantel-card .meta span { display: inline-flex; align-items: center; gap: .25rem; }
    .plantel-card .direccion { font-size: .75rem; color: var(--texto-2); margin-top: .2rem; opacity: .8; }
    .plantel-card .accion { flex-shrink: 0; }
    .plantel-card .inst-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: .3rem; flex-shrink: 0; }
    .estado-msg { padding: 2rem; text-align: center; color: var(--texto-2); font-size: .9rem; }
    .spinner { display:inline-block; width:16px; height:16px; border:2px solid var(--borde);
               border-top-color:var(--bordo); border-radius:50%; animation:spin .7s linear infinite; }
    @keyframes spin { to { transform:rotate(360deg); } }
    .filtros-inst {
      display: flex; flex-wrap: wrap; gap: .35rem;
      padding: .6rem 0; margin-bottom: .6rem;
    }
    .filtro-btn {
      font-size: .7rem; padding: .25rem .55rem; border-radius: 20px;
      border: 1.5px solid var(--borde); background: var(--fondo);
      cursor: pointer; font-family: var(--font-body); font-weight: 500;
      transition: var(--trans); color: var(--texto-2);
    }
    .filtro-btn:hover { border-color: var(--bordo); color: var(--bordo); }
    .filtro-btn.activo { background: var(--bordo); color: #fff; border-color: var(--bordo); }
    .result-count { font-size: .82rem; color: var(--texto-2); margin-bottom: .8rem; }
  </style>
</head>
<body class="page-wrapper">

  <?php require 'includes/navbar.php'; ?>

  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Módulo 5</p>
      <h1>Catálogo de Planteles</h1>
      <p>Directorio de <?= htmlspecialchars($_GET['q'] ?? '') ? "«{$_GET['q']}»" : 'opciones educativas COMIPEMS 2025' ?>.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">

      <div class="filters-panel mb-3">
        <div class="search-bar">
          <div class="filter-group" style="position:relative">
            <label>Buscar por clave o nombre</label>
            <input type="text" id="inp-buscar" class="filter-select"
                   placeholder="Ej. B00001, CETIS, Prepa…" style="width:100%">
            <div id="autocomplete" style="position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid var(--borde);border-radius:var(--radio);box-shadow:var(--sombra);z-index:200;max-height:200px;overflow-y:auto;display:none"></div>
          </div>
          <button id="btn-buscar" class="btn btn-bordo btn-sm">Buscar</button>
          <button id="btn-todos" class="btn btn-sm" style="background:var(--fondo);border:1.5px solid var(--borde)">Todos</button>
        </div>
      </div>

      <div class="filtros-inst" id="filtros-inst"></div>

      <div id="resultados">
        <div class="estado-msg"><span class="spinner"></span> Cargando planteles…</div>
      </div>

    </div>
  </section>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>

  <script>
    const INST_COLORS = {
      'CONALEP': '#2E7D32', 'DGETI': '#1E64C8', 'IPN': '#ffb703',
      'UNAM': '#fb8500', 'COLBACH': '#023047', 'IEMS': '#e63946',
      'DGB': '#6B3FA0', 'CBT': '#457b9d', 'CECyTEM': '#457b9d',
      'COBAEM': '#457b9d', 'Telebachillerato': '#a67c52',
    };

    let todosPlanteles = [];
    let filtroActual = '';

    async function cargarPlanteles(q) {
      const div = document.getElementById('resultados');
      div.innerHTML = `<div class="estado-msg"><span class="spinner"></span> Cargando…</div>`;

      try {
        if (todosPlanteles.length === 0) {
          const resp = await fetch('../backend/api/planteles.php');
          const json = await resp.json();
          if (json.status !== 'ok') throw new Error('Error al cargar');
          todosPlanteles = json.datos;
        }

        let filtrados = todosPlanteles;
        if (q) {
          const lq = q.toLowerCase();
          filtrados = todosPlanteles.filter(p =>
            (p.clave || '').toLowerCase().includes(lq) ||
            (p.nombre || '').toLowerCase().includes(lq) ||
            (p.subsistema || '').toLowerCase().includes(lq)
          );
        }
        if (filtroActual) {
          filtrados = filtrados.filter(p => p.subsistema === filtroActual);
        }

        if (filtrados.length === 0) {
          div.innerHTML = `<div class="estado-msg">${q ? 'Sin resultados para «' + q + '»' : 'No hay planteles registrados.'}</div>`;
          return;
        }

        div.innerHTML = `<div class="result-count">${filtrados.length} plantel${filtrados.length !== 1 ? 'es' : ''}</div>
          <div style="display:grid;gap:.65rem">` + filtrados.map(p => {
          const color = INST_COLORS[p.subsistema] || '#6B3FA0';
          return `<div class="plantel-card">
            <div class="info">
              <div class="clave"><span class="inst-dot" style="background:${color}"></span>${p.clave}</div>
              <div class="nombre">${(p.nombre || '—').substring(0, 120)}</div>
              <div class="meta">
                <span>${p.subsistema || '—'}</span>
                <span>${[p.municipio, p.estado].filter(Boolean).join(', ') || '—'}</span>
              </div>
              ${p.direccion ? `<div class="direccion">${p.direccion.substring(0, 150)}</div>` : ''}
            </div>
            <div class="accion">
              <a href="escuela.php?plantel=${encodeURIComponent(p.clave)}" class="btn btn-bordo btn-sm" style="font-size:.75rem;padding:.35rem .7rem">Ver stats →</a>
            </div>
          </div>`;
        }).join('') + '</div>';

        renderFiltros(filtrados);

      } catch (err) {
        div.innerHTML = `<div class="estado-msg">Error de conexión.<br><small>${err.message}</small></div>`;
        console.error(err);
      }
    }

    function renderFiltros(activos) {
      const insts = {};
      todosPlanteles.forEach(p => {
        const s = p.subsistema || 'Otras';
        insts[s] = (insts[s] || 0) + 1;
      });
      const div = document.getElementById('filtros-inst');
      const entries = Object.entries(insts).sort((a, b) => b[1] - a[1]);
      div.innerHTML = '<button class="filtro-btn' + (!filtroActual ? ' activo' : '') + '" data-inst="">Todas (' + todosPlanteles.length + ')</button>' +
        entries.map(([k, n]) =>
          `<button class="filtro-btn${filtroActual === k ? ' activo' : ''}" data-inst="${k}">${k} (${n})</button>`
        ).join('');

      div.querySelectorAll('.filtro-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          filtroActual = btn.dataset.inst;
          div.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
          btn.classList.add('activo');
          cargarPlanteles(document.getElementById('inp-buscar').value.trim());
        });
      });
    }

    const inp = document.getElementById('inp-buscar');
    const ac = document.getElementById('autocomplete');
    document.getElementById('btn-buscar').addEventListener('click', () => cargarPlanteles(inp.value.trim()));
    inp.addEventListener('keydown', e => { if (e.key === 'Enter') cargarPlanteles(inp.value.trim()); });
    document.getElementById('btn-todos').addEventListener('click', () => { inp.value = ''; filtroActual = ''; cargarPlanteles(''); });

    let db;
    inp.addEventListener('input', () => {
      clearTimeout(db);
      const q = inp.value.trim();
      if (q.length < 2) { ac.style.display = 'none'; return; }
      db = setTimeout(async () => {
        try {
          const r = await fetch('../backend/api/planteles.php?q=' + encodeURIComponent(q));
          const j = await r.json();
          if (j.status !== 'ok' || !j.datos?.length) { ac.style.display = 'none'; return; }
          ac.innerHTML = j.datos.slice(0, 10).map(i =>
            `<div style="padding:.45rem .8rem;cursor:pointer;font-size:.82rem;border-bottom:1px solid var(--borde)"
                  onmouseover="this.style.background='#f0f8ff'" onmouseout="this.style.background=''"
                  onclick="inp.value='${i.clave}';ac.style.display='none';cargarPlanteles('${i.clave}')">
              <strong>${i.clave}</strong> <span style="color:var(--texto-2);margin-left:.4rem">${(i.nombre || '').substring(0, 50)}</span>
            </div>`
          ).join('');
          ac.style.display = 'block';
        } catch (e) { ac.style.display = 'none'; }
      }, 300);
    });
    document.addEventListener('click', e => { if (!ac.contains(e.target) && e.target !== inp) ac.style.display = 'none'; });

    const params = new URLSearchParams(window.location.search);
    if (params.get('q')) {
      inp.value = params.get('q');
    }
    cargarPlanteles(inp.value.trim());
  </script>
</body>
</html>
