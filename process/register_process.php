<?php
require_once __DIR__ . '/../db/db.php';

$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$password_confirm = $_POST['password_confirm'];

if ($password !== $password_confirm) {
    die("Las contraseñas no coinciden.");
}

$pass_hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO users (nombre, email, password) VALUES (?, ?, ?)");
$stmt->execute(["$nombre $apellido", $email, $pass_hash]);

header("Location: /autentication/login.php");
exit;
