// Se carga en TODAS las pantallas (panel y paginas del paciente).
// 1) Validacion de formularios en el navegador, antes de enviar.
// 2) Cambio de idioma Español / English.
// El servidor (PHP) vuelve a validar todo: esto es para avisar antes y que sea mas comodo.

(function () {
  // ---------------------------------------------------------------
  // 1) VALIDACION
  // Un campo es obligatorio si su etiqueta tiene el asterisco <span class="obligatorio">.
  // Se revisan solo los campos visibles (los que estan dentro de algo [hidden] no cuentan).
  // ---------------------------------------------------------------
  var MENSAJES = {
    es: { vacio: 'Este campo es obligatorio.', elegir: 'Elegí una opción.', correo: 'Escribí un correo válido.', corto: 'Es demasiado corto.' },
    en: { vacio: 'This field is required.', elegir: 'Choose an option.', correo: 'Enter a valid email.', corto: 'It is too short.' }
  };

  function oculto(elemento) {
    return !!elemento.closest('[hidden]');
  }

  function marcarError(campo, texto) {
    campo.classList.add('con-error');
    var p = document.createElement('p');
    p.className = 'mensaje-error error-js';
    p.setAttribute('role', 'alert');
    p.textContent = texto;
    campo.appendChild(p);
  }

  function validar(form) {
    var m = MENSAJES[idiomaActual()];
    var primero = null;

    // Se borran los avisos de un intento anterior
    form.querySelectorAll('.error-js').forEach(function (p) {
      var campo = p.parentNode;
      p.remove();
      if (!campo.querySelector('.mensaje-error')) {
        campo.classList.remove('con-error');
      }
    });

    form.querySelectorAll('.campo').forEach(function (campo) {
      if (oculto(campo) || campo.querySelector('.error-js')) {
        return;
      }
      var obligatorio = !!campo.querySelector(':scope > label .obligatorio, :scope > legend .obligatorio, :scope > .campo-cabecera label .obligatorio');
      var opciones = campo.querySelectorAll('input[type="radio"], input[type="checkbox"]');
      var control = campo.querySelector('input:not([type="hidden"]):not([type="radio"]):not([type="checkbox"]), select, textarea');
      var error = null;

      if (opciones.length && !control) {
        // Grupo de opciones (radios o casillas): al menos una marcada
        var marcada = Array.prototype.some.call(opciones, function (o) { return o.checked; });
        if (obligatorio && !marcada) { error = m.elegir; }
      } else if (control && !control.disabled) {
        var valor = control.type === 'file' ? (control.files.length ? 'x' : '') : control.value.trim();
        var minimo = parseInt(control.getAttribute('minlength') || '0', 10);
        if (obligatorio && valor === '') {
          error = control.tagName === 'SELECT' ? m.elegir : m.vacio;
        } else if (valor !== '' && control.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
          error = m.correo;
        } else if (valor !== '' && minimo && valor.length < minimo) {
          error = m.corto;
        }
      }

      if (error) {
        marcarError(campo, error);
        primero = primero || control || opciones[0];
      }
    });

    if (primero) {
      primero.focus();
      return false;
    }
    return true;
  }

  document.querySelectorAll('form[method="post"]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!validar(form)) {
        e.preventDefault();
        e.stopImmediatePropagation();   // si hay error no se pregunta "¿Seguro?" (data-confirmar)
      }
    }, true);
  });

  // ---------------------------------------------------------------
  // ORDENAR TABLAS
  // <table data-ordenable> y en cada <th data-orden="texto|numero"> que se pueda ordenar.
  // Un toque ordena de menor a mayor (A-Z), otro toque al revés. Solo una columna a la vez.
  // ---------------------------------------------------------------
  document.querySelectorAll('table[data-ordenable]').forEach(function (tabla) {
    var cuerpo = tabla.tBodies[0];
    var titulos = tabla.querySelectorAll('th[data-orden]');

    titulos.forEach(function (th) {
      // El titulo pasa a ser un boton (se puede usar con teclado)
      var boton = document.createElement('button');
      boton.type = 'button';
      boton.className = 'orden-boton';
      boton.innerHTML = th.innerHTML + '<span class="orden-flecha" aria-hidden="true"></span>';
      th.innerHTML = '';
      th.appendChild(boton);

      boton.addEventListener('click', function () {
        var subiendo = th.getAttribute('aria-sort') !== 'ascending';
        // Se apaga el orden de las otras columnas
        titulos.forEach(function (otro) { otro.removeAttribute('aria-sort'); });
        th.setAttribute('aria-sort', subiendo ? 'ascending' : 'descending');

        var columna = Array.prototype.indexOf.call(th.parentNode.children, th);
        var numero = th.getAttribute('data-orden') === 'numero';
        var filas = Array.prototype.slice.call(cuerpo.rows);

        filas.sort(function (a, b) {
          var x = a.cells[columna].textContent.trim();
          var y = b.cells[columna].textContent.trim();
          var r = numero ? (parseFloat(x) || 0) - (parseFloat(y) || 0) : x.localeCompare(y, 'es', { numeric: true });
          return subiendo ? r : -r;
        });
        filas.forEach(function (f) { cuerpo.appendChild(f); });
      });
    });
  });

  // ---------------------------------------------------------------
  // 2) IDIOMA
  // Los textos estan escritos en español. Si se elige English, se reemplazan en pantalla
  // usando el diccionario window.TRADUCCION_EN (archivo idioma_en.js).
  // La eleccion se guarda en una cookie para que la recuerde en todas las paginas.
  // ---------------------------------------------------------------
  function idiomaActual() {
    return /(?:^|; )idioma=en/.test(document.cookie) ? 'en' : 'es';
  }

  function guardarIdioma(idioma) {
    document.cookie = 'idioma=' + idioma + '; path=/; max-age=31536000; SameSite=Lax';
  }

  var diccionario = window.TRADUCCION_EN || {};
  var patrones = window.TRADUCCION_EN_PATRONES || [];

  function traducir(texto) {
    var limpio = texto.replace(/\s+/g, ' ').trim();
    if (!limpio) {
      return null;
    }
    if (Object.prototype.hasOwnProperty.call(diccionario, limpio)) {
      return texto.replace(texto.trim(), diccionario[limpio]);
    }
    for (var i = 0; i < patrones.length; i++) {
      if (patrones[i][0].test(limpio)) {
        return texto.replace(texto.trim(), limpio.replace(patrones[i][0], patrones[i][1]));
      }
    }
    return null;
  }

  function traducirPagina(raiz) {
    var recorrido = document.createTreeWalker(raiz, NodeFilter.SHOW_TEXT, {
      acceptNode: function (n) {
        var padre = n.parentNode && n.parentNode.nodeName;
        return padre === 'SCRIPT' || padre === 'STYLE' ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT;
      }
    });
    var nodos = [];
    while (recorrido.nextNode()) { nodos.push(recorrido.currentNode); }
    nodos.forEach(function (n) {
      var nuevo = traducir(n.nodeValue);
      if (nuevo !== null) { n.nodeValue = nuevo; }
    });

    ['placeholder', 'aria-label', 'title', 'data-confirmar'].forEach(function (atributo) {
      raiz.querySelectorAll('[' + atributo + ']').forEach(function (el) {
        var nuevo = traducir(el.getAttribute(atributo));
        if (nuevo !== null) { el.setAttribute(atributo, nuevo); }
      });
    });
    var titulo = traducir(document.title);
    if (titulo !== null) { document.title = titulo; }
  }

  // Boton para cambiar: se agrega al menu del panel o, en las paginas del paciente, arriba a la derecha
  function crearBoton() {
    var ingles = idiomaActual() === 'en';
    var boton = document.createElement('button');
    boton.type = 'button';
    boton.className = 'boton-idioma';
    boton.textContent = ingles ? 'Español' : 'English';
    boton.setAttribute('lang', ingles ? 'es' : 'en');
    boton.addEventListener('click', function () {
      guardarIdioma(ingles ? 'es' : 'en');
      window.location.reload();
    });
    var lugar = document.querySelector('.sidebar-usuario');
    if (lugar) {
      lugar.insertBefore(boton, lugar.firstChild);
    } else {
      // Las paginas del paciente usan otra hoja de estilos: el estilo va aca mismo
      boton.classList.add('flotante');
      boton.style.cssText = 'position:fixed;top:10px;right:10px;z-index:50;padding:6px 12px;border:1.5px solid #cbd5e1;' +
        'border-radius:999px;background:#fff;font:inherit;font-size:13px;font-weight:700;color:#0f172a;cursor:pointer;' +
        'box-shadow:0 2px 8px rgba(15,23,42,.12)';
      document.body.appendChild(boton);
    }
  }

  crearBoton();
  if (idiomaActual() === 'en') {
    document.documentElement.lang = 'en';
    traducirPagina(document.body);
    // Textos que aparecen despues (ej: el resultado de buscar un paciente)
    new MutationObserver(function (cambios) {
      cambios.forEach(function (c) {
        c.addedNodes.forEach(function (n) {
          if (n.nodeType === 3) {
            var nuevo = traducir(n.nodeValue);
            if (nuevo !== null) { n.nodeValue = nuevo; }
          } else if (n.nodeType === 1) {
            traducirPagina(n);
          }
        });
      });
    }).observe(document.body, { childList: true, subtree: true });
  }

  window.SIGSM = { idioma: idiomaActual };
})();
