# Imagen web de S.I.G.S.M.: PHP 8.5 + Apache
FROM php:8.5-apache

RUN docker-php-ext-install mysqli pdo_mysql

# PDF de hasta 10 MB · session.upload_progress apagado (mitiga PHP2Shell, CVE-2026-109618/109619)
RUN { \
    echo 'upload_max_filesize=10M'; \
    echo 'post_max_size=12M'; \
    echo 'session.upload_progress.enabled=0'; \
} > /usr/local/etc/php/conf.d/sigsm.ini

# Apache no sirve lo que no es parte del sitio: git, scripts SQL, documentacion, docker y el .env
RUN { \
    echo 'RedirectMatch 404 "^/(\.git.*|\.env.*|BaseDatos|Documentacion|docker)(/|$)"'; \
    echo 'RedirectMatch 404 "^/README\.md$"'; \
} > /etc/apache2/conf-enabled/sigsm-bloqueos.conf
