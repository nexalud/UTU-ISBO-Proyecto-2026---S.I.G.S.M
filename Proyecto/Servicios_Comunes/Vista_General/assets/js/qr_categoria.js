// QR de una categoria: lo dibuja, lo imprime y lo descarga como PNG.
// Necesita qrcode.js antes, un #qr con data-url, #qr-impresion, #btn-imprimir y #btn-descargar.

(function () {
  var cajaQr = document.getElementById('qr');

  if (cajaQr && window.qrcode) {
    // Nivel de correccion 'M': el QR se sigue leyendo aunque este un poco sucio o doblado
    var qr = qrcode(0, 'M');
    qr.addData(cajaQr.dataset.url);
    qr.make();

    var svg = qr.createSvgTag({ cellSize: 8, margin: 2, scalable: true });
    cajaQr.innerHTML = svg;
    document.getElementById('qr-impresion').innerHTML = svg;

    document.getElementById('btn-imprimir').addEventListener('click', function () {
      window.print();
    });

    // Descarga un PNG con el QR y el nombre de la categoria abajo
    document.getElementById('btn-descargar').addEventListener('click', function () {
      var boton = this;
      var modulos = qr.getModuleCount();
      var celda = 16;
      var margen = celda * 3;
      var lado = modulos * celda + margen * 2;
      var altoTexto = 70;

      var canvas = document.createElement('canvas');
      canvas.width = lado;
      canvas.height = lado + altoTexto;
      var ctx = canvas.getContext('2d');

      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.fillStyle = '#000000';

      for (var fila = 0; fila < modulos; fila++) {
        for (var col = 0; col < modulos; col++) {
          if (qr.isDark(fila, col)) {
            ctx.fillRect(margen + col * celda, margen + fila * celda, celda, celda);
          }
        }
      }

      ctx.fillStyle = '#1a2a3a';
      ctx.font = 'bold 30px Figtree, Arial, sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(boton.dataset.titulo, lado / 2, lado + 30);

      var enlace = document.createElement('a');
      enlace.download = boton.dataset.nombre + '.png';
      enlace.href = canvas.toDataURL('image/png');
      enlace.click();
    });
  }

})();
