// Cosas chicas que se usan en todo el panel.

(function () {
  // Formularios que borran algo: piden confirmacion antes de enviarse.
  // Uso: <form data-confirmar="¿Eliminar este documento?">
  document.querySelectorAll('form[data-confirmar]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirmar'))) {
        e.preventDefault();
      }
    });
  });

  // Botones del ojito para mostrar u ocultar una contraseña: <button data-ver-clave="id-del-input">
  document.querySelectorAll('[data-ver-clave]').forEach(function (boton) {
    var campo = document.getElementById(boton.getAttribute('data-ver-clave'));
    if (!campo) {
      return;
    }
    boton.addEventListener('click', function () {
      var mostrar = campo.type === 'password';
      campo.type = mostrar ? 'text' : 'password';
      boton.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
      boton.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });
  });
})();
