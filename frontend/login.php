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
  <link rel="stylesheet" href="css/estilos.css?v=2">
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
    .auth-msg { font-size: .8rem; padding: .55rem .8rem; border-radius: var(--radio); display: none; }
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
            <input type="password" id="reg-password" required minlength="6" autocomplete="new-password">
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
            <input type="password" id="login-password" required autocomplete="current-password">
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

    function irA(url) { window.location.href = url || 'dashboard.php'; }

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
        irA(NEXT);
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
