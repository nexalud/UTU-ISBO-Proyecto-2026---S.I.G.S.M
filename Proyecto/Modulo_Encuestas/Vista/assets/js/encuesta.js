// Interaccion de la encuesta: estrellas, contador de caracteres y control de obligatorias.
// La validacion real la hace el servidor; esto solo avisa antes de enviar.

document.querySelectorAll('.estrellas').forEach(function (grupo) {
  var radios = grupo.querySelectorAll('input[type="radio"]');
  var estrellas = grupo.querySelectorAll('.estrella');
  var textoElegido = grupo.parentElement.querySelector('[data-texto-elegido]');

  function pintar(cantidad) {
    estrellas.forEach(function (estrella, i) {
      estrella.classList.toggle('activa', i < cantidad);
    });
  }

  function elegida() {
    var n = 0;
    radios.forEach(function (r, i) { if (r.checked) { n = i + 1; } });
    return n;
  }

  radios.forEach(function (radio, i) {
    radio.addEventListener('change', function () {
      pintar(i + 1);
      textoElegido.textContent = radio.dataset.texto;
    });
  });

  estrellas.forEach(function (estrella, i) {
    estrella.addEventListener('mouseenter', function () { pintar(i + 1); });
    estrella.addEventListener('mouseleave', function () { pintar(elegida()); });
  });
});

document.querySelectorAll('textarea[maxlength]').forEach(function (area) {
  var contador = area.parentElement.querySelector('[data-contador]');
  area.addEventListener('input', function () {
    contador.textContent = area.value.length;
  });
});

var form = document.getElementById('form-encuesta');
var errorGeneral = document.getElementById('error-general');

function estaRespondida(pregunta) {
  if (pregunta.dataset.tipo === 'abierta') {
    return pregunta.querySelector('textarea').value.trim() !== '';
  }
  return pregunta.querySelector('input[type="radio"]:checked') !== null;
}

function mensajeError(pregunta) {
  if (pregunta.dataset.tipo === 'abierta') { return 'Escribí una respuesta para continuar.'; }
  if (pregunta.dataset.tipo === 'escala') { return 'Elegí una calificación para continuar.'; }
  return 'Elegí una opción para continuar.';
}

function marcarError(pregunta, conError) {
  var msg = pregunta.querySelector('.error-msg');
  pregunta.classList.toggle('con-error', conError);
  msg.innerHTML = conError
    ? '<svg class="icono" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5.5"></path><path d="M12 16.5h.01"></path></svg>'
    : '';
  if (conError) { msg.appendChild(document.createTextNode(mensajeError(pregunta))); }
}

// Al responder una pregunta marcada en rojo, se le saca el error
form.querySelectorAll('.pregunta').forEach(function (pregunta) {
  pregunta.addEventListener('input', function () {
    if (pregunta.classList.contains('con-error') && estaRespondida(pregunta)) {
      marcarError(pregunta, false);
      if (!form.querySelector('.pregunta.con-error')) { errorGeneral.textContent = ''; }
    }
  });
});

form.addEventListener('submit', function (evento) {
  var primera = null;

  form.querySelectorAll('.pregunta[data-obligatoria="1"]').forEach(function (pregunta) {
    var falta = !estaRespondida(pregunta);
    marcarError(pregunta, falta);
    if (falta && !primera) { primera = pregunta; }
  });

  if (primera) {
    evento.preventDefault();
    errorGeneral.textContent = 'Faltan responder preguntas obligatorias.';
    primera.scrollIntoView({ behavior: 'smooth', block: 'center' });
    var campo = primera.querySelector('input, textarea');
    if (campo) { campo.focus({ preventScroll: true }); }
    return;
  }

  // Evita que se envie dos veces si tocan el boton rapido
  form.querySelector('.btn-enviar').disabled = true;
});
