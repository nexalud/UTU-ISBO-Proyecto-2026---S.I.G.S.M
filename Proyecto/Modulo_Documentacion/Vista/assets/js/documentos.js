// Seleccion del PDF en el formulario de nuevo documento (elegir o arrastrar).

(function () {
  // ---------- Formulario de nuevo documento ----------
  var input = document.getElementById('archivo');
  var zona = document.getElementById('zona-archivo');
  var elegido = document.getElementById('archivo-elegido');

  if (input && zona && elegido) {
    var nombre = elegido.querySelector('[data-nombre]');
    var tamano = elegido.querySelector('[data-tamano]');

    function mostrarArchivo() {
      var archivo = input.files[0];
      if (!archivo) {
        elegido.classList.remove('visible');
        return;
      }
      nombre.textContent = archivo.name;
      tamano.textContent = archivo.size >= 1048576
        ? (archivo.size / 1048576).toFixed(1).replace('.', ',') + ' MB'
        : Math.max(1, Math.round(archivo.size / 1024)) + ' KB';
      elegido.classList.add('visible');
    }

    input.addEventListener('change', mostrarArchivo);

    document.getElementById('quitar-archivo').addEventListener('click', function () {
      input.value = '';
      mostrarArchivo();
    });

    // Arrastrar y soltar el PDF sobre la zona
    ['dragenter', 'dragover'].forEach(function (evento) {
      zona.addEventListener(evento, function (e) {
        e.preventDefault();
        zona.classList.add('arrastrando');
      });
    });

    ['dragleave', 'drop'].forEach(function (evento) {
      zona.addEventListener(evento, function (e) {
        e.preventDefault();
        zona.classList.remove('arrastrando');
      });
    });

    zona.addEventListener('drop', function (e) {
      if (e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        mostrarArchivo();
      }
    });
  }
})();
