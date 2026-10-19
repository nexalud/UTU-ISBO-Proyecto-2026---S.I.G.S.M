<?php
// Mensaje verde o rojo que aparece arriba despues de guardar algo. Variable: $aviso (de tomarAviso())
if (!empty($aviso)): ?>
  <div class="aviso <?= $aviso['tipo'] === 'ok' ? 'ok' : 'error' ?>" role="status">
    <?php if ($aviso['tipo'] === 'ok'): ?>
      <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
    <?php else: ?>
      <svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5.5"></path><path d="M12 16.5h.01"></path></svg>
    <?php endif; ?>
    <?= e($aviso['texto']) ?>
  </div>
<?php endif;
