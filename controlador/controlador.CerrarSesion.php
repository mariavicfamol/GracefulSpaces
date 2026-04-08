<?php
session_start();
session_destroy();
header('Location: ../vista/vistas/Login.php');
exit;
