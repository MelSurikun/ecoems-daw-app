<?php require_once __DIR__ . '/../backend/auth.php'; requiereSesionPagina('login.php'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Portal ECOEMS, Simulador de examen</title>
<link rel="stylesheet" href="css/estilos.css?v=3">
<style>

  :root{
    --bg:var(--fondo); --card:var(--fondo-card); --ink:var(--texto); --muted:var(--texto-2);
    --line:var(--borde); --brand:var(--bordo); --accent:var(--acento);
    --ok:#1b7a43; --okbg:#e7f5ec; --bad:#c0392b; --badbg:#fcebea;
    --radius:var(--radio-lg); --maxw:820px;
  }
  *{box-sizing:border-box}
  html{-webkit-text-size-adjust:100%}
  body.simulador-body{
    padding-bottom:140px;
  }
  .wrap{max-width:var(--maxw); margin:0 auto; padding:0 16px}
  header.top{
    background:var(--brand); color:#fff; padding:28px 16px 22px; text-align:center;
  }
  header.top h1{margin:0 0 4px; font-size:1.45rem; letter-spacing:.2px}
  header.top p{margin:0; opacity:.85; font-size:.9rem}
  header.top .meta{margin-top:10px; font-size:.8rem; opacity:.75}

  .bar{
    position:sticky; top:0; z-index:30; background:rgba(244,244,242,.95);
    backdrop-filter:saturate(1.4) blur(6px); border-bottom:1px solid var(--line);
  }
  .bar .wrap{display:flex; align-items:center; gap:12px; padding:10px 16px}
  .progress{flex:1; height:12px; background:var(--line); border-radius:99px; overflow:hidden; box-shadow:inset 0 1px 2px rgba(0,0,0,.08)}
  .progress>i{display:flex; align-items:center; justify-content:flex-end; height:100%; width:0;
    background:linear-gradient(90deg, var(--accent), var(--brand)); border-radius:99px;
    transition:width .3s ease; min-width:0}
  .progress>i span{font-size:.62rem; font-weight:700; color:#fff; padding-right:6px; white-space:nowrap}
  .bar small{color:var(--muted); white-space:nowrap; font-variant-numeric:tabular-nums}

  #result{display:none; margin:18px 0 4px}
  #result.show{display:block}
  .scorecard{
    background:var(--card); border:1px solid var(--line); border-radius:var(--radius);
    padding:20px; text-align:center; box-shadow:0 1px 0 rgba(0,0,0,.02)
  }
  .scorecard .big{font-size:2.4rem; font-weight:700; line-height:1; color:var(--brand)}
  .scorecard .pct{font-size:1rem; color:var(--muted); margin-top:6px}
  .scorecard .msg{margin-top:8px; font-weight:600}
  table.bd{width:100%; border-collapse:collapse; margin-top:16px; font-size:.92rem}
  table.bd th,table.bd td{padding:7px 8px; border-bottom:1px solid var(--line); text-align:left}
  table.bd th{color:var(--muted); font-weight:600}
  table.bd td.num{text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap}

  h2.sect{
    margin:30px 0 4px; padding:10px 14px; background:var(--brand); color:#fff;
    border-radius:10px; font-size:1.05rem; letter-spacing:.3px;
    display:flex; justify-content:space-between; align-items:baseline; gap:10px
  }
  h2.sect span{font-size:.78rem; font-weight:500; opacity:.8}

  .passage{
    background:#fbf7ef; border:1px solid #ece2cf; border-left:4px solid var(--accent);
    border-radius:10px; padding:14px 16px; margin:16px 0; font-size:.95rem; white-space:pre-line
  }
  .passage .src{display:block; margin-top:8px; font-style:italic; color:var(--muted); font-size:.85rem}

  .q{
    background:var(--card); border:1px solid var(--line); border-radius:var(--radius);
    padding:16px 16px 8px; margin:14px 0; scroll-margin-top:70px
  }
  .q .head{display:flex; gap:10px; align-items:flex-start}
  .q .nbox{
    flex:none; width:30px; height:30px; border-radius:8px; background:var(--brand); color:#fff;
    display:grid; place-items:center; font-weight:700; font-size:.85rem
  }
  .q .stem{font-weight:600; margin:2px 0 0}
  .q .ctx{
    background:#f7f7f5; border:1px solid var(--line); border-radius:8px;
    padding:10px 12px; margin:10px 0 4px; font-size:.92rem; white-space:pre-line; color:#333
  }
  .q figure{margin:12px 0 6px; text-align:center}
  .q figure img{max-width:100%; height:auto; border:1px solid var(--line); border-radius:8px; background:#fff}
  .opts{list-style:none; margin:10px 0 4px; padding:0; display:grid; gap:8px}
  .opt{
    display:flex; gap:10px; align-items:flex-start; padding:10px 12px; border:1px solid var(--line);
    border-radius:10px; cursor:pointer; transition:background .12s,border-color .12s; background:#fff
  }
  .opt:hover{background:#faf7f3}
  .opt input{margin:3px 0 0; flex:none; accent-color:var(--accent)}
  .opt .lt{font-weight:700; color:var(--accent)}
  .opt.correct{background:var(--okbg); border-color:var(--ok)}
  .opt.wrong{background:var(--badbg); border-color:var(--bad)}
  .opt.correct .lt{color:var(--ok)}
  .opt.wrong .lt{color:var(--bad)}
  .verdict{font-size:.85rem; font-weight:600; margin:4px 0 8px; display:none}
  .verdict.show{display:block}
  .verdict.ok{color:var(--ok)}
  .verdict.bad{color:var(--bad)}
  .graded .opt{cursor:default}

  .actions{
    position:fixed; left:0; right:0; bottom:0; z-index:40;
    background:rgba(255,255,255,.96); backdrop-filter:blur(6px);
    border-top:1px solid var(--line); padding:12px 16px
  }
  .actions .wrap{display:flex; gap:10px; align-items:center}
  button{
    font:inherit; font-weight:600; border:none; border-radius:10px; padding:12px 18px;
    cursor:pointer; transition:transform .05s, opacity .15s
  }
  button:active{transform:translateY(1px)}
  #grade{background:var(--accent); color:#fff; flex:1}
  #reset{background:#ececec; color:#333}
  .actions small{color:var(--muted); white-space:nowrap}
  @media(max-width:520px){
    .actions small{display:none}
    header.top h1{font-size:1.2rem}
  }
  footer{padding:24px 16px 8px; text-align:center; color:var(--muted); font-size:.8rem}

</style>
</head>
<body class="page-wrapper simulador-body">

<?php require 'includes/navbar.php'; ?>

<div class="page-header">
  <div class="container">
    <p class="page-header-eyebrow">Innovación · Examen simulador</p>
    <h1>Simulador de examen IPN-UNAM</h1>
    <p>128 reactivos de muestra. Al calificar verás tu desglose por asignatura y tu resultado se guarda en tu panel.</p>
  </div>
</div>

<div class="bar"><div class="wrap">
  <div class="progress"><i id="pbar"><span id="ppct"></span></i></div>
  <small id="pcount">0 / 128</small>
</div></div>

<div class="wrap">
  <div id="result"><div class="scorecard">
    <div class="big" id="scoreBig">0 / 128</div>
    <div class="pct" id="scorePct"></div>
    <div class="msg" id="scoreMsg"></div>
    <div id="guardadoMsg" style="font-size:.85rem;margin-top:.6rem"></div>
    <table class="bd"><thead><tr><th>Asignatura</th><th class="num">Aciertos</th></tr></thead>
      <tbody id="bdBody"></tbody></table>
  </div></div>
  <div id="exam"></div>
</div>

<div class="actions"><div class="wrap">
  <button id="grade">Calificar examen</button>
  <button id="reset">Reiniciar</button>
  <small id="hint">Responde y pulsa «Calificar»</small>
</div></div>

<footer class="site-footer">
  <p>Simulador autoevaluable · uso educativo. Las respuestas correctas provienen de la clave oficial de la guía.
  &nbsp;·&nbsp; Portal de Consulta Histórica <strong>ECOEMS</strong> &nbsp;·&nbsp; IPN-LCD &nbsp;·&nbsp; 2026</p>
</footer>

<script>
  window.ECOEMS_LOGUEADO = <?= estaAutenticado() ? 'true' : 'false' ?>;
</script>
<script src="js/examen1_data.js"></script>
<script>

const LT = ["A","B","C","D"];
const exam = document.getElementById("exam");
let graded = false;
let DATA = [];
const FIGS = EXAMEN1_FIGS;

function esc(s){ const d=document.createElement("div"); d.textContent=s; return d.innerHTML; }

function renderPreguntas(){
  let curSubject = null;
  const subjCount = {};
  DATA.forEach(q => subjCount[q.s] = (subjCount[q.s]||0)+1);

  DATA.forEach(q => {
    if(q.s !== curSubject){
      curSubject = q.s;
      const h = document.createElement("h2");
      h.className = "sect";
      h.innerHTML = `<span>&nbsp;</span>`;
      h.insertAdjacentHTML("afterbegin", `${esc(q.s)}`);
      h.insertAdjacentHTML("beforeend", `<span>${subjCount[q.s]} reactivos</span>`);
      exam.appendChild(h);
    }
    if(q.passage){
      const p = document.createElement("div");
      p.className = "passage";
      p.textContent = q.passage;
      exam.appendChild(p);
    }
    const card = document.createElement("div");
    card.className = "q";
    card.id = "q"+q.n;

    let inner = `<div class="head"><div class="nbox">${q.n}</div><div class="stem">${esc(q.q)}</div></div>`;
    if(q.ctx){ inner += `<div class="ctx">${esc(q.ctx)}</div>`; }
    if(q.fig && FIGS[q.fig]){
      inner += `<figure><img alt="Figura del reactivo ${q.n}" src="img/examen1/${FIGS[q.fig]}"></figure>`;
    }
    inner += `<ul class="opts" data-n="${q.n}">`;
    q.o.forEach((opt,i) => {
      inner += `<li class="opt" data-letter="${LT[i]}">
        <input type="radio" name="q${q.n}" value="${LT[i]}" id="q${q.n}${LT[i]}">
        <span class="lt">${LT[i]})</span>
        <label for="q${q.n}${LT[i]}" style="flex:1;cursor:inherit">${esc(opt)}</label>
      </li>`;
    });
    inner += `</ul><div class="verdict" id="v${q.n}"></div>`;
    card.innerHTML = inner;
    exam.appendChild(card);

    card.querySelectorAll(".opt").forEach(li => {
      li.addEventListener("click", e => {
        if(graded) return;
        const inp = li.querySelector("input");
        inp.checked = true;
        updateProgress();
      });
    });
  });

  updateProgress();
}

// Aplica los reportes/correcciones del admin (backend/data/reportes_examen.json) sobre
// el banco base de preguntas: oculta reactivos marcados y sustituye su respuesta correcta.
async function iniciarExamen(){
  let overrides = {};
  try {
    const resp = await fetch("../backend/api/admin/reportes.php");
    const json = await resp.json();
    if(json.status === "ok" && json.datos) overrides = json.datos;
  } catch(e){ /* sin overrides, el examen se ve completo */ }

  DATA = EXAMEN1_DATA
    .filter(q => !overrides[q.n] || !overrides[q.n].oculto)
    .map(q => {
      const ov = overrides[q.n];
      if(ov && ov.respuesta) return { ...q, a: ov.respuesta };
      return q;
    });

  renderPreguntas();
}
iniciarExamen();

function answered(){ return DATA.filter(q => document.querySelector(`input[name="q${q.n}"]:checked`)).length; }
function updateProgress(){
  const a = answered();
  const pct = Math.round(a/DATA.length*100);
  document.getElementById("pcount").textContent = `${a} / ${DATA.length}`;
  document.getElementById("pbar").style.width = pct+"%";
  document.getElementById("ppct").textContent = pct > 8 ? pct+"%" : "";
}

document.getElementById("grade").addEventListener("click", () => {
  if(graded){ window.scrollTo({top:0,behavior:"smooth"}); return; }
  const a = answered();
  if(a < DATA.length){
    if(!confirm(`Has respondido ${a} de ${DATA.length}. Las no respondidas contarán como incorrectas. ¿Calificar de todos modos?`)) return;
  }
  graded = true;
  let total = 0;
  const bySub = {};
  DATA.forEach(q => {
    bySub[q.s] = bySub[q.s] || {ok:0,tot:0};
    bySub[q.s].tot++;
    const card = document.getElementById("q"+q.n);
    card.classList.add("graded");
    const chosen = document.querySelector(`input[name="q${q.n}"]:checked`);
    const pick = chosen ? chosen.value : null;
    const v = document.getElementById("v"+q.n);
    card.querySelectorAll(".opt").forEach(li => {
      const L = li.dataset.letter;
      li.querySelector("input").disabled = true;
      if(L === q.a) li.classList.add("correct");
      else if(L === pick) li.classList.add("wrong");
    });
    if(pick === q.a){
      total++; bySub[q.s].ok++;
      v.className = "verdict ok show"; v.textContent = "✓ Correcta";
    }else{
      v.className = "verdict bad show";
      v.textContent = pick ? `✗ Tu respuesta: ${pick} · Correcta: ${q.a}` : `✗ Sin responder · Correcta: ${q.a}`;
    }
  });

  const pct = Math.round(total/DATA.length*100);
  document.getElementById("scoreBig").textContent = `${total} / ${DATA.length}`;
  document.getElementById("scorePct").textContent = `${pct}% de aciertos`;
  const msg = document.getElementById("scoreMsg");
  if(pct>=80){ msg.textContent="¡Excelente desempeño!"; msg.style.color="var(--ok)"; }
  else if(pct>=60){ msg.textContent="Buen avance, sigue practicando."; msg.style.color="var(--accent)"; }
  else { msg.textContent="A reforzar: repasa los temas marcados."; msg.style.color="var(--bad)"; }

  const tb = document.getElementById("bdBody"); tb.innerHTML="";
  Object.keys(bySub).forEach(s => {
    tb.insertAdjacentHTML("beforeend",
      `<tr><td>${esc(s)}</td><td class="num">${bySub[s].ok} / ${bySub[s].tot}</td></tr>`);
  });
  document.getElementById("result").classList.add("show");
  document.getElementById("grade").textContent = "Ver resultados ▲";
  document.getElementById("hint").textContent = "Examen calificado";

  // Guardar el intento en el servidor (solo si hay sesión iniciada).
  const guardadoEl = document.getElementById("guardadoMsg");
  if (window.ECOEMS_LOGUEADO) {
    fetch('../backend/api/simulador.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ aciertos: total, total: DATA.length, detalle: bySub })
    }).then(r => r.json()).then(json => {
      if (json.status === 'ok') {
        guardadoEl.innerHTML = 'Resultado guardado en tu panel. <a href="dashboard.php" style="color:var(--brand);font-weight:600">Ver mi progreso →</a>';
      } else {
        guardadoEl.textContent = 'No se pudo guardar tu resultado: ' + (json.mensaje || 'error desconocido');
      }
    }).catch(() => { guardadoEl.textContent = 'No se pudo guardar tu resultado (sin conexión).'; });
  } else if (guardadoEl) {
    guardadoEl.innerHTML = 'Inicia sesión para guardar tus resultados y ver tu progreso. <a href="login.php?next=simulador.php" style="color:var(--brand);font-weight:600">Iniciar sesión →</a>';
  }

  window.scrollTo({top:0,behavior:"smooth"});
});

document.getElementById("reset").addEventListener("click", () => {
  if(graded && !confirm("¿Reiniciar el examen y borrar tus respuestas?")) return;
  graded = false;
  DATA.forEach(q => {
    const card = document.getElementById("q"+q.n);
    card.classList.remove("graded");
    card.querySelectorAll(".opt").forEach(li => {
      li.classList.remove("correct","wrong");
      const inp = li.querySelector("input"); inp.checked=false; inp.disabled=false;
    });
    document.getElementById("v"+q.n).className = "verdict";
  });
  document.getElementById("result").classList.remove("show");
  document.getElementById("grade").textContent = "Calificar examen";
  document.getElementById("hint").textContent = "Responde y pulsa «Calificar»";
  updateProgress();
  window.scrollTo({top:0,behavior:"smooth"});
});

</script>
</body>
</html>
