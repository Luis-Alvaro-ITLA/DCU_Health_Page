<?php
require_once __DIR__ . '/../db/db.php';

$token = $_POST['token'];
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$row = $stmt->fetch();

if (!$row) {
    die("Token inválido o expirado.");
}

$email = $row['email'];

$pass_hash = password_hash($password, PASSWORD_BCRYPT);

$pdo->prepare("UPDATE users SET password = ? WHERE email = ?")->execute([$pass_hash, $email]);

$pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

header("Location: /pages/autentication/login.php");
exit;