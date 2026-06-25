<?php
require_once __DIR__ . '/../backend/auth.php';
requiereSesionPagina('login.php?next=repaso.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS, Repaso por materia</title>
  <link rel="stylesheet" href="css/estilos.css?v=3">
  <style>
    .materia-chips { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.5rem; }
    .materia-chip {
      font-size: .8rem; padding: .4rem .9rem; border-radius: 20px;
      border: 1.5px solid var(--borde); background: var(--fondo-card);
      cursor: pointer; font-family: var(--font-body); font-weight: 600;
      color: var(--texto-2); transition: var(--trans);
    }
    .materia-chip:hover { border-color: var(--bordo); color: var(--bordo); }
    .materia-chip.activo { background: var(--bordo); color: #fff; border-color: var(--bordo); }
    .materia-chip .conteo { opacity: .7; font-weight: 400; margin-left: .25rem; }

    #flash-area { display: none; }
    #flash-area.activo { display: block; }
    .flash-contador { text-align: center; font-size: .85rem; color: var(--texto-2); margin-bottom: .8rem; font-weight: 600; }

    #card {
      position: relative; height: 360px; width: 100%; max-width: 720px; margin: 0 auto;
      perspective: 1600px; cursor: pointer;
    }
    .side {
      position: absolute; inset: 0; display: flex; flex-direction: column;
      justify-content: center; align-items: center; padding: 1.8rem;
      border-radius: var(--radio-lg); box-shadow: var(--sombra-lg);
      backface-visibility: hidden; transition: transform .6s cubic-bezier(.4,0,.2,1);
      overflow-y: auto; text-align: center;
    }
    .front {
      background: var(--bordo); color: #fefef2;
      transform: rotateY(0deg);
    }
    .back {
      background: var(--fondo-card); color: var(--texto);
      transform: rotateY(180deg); border: 1.5px solid var(--oro);
    }
    #card.volteada .front { transform: rotateY(180deg); }
    #card.volteada .back  { transform: rotateY(360deg); }

    .flash-materia { font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; color: var(--oro-cl); font-weight: 700; margin-bottom: .6rem; }
    .back .flash-materia { color: var(--acento); }
    .flash-pregunta { font-family: var(--font-display); font-size: 1.15rem; line-height: 1.5; margin: 0; }
    .flash-img { max-width: 100%; max-height: 140px; margin: .8rem auto 0; border-radius: 6px; background: #fff; padding: 4px; }
    .flash-opciones { list-style: none; padding: 0; margin: 1rem 0 0; font-size: .85rem; text-align: left; max-width: 480px; }
    .flash-opciones li { padding: .25rem 0; opacity: .9; }
    .flash-respuesta-letra {
      display: inline-flex; align-items: center; justify-content: center;
      width: 42px; height: 42px; border-radius: 50%; background: var(--oro);
      color: var(--bordo); font-family: var(--font-display); font-weight: 700;
      font-size: 1.3rem; margin-bottom: .6rem;
    }
    .flash-hint { font-size: .72rem; color: var(--texto-2); margin-top: 1rem; }
    .back .flash-hint { color: var(--texto-2); }

    .flash-nav { display: flex; justify-content: center; align-items: center; gap: 1.2rem; margin-top: 1.5rem; }
    .flash-nav button {
      font-family: var(--font-body); font-weight: 700; font-size: .85rem;
      padding: .6rem 1.3rem; border-radius: var(--radio); border: none;
      background: var(--bordo); color: #fff; cursor: pointer; transition: var(--trans);
    }
    .flash-nav button:hover:not(:disabled) { background: var(--bordo-os); }
    .flash-nav button:disabled { opacity: .35; cursor: not-allowed; }

    .estado-msg { padding: 2rem; text-align: center; color: var(--texto-2); font-size: .9rem; }
  </style>
</head>
<body class="page-wrapper">

  <?php require 'includes/navbar.php'; ?>

  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Innovación · Repaso por materia</p>
      <h1>Repaso con flashcards</h1>
      <p>Elige una materia y repasa con tarjetas que volteas: pregunta de un lado, respuesta del otro.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">

      <div class="materia-chips" id="materia-chips"></div>

      <div id="flash-area">
        <div class="flash-contador" id="flash-contador"></div>
        <div id="card">
          <div class="side front" id="flash-front"></div>
          <div class="side back" id="flash-back"></div>
        </div>
        <div class="flash-nav">
          <button id="btn-prev">← Anterior</button>
          <button id="btn-next">Siguiente →</button>
        </div>
        <p class="flash-hint" style="text-align:center">Toca la tarjeta para ver la respuesta.</p>
      </div>

      <div id="estado-vacio" class="estado-msg">Elige una materia para empezar a repasar.</div>

    </div>
  </section>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>

  <script src="js/repaso_examen2.js"></script>
  <script>
    function normalizarMateria(s) {
      return (s || '').toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, ' ').trim();
    }

    // Las materias del simulador y del examen2 no siempre coinciden en nombre exacto;
    // este mapa une variantes equivalentes a la materia tal como aparece en REPASO_DATA.
    const ALIAS_MATERIA = {
      'formacion civica y etica': 'formacion civica',
      'habilidad matematica': 'habilidad matematica',
      'habilidad verbal': 'habilidad verbal',
    };

    function materiaCanon(s) {
      const n = normalizarMateria(s);
      return ALIAS_MATERIA[n] || n;
    }

    const materias = [];
    const conteoMaterias = {};
    REPASO_DATA.forEach(q => {
      if (!conteoMaterias[q.s]) { materias.push(q.s); conteoMaterias[q.s] = 0; }
      conteoMaterias[q.s]++;
    });

    let preguntasActuales = [];
    let indiceActual = 0;
    let volteada = false;

    function renderChips() {
      const div = document.getElementById('materia-chips');
      div.innerHTML = materias.map(m =>
        `<button class="materia-chip" data-materia="${m}">${m}<span class="conteo">(${conteoMaterias[m]})</span></button>`
      ).join('');
      div.querySelectorAll('.materia-chip').forEach(btn => {
        btn.addEventListener('click', () => seleccionarMateria(btn.dataset.materia));
      });
    }

    function seleccionarMateria(materia) {
      document.querySelectorAll('.materia-chip').forEach(b => {
        b.classList.toggle('activo', b.dataset.materia === materia);
      });
      preguntasActuales = REPASO_DATA.filter(q => q.s === materia);
      indiceActual = 0;
      document.getElementById('estado-vacio').style.display = 'none';
      document.getElementById('flash-area').classList.add('activo');
      mostrarTarjeta();

      const url = new URL(window.location);
      url.searchParams.set('materia', materia);
      window.history.replaceState({}, '', url);
    }

    function esc(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
    function nl2br(s) { return esc(s).replace(/\n/g, '<br>'); }

    function mostrarTarjeta() {
      const card = document.getElementById('card');
      card.classList.remove('volteada');
      volteada = false;

      const q = preguntasActuales[indiceActual];
      const front = document.getElementById('flash-front');
      const back = document.getElementById('flash-back');

      let frontHtml = `<div class="flash-materia">${esc(q.s)} · Reactivo ${q.n}</div>`;
      if (q.p) frontHtml += `<div style="font-size:.78rem;opacity:.85;margin-bottom:.6rem">${q.p.replace(/<[^>]+>/g, ' ').substring(0, 220)}…</div>`;
      frontHtml += `<p class="flash-pregunta">${nl2br(q.t)}</p>`;
      if (q.img) frontHtml += `<img class="flash-img" src="img/examen2/${q.img}" alt="Figura del reactivo ${q.n}">`;
      if (q.o && !q.fig) {
        frontHtml += '<ul class="flash-opciones">' +
          Object.entries(q.o).map(([letra, texto]) => `<li><strong>${letra})</strong> ${esc(texto)}</li>`).join('') +
          '</ul>';
      }
      front.innerHTML = frontHtml;

      const letraCorrecta = q.a;
      const textoCorrecta = q.o ? q.o[letraCorrecta] : '';
      back.innerHTML = `
        <div class="flash-materia">Respuesta correcta</div>
        <div class="flash-respuesta-letra">${letraCorrecta}</div>
        <p class="flash-pregunta">${esc(textoCorrecta)}</p>
        <p class="flash-hint">Toca de nuevo para volver a la pregunta.</p>
      `;

      document.getElementById('flash-contador').textContent = `${indiceActual + 1} / ${preguntasActuales.length}`;
      document.getElementById('btn-prev').disabled = indiceActual === 0;
      document.getElementById('btn-next').disabled = indiceActual === preguntasActuales.length - 1;
    }

    document.getElementById('card').addEventListener('click', () => {
      volteada = !volteada;
      document.getElementById('card').classList.toggle('volteada', volteada);
    });

    document.getElementById('btn-next').addEventListener('click', (e) => {
      e.stopPropagation();
      if (indiceActual < preguntasActuales.length - 1) { indiceActual++; mostrarTarjeta(); }
    });
    document.getElementById('btn-prev').addEventListener('click', (e) => {
      e.stopPropagation();
      if (indiceActual > 0) { indiceActual--; mostrarTarjeta(); }
    });

    renderChips();

    // Si llega ?materia=X desde el panel, preseleccionar (comparando forma normalizada).
    const params = new URLSearchParams(window.location.search);
    const materiaUrl = params.get('materia');
    if (materiaUrl) {
      const canon = materiaCanon(materiaUrl);
      const match = materias.find(m => materiaCanon(m) === canon);
      if (match) seleccionarMateria(match);
    }
  </script>
</body>
</html>
