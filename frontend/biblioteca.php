<?php require_once __DIR__ . '/../backend/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS, Biblioteca de guías</title>
  <link rel="stylesheet" href="css/estilos.css?v=3">
  <style>
    .biblio-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.2rem; }
    .recurso-card { padding: 1.4rem; border-left: 4px solid var(--bordo); display: flex; flex-direction: column; gap: .6rem; }
    .recurso-materia {
      font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 700;
      color: var(--bordo); background: rgba(2,48,71,.08); padding: .2rem .55rem;
      border-radius: 20px; display: inline-block; width: fit-content;
    }
    .recurso-titulo { font-family: var(--font-display); font-size: 1rem; color: var(--texto); }
    .recurso-desc { font-size: .82rem; color: var(--texto-2); line-height: 1.5; flex: 1; }
    .recurso-tipo { font-size: .72rem; color: var(--texto-2); }
    .recurso-admin-actions { display: flex; gap: .4rem; margin-top: .4rem; }
    .estado-msg { padding: 2rem; text-align: center; color: var(--texto-2); font-size: .9rem; }
    .admin-panel { background: var(--fondo-card); border-radius: var(--radio-lg); box-shadow: var(--sombra);
      border-top: 3px solid var(--acento); padding: 1.5rem; margin-bottom: 1.5rem; }
    .admin-panel h3 { font-family: var(--font-display); color: var(--bordo); margin-bottom: 1rem; }
    .admin-form { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .admin-form .full { grid-column: 1 / -1; }
    .admin-form label { font-size: .72rem; font-weight: 600; color: var(--texto-2); text-transform: uppercase; letter-spacing: .06em; display: block; margin-bottom: .3rem; }
    .admin-form input, .admin-form select, .admin-form textarea {
      width: 100%; padding: .55rem .8rem; border-radius: var(--radio); border: 1.5px solid var(--borde);
      font-family: var(--font-body); font-size: .88rem;
    }
  </style>
</head>
<body class="page-wrapper">

  <?php if (tieneRol('admin')): ?>
    <?php $navAdminRoot = ''; $navAdminDir = 'admin/'; require 'includes/navbar_admin.php'; ?>
  <?php else: ?>
    <?php require 'includes/navbar.php'; ?>
  <?php endif; ?>

  <div class="page-header">
    <div class="container">
      <p class="page-header-eyebrow">Innovación · Acervo histórico</p>
      <h1>Biblioteca de guías y libros</h1>
      <p>Material de estudio que quedó disperso al desaparecer COMIPEMS, reunido en un solo lugar.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">

      <?php if (tieneRol('admin')): ?>
      <div class="admin-panel" id="panel-admin">
        <h3>Administrar recursos</h3>
        <form id="form-recurso" class="admin-form">
          <input type="hidden" id="r-id">
          <div>
            <label>Título</label>
            <input type="text" id="r-titulo" required>
          </div>
          <div>
            <label>Materia</label>
            <input type="text" id="r-materia" required placeholder="Ej. Matemáticas">
          </div>
          <div class="full">
            <label>Descripción</label>
            <textarea id="r-descripcion" rows="2"></textarea>
          </div>
          <div>
            <label>Tipo</label>
            <select id="r-tipo">
              <option value="enlace">Enlace</option>
              <option value="pdf">PDF</option>
            </select>
          </div>
          <div>
            <label>URL</label>
            <input type="text" id="r-url" required placeholder="https://... o ruta a PDF">
          </div>
          <div class="full d-flex gap-1">
            <button type="submit" class="btn btn-bordo btn-sm">Guardar recurso</button>
            <button type="button" id="btn-cancelar-edit" class="btn btn-sm" style="background:var(--fondo);border:1.5px solid var(--borde);display:none">Cancelar edición</button>
          </div>
        </form>
      </div>
      <?php endif; ?>

      <div class="filters-panel mb-3">
        <div class="filter-group">
          <label>Filtrar por materia</label>
          <select id="filtro-materia" class="filter-select"></select>
        </div>
      </div>

      <div id="lista-recursos">
        <div class="estado-msg"><span class="spinner"></span> Cargando recursos…</div>
      </div>
    </div>
  </section>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>

  <script>
    const ES_ADMIN = <?= tieneRol('admin') ? 'true' : 'false' ?>;
    let recursos = [];

    async function cargar() {
      const div = document.getElementById('lista-recursos');
      div.innerHTML = '<div class="estado-msg"><span class="spinner"></span> Cargando…</div>';
      try {
        const resp = await fetch('../backend/api/recursos.php');
        const json = await resp.json();
        if (json.status !== 'ok') throw new Error('Error al cargar recursos');
        recursos = json.datos;
        poblarFiltro();
        render();
      } catch (err) {
        div.innerHTML = `<div class="estado-msg">Error de conexión.<br><small>${err.message}</small></div>`;
      }
    }

    function poblarFiltro() {
      const sel = document.getElementById('filtro-materia');
      const materias = [...new Set(recursos.map(r => r.materia))].sort();
      sel.innerHTML = '<option value="">Todas las materias</option>' +
        materias.map(m => `<option value="${m}">${m}</option>`).join('');
    }

    function render() {
      const div = document.getElementById('lista-recursos');
      const materia = document.getElementById('filtro-materia').value;
      const filtrados = materia ? recursos.filter(r => r.materia === materia) : recursos;

      if (filtrados.length === 0) {
        div.innerHTML = '<div class="estado-msg">Sin recursos para esta materia todavía.</div>';
        return;
      }

      div.innerHTML = '<div class="biblio-grid">' + filtrados.map(r => `
        <div class="card recurso-card">
          <span class="recurso-materia">${r.materia}</span>
          <div class="recurso-titulo">${r.titulo}</div>
          <div class="recurso-desc">${r.descripcion || ''}</div>
          <div class="recurso-tipo">${r.tipo === 'pdf' ? 'PDF' : 'Enlace'}</div>
          <a href="${r.url}" target="_blank" rel="noopener" class="btn btn-bordo btn-sm">Abrir recurso →</a>
          ${ES_ADMIN ? `
          <div class="recurso-admin-actions">
            <button class="btn btn-sm" style="background:var(--fondo);border:1.5px solid var(--borde)" onclick="editar(${r.id})">Editar</button>
            <button class="btn btn-sm" style="background:#FFEBEE;color:#C62828" onclick="borrar(${r.id})">Borrar</button>
          </div>` : ''}
        </div>
      `).join('') + '</div>';
    }

    document.getElementById('filtro-materia').addEventListener('change', render);

    function editar(id) {
      const r = recursos.find(x => x.id === id);
      if (!r) return;
      document.getElementById('r-id').value = r.id;
      document.getElementById('r-titulo').value = r.titulo;
      document.getElementById('r-materia').value = r.materia;
      document.getElementById('r-descripcion').value = r.descripcion || '';
      document.getElementById('r-tipo').value = r.tipo;
      document.getElementById('r-url').value = r.url;
      document.getElementById('btn-cancelar-edit').style.display = 'inline-block';
      document.getElementById('panel-admin').scrollIntoView({ behavior: 'smooth' });
    }

    async function borrar(id) {
      if (!confirm('¿Borrar este recurso de la biblioteca?')) return;
      const resp = await fetch('../backend/api/recursos.php?id=' + id, { method: 'DELETE' });
      const json = await resp.json();
      if (json.status === 'ok') cargar();
      else alert(json.mensaje || 'No se pudo borrar.');
    }

    if (ES_ADMIN) {
      document.getElementById('btn-cancelar-edit').addEventListener('click', () => {
        document.getElementById('form-recurso').reset();
        document.getElementById('r-id').value = '';
        document.getElementById('btn-cancelar-edit').style.display = 'none';
      });

      document.getElementById('form-recurso').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('r-id').value;
        const payload = {
          titulo: document.getElementById('r-titulo').value.trim(),
          materia: document.getElementById('r-materia').value.trim(),
          descripcion: document.getElementById('r-descripcion').value.trim(),
          tipo: document.getElementById('r-tipo').value,
          url: document.getElementById('r-url').value.trim(),
        };
        if (id) payload.id = parseInt(id);

        const resp = await fetch('../backend/api/recursos.php', {
          method: id ? 'PUT' : 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const json = await resp.json();
        if (json.status === 'ok') {
          e.target.reset();
          document.getElementById('r-id').value = '';
          document.getElementById('btn-cancelar-edit').style.display = 'none';
          cargar();
        } else {
          alert(json.mensaje || 'No se pudo guardar.');
        }
      });
    }

    cargar();
  </script>
</body>
</html>
