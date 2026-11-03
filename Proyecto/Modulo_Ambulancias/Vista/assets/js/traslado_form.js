// Formulario de nuevo traslado: muestra u oculta campos segun lo elegido y busca al paciente por la cedula.
// Todo se vuelve a validar en el servidor; esto es solo para que sea comodo de usar.

(function () {
  var form = document.getElementById('form-traslado');
  if (!form) {
    return;
  }
  var rutas = JSON.parse(form.getAttribute('data-rutas') || '[]');
  var zonaCi = document.getElementById('zona-ci');
  var ci = document.getElementById('ci');
  var resultado = document.getElementById('resultado-paciente');
  var vehiculo = document.getElementById('vehiculo');
  var ayudaVehiculo = document.getElementById('ayuda-vehiculo');

  function tipoElegido() {
    return form.querySelector('input[name="tipo_elemento"]:checked');
  }

  // Segun el tipo: pide cedula (solo pacientes) y deja elegir solo vehiculos que lo pueden llevar
  function alCambiarTipo() {
    var tipo = tipoElegido();
    if (!tipo) {
      return;
    }
    zonaCi.hidden = tipo.getAttribute('data-requiere') !== '1';

    var hayAlguno = false;
    Array.prototype.forEach.call(vehiculo.options, function (opcion) {
      if (!opcion.value) {
        return;
      }
      var admite = (opcion.getAttribute('data-admite') || '').split(',');
      opcion.disabled = admite.indexOf(tipo.value) === -1;
      if (!opcion.disabled) {
        hayAlguno = true;
      }
    });
    if (vehiculo.selectedOptions[0] && vehiculo.selectedOptions[0].disabled) {
      vehiculo.value = '';
    }
    ayudaVehiculo.textContent = hayAlguno
      ? 'Solo aparecen los que están libres y pueden llevar lo elegido.'
      : 'No hay vehículos libres que puedan llevar esto: derivá a una empresa externa.';
  }

  // Propio o empresa externa
  function alCambiarModo() {
    var modo = form.querySelector('input[name="modo"]:checked').value;
    document.getElementById('zona-propio').hidden = modo !== 'propio';
    document.getElementById('zona-externo').hidden = modo !== 'externo';
  }

  // Si hay una ruta conocida entre origen y destino, se muestra la distancia y el tiempo
  function alCambiarRecorrido() {
    var o = document.getElementById('origen').value;
    var d = document.getElementById('destino').value;
    var info = document.getElementById('info-ruta');
    var ruta = rutas.filter(function (r) { return String(r.id_origen) === o && String(r.id_destino) === d; })[0];
    info.textContent = ruta
      ? 'Ruta conocida: ' + Number(ruta.distancia_km).toLocaleString('es-UY') + ' km · unos ' + ruta.tiempo_min + ' minutos.'
      : (o && d && o !== d ? 'No hay una ruta cargada para este recorrido: poné la llegada estimada a mano.' : '');
  }

  // Busca el paciente en la API del hospital (a traves de nuestro servidor)
  function buscarPaciente() {
    var cedula = ci.value.replace(/\D/g, '');
    if (!cedula) {
      resultado.textContent = 'Escribí la cédula.';
      return;
    }
    resultado.textContent = 'Buscando…';
    fetch('buscar_paciente.php?ci=' + encodeURIComponent(cedula), { credentials: 'same-origin' })
      .then(function (r) { return r.json().then(function (datos) { return { ok: r.ok, status: r.status, datos: datos }; }); })
      .then(function (r) {
        resultado.textContent = r.ok ? 'Paciente: ' + r.datos.nombre + ' ' + r.datos.apellido : r.datos.error;
        resultado.classList.toggle('texto-error', !r.ok && r.status !== 404);
        // No esta en el sistema del hospital: se carga a mano (ingreso manual)
        mostrarManual(!r.ok && r.status === 404);
      })
      .catch(function () {
        resultado.textContent = 'No se pudo consultar el sistema del hospital. Cargá los datos a mano.';
        mostrarManual(true);
      });
  }

  function mostrarManual(mostrar) {
    document.getElementById('zona-manual').hidden = !mostrar;
    if (mostrar) {
      document.getElementById('nombre').focus();
    }
  }

  form.addEventListener('change', function (e) {
    if (e.target.name === 'tipo_elemento') { alCambiarTipo(); }
    if (e.target.name === 'modo') { alCambiarModo(); }
    if (e.target.id === 'origen' || e.target.id === 'destino') { alCambiarRecorrido(); }
  });
  document.getElementById('buscar-paciente').addEventListener('click', buscarPaciente);
  // Si cambia la cedula, lo cargado a mano ya no corresponde
  ci.addEventListener('input', function () { mostrarManual(false); });
  ci.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); buscarPaciente(); }
  });

  alCambiarTipo();
  alCambiarRecorrido();
})();
