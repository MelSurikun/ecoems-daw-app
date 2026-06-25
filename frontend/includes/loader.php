<?php if (!defined('ECOEMS_LOADER_RENDERED')): define('ECOEMS_LOADER_RENDERED', true); ?>
<div class="page-loader" id="page-loader" aria-hidden="true">
  <div class="dots">
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
  </div>
</div>
<svg width="0" height="0" style="position:absolute">
  <defs>
    <filter id="goo-loader">
      <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur" />
      <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="goo" />
      <feBlend in="SourceGraphic" in2="goo" />
    </filter>
  </defs>
</svg>
<script>
  (function () {
    function ocultarLoader() { document.body.classList.add('cargado'); }
    window.addEventListener('load', ocultarLoader);
    setTimeout(ocultarLoader, 4000);
  })();
</script>
<?php endif; ?>
