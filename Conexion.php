<?php
$host = "bxqtzm3rnnsr7lwf9akd-mysql.services.clever-cloud.com";
$dbname = "bxqtzm3rnnsr7lwf9akd";
$username = "udgygsl4vnqzvmgp";
$password = "e1vbFaaz1kHfFlZYbqII";
$port = 3306;

$conn = new mysqli($host, $username, $password, $dbname, $port);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

echo "Conexión exitosa";
?>