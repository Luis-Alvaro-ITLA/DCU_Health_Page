<?php
session_start();
require_once __DIR__ . '/../db/db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Correo o contraseña incorrectos.";
    header("Location: /auth/login.php");
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['nombre'];
$_SESSION['role'] = $user['role'];

header("Location: /dashboard.php");
exit;
