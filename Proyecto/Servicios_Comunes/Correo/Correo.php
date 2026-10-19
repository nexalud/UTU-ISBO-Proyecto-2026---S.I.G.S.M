<?php
// Envio de correos por SMTP (por ejemplo Gmail con contraseña de aplicacion), sin librerias externas.
//
// La configuracion se toma, en este orden:
//   1) Variables de entorno del contenedor: SMTP_HOST, SMTP_PORT, SMTP_SEGURIDAD, SMTP_USUARIO, SMTP_CLAVE, SMTP_REMITENTE
//   2) El archivo config_correo.php de esta carpeta (no se sube a GitHub)
// Si no hay configuracion, el sistema esta en "modo prueba": el correo no sale,
// se guarda como archivo .html en la carpeta salida/ para poder abrirlo y probar el enlace.

class Correo
{
    public const ENVIADO = 'enviado';
    public const GUARDADO = 'guardado';   // modo prueba
    public const CARPETA_SALIDA = __DIR__ . '/salida/';

    private array $config;

    public function __construct()
    {
        $archivo = is_file(__DIR__ . '/config_correo.php') ? require __DIR__ . '/config_correo.php' : [];

        $this->config = [
            'host'      => getenv('SMTP_HOST') ?: ($archivo['host'] ?? ''),
            'puerto'    => (int) (getenv('SMTP_PORT') ?: ($archivo['puerto'] ?? 587)),
            'seguridad' => getenv('SMTP_SEGURIDAD') ?: ($archivo['seguridad'] ?? 'tls'),   // tls (587), ssl (465) o ninguna
            'usuario'   => getenv('SMTP_USUARIO') ?: ($archivo['usuario'] ?? ''),
            'clave'     => getenv('SMTP_CLAVE') ?: ($archivo['clave'] ?? ''),
            'remitente' => getenv('SMTP_REMITENTE') ?: ($archivo['remitente'] ?? ''),
        ];
        if ($this->config['remitente'] === '') {
            $this->config['remitente'] = $this->config['usuario'];
        }
    }

    public function modoPrueba(): bool
    {
        return $this->config['host'] === '';
    }

    // Devuelve ENVIADO, GUARDADO (modo prueba) o false si fallo
    public function enviar(string $para, string $nombre, string $asunto, string $html, string $texto)
    {
        if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $mensaje = $this->armarMensaje($para, $nombre, $asunto, $html, $texto);

        if ($this->modoPrueba()) {
            return $this->guardarEnArchivo($para, $asunto, $html) ? self::GUARDADO : false;
        }

        try {
            $this->enviarSmtp($para, $mensaje);
            return self::ENVIADO;
        } catch (RuntimeException $e) {
            // Nunca se muestra el detalle (puede tener datos del servidor); queda en el log
            error_log('No se pudo enviar el correo a ' . $para . ': ' . $e->getMessage());
            return false;
        }
    }

    private function codificar(string $texto): string
    {
        return '=?UTF-8?B?' . base64_encode($texto) . '?=';
    }

    private function armarMensaje(string $para, string $nombre, string $asunto, string $html, string $texto): string
    {
        $separador = 'sigsm_' . bin2hex(random_bytes(8));
        $dominio   = substr(strrchr($this->config['remitente'] ?: 'sigsm@localhost', '@'), 1) ?: 'localhost';

        $cabeceras = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . $this->codificar('S.I.G.S.M. · Hospital de Clínicas') . ' <' . $this->config['remitente'] . '>',
            'To: ' . $this->codificar($nombre) . ' <' . $para . '>',
            'Subject: ' . $this->codificar($asunto),
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $dominio . '>',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $separador . '"',
        ];

        $cuerpo = '--' . $separador . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($texto))
            . '--' . $separador . "\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html))
            . '--' . $separador . "--\r\n";

        return implode("\r\n", $cabeceras) . "\r\n\r\n" . $cuerpo;
    }

    private function guardarEnArchivo(string $para, string $asunto, string $html): bool
    {
        if (!is_dir(self::CARPETA_SALIDA) && !mkdir(self::CARPETA_SALIDA, 0770, true)) {
            return false;
        }
        // La carpeta esta dentro de la web: nombre imposible de adivinar, index vacio y .htaccess para que nadie la recorra
        if (!is_file(self::CARPETA_SALIDA . 'index.html')) {
            file_put_contents(self::CARPETA_SALIDA . 'index.html', '');
            file_put_contents(self::CARPETA_SALIDA . '.htaccess', "Require all denied\n");
        }
        $nombre = date('Ymd_His') . '_' . preg_replace('/[^A-Za-z0-9]+/', '_', $para) . '_' . bin2hex(random_bytes(8)) . '.html';
        $aviso  = '<p style="font-family:sans-serif;background:#fdf3dc;padding:10px">MODO PRUEBA · Para: '
            . htmlspecialchars($para) . ' · Asunto: ' . htmlspecialchars($asunto) . '</p>';

        return file_put_contents(self::CARPETA_SALIDA . $nombre, $aviso . $html) !== false;
    }

    // ---------- Conversacion SMTP ----------

    private $conexion;

    private function enviarSmtp(string $para, string $mensaje): void
    {
        $c = $this->config;
        $direccion = ($c['seguridad'] === 'ssl' ? 'ssl://' : 'tcp://') . $c['host'] . ':' . $c['puerto'];

        $this->conexion = @stream_socket_client($direccion, $numero, $error, 15);
        if (!$this->conexion) {
            throw new RuntimeException("No se pudo conectar a $direccion: $error");
        }
        stream_set_timeout($this->conexion, 15);

        try {
            $this->esperar(220);
            $this->comando('EHLO sigsm', 250);

            if ($c['seguridad'] === 'tls') {
                $this->comando('STARTTLS', 220);
                if (!stream_socket_enable_crypto($this->conexion, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                    throw new RuntimeException('No se pudo activar TLS');
                }
                $this->comando('EHLO sigsm', 250);
            }

            if ($c['usuario'] !== '') {
                $this->comando('AUTH LOGIN', 334);
                $this->comando(base64_encode($c['usuario']), 334);
                $this->comando(base64_encode($c['clave']), 235, true);
            }

            $this->comando('MAIL FROM:<' . $c['remitente'] . '>', 250);
            $this->comando('RCPT TO:<' . $para . '>', [250, 251]);
            $this->comando('DATA', 354);

            // Las lineas que empiezan con punto se duplican (regla de SMTP)
            $datos = preg_replace('/^\./m', '..', $mensaje);
            $this->comando($datos . "\r\n.", 250);
            $this->comando('QUIT', 221);
        } finally {
            fclose($this->conexion);
        }
    }

    private function comando(string $linea, $codigoEsperado, bool $secreto = false): void
    {
        fwrite($this->conexion, $linea . "\r\n");
        $this->esperar($codigoEsperado, $secreto ? '(clave)' : strtok($linea, "\r\n"));
    }

    private function esperar($codigos, string $despuesDe = 'conexion'): void
    {
        $respuesta = '';
        // Las respuestas de varias lineas tienen un guion despues del codigo (250-...)
        while (($linea = fgets($this->conexion, 1024)) !== false) {
            $respuesta .= $linea;
            if (strlen($linea) < 4 || $linea[3] === ' ') {
                break;
            }
        }
        $codigo = (int) substr($respuesta, 0, 3);
        if (!in_array($codigo, (array) $codigos, true)) {
            throw new RuntimeException("Respuesta inesperada despues de $despuesDe: " . trim($respuesta));
        }
    }
}
