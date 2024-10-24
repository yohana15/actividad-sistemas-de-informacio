<?php
try {
    $host = 'localhost';
    $dbname = 'miproyecto';
    $user = 'root';
    $passwordDB = '';
    $rol = '';
    $port = '3306';

    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $passwordDB);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}
?>