<?php
require_once __DIR__ . '/../../backend/auth.php';
$usuario = requiereRolPagina('admin', '../index.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ECOEMS, Panel de administración</title>
  <link rel="stylesheet" href="../css/estilos.css?v=3">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    .admin-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .admin-stat-card { background: var(--fondo-card); border-radius: var(--radio-lg); padding: 1.4rem 1.2rem; box-shadow: var(--sombra); border-top: 3px solid var(--acento); text-align: center; }
    .admin-stat-card .num { font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--bordo); line-height: 1; margin-bottom: .3rem; }
    .admin-stat-card .label { font-size: .75rem; color: var(--texto-2); text-transform: uppercase; letter-spacing: .07em; font-weight: 600; }
    .admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .admin-card { background: var(--fondo-card); border-radius: var(--radio-lg); box-shadow: var(--sombra); overflow: hidden; }
    .admin-card-header { background: var(--bordo); color: #fff; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
    .admin-card-header h3 { font-family: var(--font-display); font-size: .95rem; }
    .admin-card-header a { font-size: .78rem; color: var(--oro); text-decoration: none; }
    .admin-card-body { padding: 1.2rem 1.5rem; }
    .admin-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    .admin-table th, .admin-table td { padding: .6rem .4rem; text-align: left; border-bottom: 1px solid var(--borde); }
    .admin-table th { color: var(--texto-2); font-weight: 600; font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; }
    .admin-table td { color: var(--texto); }
    .admin-table tr:last-child td { border-bottom: none; }
    .chart-wrap { height: 240px; padding: 1rem; }
    .empty-msg { padding: 2rem; text-align: center; color: var(--texto-2); font-size: .88rem; }
    @media (max-width: 800px) { .admin-grid { grid-template-columns: 1fr; } }
    .admin-seccion-titulo {
      font-family: var(--font-display); color: var(--bordo); font-size: 1.1rem;
      margin: 2rem 0 1rem; padding-bottom: .5rem; border-bottom: 2px solid var(--borde);
    }
    .admin-seccion-titulo:first-of-type { margin-top: 0; }
    .contenido-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
    .contenido-card {
      background: var(--fondo-card); border-radius: var(--radio-lg); box-shadow: var(--sombra);
      padding: 1.3rem; border-left: 4px solid var(--bordo); display: flex; flex-direction: column; gap: .5rem;
    }
    .contenido-card .num { font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--bordo); }
    .contenido-card .label { font-size: .78rem; color: var(--texto-2); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
    .contenido-card a { font-size: .8rem; color: var(--acento); font-weight: 600; margin-top: auto; }
  </style>
</head>
<body class="page-wrapper">

  <?php require '../includes/navbar_admin.php'; ?>

  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Administración</p>
      <h1>Panel de control</h1>
      <p>Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?>. Resumen general del portal ECOEMS.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">

      <h2 class="admin-seccion-titulo">Aspirantes — cómo van</h2>

      <div id="kpis" class="admin-stats">
        <div class="admin-stat-card"><div class="num" id="stat-aspirantes">—</div><div class="label">Aspirantes registrados</div></div>
        <div class="admin-stat-card"><div class="num" id="stat-intentos">—</div><div class="label">Intentos de simulador</div></div>
        <div class="admin-stat-card"><div class="num" id="stat-promedio">—</div><div class="label">Promedio global</div></div>
        <div class="admin-stat-card"><div class="num" id="stat-admins">—</div><div class="label">Administradores</div></div>
      </div>

      <div class="admin-grid">
        <div class="admin-card">
          <div class="admin-card-header">
            <h3>Últimos registros</h3>
            <a href="usuarios.php">Ver todos →</a>
          </div>
          <div class="admin-card-body" id="recent-users"><div class="empty-msg">Cargando…</div></div>
        </div>

        <div class="admin-card">
          <div class="admin-card-header">
            <h3>Últimos intentos del simulador</h3>
          </div>
          <div class="admin-card-body" id="recent-intentos"><div class="empty-msg">Cargando…</div></div>
        </div>

        <div class="admin-card">
          <div class="admin-card-header"><h3>Progreso del simulador</h3></div>
          <div class="chart-wrap"><canvas id="chart-simulador"></canvas></div>
        </div>

        <div class="admin-card">
          <div class="admin-card-header"><h3>% de aciertos promedio por materia</h3></div>
          <div class="chart-wrap"><canvas id="chart-materia"></canvas></div>
        </div>
      </div>

      <h2 class="admin-seccion-titulo">Contenido del sitio</h2>

      <div class="contenido-grid">
        <div class="contenido-card">
          <div class="num" id="cont-recursos">—</div>
          <div class="label">Recursos en biblioteca</div>
          <a href="../biblioteca.php">Gestionar biblioteca →</a>
        </div>
        <div class="contenido-card">
          <div class="num" id="cont-planteles">—</div>
          <div class="label">Planteles en catálogo</div>
          <a href="planteles.php">Ver planteles →</a>
        </div>
        <div class="contenido-card">
          <div class="num">128</div>
          <div class="label">Reactivos del simulador</div>
          <a href="examen.php">Gestionar examen →</a>
        </div>
        <div class="contenido-card">
          <div class="num">128</div>
          <div class="label">Flashcards de repaso</div>
          <a href="../repaso.php">Ver repaso →</a>
        </div>
      </div>

    </div>
  </section>

  <footer class="site-footer">
    <p>Panel de Administración <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; 2026</p>
  </footer>

  <script>
    async function cargarDashboard() {
      try {
        const [resUsers, resSim, resRec, resPlanteles] = await Promise.all([
          fetch('../../backend/api/admin/usuarios.php'),
          fetch('../../backend/api/admin/simulador_stats.php'),
          fetch('../../backend/api/recursos.php'),
          fetch('../../backend/api/planteles.php'),
        ]);
        const users = await resUsers.json();
        const sim = await resSim.json();
        const rec = await resRec.json();
        const planteles = await resPlanteles.json();

        const aspirantes = (users.datos || []).filter(u => u.rol === 'aspirante').length;
        const admins = (users.datos || []).filter(u => u.rol === 'admin').length;
        const intentos = (sim.datos?.intentos || []);
        const totalIntentos = intentos.length;
        const promedioGlobal = totalIntentos > 0
          ? (intentos.reduce((s, i) => s + parseFloat(i.porcentaje), 0) / totalIntentos).toFixed(1)
          : 0;

        document.getElementById('stat-aspirantes').textContent = aspirantes;
        document.getElementById('stat-admins').textContent = admins;
        document.getElementById('stat-intentos').textContent = totalIntentos;
        document.getElementById('stat-promedio').textContent = totalIntentos > 0 ? promedioGlobal + '%' : '—';
        document.getElementById('cont-recursos').textContent = (rec.datos || []).length;
        document.getElementById('cont-planteles').textContent = (planteles.datos || []).length;

        // Últimos 5 registros
        const recientes = (users.datos || []).slice(-5).reverse();
        const tbody1 = document.getElementById('recent-users');
        if (recientes.length === 0) {
          tbody1.innerHTML = '<div class="empty-msg">Sin usuarios registrados.</div>';
        } else {
          tbody1.innerHTML = `<table class="admin-table">
            <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Registro</th></tr></thead>
            <tbody>${recientes.map(u => `
              <tr>
                <td><strong><a href="usuarios.php?usuario_id=${u.id}" style="color:var(--bordo);text-decoration:underline">${u.nombre}</a></strong></td>
                <td style="color:var(--texto-2)">${u.email}</td>
                <td><span style="font-size:.72rem;font-weight:600;text-transform:uppercase;color:${u.rol === 'admin' ? 'var(--acento)' : 'var(--bordo)'}">${u.rol}</span></td>
                <td style="font-size:.78rem;color:var(--texto-2)">${u.creado ? new Date(u.creado.replace(' ', 'T')).toLocaleDateString('es-MX') : '—'}</td>
              </tr>
            `).join('')}</tbody>
          </table>`;
        }

        // Últimos 5 intentos
        const ultimosIntentos = intentos.slice(-5).reverse();
        const tbody2 = document.getElementById('recent-intentos');
        if (ultimosIntentos.length === 0) {
          tbody2.innerHTML = '<div class="empty-msg">Sin intentos todavía.</div>';
        } else {
          tbody2.innerHTML = `<table class="admin-table">
            <thead><tr><th>Usuario</th><th>Aciertos</th><th>%</th><th>Fecha</th></tr></thead>
            <tbody>${ultimosIntentos.map(i => `
              <tr>
                <td><strong><a href="usuarios.php?usuario_id=${i.usuario_id}" style="color:var(--bordo);text-decoration:underline">${i.usuario_nombre || '—'}</a></strong></td>
                <td>${i.aciertos} / ${i.total}</td>
                <td style="font-weight:600;color:${parseFloat(i.porcentaje) >= 60 ? 'var(--ok,#1b7a43)' : 'var(--bad,#c0392b)'}">${i.porcentaje}%</td>
                <td style="font-size:.78rem;color:var(--texto-2)">${i.fecha ? new Date(i.fecha.replace(' ', 'T')).toLocaleDateString('es-MX') : '—'}</td>
              </tr>
            `).join('')}</tbody>
          </table>`;
        }

        // Gráfica: distribución de puntajes en el simulador
        const ctx1 = document.getElementById('chart-simulador').getContext('2d');
        const rangos = { '0-20': 0, '21-40': 0, '41-60': 0, '61-80': 0, '81-100': 0 };
        intentos.forEach(i => {
          const p = parseFloat(i.porcentaje);
          if (p <= 20) rangos['0-20']++;
          else if (p <= 40) rangos['21-40']++;
          else if (p <= 60) rangos['41-60']++;
          else if (p <= 80) rangos['61-80']++;
          else rangos['81-100']++;
        });
        new Chart(ctx1, {
          type: 'bar',
          data: {
            labels: Object.keys(rangos),
            datasets: [{
              label: 'Intentos',
              data: Object.values(rangos),
              backgroundColor: ['#c0392b88', '#e67e2288', '#f1c40f88', '#2ecc7188', '#1b7a4388'],
              borderColor: ['#c0392b', '#e67e22', '#f1c40f', '#2ecc71', '#1b7a43'],
              borderWidth: 1.5, borderRadius: 4,
            }]
          },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              y: { beginAtZero: true, ticks: { stepSize: 1, font: { family: 'Sora' } }, grid: { color: '#e2eaf0' } },
              x: { ticks: { font: { family: 'Sora', size: 10 } }, grid: { display: false } }
            }
          }
        });

        // Gráfica: % de aciertos promedio por materia (agregado de todos los intentos)
        const ctx2 = document.getElementById('chart-materia').getContext('2d');
        const porMateria = {};
        intentos.forEach(i => {
          if (!i.detalle) return;
          Object.entries(i.detalle).forEach(([materia, d]) => {
            if (!porMateria[materia]) porMateria[materia] = { ok: 0, tot: 0 };
            porMateria[materia].ok += d.ok;
            porMateria[materia].tot += d.tot;
          });
        });
        const materias = Object.keys(porMateria);
        const pctMaterias = materias.map(m => Math.round((porMateria[m].ok / porMateria[m].tot) * 100));
        new Chart(ctx2, {
          type: 'bar',
          data: {
            labels: materias,
            datasets: [{
              label: '% de aciertos',
              data: pctMaterias,
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
        document.querySelectorAll('.empty-msg').forEach(el => el.textContent = 'Error de conexión.');
      }
    }
    cargarDashboard();
  </script>
</body>
</html>
