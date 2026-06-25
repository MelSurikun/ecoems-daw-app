<?php require_once __DIR__ . '/../backend/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS, Acerca del Proyecto</title>
  <link rel="stylesheet" href="css/estilos.css?v=3">
  <style>
    .team-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.2rem;
      margin-bottom: 2rem;
    }
    .team-card {
      background: var(--fondo-card);
      border-radius: var(--radio-lg);
      padding: 1.8rem 1.4rem;
      box-shadow: var(--sombra);
      text-align: center;
      border-top: 4px solid var(--bordo);
      transition: var(--trans);
    }
    .team-card:hover { transform: translateY(-4px); box-shadow: var(--sombra-lg); }
    .team-avatar {
      width: 64px; height: 64px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-family: var(--font-display);
      font-size: 1.5rem;
      font-weight: 700;
      color: #fff;
      margin: 0 auto 1rem;
    }
    .av-1 { background: var(--bordo); }
    .av-2 { background: #1E64C8; }
    .av-3 { background: #2E7D32; }
    .team-card h3 { font-family: var(--font-display); color: var(--bordo); font-size: 1.1rem; margin-bottom: .2rem; }
    .team-card .rol { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--texto-2); margin-bottom: .6rem; }
    .team-card p { font-size: .82rem; color: var(--texto-2); line-height: 1.55; }

    .data-info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.2rem;
    }
    .info-box {
      background: var(--fondo-card);
      border-radius: var(--radio-lg);
      padding: 1.4rem;
      box-shadow: var(--sombra);
    }
    .info-box h4 {
      font-family: var(--font-display);
      color: var(--bordo);
      font-size: .98rem;
      margin-bottom: .8rem;
      padding-bottom: .5rem;
      border-bottom: 1px solid var(--borde);
    }
    .info-row {
      display: flex;
      justify-content: space-between;
      font-size: .83rem;
      padding: .35rem 0;
      border-bottom: 1px solid var(--borde);
      align-items: center;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row span { color: var(--texto-2); }
    .info-row strong { color: var(--texto); }

    .tech-pills {
      display: flex;
      flex-wrap: wrap;
      gap: .5rem;
      margin-top: .8rem;
    }
    .tech-pill {
      background: rgba(2,48,71,.08);
      color: var(--bordo);
      border: 1.5px solid rgba(2,48,71,.2);
      border-radius: 20px;
      padding: .25rem .75rem;
      font-size: .78rem;
      font-weight: 600;
    }
  </style>
</head>
<body class="page-wrapper">

  <?php require 'includes/navbar.php'; ?>

  <!-- Page header -->
  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Información del proyecto</p>
      <h1>Acerca del Portal ECOEMS</h1>
      <p>Descripción del sistema, fuente de datos, equipo de desarrollo y stack tecnológico.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">

      <!-- Descripción -->
      <h2 class="section-title"><span>¿Qué es?</span>Descripción del sistema</h2>
      <div style="background:var(--fondo-card);border-radius:var(--radio-lg);padding:1.8rem;box-shadow:var(--sombra);margin-bottom:2rem;border-left:4px solid var(--bordo)">
        <p style="font-size:.98rem;line-height:1.8;color:var(--texto)">
          El <strong>Portal de Consulta Histórica ECOEMS</strong> es una aplicación web desarrollada como proyecto
          escolar de la materia <em>Desarrollo de Aplicaciones Web</em> de la Licenciatura en Ciencia de Datos del IPN.
          Su propósito es dar a aspirantes, padres de familia e investigadores una herramienta de acceso libre para
          explorar el histórico de puntajes de corte, demanda y oferta del concurso de asignación a la
          Educación Media Superior en la Zona Metropolitana de la Ciudad de México.
        </p>
        <p style="font-size:.98rem;line-height:1.8;color:var(--texto);margin-top:1rem">
          Los datos son proporcionados por <strong>XABER A.C.</strong>, una organización civil sin fines de lucro
          que recopila y publica en abierto bases de datos educativas financiadas con recursos públicos, con el objetivo
          de impulsar la investigación y el diseño de políticas educativas basadas en evidencia.
        </p>
        <p style="font-size:.98rem;line-height:1.8;color:var(--texto);margin-top:1rem">
          El tratamiento de los datos personales de quienes crean una cuenta se describe en el aviso de privacidad
          y en los términos y condiciones disponibles desde el formulario de registro.
        </p>
      </div>

      <!-- Equipo -->
      <h2 class="section-title"><span>Quiénes somos</span>Equipo de desarrollo</h2>
      <div class="team-grid">
        <div class="team-card">
          <div class="team-avatar av-1">H</div>
          <h3>Héctor</h3>
          <p class="rol">Analista de Datos</p>
          <p>Exploración y validación del dataset ECOEMS, definición de métricas, consultas SQL y lógica de análisis estadístico.</p>
        </div>
        <div class="team-card">
          <div class="team-avatar av-2">M</div>
          <h3>Melanie</h3>
          <p class="rol">Backend & Base de Datos</p>
          <p>Desarrollo en PHP, diseño de la base de datos MariaDB, scripts ETL de carga de CSV y endpoints de API.</p>
        </div>
        <div class="team-card">
          <div class="team-avatar av-3">A</div>
          <h3>Amalia</h3>
          <p class="rol">Frontend</p>
          <p>Diseño e implementación de interfaces HTML5/CSS3, integración de Chart.js para gráficas y Leaflet.js para el mapa.</p>
        </div>
      </div>

      <!-- Fuente de datos + stack -->
      <div class="data-info-grid">
        <!-- Fuente de datos -->
        <div class="info-box">
          <h4>Fuente de datos, XABER A.C.</h4>
          <div class="info-row"><span>Dataset</span><strong>ECOEMS, Concurso de asignación EMS</strong></div>
          <div class="info-row"><span>Organización</span><strong>XABER A.C.</strong></div>
          <div class="info-row"><span>URL</span><strong style="font-size:.78rem">xaber.org.mx/repositorio-de-datos</strong></div>
          <div class="info-row"><span>Cobertura temporal</span><strong>1996 a la fecha</strong></div>
          <div class="info-row"><span>Cobertura geográfica</span><strong>ZMCDMX</strong></div>
          <div class="info-row"><span>Formato</span><strong>CSV comprimidos por año</strong></div>
          <div class="info-row"><span>Licencia</span><strong>Datos abiertos (recursos públicos)</strong></div>
          <div class="info-row"><span>Volumen aprox.</span><strong>350 000 a 400 000 registros/año</strong></div>
        </div>

        <!-- Stack -->
        <div class="info-box">
          <h4>Stack tecnológico</h4>
          <div class="info-row"><span>Frontend</span><strong>HTML5, CSS3, JavaScript</strong></div>
          <div class="info-row"><span>Visualización</span><strong>Chart.js</strong></div>
          <div class="info-row"><span>Mapas</span><strong>Leaflet.js + OpenStreetMap</strong></div>
          <div class="info-row"><span>Backend</span><strong>PHP 8.x</strong></div>
          <div class="info-row"><span>Base de datos</span><strong>MariaDB</strong></div>
          <div class="info-row"><span>ETL</span><strong>Scripts PHP y Python (carga de CSV y catálogo de planteles)</strong></div>
          <div class="info-row"><span>Control de versiones</span><strong>Git + GitHub</strong></div>
          <div class="info-row"><span>Tipografía</span><strong>Sora + IBM Plex Serif (Google Fonts)</strong></div>
          <p style="font-size:.76rem;color:var(--texto-2);margin-top:.8rem">Tecnologías utilizadas:</p>
          <div class="tech-pills">
            <span class="tech-pill">HTML5</span>
            <span class="tech-pill">CSS3</span>
            <span class="tech-pill">JavaScript</span>
            <span class="tech-pill">PHP 8</span>
            <span class="tech-pill">MariaDB</span>
            <span class="tech-pill">Chart.js</span>
            <span class="tech-pill">Leaflet.js</span>
            <span class="tech-pill">Git</span>
          </div>
        </div>

        <!-- Mapa de sitio resumido -->
        <div class="info-box">
          <h4>Mapa de sitio</h4>
          <div class="info-row"><span>/index.php</span><strong>Inicio, buscador rápido</strong></div>
          <div class="info-row"><span>/escuela.php</span><strong>Consulta por plantel</strong></div>
          <div class="info-row"><span>/comparar.php</span><strong>Comparador (hasta 4 opciones)</strong></div>
          <div class="info-row"><span>/mapa.php</span><strong>Mapa interactivo ZMCDMX</strong></div>
          <div class="info-row"><span>/resumen.php</span><strong>Resumen estadístico</strong></div>
          <div class="info-row"><span>/biblioteca.php</span><strong>Biblioteca de guías</strong></div>
          <div class="info-row"><span>/simulador.php</span><strong>Examen simulador</strong></div>
          <div class="info-row"><span>/repaso.php</span><strong>Repaso por materia, flashcards</strong></div>
          <div class="info-row"><span>/dashboard.php</span><strong>Mi panel, opciones y progreso</strong></div>
          <div class="info-row"><span>/admin/dashboard.php</span><strong>Panel de administración</strong></div>
          <div class="info-row"><span>/acerca.php</span><strong>Esta página</strong></div>
        </div>

        <!-- Créditos -->
        <div class="info-box">
          <h4>Créditos y contexto académico</h4>
          <div class="info-row"><span>Materia</span><strong>Desarrollo de Aplicaciones Web</strong></div>
          <div class="info-row"><span>Semestre</span><strong>Lic. en Ciencia de Datos, IPN</strong></div>
          <div class="info-row"><span>Institución</span><strong>IPN, Licenciatura en Ciencia de Datos</strong></div>
          <div class="info-row"><span>Ciclo</span><strong>2025, 2026</strong></div>
          <div class="info-row"><span>Entrega</span><strong>Proyecto Final</strong></div>
          <div class="info-row"><span>Fecha</span><strong>Junio de 2026</strong></div>
        </div>
      </div>

    </div>
  </section>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="https://xaber.org.mx" target="_blank">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>
</body>
</html>
