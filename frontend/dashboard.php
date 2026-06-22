<?php
require_once __DIR__ . '/../backend/auth.php';
$usuario = requiereSesionPagina('login.php?next=dashboard.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS — Mi panel</title>
  <link rel="stylesheet" href="css/estilos.css?v=2">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    .estado-msg { padding: 2rem; text-align: center; color: var(--texto-2); font-size: .9rem; }
    .chart-box { background: var(--fondo-card); border-radius: var(--radio-lg); box-shadow: var(--sombra);
      padding: 1.5rem; height: 320px; }
    .chart-box h3 { font-family: var(--font-display); color: var(--bordo); font-size: 1rem; margin-bottom: 1rem; }
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

      <div id="contenido">
        <div class="estado-msg"><span class="spinner"></span> Cargando tu historial…</div>
      </div>

    </div>
  </section>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>

  <script>
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
          return;
        }

        const mejor = Math.max(...intentos.map(i => parseFloat(i.porcentaje)));
        const promedio = (intentos.reduce((s, i) => s + parseFloat(i.porcentaje), 0) / intentos.length).toFixed(1);
        const ultimo = intentos[intentos.length - 1];

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
            <div class="chart-box"><h3>📈 Progreso por intento</h3><canvas id="chart-progreso"></canvas></div>
            <div class="chart-box"><h3>📊 Aciertos por materia (último intento)</h3><canvas id="chart-materias"></canvas></div>
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
        div.innerHTML = `<div class="estado-msg">❌ Error de conexión.<br><small>${err.message}</small></div>`;
      }
    }
    cargar();
  </script>
</body>
</html>
