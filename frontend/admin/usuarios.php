<?php
require_once __DIR__ . '/../../backend/auth.php';
$usuario = requiereRolPagina('admin', '../index.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ECOEMS — Gestión de usuarios</title>
  <link rel="stylesheet" href="../css/estilos.css?v=2">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    .admin-table-wrap { background: var(--fondo-card); border-radius: var(--radio-lg); box-shadow: var(--sombra); overflow: hidden; }
    .admin-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    .admin-table th { background: var(--bordo); color: #fff; padding: .85rem 1rem; text-align: left; font-weight: 600; font-size: .75rem; text-transform: uppercase; letter-spacing: .06em; }
    .admin-table td { padding: .75rem 1rem; border-bottom: 1px solid var(--borde); color: var(--texto); }
    .admin-table tbody tr { cursor: pointer; transition: var(--trans); }
    .admin-table tbody tr:hover td { background: rgba(2,48,71,.06); }
    .admin-table tbody tr.seleccionado td { background: rgba(251,133,0,.12); }
    .role-badge { display: inline-block; padding: .2rem .6rem; border-radius: 20px; font-size: .72rem; font-weight: 600; text-transform: uppercase; }
    .role-aspirante { background: rgba(2,48,71,.1); color: var(--bordo); }
    .role-admin { background: rgba(251,133,0,.15); color: var(--acento); }
    .estado-msg { padding: 2rem; text-align: center; color: var(--texto-2); }

    /* ── Detalle de usuario ────────────────────────────── */
    #detalle { display: none; margin-top: 2rem; }
    #detalle.visible { display: block; }
    .detalle-header { background: var(--bordo); color: #fff; border-radius: var(--radio-lg) var(--radio-lg) 0 0; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .5rem; }
    .detalle-header h2 { font-family: var(--font-display); font-size: 1.2rem; }
    .detalle-header small { font-size: .78rem; color: rgba(255,255,255,.65); }
    .detalle-body { background: var(--fondo-card); border-radius: 0 0 var(--radio-lg) var(--radio-lg); box-shadow: var(--sombra); padding: 1.5rem; }
    .detalle-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .detalle-stat { text-align: center; padding: 1rem; background: var(--fondo); border-radius: var(--radio); }
    .detalle-stat .num { font-family: var(--font-display); font-size: 1.6rem; font-weight: 700; color: var(--bordo); line-height: 1; }
    .detalle-stat .label { font-size: .72rem; color: var(--texto-2); text-transform: uppercase; letter-spacing: .06em; margin-top: .25rem; }
    .chart-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin: 1.5rem 0; }
    .chart-box { background: var(--fondo); border-radius: var(--radio-lg); padding: 1rem; height: 260px; }
    .chart-box h4 { font-family: var(--font-display); font-size: .9rem; color: var(--bordo); margin-bottom: .8rem; }
    .cerrar-detalle { background: rgba(255,255,255,.15); color: #fff; border: none; padding: .4rem 1rem; border-radius: var(--radio); cursor: pointer; font-family: var(--font-body); font-size: .82rem; font-weight: 600; transition: var(--trans); }
    .cerrar-detalle:hover { background: rgba(255,255,255,.25); }
    @media (max-width: 700px) { .chart-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body class="page-wrapper">
  <?php require '../includes/navbar.php'; ?>
  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Administración</p>
      <h1>Usuarios</h1>
      <p>Listado de cuentas registradas. Da clic en un usuario para ver su detalle.</p>
    </div>
  </div>
  <section class="section">
    <div class="container">
      <div id="lista" class="admin-table-wrap">
        <div class="estado-msg"><span class="spinner"></span> Cargando usuarios…</div>
      </div>

      <!-- Detalle de usuario -->
      <div id="detalle">
        <div class="detalle-header">
          <div>
            <h2 id="det-nombre"></h2>
            <small id="det-email"></small>
          </div>
          <button class="cerrar-detalle" id="btn-cerrar-detalle">✕ Cerrar</button>
        </div>
        <div class="detalle-body">
          <div class="detalle-stats" id="det-stats"></div>
          <div class="chart-grid">
            <div class="chart-box"><h4>📈 Progreso por intento</h4><canvas id="chart-progreso"></canvas></div>
            <div class="chart-box"><h4>📊 Aciertos por materia</h4><canvas id="chart-materias"></canvas></div>
          </div>
          <div class="data-table-wrap">
            <table class="data-table">
              <thead><tr><th>Fecha</th><th>Aciertos</th><th>%</th><th></th></tr></thead>
              <tbody id="det-tabla"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
  <footer class="site-footer">
    <p>Panel de Administración <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; 2026</p>
  </footer>

  <script>
    let usuarios = [];
    let chartProgreso = null;
    let chartMaterias = null;

    async function cargarUsuarios() {
      const div = document.getElementById('lista');
      try {
        const resp = await fetch('../backend/api/admin/usuarios.php');
        const json = await resp.json();
        if (json.status !== 'ok' || !json.datos?.length) {
          div.innerHTML = '<div class="estado-msg">Sin usuarios registrados.</div>'; return;
        }
        usuarios = json.datos;
        div.innerHTML = `<table class="admin-table">
          <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Registro</th><th></th></tr></thead>
          <tbody>${usuarios.map(u => `
            <tr onclick="verDetalle(${u.id})" data-id="${u.id}">
              <td><strong>${u.nombre}</strong></td>
              <td style="color:var(--texto-2)">${u.email}</td>
              <td><span class="role-badge role-${u.rol}">${u.rol}</span></td>
              <td style="font-size:.8rem;color:var(--texto-2)">${u.creado ? new Date(u.creado.replace(' ', 'T')).toLocaleString('es-MX') : '—'}</td>
              <td style="text-align:right;color:var(--acento);font-size:.78rem">Ver →</td>
            </tr>
          `).join('')}</tbody>
        </table>`;
      } catch (err) {
        div.innerHTML = '<div class="estado-msg">❌ Error al cargar usuarios.</div>';
      }
    }

    async function verDetalle(id) {
      document.querySelectorAll('.seleccionado').forEach(el => el.classList.remove('seleccionado'));
      const row = document.querySelector(`tr[data-id="${id}"]`);
      if (row) row.classList.add('seleccionado');

      const det = document.getElementById('detalle');
      det.classList.add('visible');
      det.scrollIntoView({ behavior: 'smooth', block: 'start' });

      try {
        const resp = await fetch(`../backend/api/admin/usuarios_detalle.php?id=${id}`);
        const json = await resp.json();
        if (json.status !== 'ok') throw new Error(json.mensaje);

        const d = json.datos;
        const user = d.usuario;
        const intentos = d.intentos;
        const porMateria = d.por_materia;

        document.getElementById('det-nombre').textContent = user.nombre;
        document.getElementById('det-email').textContent = `${user.email} · Rol: ${user.rol} · Registro: ${user.creado ? new Date(user.creado.replace(' ', 'T')).toLocaleDateString('es-MX') : '—'}`;

        const totalIntentos = intentos.length;
        const mejor = totalIntentos > 0 ? Math.max(...intentos.map(i => parseFloat(i.porcentaje))) : 0;
        const promedio = totalIntentos > 0 ? (intentos.reduce((s, i) => s + parseFloat(i.porcentaje), 0) / totalIntentos).toFixed(1) : 0;
        const ultimo = totalIntentos > 0 ? intentos[totalIntentos - 1] : null;

        document.getElementById('det-stats').innerHTML = `
          <div class="detalle-stat"><div class="num">${totalIntentos}</div><div class="label">Intentos</div></div>
          <div class="detalle-stat"><div class="num">${mejor}%</div><div class="label">Mejor resultado</div></div>
          <div class="detalle-stat"><div class="num">${promedio}%</div><div class="label">Promedio</div></div>
          <div class="detalle-stat"><div class="num">${ultimo ? ultimo.aciertos + '/' + ultimo.total : '—'}</div><div class="label">Último intento</div></div>
        `;

        // Tabla de intentos
        const tb = document.getElementById('det-tabla');
        if (totalIntentos === 0) {
          tb.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--texto-2);padding:1.5rem">Este alumno aún no ha hecho ningún intento.</td></tr>';
        } else {
          tb.innerHTML = intentos.slice().reverse().map(i => `
            <tr>
              <td>${new Date(i.fecha.replace(' ', 'T')).toLocaleString('es-MX')}</td>
              <td class="num">${i.aciertos} / ${i.total}</td>
              <td class="num" style="font-weight:600;color:${parseFloat(i.porcentaje) >= 60 ? '#1b7a43' : '#c0392b'}">${i.porcentaje}%</td>
              <td style="font-size:.78rem;color:var(--texto-2)">${i.detalle ? Object.keys(i.detalle).length + ' materias' : '—'}</td>
            </tr>
          `).join('');
        }

        // Gráfica: progreso por intento
        if (chartProgreso) chartProgreso.destroy();
        const ctx1 = document.getElementById('chart-progreso').getContext('2d');
        if (totalIntentos > 0) {
          chartProgreso = new Chart(ctx1, {
            type: 'line',
            data: {
              labels: intentos.map((_, i) => '#' + (i + 1)),
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
        } else {
          chartProgreso = new Chart(ctx1, {
            type: 'bar',
            data: { labels: ['Sin datos'], datasets: [{ data: [0] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
          });
        }

        // Gráfica: aciertos por materia (agregado)
        if (chartMaterias) chartMaterias.destroy();
        const ctx2 = document.getElementById('chart-materias').getContext('2d');
        const materias = Object.keys(porMateria);
        if (materias.length > 0) {
          chartMaterias = new Chart(ctx2, {
            type: 'bar',
            data: {
              labels: materias,
              datasets: [{
                label: '% de aciertos',
                data: materias.map(m => porMateria[m].pct),
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
        } else {
          chartMaterias = new Chart(ctx2, {
            type: 'bar',
            data: { labels: ['Sin datos'], datasets: [{ data: [0] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
          });
        }

      } catch (err) {
        document.getElementById('det-stats').innerHTML = `<div class="estado-msg">❌ ${err.message}</div>`;
      }
    }

    document.getElementById('btn-cerrar-detalle').addEventListener('click', () => {
      document.getElementById('detalle').classList.remove('visible');
      document.querySelectorAll('.seleccionado').forEach(el => el.classList.remove('seleccionado'));
    });

    cargarUsuarios();

    // Si llegamos con ?usuario_id=X, abrir ese usuario automáticamente
    const params = new URLSearchParams(window.location.search);
    const uid = params.get('usuario_id');
    if (uid) {
      setTimeout(() => verDetalle(parseInt(uid)), 500);
    }
  </script>
</body>
</html>
