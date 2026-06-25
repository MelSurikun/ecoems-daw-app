<?php
require_once __DIR__ . '/../../backend/auth.php';
$usuario = requiereRolPagina('admin', '../index.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ECOEMS, Gestión del examen</title>
  <link rel="stylesheet" href="../css/estilos.css?v=3">
  <style>
    .toolbar { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .toolbar .form-group { min-width: 180px; }
    .toolbar .form-group label { font-size: .72rem; font-weight: 600; color: var(--texto-2); text-transform: uppercase; letter-spacing: .06em; display: block; margin-bottom: .3rem; }
    .toolbar .form-group select, .toolbar .form-group input { width: 100%; padding: .5rem .75rem; border-radius: var(--radio); border: 1.5px solid var(--borde); font-family: var(--font-body); font-size: .85rem; }

    .reactivo-card { background: var(--fondo-card); border-radius: var(--radio-lg); box-shadow: var(--sombra); margin-bottom: .75rem; overflow: hidden; }
    .reactivo-card.con-reporte { border-left: 4px solid var(--acento); }
    .reactivo-card:not(.con-reporte) { border-left: 4px solid var(--bordo); }
    .reactivo-header { display: flex; align-items: center; gap: 1rem; padding: .85rem 1.2rem; cursor: pointer; transition: var(--trans); }
    .reactivo-header:hover { background: rgba(2,48,71,.03); }
    .reactivo-header .num { flex: none; width: 32px; height: 32px; border-radius: 8px; background: var(--bordo); color: #fff; display: grid; place-items: center; font-weight: 700; font-size: .82rem; }
    .reactivo-header .materia { font-size: .72rem; font-weight: 600; color: var(--bordo); text-transform: uppercase; letter-spacing: .06em; background: rgba(2,48,71,.08); padding: .2rem .6rem; border-radius: 4px; min-width: 70px; text-align: center; }
    .reactivo-header .preview { flex: 1; font-size: .85rem; color: var(--texto); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .reactivo-header .preview.oculto-txt { text-decoration: line-through; color: var(--texto-2); }
    .reactivo-header .respuesta { font-size: .78rem; font-weight: 700; color: var(--acento); background: rgba(251,133,0,.12); padding: .2rem .6rem; border-radius: 4px; }
    .reactivo-header .estado { font-size: .7rem; font-weight: 700; text-transform: uppercase; padding: .2rem .5rem; border-radius: 4px; }
    .reactivo-header .estado.oculto { background: #FFEBEE; color: #C62828; }
    .reactivo-header .estado.corregido { background: #FFF3E0; color: #E65100; }

    .reactivo-body { display: none; padding: 1.2rem; border-top: 1px solid var(--borde); }
    .reactivo-body.open { display: block; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-grid .full { grid-column: 1 / -1; }
    .form-grid label { font-size: .72rem; font-weight: 600; color: var(--texto-2); text-transform: uppercase; letter-spacing: .06em; display: block; margin-bottom: .25rem; }
    .form-grid select, .form-grid textarea { width: 100%; padding: .5rem .75rem; border-radius: var(--radio); border: 1.5px solid var(--borde); font-family: var(--font-body); font-size: .85rem; }
    .form-grid textarea { resize: vertical; min-height: 50px; }
    .pregunta-original { background: var(--fondo); border-radius: var(--radio); padding: .8rem 1rem; font-size: .85rem; color: var(--texto); margin-bottom: 1rem; }
    .pregunta-original .opciones-lista { margin-top: .5rem; font-size: .8rem; color: var(--texto-2); }
    .pregunta-original .opciones-lista .correcta { color: #1b7a43; font-weight: 700; }
    .chk-row { display: flex; align-items: center; gap: .5rem; font-size: .85rem; font-weight: 500; }
    .acciones { margin-top: 1rem; display: flex; gap: .5rem; }
    .toast { position: fixed; bottom: 2rem; right: 2rem; background: var(--bordo); color: #fff; padding: .8rem 1.5rem; border-radius: var(--radio); box-shadow: var(--sombra-lg); font-size: .85rem; z-index: 999; opacity: 0; transform: translateY(10px); transition: all .3s ease; pointer-events: none; }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast.ok { background: #2E7D32; }
    .toast.error { background: #C62828; }
    @media (max-width: 700px) { .form-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body class="page-wrapper">
  <?php require '../includes/navbar_admin.php'; ?>
  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Administración</p>
      <h1>Gestión del examen</h1>
      <p>Reporta y corrige reactivos del simulador (128 preguntas tipo COMIPEMS). Los cambios se aplican directo al examen del aspirante.</p>
    </div>
  </div>
  <section class="section">
    <div class="container">
      <div class="toolbar">
        <div class="form-group">
          <label>Filtrar por materia</label>
          <select id="filtro-materia"><option value="">Todas</option></select>
        </div>
        <div class="form-group">
          <label>Buscar</label>
          <input type="text" id="filtro-buscar" placeholder="Palabra clave…">
        </div>
        <div class="form-group" style="display:flex;align-items:end;gap:.5rem">
          <label style="display:flex;align-items:center;gap:.4rem;text-transform:none;font-weight:400;font-size:.82rem">
            <input type="checkbox" id="chk-solo-reportados"> Solo con reporte
          </label>
        </div>
      </div>

      <div id="lista"></div>
    </div>
  </section>
  <div id="toast" class="toast"></div>
  <footer class="site-footer">
    <p>Panel de Administración <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; 2026</p>
  </footer>

  <script src="../js/examen1_data.js"></script>
  <script>
    const LETRAS = ['A','B','C','D'];
    let overrides = {};
    let editandoN = null;

    function toast(msg, tipo = 'ok') {
      const el = document.getElementById('toast');
      el.textContent = msg; el.className = 'toast ' + tipo + ' show';
      setTimeout(() => el.classList.remove('show'), 3000);
    }

    function esc(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

    async function cargarReportes() {
      try {
        const resp = await fetch('../../backend/api/admin/reportes.php');
        const json = await resp.json();
        if (json.status === 'ok') overrides = json.datos || {};
      } catch (e) { overrides = {}; }
      render();
    }

    async function guardarReportes() {
      try {
        const resp = await fetch('../../backend/api/admin/reportes.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(overrides),
        });
        const json = await resp.json();
        if (json.status !== 'ok') throw new Error(json.mensaje || 'No se pudo guardar.');
        return true;
      } catch (err) {
        toast(err.message, 'error');
        return false;
      }
    }

    function render() {
      const div = document.getElementById('lista');
      const materia = document.getElementById('filtro-materia').value;
      const q = document.getElementById('filtro-buscar').value.toLowerCase().trim();
      const soloReportados = document.getElementById('chk-solo-reportados').checked;

      let filtrados = EXAMEN1_DATA;
      if (materia) filtrados = filtrados.filter(r => r.s === materia);
      if (q) filtrados = filtrados.filter(r => r.q.toLowerCase().includes(q) || r.s.toLowerCase().includes(q));
      if (soloReportados) filtrados = filtrados.filter(r => overrides[r.n]);

      if (filtrados.length === 0) {
        div.innerHTML = '<div class="estado-msg" style="padding:3rem;text-align:center;color:var(--texto-2)">Sin reactivos para mostrar.</div>';
        return;
      }

      div.innerHTML = filtrados.map(r => {
        const ov = overrides[r.n];
        const abierto = editandoN === r.n;
        const preview = r.q.length > 90 ? r.q.slice(0, 90) + '…' : r.q;
        const respuestaActual = (ov && ov.respuesta) ? ov.respuesta : r.a;
        let badges = '';
        if (ov && ov.oculto) badges += '<span class="estado oculto">Oculta</span>';
        if (ov && ov.respuesta) badges += '<span class="estado corregido">Corregida</span>';

        return `<div class="reactivo-card ${ov ? 'con-reporte' : ''}">
          <div class="reactivo-header" onclick="toggleBody(${r.n})">
            <span class="num">${r.n}</span>
            <span class="materia">${esc(r.s)}</span>
            <span class="preview ${ov && ov.oculto ? 'oculto-txt' : ''}">${esc(preview)}</span>
            <span class="respuesta">${respuestaActual}</span>
            ${badges}
          </div>
          <div class="reactivo-body ${abierto ? 'open' : ''}" id="body-${r.n}">
            ${abierto ? renderForm(r, ov) : ''}
          </div>
        </div>`;
      }).join('');
    }

    function renderForm(r, ov) {
      const div = document.createElement('div');
      const respuestaCorregida = (ov && ov.respuesta) || '';
      const oculto = !!(ov && ov.oculto);
      const nota = (ov && ov.nota) || '';

      div.innerHTML = `
        <div class="pregunta-original">
          <strong>Pregunta #${r.n}</strong> — ${esc(r.q)}
          <div class="opciones-lista">
            ${r.o.map((opt, i) => `<div ${LETRAS[i] === r.a ? 'class="correcta"' : ''}>${LETRAS[i]}) ${esc(opt)}${LETRAS[i] === r.a ? ' (respuesta original)' : ''}</div>`).join('')}
          </div>
        </div>
        <div class="form-grid">
          <div class="chk-row full">
            <input type="checkbox" class="f-oculto" id="oculto-${r.n}" ${oculto ? 'checked' : ''}>
            <label for="oculto-${r.n}" style="margin:0;text-transform:none;font-weight:500">Ocultar este reactivo del simulador</label>
          </div>
          <div>
            <label>Corregir respuesta correcta</label>
            <select class="f-respuesta">
              <option value="">Sin corrección (usar la original: ${r.a})</option>
              ${LETRAS.map(l => `<option value="${l}" ${respuestaCorregida === l ? 'selected' : ''}>${l}</option>`).join('')}
            </select>
          </div>
          <div class="full">
            <label>Nota / motivo del reporte (opcional)</label>
            <textarea class="f-nota" rows="2" placeholder="Ej. La opción correcta es C, no B.">${esc(nota)}</textarea>
          </div>
        </div>
        <div class="acciones">
          <button class="btn btn-bordo btn-sm btn-guardar">Guardar</button>
          ${ov ? '<button class="btn btn-sm btn-restablecer" style="background:#FFEBEE;color:#C62828">Restablecer original</button>' : ''}
          <button class="btn btn-sm btn-cancelar" style="background:var(--fondo);border:1.5px solid var(--borde)">Cancelar</button>
        </div>
      `;

      div.querySelector('.btn-guardar').addEventListener('click', () => guardarUno(r.n, div));
      div.querySelector('.btn-cancelar').addEventListener('click', cerrarEdicion);
      const btnRestablecer = div.querySelector('.btn-restablecer');
      if (btnRestablecer) btnRestablecer.addEventListener('click', () => restablecerUno(r.n));

      return div.outerHTML;
    }

    function toggleBody(n) {
      if (editandoN === n) { cerrarEdicion(); return; }
      editandoN = n;
      render();
      setTimeout(() => {
        const el = document.getElementById('body-' + n);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }, 50);
    }

    function cerrarEdicion() { editandoN = null; render(); }

    async function guardarUno(n, form) {
      const oculto = form.querySelector('.f-oculto').checked;
      const respuesta = form.querySelector('.f-respuesta').value;
      const nota = form.querySelector('.f-nota').value.trim();

      if (!oculto && !respuesta && !nota) {
        delete overrides[n];
      } else {
        overrides[n] = {};
        if (oculto) overrides[n].oculto = true;
        if (respuesta) overrides[n].respuesta = respuesta;
        if (nota) overrides[n].nota = nota;
      }

      const ok = await guardarReportes();
      if (ok) {
        toast('Reactivo #' + n + ' actualizado.');
        editandoN = null;
        render();
      }
    }

    async function restablecerUno(n) {
      delete overrides[n];
      const ok = await guardarReportes();
      if (ok) {
        toast('Reactivo #' + n + ' restablecido a su versión original.');
        editandoN = null;
        render();
      }
    }

    document.getElementById('filtro-materia').addEventListener('change', render);
    document.getElementById('chk-solo-reportados').addEventListener('change', render);
    document.getElementById('filtro-buscar').addEventListener('input', () => { clearTimeout(window._bf); window._bf = setTimeout(render, 300); });

    // Poblar filtro de materias en el orden en que aparecen en el examen
    const materiasUnicas = [];
    EXAMEN1_DATA.forEach(q => { if (!materiasUnicas.includes(q.s)) materiasUnicas.push(q.s); });
    const sel = document.getElementById('filtro-materia');
    materiasUnicas.forEach(m => { sel.innerHTML += `<option value="${m}">${m}</option>`; });

    cargarReportes();
  </script>
</body>
</html>
