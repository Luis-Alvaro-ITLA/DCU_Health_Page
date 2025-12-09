<?php
require_once __DIR__ . '/../db/db.php';

$email = $_POST['email'];

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    die("El correo no existe.");
}

$token = bin2hex(random_bytes(32));
$expira = date("Y-m-d H:i:s", time() + 3600);

$pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)")
    ->execute([$email, $token, $expira]);

$enlace = "https://tu-dominio.com/autentication/reset_password.php?token=$token";

echo "Se envió un enlace a tu correo (simulación): $enlace";
