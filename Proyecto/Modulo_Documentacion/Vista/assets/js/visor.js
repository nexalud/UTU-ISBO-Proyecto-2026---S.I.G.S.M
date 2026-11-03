// Lector de PDF del paciente (usa PDF.js de Mozilla, guardado dentro del proyecto).
// 1) Dibuja cada pagina del PDF una abajo de la otra, al ancho del celular.
// 2) Cuando termina, muestra la tarjeta de la encuesta al final.
// 3) Cuando el paciente llega a esa tarjeta, se anima para que la note.

import * as pdfjsLib from '../../../../Servicios_Comunes/Vista_General/assets/js/pdfjs/pdf.min.js';

pdfjsLib.GlobalWorkerOptions.workerSrc = '../../Servicios_Comunes/Vista_General/assets/js/pdfjs/pdf.worker.min.js';

const contenedor = document.getElementById('paginas');
const estado = document.getElementById('visor-estado');
const final = document.getElementById('final-documento');
const urlPdf = contenedor.dataset.pdf;

async function dibujar() {
  // isEvalSupported: false -> el PDF nunca puede ejecutar codigo en la pagina
  const pdf = await pdfjsLib.getDocument({ url: urlPdf, isEvalSupported: false }).promise;
  const ancho = contenedor.clientWidth;
  const nitidez = Math.min(window.devicePixelRatio || 1, 2);

  for (let n = 1; n <= pdf.numPages; n++) {
    const pagina = await pdf.getPage(n);
    const escala = ancho / pagina.getViewport({ scale: 1 }).width;
    const vista = pagina.getViewport({ scale: escala * nitidez });

    const canvas = document.createElement('canvas');
    canvas.width = vista.width;
    canvas.height = vista.height;
    canvas.className = 'pagina-pdf';
    canvas.setAttribute('role', 'img');
    canvas.setAttribute('aria-label', 'Página ' + n + ' de ' + pdf.numPages);
    contenedor.appendChild(canvas);

    await pagina.render({ canvasContext: canvas.getContext('2d'), viewport: vista }).promise;
    estado.textContent = 'Página ' + n + ' de ' + pdf.numPages;
  }

  estado.remove();
  mostrarFinal();
}

function mostrarFinal() {
  if (!final) {
    return;
  }
  final.hidden = false;

  // Avisa cuando la tarjeta entra en pantalla, o sea cuando el paciente llego al final
  const observador = new IntersectionObserver(function (entradas) {
    if (entradas[0].isIntersecting) {
      final.classList.add('visible');
      observador.disconnect();
    }
  }, { threshold: 0.4 });
  observador.observe(final);
}

dibujar().catch(function (error) {
  console.error(error);
  estado.innerHTML = 'No se pudo mostrar el documento acá. <a href="' + urlPdf + '">Abrirlo en el navegador</a>';
  mostrarFinal();
});
