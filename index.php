<?php
// Entrada del sistema en el servidor (http://192.168.1.19/).
// Los funcionarios caen en el login; los pacientes nunca pasan por aca, entran directo por el QR.

header('Location: Proyecto/Servicios_Comunes/Autenticacion/login.php', true, 302);
exit;
