#!/bin/bash
#
# Arma la replicacion Master-Slave de S.I.G.S.M. desde cero.
# Copia la base del master al slave y deja la replicacion corriendo.
#
# Uso:  sudo setup_replicacion.sh            arma la replicacion si no esta andando
#       sudo setup_replicacion.sh --forzar   la rehace aunque este andando
#
# Orden en una instalacion nueva:
#   docker compose up -d -> setup_replicacion.sh -> usuarios_bd.sql -> contrasenas

set -o pipefail

if [[ $EUID -ne 0 ]]; then
    echo Este script necesita permisos de administrador. Usa sudo
    exit 1
fi

master=${SIGSM_MASTER:-sigsm_db}
slave=${SIGSM_SLAVE:-sigsm_db_slave}
master_host=${SIGSM_MASTER_HOST:-db}
base=sigsm
forzar=$1

sql(){
    local container=$1
    shift
    docker exec -i "$container" sh -c 'exec mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" "$@"' sh "$@"
}

estado_slave(){
    sql "$slave" -e 'SHOW SLAVE STATUS\G' 2>/dev/null | awk -F': ' -v campo="$1" '$1 ~ campo"$" {print $2}'
}

esperar_base(){
    local intentos=0
    while [[ $intentos -lt 36 ]]; do
        if sql "$1" -N -e 'SELECT 1' &>/dev/null; then
            return 0
        fi
        sleep 5
        intentos=$((intentos + 1))
    done
    return 1
}

# ---------- Controles antes de tocar nada ----------

for container in "$master" "$slave"; do
    if ! esperar_base "$container"; then
        echo "Error: la base $container no responde"
        exit 1
    fi
done

tablas=$(sql "$master" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$base'")
if [[ -z $tablas || $tablas -eq 0 ]]; then
    echo "Error: el master no tiene la base $base cargada"
    exit 1
fi

if [[ $(sql "$master" -N -e 'SELECT @@log_bin') != "1" ]]; then
    echo "Error: el master no tiene el binlog activo. Revisa docker-compose.yml"
    exit 1
fi

if [[ $(estado_slave Slave_IO_Running) == "Yes" && $(estado_slave Slave_SQL_Running) == "Yes" && $forzar != "--forzar" ]]; then
    echo "La replicacion ya esta funcionando. Para rehacerla usa --forzar"
    exit 0
fi

# ---------- Usuario de replicacion ----------
# La clave se genera en cada corrida y solo la guarda el slave.
# Va por stdin para que no aparezca en la lista de procesos.

clave=$(openssl rand -hex 16)
if [[ -z $clave ]]; then
    echo "Error: no se pudo generar la clave"
    exit 1
fi

echo "Creando usuario de replicacion en el master..."
sql "$master" <<EOF
SET SESSION sql_log_bin=0;
CREATE USER IF NOT EXISTS 'replicador'@'%' IDENTIFIED BY '$clave';
ALTER USER 'replicador'@'%' IDENTIFIED BY '$clave';
GRANT REPLICATION SLAVE ON *.* TO 'replicador'@'%';
EOF
if [[ $? -ne 0 ]]; then
    echo "Error: no se pudo crear el usuario de replicacion"
    exit 1
fi

# ---------- Copia de la base al slave ----------

echo "Preparando el slave..."
sql "$slave" <<EOF
STOP SLAVE;
RESET SLAVE ALL;
DROP DATABASE IF EXISTS $base;
CHANGE MASTER TO
    MASTER_HOST='$master_host',
    MASTER_USER='replicador',
    MASTER_PASSWORD='$clave',
    MASTER_PORT=3306,
    MASTER_CONNECT_RETRY=10;
EOF
if [[ $? -ne 0 ]]; then
    echo "Error: no se pudo preparar el slave"
    exit 1
fi

# --master-data=1 agrega al dump la posicion exacta del binlog
echo "Copiando la base del master al slave..."
docker exec "$master" sh -c 'exec mariadb-dump -uroot -p"$MARIADB_ROOT_PASSWORD" --single-transaction --master-data=1 --routines --events --triggers --databases '"$base" \
    | sql "$slave"
if [[ $? -ne 0 ]]; then
    echo "Error: fallo la copia de la base"
    exit 1
fi

sql "$slave" -e 'START SLAVE;'

# ---------- Verificacion ----------

echo "Esperando que el slave se conecte..."
intentos=0
io=""
sql_hilo=""
while [[ $intentos -lt 30 && ( $io != "Yes" || $sql_hilo != "Yes" ) ]]; do
    sleep 2
    io=$(estado_slave Slave_IO_Running)
    sql_hilo=$(estado_slave Slave_SQL_Running)
    intentos=$((intentos + 1))
done

if [[ $io != "Yes" || $sql_hilo != "Yes" ]]; then
    echo "Error: la replicacion no arranco (IO=$io SQL=$sql_hilo)"
    estado_slave Last_IO_Error
    estado_slave Last_SQL_Error
    exit 1
fi

tablas_slave=$(sql "$slave" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$base'")
if [[ $tablas != "$tablas_slave" ]]; then
    echo "Error: el master tiene $tablas tablas y el slave $tablas_slave"
    exit 1
fi

echo "Replicacion funcionando: IO=$io SQL=$sql_hilo, $tablas_slave tablas en el slave"

# Los usuarios de la base no se copian (el dump es solo de $base): llegan por replicacion.
usuarios_master=$(sql "$master" -N -e "SELECT COUNT(*) FROM mysql.user WHERE user IN ('sigsm_app','sigsm_admin')")
usuarios_slave=$(sql "$slave" -N -e "SELECT COUNT(*) FROM mysql.user WHERE user IN ('sigsm_app','sigsm_admin')")
if [[ $usuarios_master != "$usuarios_slave" ]]; then
    echo "Aviso: faltan usuarios de la base en el slave. Volve a cargar usuarios_bd.sql en el master y se replican solos"
fi
