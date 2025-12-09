<?php
// pages/autentication/profile.php - Perfil de usuario
session_start();
require_once "../../db/db.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];
$message = '';
$messageType = '';

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: login.php');
    exit;
}

// Obtener perfil específico según rol
$perfil = null;
if ($role === 'patient') {
    $stmt = $pdo->prepare("SELECT * FROM patient_profile WHERE user_id = ?");
    $stmt->execute([$userId]);
    $perfil = $stmt->fetch();
    
    if (!$perfil) {
        // Crear perfil vacío si no existe
        $stmt = $pdo->prepare("INSERT INTO patient_profile (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        $perfil = ['user_id' => $userId];
    }
} elseif ($role === 'doctor') {
    $stmt = $pdo->prepare("SELECT * FROM doctor_profile WHERE user_id = ?");
    $stmt->execute([$userId]);
    $perfil = $stmt->fetch();
    
    if (!$perfil) {
        $stmt = $pdo->prepare("INSERT INTO doctor_profile (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        $perfil = ['user_id' => $userId];
    }
}

// Actualizar perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar información básica del usuario
    if (isset($_POST['actualizar_basico'])) {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $telefono = trim($_POST['telefono'] ?? '');
        
        // Verificar email único (excepto para el mismo usuario)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        
        if ($stmt->fetch()) {
            $message = 'El email ya está registrado por otro usuario';
            $messageType = 'danger';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nombre = ?, email = ?, telefono = ?, updated_at = NOW() WHERE id = ?");
            
            if ($stmt->execute([$nombre, $email, $telefono, $userId])) {
                $usuario['nombre'] = $nombre;
                $usuario['email'] = $email;
                $usuario['telefono'] = $telefono;
                
                $message = 'Información básica actualizada';
                $messageType = 'success';
            } else {
                $message = 'Error al actualizar información';
                $messageType = 'danger';
            }
        }
    }
    
    // Actualizar perfil específico
    if (isset($_POST['actualizar_perfil'])) {
        if ($role === 'patient') {
            $fecha_nacimiento = $_POST['fecha_nacimiento'] ?: null;
            $sexo = $_POST['sexo'] ?? null;
            $tipo_sangre = trim($_POST['tipo_sangre'] ?? '');
            $alergias = trim($_POST['alergias'] ?? '');
            $condiciones_medicas = trim($_POST['condiciones_medicas'] ?? '');
            $medicamentos = trim($_POST['medicamentos'] ?? '');
            $contacto_emergencia = trim($_POST['contacto_emergencia'] ?? '');
            $telefono_emergencia = trim($_POST['telefono_emergencia'] ?? '');
            
            $stmt = $pdo->prepare("
                UPDATE patient_profile 
                SET fecha_nacimiento = ?, sexo = ?, tipo_sangre = ?, alergias = ?, 
                    condiciones_medicas = ?, medicamentos = ?, contacto_emergencia = ?, 
                    telefono_emergencia = ?, updated_at = NOW() 
                WHERE user_id = ?
            ");
            
            if ($stmt->execute([
                $fecha_nacimiento, $sexo, $tipo_sangre, $alergias, $condiciones_medicas,
                $medicamentos, $contacto_emergencia, $telefono_emergencia, $userId
            ])) {
                $perfil = array_merge($perfil ?: [], [
                    'fecha_nacimiento' => $fecha_nacimiento,
                    'sexo' => $sexo,
                    'tipo_sangre' => $tipo_sangre,
                    'alergias' => $alergias,
                    'condiciones_medicas' => $condiciones_medicas,
                    'medicamentos' => $medicamentos,
                    'contacto_emergencia' => $contacto_emergencia,
                    'telefono_emergencia' => $telefono_emergencia
                ]);
                
                $message = 'Perfil médico actualizado';
                $messageType = 'success';
            }
        } elseif ($role === 'doctor') {
            $especialidad = trim($_POST['especialidad'] ?? '');
            $numero_licencia = trim($_POST['numero_licencia'] ?? '');
            $universidad = trim($_POST['universidad'] ?? '');
            $ano_graduacion = $_POST['ano_graduacion'] ?: null;
            $telefono_profesional = trim($_POST['telefono_profesional'] ?? '');
            $horario_consulta = trim($_POST['horario_consulta'] ?? '');
            $biografia = trim($_POST['biografia'] ?? '');
            
            $stmt = $pdo->prepare("
                UPDATE doctor_profile 
                SET especialidad = ?, numero_licencia = ?, universidad = ?, ano_graduacion = ?, 
                    telefono_profesional = ?, horario_consulta = ?, biografia = ?, updated_at = NOW() 
                WHERE user_id = ?
            ");
            
            if ($stmt->execute([
                $especialidad, $numero_licencia, $universidad, $ano_graduacion,
                $telefono_profesional, $horario_consulta, $biografia, $userId
            ])) {
                $perfil = array_merge($perfil ?: [], [
                    'especialidad' => $especialidad,
                    'numero_licencia' => $numero_licencia,
                    'universidad' => $universidad,
                    'ano_graduacion' => $ano_graduacion,
                    'telefono_profesional' => $telefono_profesional,
                    'horario_consulta' => $horario_consulta,
                    'biografia' => $biografia
                ]);
                
                $message = 'Perfil profesional actualizado';
                $messageType = 'success';
            }
        }
    }
    
    // Cambiar contraseña
    if (isset($_POST['cambiar_password'])) {
        $password_actual = $_POST['password_actual'];
        $password_nueva = $_POST['password_nueva'];
        $password_confirmar = $_POST['password_confirmar'];
        
        // Verificar contraseña actual
        if (!password_verify($password_actual, $usuario['password'])) {
            $message = 'La contraseña actual es incorrecta';
            $messageType = 'danger';
        } elseif ($password_nueva !== $password_confirmar) {
            $message = 'Las nuevas contraseñas no coinciden';
            $messageType = 'danger';
        } elseif (strlen($password_nueva) < 6) {
            $message = 'La nueva contraseña debe tener al menos 6 caracteres';
            $messageType = 'danger';
        } else {
            $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            
            if ($stmt->execute([$password_hash, $userId])) {
                $message = 'Contraseña actualizada exitosamente';
                $messageType = 'success';
            } else {
                $message = 'Error al actualizar contraseña';
                $messageType = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mi Perfil - DCU Medical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Mi Perfil</h1>
                    <span class="badge bg-<?= $role === 'admin' ? 'danger' : ($role === 'doctor' ? 'primary' : 'success') ?>">
                        <?= htmlspecialchars(ucfirst($role)) ?>
                    </span>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Información Básica -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-person-circle"></i> Información Básica</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre Completo</label>
                                        <input type="text" name="nombre" class="form-control" 
                                               value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" 
                                               value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="tel" name="telefono" class="form-control" 
                                               value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Rol</label>
                                        <input type="text" class="form-control" 
                                               value="<?= htmlspecialchars(ucfirst($role)) ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Fecha de Registro</label>
                                        <input type="text" class="form-control" 
                                               value="<?= date('d/m/Y H:i', strtotime($usuario['created_at'])) ?>" disabled>
                                    </div>
                                    <button type="submit" name="actualizar_basico" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Actualizar Información
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Perfil Específico -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-<?= $role === 'doctor' ? 'person-badge' : 'heart-pulse' ?>"></i>
                                    <?= $role === 'patient' ? 'Perfil Médico' : 'Perfil Profesional' ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <?php if ($role === 'patient'): ?>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Fecha de Nacimiento</label>
                                                <input type="date" name="fecha_nacimiento" class="form-control" 
                                                       value="<?= htmlspecialchars($perfil['fecha_nacimiento'] ?? '') ?>"
                                                       max="<?= date('Y-m-d') ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Sexo</label>
                                                <select name="sexo" class="form-select">
                                                    <option value="">Seleccionar...</option>
                                                    <option value="Masculino" <?= ($perfil['sexo'] ?? '') === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                                    <option value="Femenino" <?= ($perfil['sexo'] ?? '') === 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                                                    <option value="Otro" <?= ($perfil['sexo'] ?? '') === 'Otro' ? 'selected' : '' ?>>Otro</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tipo de Sangre</label>
                                            <select name="tipo_sangre" class="form-select">
                                                <option value="">Seleccionar...</option>
                                                <option value="A+" <?= ($perfil['tipo_sangre'] ?? '') === 'A+' ? 'selected' : '' ?>>A+</option>
                                                <option value="A-" <?= ($perfil['tipo_sangre'] ?? '') === 'A-' ? 'selected' : '' ?>>A-</option>
                                                <option value="B+" <?= ($perfil['tipo_sangre'] ?? '') === 'B+' ? 'selected' : '' ?>>B+</option>
                                                <option value="B-" <?= ($perfil['tipo_sangre'] ?? '') === 'B-' ? 'selected' : '' ?>>B-</option>
                                                <option value="AB+" <?= ($perfil['tipo_sangre'] ?? '') === 'AB+' ? 'selected' : '' ?>>AB+</option>
                                                <option value="AB-" <?= ($perfil['tipo_sangre'] ?? '') === 'AB-' ? 'selected' : '' ?>>AB-</option>
                                                <option value="O+" <?= ($perfil['tipo_sangre'] ?? '') === 'O+' ? 'selected' : '' ?>>O+</option>
                                                <option value="O-" <?= ($perfil['tipo_sangre'] ?? '') === 'O-' ? 'selected' : '' ?>>O-</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Alergias Conocidas</label>
                                            <textarea name="alergias" class="form-control" rows="2"><?= htmlspecialchars($perfil['alergias'] ?? '') ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Condiciones Médicas Crónicas</label>
                                            <textarea name="condiciones_medicas" class="form-control" rows="2"><?= htmlspecialchars($perfil['condiciones_medicas'] ?? '') ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Medicamentos Actuales</label>
                                            <textarea name="medicamentos" class="form-control" rows="2"><?= htmlspecialchars($perfil['medicamentos'] ?? '') ?></textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Contacto de Emergencia</label>
                                                <input type="text" name="contacto_emergencia" class="form-control" 
                                                       value="<?= htmlspecialchars($perfil['contacto_emergencia'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Teléfono de Emergencia</label>
                                                <input type="tel" name="telefono_emergencia" class="form-control" 
                                                       value="<?= htmlspecialchars($perfil['telefono_emergencia'] ?? '') ?>">
                                            </div>
                                        </div>
                                        
                                    <?php elseif ($role === 'doctor'): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Especialidad</label>
                                            <input type="text" name="especialidad" class="form-control" 
                                                   value="<?= htmlspecialchars($perfil['especialidad'] ?? '') ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Número de Licencia</label>
                                            <input type="text" name="numero_licencia" class="form-control" 
                                                   value="<?= htmlspecialchars($perfil['numero_licencia'] ?? '') ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Universidad</label>
                                            <input type="text" name="universidad" class="form-control" 
                                                   value="<?= htmlspecialchars($perfil['universidad'] ?? '') ?>">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Año de Graduación</label>
                                                <input type="number" name="ano_graduacion" class="form-control" 
                                                       min="1900" max="<?= date('Y') ?>" 
                                                       value="<?= htmlspecialchars($perfil['ano_graduacion'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Teléfono Profesional</label>
                                                <input type="tel" name="telefono_profesional" class="form-control" 
                                                       value="<?= htmlspecialchars($perfil['telefono_profesional'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Horario de Consulta</label>
                                            <input type="text" name="horario_consulta" class="form-control" 
                                                   value="<?= htmlspecialchars($perfil['horario_consulta'] ?? '') ?>"
                                                   placeholder="Ej: Lunes a Viernes 8:00 AM - 5:00 PM">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Biografía Profesional</label>
                                            <textarea name="biografia" class="form-control" rows="4"><?= htmlspecialchars($perfil['biografia'] ?? '') ?></textarea>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <button type="submit" name="actualizar_perfil" class="btn btn-success">
                                        <i class="bi bi-save"></i> Actualizar Perfil
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Cambiar Contraseña -->
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Seguridad</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Contraseña Actual</label>
                                            <input type="password" name="password_actual" class="form-control" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Nueva Contraseña</label>
                                            <input type="password" name="password_nueva" class="form-control" required minlength="6">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Confirmar Nueva Contraseña</label>
                                            <input type="password" name="password_confirmar" class="form-control" required minlength="6">
                                        </div>
                                    </div>
                                    <button type="submit" name="cambiar_password" class="btn btn-warning">
                                        <i class="bi bi-key"></i> Cambiar Contraseña
                                    </button>
                                    <small class="text-muted ms-3">La contraseña debe tener al menos 6 caracteres</small>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Sistema -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Sistema</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>ID de Usuario:</strong> <?= $userId ?></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Última Actualización:</strong> 
                                    <?= $usuario['updated_at'] ? date('d/m/Y H:i', strtotime($usuario['updated_at'])) : 'Nunca' ?>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Último Acceso:</strong> 
                                    <?= $usuario['last_login'] ? date('d/m/Y H:i', strtotime($usuario['last_login'])) : 'Nunca' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>