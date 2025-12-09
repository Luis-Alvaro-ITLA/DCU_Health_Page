<?php
// pages/admin/users.php - Gestión de usuarios para administrador
session_start();
require_once "../../db/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/autentication/login.php');
    exit;
}

$message = '';
$messageType = '';

// Operaciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear usuario
    if (isset($_POST['crear_usuario'])) {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        
        // Verificar email único
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $message = 'El email ya está registrado';
            $messageType = 'danger';
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (nombre, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$nombre, $email, $password, $role])) {
                $nuevoUserId = $pdo->lastInsertId();
                
                // Crear perfil según rol
                if ($role === 'patient') {
                    $stmt = $pdo->prepare("INSERT INTO patient_profile (user_id) VALUES (?)");
                    $stmt->execute([$nuevoUserId]);
                } elseif ($role === 'doctor') {
                    $stmt = $pdo->prepare("INSERT INTO doctor_profile (user_id) VALUES (?)");
                    $stmt->execute([$nuevoUserId]);
                }
                
                $message = 'Usuario creado exitosamente';
                $messageType = 'success';
            } else {
                $message = 'Error al crear usuario';
                $messageType = 'danger';
            }
        }
    }
    
    // Actualizar usuario
    if (isset($_POST['actualizar_usuario'])) {
        $id = (int)$_POST['id'];
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE users SET nombre = ?, email = ?, role = ?, activo = ?, updated_at = NOW() WHERE id = ?");
        
        if ($stmt->execute([$nombre, $email, $role, $activo, $id])) {
            $message = 'Usuario actualizado';
            $messageType = 'success';
        } else {
            $message = 'Error al actualizar';
            $messageType = 'danger';
        }
    }
    
    // Restablecer contraseña
    if (isset($_POST['resetear_password'])) {
        $id = (int)$_POST['id'];
        $nuevaPassword = password_hash('Temp1234', PASSWORD_DEFAULT); // Contraseña temporal
        
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        
        if ($stmt->execute([$nuevaPassword, $id])) {
            $message = 'Contraseña restablecida a "Temp1234"';
            $messageType = 'success';
        } else {
            $message = 'Error al restablecer contraseña';
            $messageType = 'danger';
        }
    }
}

// Eliminar usuario
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    
    // No permitir eliminar a sí mismo
    if ($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        
        if ($stmt->execute([$id])) {
            $message = 'Usuario eliminado';
            $messageType = 'success';
        } else {
            $message = 'Error al eliminar usuario';
            $messageType = 'danger';
        }
    } else {
        $message = 'No puedes eliminar tu propio usuario';
        $messageType = 'danger';
    }
}

// Obtener lista de usuarios
$filtroRol = $_GET['rol'] ?? 'todos';
$where = "1=1";
$params = [];

if ($filtroRol !== 'todos') {
    $where = "role = ?";
    $params[] = $filtroRol;
}

$stmt = $pdo->prepare("
    SELECT u.*, 
           (SELECT COUNT(*) FROM appointments WHERE patient_id = u.id OR doctor_id = u.id) as total_citas,
           (SELECT COUNT(*) FROM medical_reports WHERE patient_id = u.id OR doctor_id = u.id) as total_reportes
    FROM users u 
    WHERE $where 
    ORDER BY u.created_at DESC
");
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

// Estadísticas
$stmt = $pdo->query("SELECT role, COUNT(*) as total FROM users GROUP BY role");
$statsRoles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de Usuarios - DCU Medical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestión de Usuarios</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoUsuarioModal">
                        <i class="bi bi-person-plus"></i> Nuevo Usuario
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <?php foreach ($statsRoles as $stat): ?>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5><?= $stat['total'] ?></h5>
                                    <p class="card-text"><?= ucfirst($stat['role']) ?>s</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5><?= count($usuarios) ?></h5>
                                <p class="card-text">Total Usuarios</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="?rol=todos" class="btn btn-outline-primary <?= $filtroRol === 'todos' ? 'active' : '' ?>">Todos</a>
                            <a href="?rol=patient" class="btn btn-outline-primary <?= $filtroRol === 'patient' ? 'active' : '' ?>">Pacientes</a>
                            <a href="?rol=doctor" class="btn btn-outline-primary <?= $filtroRol === 'doctor' ? 'active' : '' ?>">Doctores</a>
                            <a href="?rol=admin" class="btn btn-outline-primary <?= $filtroRol === 'admin' ? 'active' : '' ?>">Administradores</a>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Usuarios -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Usuarios</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaUsuarios">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Registro</th>
                                        <th>Estadísticas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?= $usuario['id'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>
                                                <?php if ($usuario['id'] == $_SESSION['user_id']): ?>
                                                    <span class="badge bg-info">Tú</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $usuario['role'] === 'admin' ? 'danger' : ($usuario['role'] === 'doctor' ? 'primary' : 'success') ?>">
                                                    <?= htmlspecialchars(ucfirst($usuario['role'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($usuario['activo']): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($usuario['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small>
                                                    Citas: <?= $usuario['total_citas'] ?><br>
                                                    Reportes: <?= $usuario['total_reportes'] ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" 
                                                            class="btn btn-info"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#verUsuarioModal<?= $usuario['id'] ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-warning"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editarUsuarioModal<?= $usuario['id'] ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-secondary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#resetPasswordModal<?= $usuario['id'] ?>">
                                                        <i class="bi bi-key"></i>
                                                    </button>
                                                    <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                                        <a href="?eliminar=<?= $usuario['id'] ?>" 
                                                           class="btn btn-danger"
                                                           onclick="return confirm('¿Eliminar usuario <?= addslashes($usuario['nombre']) ?>?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Ver Usuario -->
                                        <div class="modal fade" id="verUsuarioModal<?= $usuario['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Usuario #<?= $usuario['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6>Información Básica</h6>
                                                                <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
                                                                <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
                                                                <p><strong>Rol:</strong> 
                                                                    <span class="badge bg-<?= $usuario['role'] === 'admin' ? 'danger' : ($usuario['role'] === 'doctor' ? 'primary' : 'success') ?>">
                                                                        <?= htmlspecialchars(ucfirst($usuario['role'])) ?>
                                                                    </span>
                                                                </p>
                                                                <p><strong>Estado:</strong> 
                                                                    <?php if ($usuario['activo']): ?>
                                                                        <span class="badge bg-success">Activo</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-danger">Inactivo</span>
                                                                    <?php endif; ?>
                                                                </p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>Información del Sistema</h6>
                                                                <p><strong>ID:</strong> <?= $usuario['id'] ?></p>
                                                                <p><strong>Registro:</strong> <?= date('d/m/Y H:i', strtotime($usuario['created_at'])) ?></p>
                                                                <?php if ($usuario['updated_at']): ?>
                                                                    <p><strong>Última actualización:</strong> <?= date('d/m/Y H:i', strtotime($usuario['updated_at'])) ?></p>
                                                                <?php endif; ?>
                                                                <?php if ($usuario['last_login']): ?>
                                                                    <p><strong>Último acceso:</strong> <?= date('d/m/Y H:i', strtotime($usuario['last_login'])) ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <h6>Estadísticas</h6>
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="card">
                                                                            <div class="card-body text-center">
                                                                                <h3><?= $usuario['total_citas'] ?></h3>
                                                                                <p class="card-text">Citas</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="card">
                                                                            <div class="card-body text-center">
                                                                                <h3><?= $usuario['total_reportes'] ?></h3>
                                                                                <p class="card-text">Reportes</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Editar Usuario -->
                                        <div class="modal fade" id="editarUsuarioModal<?= $usuario['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Editar Usuario #<?= $usuario['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Nombre</label>
                                                                <input type="text" name="nombre" class="form-control" 
                                                                       value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Email</label>
                                                                <input type="email" name="email" class="form-control" 
                                                                       value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Rol</label>
                                                                <select name="role" class="form-select" required>
                                                                    <option value="patient" <?= $usuario['role'] === 'patient' ? 'selected' : '' ?>>Paciente</option>
                                                                    <option value="doctor" <?= $usuario['role'] === 'doctor' ? 'selected' : '' ?>>Doctor</option>
                                                                    <option value="admin" <?= $usuario['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3 form-check">
                                                                <input type="checkbox" name="activo" class="form-check-input" 
                                                                       id="activo<?= $usuario['id'] ?>" <?= $usuario['activo'] ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="activo<?= $usuario['id'] ?>">Usuario Activo</label>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" name="actualizar_usuario" class="btn btn-primary">Actualizar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Reset Password -->
                                        <div class="modal fade" id="resetPasswordModal<?= $usuario['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Restablecer Contraseña</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                                        <div class="modal-body">
                                                            <div class="alert alert-warning">
                                                                <i class="bi bi-exclamation-triangle"></i>
                                                                <strong>Advertencia:</strong> Se restablecerá la contraseña del usuario 
                                                                <strong><?= htmlspecialchars($usuario['nombre']) ?></strong> a "Temp1234".
                                                                El usuario deberá cambiar su contraseña en el próximo inicio de sesión.
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" name="resetear_password" class="btn btn-warning">Restablecer</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Nuevo Usuario -->
    <div class="modal fade" id="nuevoUsuarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña Temporal</label>
                            <input type="password" name="password" class="form-control" 
                                   value="Temp1234" required>
                            <small class="text-muted">El usuario deberá cambiar la contraseña en su primer acceso</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select name="role" class="form-select" required>
                                <option value="patient">Paciente</option>
                                <option value="doctor">Doctor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="crear_usuario" class="btn btn-primary">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tablaUsuarios').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                pageLength: 25,
                order: [[0, 'desc']]
            });
        });
    </script>
</body>
</html>