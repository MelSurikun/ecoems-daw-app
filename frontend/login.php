<?php
require_once __DIR__ . '/../backend/auth.php';
$modoInicial = ($_GET['modo'] ?? '') === 'registro' ? 'registro' : 'login';
$next = $_GET['next'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal ECOEMS, Acceso</title>
  <link rel="stylesheet" href="css/estilos.css?v=3">
  <style>
    .auth-page-body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background: var(--fondo);
    }
    .auth-mini-header { display: flex; justify-content: center; padding: 2.2rem 1rem 0; }
    .auth-mini-header a { display: inline-flex; }
    .auth-mini-header svg { height: 38px; width: auto; }
    .auth-main { flex: 1; }
    .auth-section { display: flex; justify-content: center; padding: 1.6rem 1rem 3.5rem; }
    .auth-shell {
      position: relative;
      width: 100%;
      max-width: 860px;
      min-height: 520px;
      background: var(--fondo-card);
      border-radius: var(--radio-lg);
      box-shadow: var(--sombra-lg);
      overflow: hidden;
    }

    /* ── Paneles de formulario ───────────────────────────── */
    .auth-form-pane {
      position: absolute;
      top: 0;
      width: 50%;
      height: 100%;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: .9rem;
      padding: 3rem 3.2rem;
      transition: opacity .45s ease;
    }
    .auth-form-registro { left: 0;   opacity: 0; z-index: 1; pointer-events: none; }
    .auth-form-login    { left: 50%; opacity: 1; z-index: 2; pointer-events: auto; }
    .auth-shell.modo-registro .auth-form-registro { opacity: 1; z-index: 2; pointer-events: auto; }
    .auth-shell.modo-registro .auth-form-login    { opacity: 0; z-index: 1; pointer-events: none; }

    .auth-title {
      font-family: var(--font-display);
      font-size: 1.5rem;
      color: var(--bordo);
      margin-bottom: .2rem;
    }
    .auth-subtitle { font-size: .82rem; color: var(--texto-2); margin-bottom: .6rem; }
    .auth-field { display: flex; flex-direction: column; gap: .3rem; }
    .auth-field label { font-size: .7rem; font-weight: 600; color: var(--texto-2); text-transform: uppercase; letter-spacing: .06em; }
    .auth-field input {
      padding: .6rem .85rem; border-radius: var(--radio); border: 1.5px solid var(--borde);
      font-family: var(--font-body); font-size: .9rem; outline: none; transition: var(--trans);
    }
    .auth-field input:focus { border-color: var(--bordo); }
    .password-wrap { position: relative; display: flex; }
    .password-wrap input { width: 100%; padding-right: 2.6rem; }
    .password-toggle {
      position: absolute; right: .5rem; top: 50%; transform: translateY(-50%);
      background: none; border: none; padding: .3rem; cursor: pointer;
      color: var(--texto-2); display: flex; align-items: center; transition: var(--trans);
    }
    .password-toggle:hover { color: var(--bordo); }
    .auth-msg { font-size: .8rem; padding: .55rem .8rem; border-radius: var(--radio); display: none; }
    .auth-check-row { display: flex; align-items: flex-start; gap: .55rem; margin-top: .8rem; }
    .auth-check-row input { margin-top: .2rem; flex-shrink: 0; accent-color: var(--bordo); cursor: pointer; }
    .auth-check-row label { font-size: .78rem; color: var(--texto-2); line-height: 1.5; }
    .auth-link-modal {
      background: none; border: none; padding: 0; color: var(--bordo); font-weight: 600;
      font-family: var(--font-body); font-size: .78rem; cursor: pointer; text-decoration: underline;
    }
    .auth-link-modal:hover { color: var(--bordo-cl); }

    /* Modales de aviso de privacidad / términos */
    .auth-modal-overlay {
      display: none; position: fixed; inset: 0; background: rgba(1,32,48,.55);
      z-index: 500; align-items: center; justify-content: center; padding: 1.5rem;
    }
    .auth-modal-overlay.show { display: flex; }
    .auth-modal {
      background: var(--fondo-card); border-radius: var(--radio-lg); box-shadow: var(--sombra-lg);
      max-width: 640px; width: 100%; max-height: 85vh; display: flex; flex-direction: column;
      overflow: hidden;
    }
    .auth-modal-header {
      background: var(--bordo); color: #fff; padding: 1.2rem 1.5rem;
      display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;
    }
    .auth-modal-header h2 { font-family: var(--font-display); font-size: 1.15rem; }
    .auth-modal-cerrar {
      background: rgba(255,255,255,.15); border: none; color: #fff; width: 30px; height: 30px;
      border-radius: 50%; font-size: 1.2rem; line-height: 1; cursor: pointer; transition: var(--trans);
    }
    .auth-modal-cerrar:hover { background: rgba(255,255,255,.3); }
    .auth-modal-body { padding: 1.5rem; overflow-y: auto; }
    .auth-modal-body h3 { font-family: var(--font-display); color: var(--bordo); font-size: .95rem; margin: 1.1rem 0 .4rem; }
    .auth-modal-body h3:first-child { margin-top: 0; }
    .auth-modal-body p { font-size: .85rem; color: var(--texto); line-height: 1.7; margin-bottom: .5rem; }
    .auth-msg.error { display: block; background: #FFEBEE; color: #C62828; }
    .auth-msg.ok    { display: block; background: #E8F5E9; color: #2E7D32; }
    .auth-demo {
      font-size: .72rem; color: var(--texto-2); background: var(--fondo);
      border-radius: var(--radio); padding: .6rem .8rem; line-height: 1.6;
    }
    .auth-mobile-toggle { display: none; font-size: .8rem; color: var(--texto-2); text-align: center; }
    .auth-mobile-toggle button { background: none; border: none; color: var(--bordo); font-weight: 700; cursor: pointer; font-family: var(--font-body); font-size: .8rem; }

    /* ── Panel curvo deslizante ───────────────────────────── */
    .auth-switch {
      position: absolute;
      top: 0; left: 0;
      width: 50%;
      height: 100%;
      z-index: 3;
      overflow: hidden;
      color: #fff;
      background: linear-gradient(160deg, var(--bordo) 0%, var(--bordo-os) 100%);
      transition: transform .6s cubic-bezier(.4,0,.2,1);
    }
    .auth-shell.modo-registro .auth-switch { transform: translateX(100%); }

    .auth-switch-circle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,.08);
    }
    .auth-switch-circle.c-top    { width: 420px; height: 420px; top: -240px;  left: -90px; }
    .auth-switch-circle.c-bottom { width: 420px; height: 420px; bottom: -240px; right: -90px; }

    .auth-switch-content {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      gap: .9rem;
      padding: 2.5rem;
      opacity: 0;
      pointer-events: none;
      transition: opacity .35s ease .15s;
    }
    .auth-switch-content.content-registro-cta { opacity: 1; pointer-events: auto; }
    .auth-shell.modo-registro .content-registro-cta { opacity: 0; pointer-events: none; }
    .auth-shell.modo-registro .content-login-cta    { opacity: 1; pointer-events: auto; }

    .auth-switch-content h2 { font-family: var(--font-display); font-size: 1.4rem; }
    .auth-switch-content p  { font-size: .85rem; color: rgba(255,255,255,.78); line-height: 1.6; max-width: 280px; }
    .btn-switch {
      background: var(--oro); color: var(--bordo); border: none;
      padding: .65rem 1.6rem; border-radius: 24px; font-family: var(--font-body);
      font-weight: 700; font-size: .85rem; cursor: pointer; transition: var(--trans);
    }
    .btn-switch:hover { background: var(--oro-cl); transform: translateY(-2px); }

    @media (max-width: 760px) {
      .auth-shell { min-height: auto; }
      .auth-switch { display: none; }
      .auth-form-pane { position: relative; width: 100%; left: 0 !important; padding: 2.2rem 1.4rem; display: none; }
      .auth-form-login    { display: flex; opacity: 1 !important; pointer-events: auto !important; }
      .auth-shell.modo-registro .auth-form-login    { display: none; }
      .auth-shell.modo-registro .auth-form-registro { display: flex; opacity: 1 !important; pointer-events: auto !important; }
      .auth-mobile-toggle { display: block; }
    }
  </style>
</head>
<body class="auth-page-body">

  <?php require __DIR__ . '/includes/loader.php'; ?>

  <header class="auth-mini-header">
    <a href="index.php" aria-label="Ir al inicio">
      <svg viewBox="0 0 300 70" aria-label="ECOEMS">
        <defs>
          <linearGradient id="goldGrad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#FFD700"/>
            <stop offset="100%" stop-color="#DAA520"/>
          </linearGradient>
        </defs>
        <rect width="300" height="70" rx="8" fill="#023047"/>
        <polygon points="40,6 62,13 40,20 18,13" fill="url(#goldGrad)"/>
        <rect x="26" y="18" width="28" height="5" rx="2" fill="url(#goldGrad)"/>
        <path d="M55,20 Q58,25 55,33" stroke="url(#goldGrad)" stroke-width="2" fill="none" stroke-linecap="round"/>
        <path d="M53,31 L57,31 Q55,35 55,38" stroke="url(#goldGrad)" stroke-width="2.5" fill="none" stroke-linecap="round"/>
        <line x1="284" y1="14" x2="284" y2="56" stroke="url(#goldGrad)" stroke-width="2" stroke-linecap="round"/>
        <text x="72" y="33" font-family="'Sora',Arial,sans-serif" font-size="22" font-weight="800" fill="#00e5ff" letter-spacing="3">ECOEMS</text>
        <text x="72" y="51" font-family="'Sora',Arial,sans-serif" font-size="10" font-weight="600" fill="rgba(255,255,255,.6)" letter-spacing="2.5">PORTAL</text>
      </svg>
    </a>
  </header>

  <main class="auth-main">
  <section class="auth-section">
    <div class="auth-shell<?= $modoInicial === 'registro' ? ' modo-registro' : '' ?>" id="authShell">

      <!-- Panel: Crear cuenta -->
      <div class="auth-form-pane auth-form-registro">
        <div class="auth-title">Crear cuenta</div>
        <p class="auth-subtitle">Regístrate como aspirante para guardar tus resultados.</p>
        <div id="msg-registro" class="auth-msg"></div>
        <form id="form-registro">
          <div class="auth-field">
            <label for="reg-nombre">Nombre completo</label>
            <input type="text" id="reg-nombre" required autocomplete="name">
          </div>
          <div class="auth-field" style="margin-top:.7rem">
            <label for="reg-email">Correo electrónico</label>
            <input type="email" id="reg-email" required autocomplete="username">
          </div>
          <div class="auth-field" style="margin-top:.7rem">
            <label for="reg-password">Contraseña (mínimo 6 caracteres)</label>
            <div class="password-wrap">
              <input type="password" id="reg-password" required minlength="6" autocomplete="new-password">
              <button type="button" class="password-toggle" data-target="reg-password" aria-label="Mostrar contraseña">
                <svg class="icon-eye" viewBox="0 0 24 24" width="18" height="18"><path d="M12 5c-5 0-9 4-10.5 7C2.5 15 6.5 19 12 19s9.5-4 10.5-7C21 9 17 5 12 5Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z" fill="currentColor"/></svg>
                <svg class="icon-eye-off" viewBox="0 0 24 24" width="18" height="18" style="display:none"><path d="M3.5 4 21 21.5l-1.4 1.4-3-3C15.3 20.6 13.7 21 12 21c-5.5 0-9.5-4-11-7 .8-1.6 2.4-3.7 4.7-5.3L2.1 5.4 3.5 4Zm5.4 5.4 1.5 1.5a2 2 0 0 0 2.7 2.7l1.5 1.5a4 4 0 0 1-5.7-5.7ZM12 5c5 0 9 4 10.5 7-.5 1-1.4 2.3-2.6 3.5l-1.5-1.5C19.5 12.9 20.1 12 20.4 12c-1.3-2.6-4.4-5-8.4-5-.8 0-1.6.1-2.3.3L8.2 5.8C9.4 5.3 10.7 5 12 5Z" fill="currentColor"/></svg>
              </button>
            </div>
          </div>
          <div class="auth-check-row">
            <input type="checkbox" id="reg-acepta" required>
            <label for="reg-acepta">He leído y acepto el <button type="button" class="auth-link-modal" data-modal="modal-privacidad">Aviso de privacidad</button> y los <button type="button" class="auth-link-modal" data-modal="modal-terminos">Términos y condiciones</button>.</label>
          </div>
          <button type="submit" class="btn btn-bordo" style="width:100%;margin-top:1.1rem">Crear cuenta</button>
        </form>
        <div class="auth-mobile-toggle">
          ¿Ya tienes cuenta? <button type="button" class="btn-switch-mobile" data-modo="login">Inicia sesión</button>
        </div>
      </div>

      <!-- Panel: Iniciar sesión -->
      <div class="auth-form-pane auth-form-login">
        <div class="auth-title">Iniciar sesión</div>
        <p class="auth-subtitle">Accede con tu correo y contraseña.</p>
        <div id="msg-login" class="auth-msg"></div>
        <form id="form-login">
          <div class="auth-field">
            <label for="login-email">Correo electrónico</label>
            <input type="email" id="login-email" required autocomplete="username">
          </div>
          <div class="auth-field" style="margin-top:.7rem">
            <label for="login-password">Contraseña</label>
            <div class="password-wrap">
              <input type="password" id="login-password" required autocomplete="current-password">
              <button type="button" class="password-toggle" data-target="login-password" aria-label="Mostrar contraseña">
                <svg class="icon-eye" viewBox="0 0 24 24" width="18" height="18"><path d="M12 5c-5 0-9 4-10.5 7C2.5 15 6.5 19 12 19s9.5-4 10.5-7C21 9 17 5 12 5Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z" fill="currentColor"/></svg>
                <svg class="icon-eye-off" viewBox="0 0 24 24" width="18" height="18" style="display:none"><path d="M3.5 4 21 21.5l-1.4 1.4-3-3C15.3 20.6 13.7 21 12 21c-5.5 0-9.5-4-11-7 .8-1.6 2.4-3.7 4.7-5.3L2.1 5.4 3.5 4Zm5.4 5.4 1.5 1.5a2 2 0 0 0 2.7 2.7l1.5 1.5a4 4 0 0 1-5.7-5.7ZM12 5c5 0 9 4 10.5 7-.5 1-1.4 2.3-2.6 3.5l-1.5-1.5C19.5 12.9 20.1 12 20.4 12c-1.3-2.6-4.4-5-8.4-5-.8 0-1.6.1-2.3.3L8.2 5.8C9.4 5.3 10.7 5 12 5Z" fill="currentColor"/></svg>
              </button>
            </div>
          </div>
          <button type="submit" class="btn btn-bordo" style="width:100%;margin-top:1.1rem">Entrar</button>
        </form>
        <div class="auth-demo">
          <strong>Cuentas de demostración:</strong><br>
          Admin: admin@ecoems.mx / Admin123!<br>
          Aspirante: aspirante@ecoems.mx / Aspirante123!
        </div>
        <div class="auth-mobile-toggle">
          ¿No tienes cuenta? <button type="button" class="btn-switch-mobile" data-modo="registro">Regístrate</button>
        </div>
      </div>

      <!-- Panel curvo deslizante -->
      <div class="auth-switch" id="authSwitch">
        <div class="auth-switch-circle c-top"></div>
        <div class="auth-switch-circle c-bottom"></div>

        <div class="auth-switch-content content-registro-cta">
          <h2>¡Hola, aspirante!</h2>
          <p>Crea tu cuenta y guarda tu progreso en el simulador de examen.</p>
          <button type="button" class="btn-switch" data-modo="registro">Crear cuenta</button>
        </div>

        <div class="auth-switch-content content-login-cta">
          <h2>¡Bienvenido de nuevo!</h2>
          <p>Para mantenerte conectado, inicia sesión con tus datos.</p>
          <button type="button" class="btn-switch" data-modo="login">Iniciar sesión</button>
        </div>
      </div>

    </div>
  </section>
  </main>

  <!-- Modal: Aviso de privacidad -->
  <div class="auth-modal-overlay" id="modal-privacidad">
    <div class="auth-modal">
      <div class="auth-modal-header">
        <h2>Aviso de privacidad</h2>
        <button type="button" class="auth-modal-cerrar" data-cerrar="modal-privacidad" aria-label="Cerrar">&times;</button>
      </div>
      <div class="auth-modal-body">
        <p>El Portal ECOEMS es un proyecto académico desarrollado para la materia de Desarrollo de Aplicaciones Web de la Licenciatura en Ciencia de Datos del IPN. Este aviso describe el tratamiento que se da a los datos personales de quienes crean una cuenta en el portal.</p>

        <h3>Datos que se recaban</h3>
        <p>Al registrarte se solicitan tu nombre completo, tu correo electrónico y una contraseña. Si utilizas el simulador de examen, también se guarda el historial de tus intentos: aciertos, porcentaje obtenido y desglose por materia.</p>

        <h3>Finalidad del tratamiento</h3>
        <p>Estos datos se usan únicamente para identificarte dentro del portal, permitirte iniciar sesión, guardar tu progreso en el simulador y mostrarte tu propio historial en tu panel personal. No se utilizan con fines comerciales ni se comparten con terceros.</p>

        <h3>Resguardo de la información</h3>
        <p>La contraseña se almacena de forma cifrada y no es visible para el equipo del proyecto. La información se conserva en la base de datos del portal mientras la cuenta permanezca activa.</p>

        <h3>Derechos del usuario</h3>
        <p>Puedes solicitar el acceso, corrección o eliminación de tus datos personales en cualquier momento, comunicándote con el equipo de desarrollo a través de los medios de contacto indicados en la sección Acerca del portal.</p>

        <h3>Cambios a este aviso</h3>
        <p>Este aviso puede actualizarse conforme evolucione el proyecto. Se recomienda revisarlo periódicamente.</p>
      </div>
    </div>
  </div>

  <!-- Modal: Términos y condiciones -->
  <div class="auth-modal-overlay" id="modal-terminos">
    <div class="auth-modal">
      <div class="auth-modal-header">
        <h2>Términos y condiciones</h2>
        <button type="button" class="auth-modal-cerrar" data-cerrar="modal-terminos" aria-label="Cerrar">&times;</button>
      </div>
      <div class="auth-modal-body">
        <p>Al crear una cuenta en el Portal ECOEMS aceptas los siguientes términos de uso.</p>

        <h3>Naturaleza del portal</h3>
        <p>El Portal ECOEMS es una herramienta educativa, sin fines de lucro, desarrollada como proyecto escolar. No tiene relación oficial con el concurso COMIPEMS ni con las instituciones educativas que en él participan.</p>

        <h3>Uso de la cuenta</h3>
        <p>La cuenta es de uso personal e intransferible. Eres responsable de mantener la confidencialidad de tu contraseña y de toda actividad realizada desde tu sesión.</p>

        <h3>Uso del simulador</h3>
        <p>El examen simulador tiene fines de práctica y autoevaluación. Los resultados obtenidos no constituyen una predicción oficial del desempeño en el concurso real.</p>

        <h3>Origen de los datos estadísticos</h3>
        <p>Las estadísticas históricas, puntajes de corte y demás información del concurso mostrada en el portal provienen de datos abiertos publicados por XABER A.C. y se presentan únicamente con fines de consulta e investigación.</p>

        <h3>Conducta esperada</h3>
        <p>Se espera que el portal se utilice de buena fe, sin intentar vulnerar su funcionamiento ni acceder a información de otras cuentas.</p>

        <h3>Disponibilidad del servicio</h3>
        <p>Por tratarse de un proyecto escolar, el portal se ofrece sin garantía de disponibilidad continua y puede sufrir interrupciones o cambios sin previo aviso.</p>
      </div>
    </div>
  </div>

  <footer class="site-footer">
    <p>Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; Datos: <a href="#">XABER A.C.</a> &nbsp;·&nbsp; 2026</p>
  </footer>

  <script>
    const NEXT = <?= json_encode($next) ?>;
    const shell = document.getElementById('authShell');

    document.querySelectorAll('.btn-switch, .btn-switch-mobile').forEach(btn => {
      btn.addEventListener('click', () => {
        shell.classList.toggle('modo-registro', btn.dataset.modo === 'registro');
      });
    });

    document.querySelectorAll('.password-toggle').forEach(btn => {
      btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const verEye = btn.querySelector('.icon-eye');
        const verEyeOff = btn.querySelector('.icon-eye-off');
        const mostrar = input.type === 'password';
        input.type = mostrar ? 'text' : 'password';
        verEye.style.display = mostrar ? 'none' : '';
        verEyeOff.style.display = mostrar ? '' : 'none';
        btn.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
      });
    });

    document.querySelectorAll('.auth-link-modal').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById(btn.dataset.modal).classList.add('show');
      });
    });
    document.querySelectorAll('.auth-modal-cerrar').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById(btn.dataset.cerrar).classList.remove('show');
      });
    });
    document.querySelectorAll('.auth-modal-overlay').forEach(overlay => {
      overlay.addEventListener('click', (e) => {
        if (e.target === overlay) overlay.classList.remove('show');
      });
    });

    function irA(url, rol) {
      if (url) { window.location.href = url; return; }
      window.location.href = rol === 'admin' ? 'admin/dashboard.php' : 'dashboard.php';
    }

    document.getElementById('form-login').addEventListener('submit', async (e) => {
      e.preventDefault();
      const msg = document.getElementById('msg-login');
      msg.className = 'auth-msg';
      const email = document.getElementById('login-email').value.trim();
      const password = document.getElementById('login-password').value;

      try {
        const resp = await fetch('../backend/api/auth/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password })
        });
        const json = await resp.json();
        if (json.status !== 'ok') {
          msg.textContent = json.mensaje || 'No se pudo iniciar sesión.';
          msg.className = 'auth-msg error';
          return;
        }
        irA(NEXT, json.datos?.rol);
      } catch (err) {
        msg.textContent = 'Error de conexión con el servidor.';
        msg.className = 'auth-msg error';
      }
    });

    document.getElementById('form-registro').addEventListener('submit', async (e) => {
      e.preventDefault();
      const msg = document.getElementById('msg-registro');
      msg.className = 'auth-msg';
      const nombre = document.getElementById('reg-nombre').value.trim();
      const email = document.getElementById('reg-email').value.trim();
      const password = document.getElementById('reg-password').value;

      if (!document.getElementById('reg-acepta').checked) {
        msg.textContent = 'Debes leer y aceptar el aviso de privacidad y los términos y condiciones.';
        msg.className = 'auth-msg error';
        return;
      }

      try {
        const resp = await fetch('../backend/api/auth/registro.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ nombre, email, password })
        });
        const json = await resp.json();
        if (json.status !== 'ok') {
          msg.textContent = json.mensaje || 'No se pudo crear la cuenta.';
          msg.className = 'auth-msg error';
          return;
        }
        irA(NEXT);
      } catch (err) {
        msg.textContent = 'Error de conexión con el servidor.';
        msg.className = 'auth-msg error';
      }
    });
  </script>
</body>
</html>
