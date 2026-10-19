// Editor de preguntas de la encuesta: agregar, quitar, ordenar y cambiar el tipo.
// Las preguntas ya vienen dibujadas desde PHP; aca solo se manejan los botones.

(function () {
  var lista = document.getElementById('lista-preguntas');
  var plantilla = document.getElementById('plantilla-pregunta');
  var botonAgregar = document.getElementById('agregar-pregunta');
  if (!lista || !plantilla || !botonAgregar) {
    return;
  }

  // Cada pregunta nueva usa un indice que no se repite (preguntas[7][texto], ...)
  var siguiente = parseInt(lista.getAttribute('data-siguiente'), 10) || 0;

  // Numeros visibles y botones de subir/bajar habilitados segun la posicion
  function actualizar() {
    var preguntas = lista.querySelectorAll('[data-pregunta]');
    preguntas.forEach(function (p, n) {
      p.querySelector('[data-numero]').textContent = n + 1;
      p.querySelector('[data-subir]').disabled = n === 0;
      p.querySelector('[data-bajar]').disabled = n === preguntas.length - 1;
      p.querySelector('[data-quitar-pregunta]').disabled = preguntas.length === 1;
    });
  }

  // Muestra solo la zona que corresponde al tipo elegido
  function mostrarZona(pregunta) {
    var tipo = pregunta.querySelector('[data-tipo]').value;
    pregunta.querySelectorAll('[data-zona]').forEach(function (zona) {
      zona.hidden = zona.getAttribute('data-zona') !== tipo;
    });
  }

  botonAgregar.addEventListener('click', function () {
    var html = plantilla.innerHTML.replace(/__N__/g, String(siguiente++));
    var contenedor = document.createElement('div');
    contenedor.innerHTML = html.trim();
    var nueva = contenedor.firstElementChild;
    lista.appendChild(nueva);
    actualizar();
    nueva.querySelector('input[type="text"]').focus();
  });

  // Un solo escuchador para todos los botones de la lista (tambien sirve para las preguntas nuevas)
  lista.addEventListener('click', function (e) {
    var boton = e.target.closest('button');
    if (!boton) {
      return;
    }
    var pregunta = boton.closest('[data-pregunta]');

    if (boton.hasAttribute('data-subir') && pregunta.previousElementSibling) {
      lista.insertBefore(pregunta, pregunta.previousElementSibling);
      boton.focus();
    } else if (boton.hasAttribute('data-bajar') && pregunta.nextElementSibling) {
      lista.insertBefore(pregunta.nextElementSibling, pregunta);
      boton.focus();
    } else if (boton.hasAttribute('data-quitar-pregunta')) {
      pregunta.remove();
    } else if (boton.hasAttribute('data-agregar-opcion')) {
      var opciones = pregunta.querySelector('[data-opciones]');
      var nuevaOpcion = opciones.firstElementChild.cloneNode(true);
      nuevaOpcion.querySelector('input').value = '';
      opciones.appendChild(nuevaOpcion);
      nuevaOpcion.querySelector('input').focus();
    } else if (boton.hasAttribute('data-quitar-opcion')) {
      var item = boton.closest('li');
      // Siempre queda al menos un casillero para escribir
      if (item.parentElement.children.length > 1) {
        item.remove();
      } else {
        item.querySelector('input').value = '';
      }
    }
    actualizar();
  });

  lista.addEventListener('change', function (e) {
    if (e.target.matches('[data-tipo]')) {
      mostrarZona(e.target.closest('[data-pregunta]'));
    }
  });

  // Enter dentro de una opcion agrega la siguiente en vez de enviar el formulario
  lista.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && e.target.matches('[data-opciones] input')) {
      e.preventDefault();
      e.target.closest('[data-pregunta]').querySelector('[data-agregar-opcion]').click();
    }
  });

  actualizar();
})();
