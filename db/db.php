<?php
// db.php - conexión PDO reutilizable con tus credenciales correctas

$host = 'sql213.infinityfree.com';
$db   = 'if0_40547054_dcu_medical';
$user = 'if0_40547054';
$pass = 'C3OQEmEzSc'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('Error de conexión a la base de datos: ' . $e->getMessage());
}