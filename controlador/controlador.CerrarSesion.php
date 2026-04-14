<?php
//Inicia la sesión y luego la destruye para cerrar la sesión
session_start();
session_destroy();
//Redirige al login
header('Location: ../vista/vistas/Login.php');
exit;
