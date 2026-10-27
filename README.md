<div align="center">

<img src="https://capsule-render.vercel.app/api?type=waving&color=0:0e4f80,100:1565a0&height=190&section=header&text=S.I.G.S.M.&fontSize=58&fontColor=ffffff&fontAlignY=36&desc=Sistema%20Inform%C3%A1tico%20de%20Gesti%C3%B3n%20de%20Servicios%20M%C3%A9dicos&descSize=16&descAlignY=58" width="100%" alt="S.I.G.S.M.">

<img src="https://readme-typing-svg.demolab.com?font=Fira+Code&weight=500&size=18&duration=2800&pause=900&color=1565A0&center=true&vCenter=true&width=560&lines=Documentos+para+pacientes+por+QR;Encuestas+de+satisfacci%C3%B3n+an%C3%B3nimas;Trazabilidad+de+ambulancias;Hospital+de+Cl%C3%ADnicas+%C2%B7+Nexalud" alt="Typing SVG">

<br>

![PHP](https://img.shields.io/badge/PHP-8-1565a0?style=flat-square&logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-11-1565a0?style=flat-square&logo=mariadb&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-1565a0?style=flat-square&logo=docker&logoColor=white)
![AlmaLinux](https://img.shields.io/badge/AlmaLinux-10-1565a0?style=flat-square&logo=almalinux&logoColor=white)

<sub>3ro BT Informática · Instituto Superior Brazo Oriental (I.S.B.O.) · UTU · 2026</sub>

</div>

---

## ✦ Qué es

| Módulo | Qué hace |
|---|---|
| **Documentación y encuestas** | El funcionario carga PDFs por categoría y cada categoría tiene un QR. El paciente lo escanea y ve los documentos desde el celular. Encuestas anónimas con resultados e indicadores. |
| **Ambulancias** | Solicitudes de traslado, asignación de vehículo, chofer y enfermero, y seguimiento del estado. |

## ✦ Equipo Nexalud

| Rol | Integrante |
|---|---|
| Coordinador | Ezequiel Banfi |
| Sub-Coordinador | Thiago Leites |
| Integrante | Sabrina Almeida |
| Integrante | Mathias Gómez |

<sub>Cliente: Hospital de Clínicas · Docente: Fabián Acosta</sub>

---

## ⌨ Instalación local · Windows + XAMPP

> Necesitás **[XAMPP](https://www.apachefriends.org/)** y **[Git](https://git-scm.com/)**.

**1 · Clonar el repositorio**

```bash
git clone https://github.com/nexalud/UTU-ISBO-Proyecto-2026---S.I.G.S.M.git
cd UTU-ISBO-Proyecto-2026---S.I.G.S.M
```

**2 · Prender la base**

Abrí el **XAMPP Control Panel** → **Start** en **MySQL** (y en **Apache** para usar phpMyAdmin).

**3 · Crear la base** — en `http://localhost/phpmyadmin`, pestaña **SQL**:

```sql
CREATE DATABASE sigsm CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

Seleccioná `sigsm` → **Importar**, en este orden:

| # | Archivo | Para qué |
|:-:|---|---|
| 1 | `BaseDatos/sigsm.sql` | Tablas y datos iniciales |
| 2 | `BaseDatos/actualizacion_tercera_entrega.sql` | Cambios de la 3ra entrega |
| 3 | `BaseDatos/datos_prueba_encuestas.sql` | Respuestas de prueba *(opcional)* |

**4 · Contraseña del administrador**

```powershell
C:\xampp\php\php.exe Proyecto\Servicios_Comunes\Autenticacion\cambiar_contrasena.php eBanfi
```

**5 · Levantar la página**

```powershell
C:\xampp\php\php.exe -S localhost:8000
```

Abrí **http://localhost:8000** y entrá con `eBanfi`. ✔

<details>
<summary><b>Correo para recuperar contraseñas</b> <sub>(opcional)</sub></summary>
<br>

Sin configurar, el sistema queda en **modo prueba**: los correos se guardan en `Proyecto/Servicios_Comunes/Correo/salida/`.

Para enviarlos de verdad, copiá `Correo/config_correo.ejemplo.php` como `config_correo.php` y completá el Gmail y su **contraseña de aplicación**. Ese archivo no se sube a GitHub.

</details>

---

## ☁ Instalación en servidor · AlmaLinux + Docker

> Ejemplo probado en **AlmaLinux 10** con un usuario con `sudo`. Reemplazá `192.168.1.19` por la IP de tu servidor.

**1 · Instalar Docker y Git**

```bash
sudo dnf -y install git dnf-plugins-core
sudo dnf config-manager --add-repo https://download.docker.com/linux/rhel/docker-ce.repo
sudo dnf -y install docker-ce docker-ce-cli containerd.io docker-compose-plugin
sudo systemctl enable --now docker
```

**2 · Clonar el proyecto**

```bash
sudo mkdir -p /opt/sigsm && cd /opt/sigsm
sudo git clone https://github.com/nexalud/UTU-ISBO-Proyecto-2026---S.I.G.S.M.git app
```

**3 · Crear los archivos de Docker** en `/opt/sigsm`

<details>
<summary><code>Dockerfile</code></summary>

```dockerfile
FROM php:8.5-apache
RUN docker-php-ext-install mysqli pdo_mysql \
 && { echo 'upload_max_filesize=10M'; echo 'post_max_size=12M'; } > /usr/local/etc/php/conf.d/sigsm.ini
```

</details>

<details>
<summary><code>docker-compose.yml</code></summary>

```yaml
services:
  app:
    build: .
    container_name: sigsm_app
    restart: unless-stopped
    ports:
      - "8080:80"
    environment:
      DB_HOST: db
      DB_NAME: ${DB_NAME}
      DB_USER: ${DB_USER}
      DB_PASSWORD: ${DB_PASSWORD}
      APP_URL: ${APP_URL}
      SMTP_HOST: ${SMTP_HOST}
      SMTP_PORT: ${SMTP_PORT}
      SMTP_SEGURIDAD: ${SMTP_SEGURIDAD}
      SMTP_USUARIO: ${SMTP_USUARIO}
      SMTP_CLAVE: ${SMTP_CLAVE}
    volumes:
      - ./app:/var/www/html:Z
    depends_on:
      db:
        condition: service_healthy
    networks: [sigsm_red]

  db:
    image: mariadb:11.8
    container_name: sigsm_db
    restart: unless-stopped
    environment:
      MARIADB_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MARIADB_DATABASE: ${DB_NAME}
      MARIADB_USER: ${DB_USER}
      MARIADB_PASSWORD: ${DB_PASSWORD}
    volumes:
      - db_datos:/var/lib/mysql
    healthcheck:
      test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
      interval: 10s
      retries: 5
    networks: [sigsm_red]

volumes:
  db_datos:

networks:
  sigsm_red:
```

</details>

<details>
<summary><code>.env</code> <sub>— contraseñas, nunca se sube a GitHub</sub></summary>

```ini
DB_ROOT_PASSWORD=cambiar_esto
DB_NAME=sigsm
DB_USER=sigsm_app
DB_PASSWORD=cambiar_esto
APP_URL=http://192.168.1.19:8080

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_SEGURIDAD=tls
SMTP_USUARIO=cuenta@gmail.com
SMTP_CLAVE=contraseña_de_aplicacion
```

```bash
sudo chmod 600 /opt/sigsm/.env
```

</details>

**4 · Levantar los contenedores**

```bash
cd /opt/sigsm
sudo docker compose up -d --build
sudo docker compose ps
```

**5 · Cargar la base**

```bash
for f in sigsm.sql actualizacion_tercera_entrega.sql datos_prueba_encuestas.sql; do
  sudo docker exec -i sigsm_db sh -c 'mariadb -u root -p"$MARIADB_ROOT_PASSWORD" sigsm' < app/BaseDatos/$f
done
```

**6 · Permisos, contraseña y firewall**

```bash
# Apache (usuario 33) tiene que poder guardar los PDF
sudo chown -R 33:33 app/Proyecto/Modulo_Documentacion/archivos

# Contraseña del administrador
sudo docker exec -it sigsm_app php /var/www/html/Proyecto/Servicios_Comunes/Autenticacion/cambiar_contrasena.php eBanfi

# Abrir el puerto
sudo firewall-cmd --permanent --add-port=8080/tcp
sudo firewall-cmd --reload
```

Abrí **http://192.168.1.19:8080** desde cualquier equipo de la red, también desde el celular para probar los QR. ✔

<details>
<summary><b>Actualizar a la última versión</b></summary>
<br>

```bash
cd /opt/sigsm/app && sudo git pull
cd /opt/sigsm && sudo docker compose restart app
```

</details>

---

## ✦ Estructura

```text
├── BaseDatos/              scripts SQL
├── Proyecto/
│   ├── Modulo_Documentacion/   documentos, categorías, QR, inicio
│   ├── Modulo_Encuestas/       encuestas y resultados
│   ├── Modulo_Ambulancias/     traslados
│   ├── Servicios_Comunes/      login, usuarios, correo, sesión
│   └── assets/                 imágenes compartidas
└── index.php               entrada → login
```

<div align="center">
<img src="https://capsule-render.vercel.app/api?type=waving&color=0:1565a0,100:0e4f80&height=110&section=footer" width="100%" alt="">
</div>
