<?php
$host = "localhost";
$port = "3307"; 
$dbname = "jewelry_manager";
$username = "root";
$password = "";

try {
    $conexion = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Esto te mostrara el error exacto en rojo si algo falla
    die("Error de conexion a la base de datos: " . $e->getMessage());
}
?>