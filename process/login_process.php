<?php
// process/login_process.php - Procesamiento de login
session_start();
require_once __DIR__ . '/../db/db.php';

// Obtener datos del formulario
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$loginType = $_POST['login_type'] ?? 'regular';

// Validaciones básicas
if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Por favor complete todos los campos.";
    header("Location: /pages/autentication/login.php");
    exit;
}

// Buscar usuario
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Verificar credenciales
if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Correo o contraseña incorrectos.";
    
    // Si fue intento de admin, redirigir con parámetro
    if ($loginType === 'admin') {
        header("Location: /pages/autentication/login.php?admin=true");
    } else {
        header("Location: /pages/autentication/login.php");
    }
    exit;
}

// Verificar acceso administrativo si se intentó login como admin
if ($loginType === 'admin' && $user['role'] !== 'admin') {
    $_SESSION['login_error'] = "Acceso administrativo denegado. Solo usuarios con rol 'admin' pueden acceder.";
    header("Location: /pages/autentication/login.php?admin=true");
    exit;
}

// Configurar sesión
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['nombre'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

// Agregar mensaje de bienvenida según rol
$welcomeMessages = [
    'patient' => "¡Bienvenido de nuevo, " . htmlspecialchars($user['nombre']) . "!",
    'doctor' => "¡Bienvenido Dr. " . htmlspecialchars($user['nombre']) . "!",
    'admin' => "¡Panel administrativo activado!"
];

if (isset($welcomeMessages[$user['role']])) {
    $_SESSION['success'] = $welcomeMessages[$user['role']];
}

// Redireccionar según rol
switch ($user['role']) {
    case 'patient':
        header("Location: /dashboard.php");
        break;
    case 'doctor':
        header("Location: /dashboard.php");
        break;
    case 'admin':
        // Si vino del formulario de admin o es admin, ir directamente a users.php
        if ($loginType === 'admin' || $user['role'] === 'admin') {
            header("Location: /pages/admin/users.php");
        } else {
            header("Location: /dashboard.php");
        }
        break;
    default:
        header("Location: /dashboard.php");
        break;
}
exit;