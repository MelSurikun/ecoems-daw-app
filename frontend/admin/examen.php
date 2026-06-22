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
  <link rel="stylesheet" href="../css/estilos.css?v=2">
  <style>
    .toolbar { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .toolbar .form-group { min-width: 180px; }
    .toolbar .form-group label { font-size: .72rem; font-weight: 600; color: var(--texto-2); text-transform: uppercase; letter-spacing: .06em; display: block; margin-bottom: .3rem; }
    .toolbar .form-group select, .toolbar .form-group input { width: 100%; padding: .5rem .75rem; border-radius: var(--radio); border: 1.5px solid var(--borde); font-family: var(--font-body); font-size: .85rem; }

    .reactivo-card { background: var(--fondo-card); border-radius: var(--radio-lg); box-shadow: var(--sombra); margin-bottom: .75rem; overflow: hidden; }
    .reactivo-header { display: flex; align-items: center; gap: 1rem; padding: .85rem 1.2rem; cursor: pointer; transition: var(--trans); border-left: 4px solid var(--bordo); }
    .reactivo-header:hover { background: rgba(2,48,71,.03); }
    .reactivo-header .num { flex: none; width: 32px; height: 32px; border-radius: 8px; background: var(--bordo); color: #fff; display: grid; place-items: center; font-weight: 700; font-size: .82rem; }
    .reactivo-header .materia { font-size: .72rem; font-weight: 600; color: var(--bordo); text-transform: uppercase; letter-spacing: .06em; background: rgba(2,48,71,.08); padding: .2rem .6rem; border-radius: 4px; min-width: 70px; text-align: center; }
    .reactivo-header .preview { flex: 1; font-size: .85rem; color: var(--texto); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .reactivo-header .estado { font-size: .7rem; font-weight: 700; text-transform: uppercase; padding: .2rem .5rem; border-radius: 4px; }
    .reactivo-header .estado.activo { background: #E8F5E9; color: #2E7D32; }
    .reactivo-header .estado.inactivo { background: #FFEBEE; color: #C62828; }
    .reactivo-header .respuesta { font-size: .78rem; font-weight: 700; color: var(--acento); background: rgba(251,133,0,.12); padding: .2rem .6rem; border-radius: 4px; }

    .reactivo-body { display: none; padding: 1.2rem; border-top: 1px solid var(--borde); }
    .reactivo-body.open { display: block; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-grid .full { grid-column: 1 / -1; }
    .form-grid label { font-size: .72rem; font-weight: 600; color: var(--texto-2); text-transform: uppercase; letter-spacing: .06em; display: block; margin-bottom: .25rem; }
    .form-grid input, .form-grid select, .form-grid textarea { width: 100%; padding: .5rem .75rem; border-radius: var(--radio); border: 1.5px solid var(--borde); font-family: var(--font-body); font-size: .85rem; }
    .form-grid textarea { resize: vertical; min-height: 60px; }
    .opcion-row { display: flex; align-items: center; gap: .5rem; margin-bottom: .4rem; }
    .opcion-row .letra { font-weight: 700; font-size: .85rem; color: var(--bordo); min-width: 20px; }
    .opcion-row input { flex: 1; }
    .opcion-row .check { width: 18px; height: 18px; accent-color: var(--acento); cursor: pointer; }
    .btn-icon { background: none; border: none; font-size: 1rem; cursor: pointer; opacity: .5; transition: var(--trans); padding: .25rem; }
    .btn-icon:hover { opacity: 1; }
    .acciones { margin-top: 1rem; display: flex; gap: .5rem; }
    .toast { position: fixed; bottom: 2rem; right: 2rem; background: var(--bordo); color: #fff; padding: .8rem 1.5rem; border-radius: var(--radio); box-shadow: var(--sombra-lg); font-size: .85rem; z-index: 999; opacity: 0; transform: translateY(10px); transition: all .3s ease; pointer-events: none; }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast.ok { background: #2E7D32; }
    .toast.error { background: #C62828; }
    @media (max-width: 700px) { .form-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body class="page-wrapper">
  <?php require '../includes/navbar.php'; ?>
  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Administración</p>
      <h1>Gestión del examen</h1>
      <p>Administra los reactivos del simulador. 128 preguntas tipo COMIPEMS.</p>
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
            <input type="checkbox" id="chk-inactivos"> Ver inactivos
          </label>
        </div>
        <button class="btn btn-bordo btn-sm" id="btn-nuevo" style="margin-left:auto">+ Nuevo reactivo</button>
      </div>

      <div id="lista"></div>

      <!-- Formulario de edición/creación (oculto dentro de cada card) -->
      <div id="form-template" style="display:none">
        <div class="form-grid">
          <div><label>Número</label><input type="number" class="f-num" min="1" max="999"></div>
          <div><label>Materia</label>
            <select class="f-materia">
              <option value="Español">Español</option>
              <option value="Matemáticas">Matemáticas</option>
              <option value="Historia">Historia</option>
              <option value="Geografía">Geografía</option>
              <option value="Formación Cívica y Ética">Formación Cívica y Ética</option>
              <option value="Física">Física</option>
              <option value="Química">Química</option>
              <option value="Biología">Biología</option>
              <option value="Habilidad Verbal">Habilidad Verbal</option>
              <option value="Habilidad Matemática">Habilidad Matemática</option>
            </select>
          </div>
          <div class="full"><label>Pregunta</label><textarea class="f-pregunta" rows="2"></textarea></div>
          <div class="full"><label>Contexto (texto previo, opcional)</label><textarea class="f-contexto" rows="2"></textarea></div>
          <div class="full"><label>Texto de lectura (pasaje, opcional)</label><textarea class="f-passage" rows="3"></textarea></div>
          <div class="full">
            <label>Opciones</label>
            <div class="f-opciones"></div>
            <div style="font-size:.72rem;color:var(--texto-2);margin-top:.3rem">Selecciona el círculo de la opción correcta.</div>
          </div>
          <div><label>Clave de figura (opcional)</label><input class="f-figura" placeholder="Ej. q36_tabla"></div>
          <div><label>Activo</label><select class="f-activo"><option value="1">Sí</option><option value="0">No</option></select></div>
        </div>
        <div class="acciones">
          <button class="btn btn-bordo btn-sm btn-guardar">Guardar</button>
          <button class="btn btn-sm btn-cancelar" style="background:var(--fondo);border:1.5px solid var(--borde)">Cancelar</button>
        </div>
      </div>
    </div>
  </section>
  <div id="toast" class="toast"></div>
  <footer class="site-footer">
    <p>Panel de Administración <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; 2026</p>
  </footer>
  <script>
    const MATERIAS = ['Español','Matemáticas','Historia','Geografía','Formación Cívica y Ética','Física','Química','Biología','Habilidad Verbal','Habilidad Matemática'];
    let reactivos = [];
    let editandoId = null;

    function toast(msg, tipo = 'ok') {
      const el = document.getElementById('toast');
      el.textContent = msg; el.className = 'toast ' + tipo + ' show';
      setTimeout(() => el.classList.remove('show'), 3000);
    }

    async function cargar() {
      const params = new URLSearchParams();
      const materia = document.getElementById('filtro-materia').value;
      if (materia) params.set('materia', materia);
      if (document.getElementById('chk-inactivos').checked) params.set('inactivos', '1');
      try {
        const resp = await fetch('../backend/api/admin/reactivos.php?' + params.toString());
        const json = await resp.json();
        if (json.status !== 'ok') throw new Error(json.mensaje);
        reactivos = json.datos;
        render();
      } catch (err) {
        document.getElementById('lista').innerHTML = '<div class="estado-msg" style="padding:2rem;text-align:center">Error al cargar reactivos.</div>';
      }
    }

    function render() {
      const div = document.getElementById('lista');
      const q = document.getElementById('filtro-buscar').value.toLowerCase().trim();
      let filtrados = reactivos;
      if (q) filtrados = filtrados.filter(r => r.pregunta.toLowerCase().includes(q) || (r.materia || '').toLowerCase().includes(q));

      if (filtrados.length === 0) {
        div.innerHTML = '<div class="estado-msg" style="padding:3rem;text-align:center;color:var(--texto-2)">Sin reactivos para mostrar.</div>';
        return;
      }

      div.innerHTML = filtrados.map(r => {
        const abierto = editandoId === r.id;
        const preview = r.pregunta.length > 80 ? r.pregunta.slice(0, 80) + '…' : r.pregunta;
        return `<div class="reactivo-card">
          <div class="reactivo-header" onclick="toggleBody(${r.id})">
            <span class="num">${r.numero}</span>
            <span class="materia">${r.materia}</span>
            <span class="preview">${preview}</span>
            <span class="respuesta">${r.respuesta}</span>
            <span class="estado ${r.activo == 1 ? 'activo' : 'inactivo'}">${r.activo == 1 ? 'Activo' : 'Inactivo'}</span>
          </div>
          <div class="reactivo-body ${abierto ? 'open' : ''}" id="body-${r.id}">
            ${abierto ? renderForm(r) : ''}
          </div>
        </div>`;
      }).join('');
    }

    function renderForm(r) {
      const tmpl = document.getElementById('form-template').innerHTML;
      const div = document.createElement('div');
      div.innerHTML = tmpl;
      const form = div.firstElementChild;

      form.querySelector('.f-num').value = r.numero;
      form.querySelector('.f-materia').value = r.materia;
      form.querySelector('.f-pregunta').value = r.pregunta;
      form.querySelector('.f-contexto').value = r.contexto || '';
      form.querySelector('.f-passage').value = r.passage || '';
      form.querySelector('.f-figura').value = r.figura_clave || '';
      form.querySelector('.f-activo').value = r.activo;

      const opsContainer = form.querySelector('.f-opciones');
      const opciones = r.opciones || ['','','',''];
      const letras = ['A','B','C','D'];
      opsContainer.innerHTML = opciones.map((opt, i) => `
        <div class="opcion-row">
          <span class="letra">${letras[i]})</span>
          <input type="text" class="f-opc" data-idx="${i}" value="${esc(opt)}" placeholder="Opción ${letras[i]}">
          <input type="radio" class="check" name="resp-${r.id}" value="${letras[i]}" ${r.respuesta === letras[i] ? 'checked' : ''}>
        </div>
      `).join('');

      form.querySelector('.btn-guardar').addEventListener('click', () => guardar(r.id, form));
      form.querySelector('.btn-cancelar').addEventListener('click', () => cerrarEdicion());

      return form.outerHTML;
    }

    function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    function toggleBody(id) {
      const body = document.getElementById('body-' + id);
      if (body && body.classList.contains('open')) {
        cerrarEdicion();
        return;
      }
      abrirEdicion(id);
    }

    function abrirEdicion(id) {
      editandoId = id;
      render();
      setTimeout(() => {
        const el = document.getElementById('body-' + id);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }, 50);
    }

    function cerrarEdicion() {
      editandoId = null;
      render();
    }

    async function guardar(id, form) {
      const opciones = [];
      form.querySelectorAll('.f-opc').forEach(el => opciones.push(el.value.trim()));
      const respuesta = form.querySelector('.check:checked');
      if (!respuesta || opciones.some(o => !o)) {
        toast('Completa todas las opciones y selecciona la respuesta correcta.', 'error');
        return;
      }

      const payload = {
        id, numero: parseInt(form.querySelector('.f-num').value),
        materia: form.querySelector('.f-materia').value,
        pregunta: form.querySelector('.f-pregunta').value.trim(),
        opciones, respuesta: respuesta.value,
        contexto: form.querySelector('.f-contexto').value.trim() || null,
        passage: form.querySelector('.f-passage').value.trim() || null,
        figura_clave: form.querySelector('.f-figura').value.trim() || null,
        activo: parseInt(form.querySelector('.f-activo').value),
      };

      try {
        const resp = await fetch('../backend/api/admin/reactivos.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
        const json = await resp.json();
        if (json.status !== 'ok') throw new Error(json.mensaje || 'Error al guardar');
        toast('Reactivo #' + payload.numero + ' guardado.');
        editandoId = null;
        cargar();
      } catch (err) {
        toast(err.message, 'error');
      }
    }

    document.getElementById('btn-nuevo').addEventListener('click', async () => {
      const maxNum = reactivos.length > 0 ? Math.max(...reactivos.map(r => r.numero)) : 0;
      const nuevo = { id: 0, numero: maxNum + 1, materia: 'Español', pregunta: '', opciones: ['','','',''], respuesta: 'A', contexto: '', passage: '', figura_clave: '', activo: 1 };
      try {
        const resp = await fetch('../backend/api/admin/reactivos.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(nuevo),
        });
        const json = await resp.json();
        if (json.status !== 'ok') throw new Error(json.mensaje || 'Error al crear');
        toast('Reactivo #' + nuevo.numero + ' creado.');
        cargar();
      } catch (err) {
        toast(err.message, 'error');
      }
    });

    document.getElementById('filtro-materia').addEventListener('change', cargar);
    document.getElementById('chk-inactivos').addEventListener('change', cargar);
    document.getElementById('filtro-buscar').addEventListener('input', () => { clearTimeout(window._bf); window._bf = setTimeout(render, 300); });

    // Poblar filtro de materias
    const sel = document.getElementById('filtro-materia');
    MATERIAS.forEach(m => { sel.innerHTML += `<option value="${m}">${m}</option>`; });

    cargar();
  </script>
</body>
</html>
