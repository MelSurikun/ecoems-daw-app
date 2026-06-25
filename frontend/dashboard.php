<?php
require_once __DIR__ . '/../backend/auth.php';
$usuario = requiereSesionPagina('login.php?next=dashboard.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS, Mi panel</title>
  <link rel="stylesheet" href="css/estilos.css?v=3">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    .estado-msg { padding: 2rem; text-align: center; color: var(--texto-2); font-size: .9rem; }
    .chart-box { background: var(--fondo-card); border-radius: var(--radio-lg); box-shadow: var(--sombra);
      padding: 1.5rem; height: 320px; }
    .chart-box h3 { font-family: var(--font-display); color: var(--bordo); font-size: 1rem; margin-bottom: 1rem; }

    .meta-card { background: var(--fondo-card); border-radius: var(--radio-lg); box-shadow: var(--sombra);
      padding: 1.5rem; margin-bottom: 1.5rem; }
    .meta-card h3 { font-family: var(--font-display); color: var(--bordo); font-size: 1rem; margin-bottom: 1rem; }
    .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    @media (max-width: 720px) { .meta-grid { grid-template-columns: 1fr; } }
    .opcion-buscar { position: relative; margin-bottom: .8rem; }
    .opcion-buscar input {
      width: 100%; padding: .6rem .9rem; border-radius: var(--radio);
      border: 1.5px solid var(--borde); background: #fff; color: var(--texto);
      font-family: var(--font-body); font-size: .9rem; outline: none; transition: var(--trans);
    }
    .opcion-buscar input::placeholder { color: var(--texto-2); }
    .opcion-buscar input:focus { border-color: var(--bordo); }
    .opcion-autocomplete { position: absolute; top: 100%; left: 0; right: 0; background: #fff;
      border: 1px solid var(--borde); border-radius: var(--radio); box-shadow: var(--sombra);
      z-index: 50; max-height: 200px; overflow-y: auto; display: none; }
    .opcion-autocomplete div { padding: .5rem .8rem; cursor: pointer; font-size: .82rem;
      border-bottom: 1px solid var(--borde); color: #023047; }
    .opcion-autocomplete div:hover { background: #f0f8ff; }
    .opcion-item { display: flex; align-items: center; gap: .6rem; padding: .5rem .3rem;
      border-bottom: 1px solid var(--borde); font-size: .82rem; }
    .opcion-item-info { flex: 1; min-width: 0; }
    .opcion-item-info strong { display: block; color: var(--texto); font-size: .85rem; }
    .opcion-item-info small { color: var(--texto-2); font-size: .72rem; }
    .opcion-corte { font-family: var(--font-display); font-weight: 700; color: var(--bordo); font-size: .82rem; }
    .opcion-quitar { background: none; border: none; color: #c0392b; cursor: pointer; font-size: .78rem; padding: .2rem .4rem; }
    .opcion-orden { display: flex; flex-direction: column; }
    .opcion-orden button { background: none; border: none; color: var(--texto-2); cursor: pointer; font-size: .7rem; line-height: 1; padding: .15rem .3rem; }
    .opcion-orden button:hover:not(:disabled) { color: var(--bordo); }
    .opcion-orden button:disabled { opacity: .25; cursor: default; }
    .opcion-numero { font-family: var(--font-display); font-weight: 700; color: var(--texto-2); font-size: .8rem; width: 1.2rem; text-align: center; }
    .meta-input-row { display: flex; gap: .6rem; align-items: center; margin-top: .6rem; }
    .meta-input-row input { width: 100px; padding: .5rem .7rem; border-radius: var(--radio); border: 1.5px solid var(--borde); font-size: .9rem; }
    .meta-sugerida { font-size: .76rem; color: var(--texto-2); margin-top: .4rem; }
    .meta-progress-bar { height: 12px; background: var(--borde); border-radius: 99px; overflow: hidden; margin: .6rem 0; }
    .meta-progress-bar > i { display: block; height: 100%; background: linear-gradient(90deg, var(--acento), var(--bordo)); border-radius: 99px; transition: width .3s; }
    .meta-progress-label { font-size: .85rem; color: var(--texto); font-weight: 600; }
    .reforzar-list { list-style: none; padding: 0; margin: .6rem 0 0; }
    .reforzar-list li { display: flex; justify-content: space-between; padding: .4rem 0; border-bottom: 1px solid var(--borde); font-size: .84rem; }
    .reforzar-list .pct-baja { color: #c0392b; font-weight: 700; }
    .reforzar-list .pct-ok { color: #1b7a43; font-weight: 700; }
  </style>
</head>
<body class="page-wrapper">

  <?php require 'includes/navbar.php'; ?>

  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Mi panel</p>
      <h1>Hola, <?= htmlspecialchars($usuario['nombre']) ?></h1>
      <p>Historial de tus intentos en el simulador y tu progreso por materia.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">

      <div class="meta-card">
        <h3>Mis opciones y puntaje meta</h3>
        <div class="meta-grid">
          <div>
            <div class="opcion-buscar">
              <input type="text" id="opcion-buscar" placeholder="Buscar plantel por clave o nombre…" autocomplete="off">
              <div id="opcion-autocomplete" class="opcion-autocomplete"></div>
            </div>
            <ul class="result-list" id="lista-opciones" style="list-style:none;padding:0">
              <li style="padding:.6rem;color:var(--texto-2);font-size:.8rem">Aún no agregas opciones.</li>
            </ul>
            <p style="font-size:.74rem;color:var(--texto-2);margin-top:.4rem">Hasta 5 opciones, en el orden que prefieras.</p>

            <div class="meta-input-row">
              <label style="font-size:.84rem;font-weight:600">Puntaje meta</label>
              <input type="number" id="input-meta" min="0" max="128" placeholder="—">
              <button id="btn-guardar-meta" class="btn btn-bordo btn-sm">Guardar</button>
            </div>
            <p class="meta-sugerida" id="meta-sugerida-txt"></p>
            <p id="meta-guardado-msg" style="font-size:.78rem;color:var(--texto-2);margin-top:.4rem"></p>
          </div>

          <div>
            <h4 style="font-family:var(--font-display);color:var(--bordo);font-size:.9rem;margin-bottom:.5rem">Progreso vs. meta</h4>
            <div id="progreso-meta-box">
              <p style="font-size:.82rem;color:var(--texto-2)">Agrega tus opciones y haz al menos un intento del simulador para ver tu progreso.</p>
            </div>

            <h4 style="font-family:var(--font-display);color:var(--bordo);font-size:.9rem;margin:1.2rem 0 .3rem">Temas a reforzar</h4>
            <ul class="reforzar-list" id="reforzar-list">
              <li style="color:var(--texto-2)">Aún no tienes intentos registrados.</li>
            </ul>
          </div>
        </div>
      </div>

      <div id="contenido">
        <div class="estado-msg"><span class="spinner"></span> Cargando tu historial…</div>
      </div>

    </div>
  </section>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>

  <script>
    let estadoIntentos = { mejorAciertos: null, ultimoDetalle: null };
    let opcionesActuales = [];

    async function cargar() {
      const div = document.getElementById('contenido');
      try {
        const resp = await fetch('../backend/api/simulador.php');
        const json = await resp.json();
        if (json.status !== 'ok') throw new Error(json.mensaje || 'Error al cargar tu historial');
        const intentos = json.datos;

        if (intentos.length === 0) {
          div.innerHTML = `
            <div class="estado-msg">
              Aún no has hecho ningún intento.<br>
              <a href="simulador.php" class="btn btn-bordo btn-sm mt-2">Ir al simulador →</a>
            </div>`;
          actualizarProgresoYReforzar();
          return;
        }

        const mejor = Math.max(...intentos.map(i => parseFloat(i.porcentaje)));
        const promedio = (intentos.reduce((s, i) => s + parseFloat(i.porcentaje), 0) / intentos.length).toFixed(1);
        const ultimo = intentos[intentos.length - 1];

        estadoIntentos.mejorAciertos = Math.max(...intentos.map(i => parseInt(i.aciertos)));
        estadoIntentos.ultimoDetalle = ultimo.detalle || null;
        actualizarProgresoYReforzar();

        div.innerHTML = `
          <div class="stats-row mb-3">
            <div class="stat-card">
              <div class="stat-num">${intentos.length}</div>
              <div class="stat-label">Intentos realizados</div>
            </div>
            <div class="stat-card">
              <div class="stat-num">${mejor}%</div>
              <div class="stat-label">Mejor resultado</div>
            </div>
            <div class="stat-card">
              <div class="stat-num">${promedio}%</div>
              <div class="stat-label">Promedio general</div>
            </div>
            <div class="stat-card">
              <div class="stat-num">${ultimo.aciertos} / ${ultimo.total}</div>
              <div class="stat-label">Último intento</div>
            </div>
          </div>

          <div class="grid-2 mb-3">
            <div class="chart-box"><h3>Progreso por intento</h3><canvas id="chart-progreso"></canvas></div>
            <div class="chart-box"><h3>Aciertos por materia (último intento)</h3><canvas id="chart-materias"></canvas></div>
          </div>

          <div class="data-table-wrap">
            <table class="data-table">
              <thead><tr><th>Fecha</th><th>Aciertos</th><th>%</th></tr></thead>
              <tbody>
                ${intentos.slice().reverse().map(i => `
                  <tr>
                    <td>${new Date(i.fecha.replace(' ', 'T')).toLocaleString('es-MX')}</td>
                    <td class="num">${i.aciertos} / ${i.total}</td>
                    <td class="num">${i.porcentaje}%</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        `;

        new Chart(document.getElementById('chart-progreso'), {
          type: 'line',
          data: {
            labels: intentos.map((_, i) => 'Intento ' + (i + 1)),
            datasets: [{
              label: '% de aciertos',
              data: intentos.map(i => parseFloat(i.porcentaje)),
              borderColor: '#023047',
              backgroundColor: 'rgba(2,48,71,.1)',
              fill: true, tension: .3, pointBackgroundColor: '#ffb703', pointRadius: 4,
            }]
          },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              y: { beginAtZero: true, max: 100, ticks: { font: { family: 'Sora' } }, grid: { color: '#e2eaf0' } },
              x: { ticks: { font: { family: 'Sora', size: 10 } }, grid: { display: false } }
            }
          }
        });

        const detalle = ultimo.detalle || {};
        const materias = Object.keys(detalle);
        new Chart(document.getElementById('chart-materias'), {
          type: 'bar',
          data: {
            labels: materias,
            datasets: [{
              label: '% de aciertos',
              data: materias.map(m => Math.round((detalle[m].ok / detalle[m].tot) * 100)),
              backgroundColor: '#fb8500cc',
              borderColor: '#fb8500',
              borderWidth: 1.5, borderRadius: 4,
            }]
          },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              y: { beginAtZero: true, max: 100, ticks: { font: { family: 'Sora' } }, grid: { color: '#e2eaf0' } },
              x: { ticks: { font: { family: 'Sora', size: 9 } }, grid: { display: false } }
            }
          }
        });

      } catch (err) {
        div.innerHTML = `<div class="estado-msg">Error de conexión.<br><small>${err.message}</small></div>`;
      }
    }

    // ── Mis opciones y puntaje meta ─────────────────────────
    async function cargarMetas() {
      try {
        const resp = await fetch('../backend/api/metas.php');
        const json = await resp.json();
        if (json.status !== 'ok') throw new Error(json.mensaje || 'Error al cargar tu perfil');

        opcionesActuales = json.datos.opciones || [];
        renderOpciones();

        const inputMeta = document.getElementById('input-meta');
        if (json.datos.puntaje_meta !== null) {
          inputMeta.value = json.datos.puntaje_meta;
        } else if (json.datos.meta_sugerida !== null) {
          inputMeta.value = json.datos.meta_sugerida;
        }
        const sugTxt = document.getElementById('meta-sugerida-txt');
        sugTxt.textContent = json.datos.meta_sugerida !== null
          ? `Sugerida según tus opciones: ${json.datos.meta_sugerida} aciertos (puedes ajustarla).`
          : '';

        actualizarProgresoYReforzar();
      } catch (err) {
        document.getElementById('lista-opciones').innerHTML =
          `<li style="padding:.6rem;color:#c00;font-size:.8rem">Error al cargar tus opciones.</li>`;
      }
    }

    function renderOpciones() {
      const ul = document.getElementById('lista-opciones');
      if (opcionesActuales.length === 0) {
        ul.innerHTML = '<li style="padding:.6rem;color:var(--texto-2);font-size:.8rem">Aún no agregas opciones.</li>';
        return;
      }
      ul.innerHTML = opcionesActuales.map((o, i) => `
        <li class="opcion-item">
          <span class="opcion-numero">${i + 1}</span>
          <div class="opcion-item-info">
            <strong>${o.nombre || o.clave}</strong>
            <small>${o.clave}${o.subsistema ? ' · ' + o.subsistema : ''}</small>
          </div>
          <span class="opcion-corte" title="Puntaje de corte promedio">${o.puntaje_corte_prom != null ? Math.round(o.puntaje_corte_prom) : '—'}</span>
          <div class="opcion-orden">
            <button data-i="${i}" data-dir="-1" title="Subir" ${i === 0 ? 'disabled' : ''}>▲</button>
            <button data-i="${i}" data-dir="1" title="Bajar" ${i === opcionesActuales.length - 1 ? 'disabled' : ''}>▼</button>
          </div>
          <button class="opcion-quitar" data-clave="${o.clave}" title="Quitar">Quitar</button>
        </li>
      `).join('');
      ul.querySelectorAll('.opcion-quitar').forEach(btn => {
        btn.addEventListener('click', () => {
          opcionesActuales = opcionesActuales.filter(o => o.clave !== btn.dataset.clave);
          renderOpciones();
        });
      });
      ul.querySelectorAll('.opcion-orden button').forEach(btn => {
        btn.addEventListener('click', () => {
          const i = parseInt(btn.dataset.i);
          const j = i + parseInt(btn.dataset.dir);
          [opcionesActuales[i], opcionesActuales[j]] = [opcionesActuales[j], opcionesActuales[i]];
          renderOpciones();
        });
      });
    }

    async function obtenerCorte(clave) {
      try {
        const resp = await fetch('../backend/api/escuela.php?plantel=' + encodeURIComponent(clave));
        const json = await resp.json();
        return json.status === 'ok' && json.datos.tiene_datos ? Math.round(json.datos.puntaje_corte_prom) : null;
      } catch (e) { return null; }
    }

    function actualizarProgresoYReforzar() {
      const box = document.getElementById('progreso-meta-box');
      const metaVal = parseInt(document.getElementById('input-meta').value);
      const mejor = estadoIntentos.mejorAciertos;

      if (mejor === null) {
        box.innerHTML = '<p style="font-size:.82rem;color:var(--texto-2)">Haz al menos un intento del simulador para ver tu progreso.</p>';
      } else if (!metaVal) {
        box.innerHTML = '<p style="font-size:.82rem;color:var(--texto-2)">Define tu puntaje meta para comparar tu avance.</p>';
      } else {
        const pct = Math.min(100, Math.round((mejor / metaVal) * 100));
        const restante = metaVal - mejor;
        box.innerHTML = `
          <div class="meta-progress-label">Tu mejor: ${mejor} / 128 · Meta: ${metaVal}</div>
          <div class="meta-progress-bar"><i style="width:${pct}%"></i></div>
          <p style="font-size:.8rem;color:var(--texto-2)">
            ${restante > 0 ? `Te faltan ${restante} aciertos para tu meta.` : '¡Ya superas tu meta!'}
          </p>
        `;
      }

      const ul = document.getElementById('reforzar-list');
      const detalle = estadoIntentos.ultimoDetalle;
      if (!detalle) {
        ul.innerHTML = '<li style="color:var(--texto-2)">Aún no tienes intentos registrados.</li>';
        return;
      }
      const materias = Object.keys(detalle)
        .map(m => ({ materia: m, pct: Math.round((detalle[m].ok / detalle[m].tot) * 100) }))
        .sort((a, b) => a.pct - b.pct)
        .slice(0, 3);
      ul.innerHTML = materias.map(m => `
        <li>
          <span>${m.materia}</span>
          <span>
            <span class="${m.pct < 60 ? 'pct-baja' : 'pct-ok'}">${m.pct}%</span>
            <a href="repaso.php?materia=${encodeURIComponent(m.materia)}" style="margin-left:.6rem;font-size:.78rem;color:var(--bordo);font-weight:600">Repasar →</a>
          </span>
        </li>
      `).join('');
    }

    document.getElementById('input-meta').addEventListener('input', actualizarProgresoYReforzar);

    document.getElementById('btn-guardar-meta').addEventListener('click', async () => {
      const msg = document.getElementById('meta-guardado-msg');
      const meta = document.getElementById('input-meta').value;
      try {
        const resp = await fetch('../backend/api/metas.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            puntaje_meta: meta ? parseInt(meta) : null,
            opciones: opcionesActuales.map(o => o.clave),
          })
        });
        const json = await resp.json();
        msg.textContent = json.status === 'ok' ? 'Guardado.' : (json.mensaje || 'No se pudo guardar.');
      } catch (e) {
        msg.textContent = 'No se pudo guardar (sin conexión).';
      }
    });

    const opBuscar = document.getElementById('opcion-buscar');
    const opAutocomplete = document.getElementById('opcion-autocomplete');
    let opDebounce;
    opBuscar.addEventListener('input', () => {
      clearTimeout(opDebounce);
      const q = opBuscar.value.trim();
      if (q.length < 2) { opAutocomplete.style.display = 'none'; return; }
      opDebounce = setTimeout(async () => {
        try {
          const resp = await fetch('../backend/api/planteles.php?q=' + encodeURIComponent(q));
          const json = await resp.json();
          if (json.status !== 'ok' || !json.datos?.length) {
            opAutocomplete.style.display = 'none'; return;
          }
          opAutocomplete.innerHTML = json.datos.map(item => `
            <div data-clave="${item.clave}" data-nombre="${(item.nombre || '').replace(/"/g, '&quot;')}">
              <strong>${item.clave}</strong>
              <span style="color:#4a6070;margin-left:.5rem">${item.nombre || ''}</span>
            </div>
          `).join('');
          opAutocomplete.style.display = 'block';
          opAutocomplete.querySelectorAll('div').forEach(d => {
            d.addEventListener('click', async () => {
              const clave = d.dataset.clave;
              opBuscar.value = '';
              opAutocomplete.style.display = 'none';
              if (opcionesActuales.length >= 5) {
                alert('Ya tienes 5 opciones. Quita alguna para agregar otra.');
                return;
              }
              if (opcionesActuales.some(o => o.clave === clave)) return;
              const nueva = { clave, nombre: d.dataset.nombre, puntaje_corte_prom: null };
              opcionesActuales.push(nueva);
              renderOpciones();
              nueva.puntaje_corte_prom = await obtenerCorte(clave);
              renderOpciones();
            });
          });
        } catch (e) { opAutocomplete.style.display = 'none'; }
      }, 350);
    });
    document.addEventListener('click', e => {
      if (!opAutocomplete.contains(e.target) && e.target !== opBuscar) opAutocomplete.style.display = 'none';
    });

    cargar();
    cargarMetas();
  </script>
</body>
</html>
