<?php
// pages/admin/users.php - Gestión de usuarios para administrador
session_start();

// Verificación de sesión (mantener real)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/autentication/login.php');
    exit;
}

$message = '';
$messageType = '';

// Simulación de operaciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear usuario (simulación)
    if (isset($_POST['crear_usuario'])) {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        
        if (empty($nombre) || empty($email) || empty($role)) {
            $message = 'Por favor complete todos los campos';
            $messageType = 'danger';
        } else {
            $message = "Usuario '$nombre' creado exitosamente (modo demostración)";
            $messageType = 'success';
        }
    }
    
    // Actualizar usuario (simulación)
    if (isset($_POST['actualizar_usuario'])) {
        $id = (int)$_POST['id'];
        $nombre = trim($_POST['nombre']);
        
        $message = "Usuario #$id actualizado (modo demostración)";
        $messageType = 'success';
    }
    
    // Restablecer contraseña (simulación)
    if (isset($_POST['resetear_password'])) {
        $id = (int)$_POST['id'];
        $message = "Contraseña restablecida para usuario #$id (modo demostración)";
        $messageType = 'success';
    }
}

// Simulación de operación GET
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    
    // No permitir eliminar a sí mismo en simulación
    if ($id != ($_SESSION['user_id'] ?? 1)) {
        $message = "Usuario #$id eliminado (modo demostración)";
        $messageType = 'success';
    } else {
        $message = 'No puedes eliminar tu propio usuario';
        $messageType = 'danger';
    }
}

// Datos de ejemplo para demostración
$rolesFiltro = ['todos', 'patient', 'doctor', 'admin'];
$filtroRol = $_GET['rol'] ?? 'todos';
if (!in_array($filtroRol, $rolesFiltro)) {
    $filtroRol = 'todos';
}

// Generar usuarios de ejemplo
$usuarios = [];
$rolesEjemplo = ['patient', 'doctor', 'admin'];
$nombresEjemplo = [
    'Ana García López', 'Carlos Martínez Ruiz', 'María Rodríguez Pérez',
    'Juan Fernández Gómez', 'Laura Sánchez Díaz', 'Pedro Jiménez Castro',
    'Sofía Navarro Vidal', 'David Romero Ortega', 'Elena Torres Medina',
    'Miguel Ángel Gil Santos', 'Isabel Castro Vega', 'Francisco Morales León'
];

for ($i = 1; $i <= 15; $i++) {
    $rol = $rolesEjemplo[array_rand($rolesEjemplo)];
    
    // Aplicar filtro si no es "todos"
    if ($filtroRol !== 'todos' && $filtroRol !== $rol) {
        continue;
    }
    
    $nombre = $nombresEjemplo[array_rand($nombresEjemplo)];
    $email = strtolower(str_replace(' ', '.', $nombre)) . '@ejemplo.com';
    
    $usuarios[] = [
        'id' => $i,
        'nombre' => $nombre,
        'email' => $email,
        'role' => $rol,
        'activo' => rand(0, 1),
        'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(0, 180) . ' days')),
        'updated_at' => rand(0, 1) ? date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' days')) : null,
        'last_login' => rand(0, 1) ? date('Y-m-d H:i:s', strtotime('-' . rand(0, 7) . ' days')) : null,
        'total_citas' => rand(0, 50),
        'total_reportes' => rand(0, 20)
    ];
}

// Estadísticas de ejemplo
$statsRoles = [
    ['role' => 'patient', 'total' => rand(5, 10)],
    ['role' => 'doctor', 'total' => rand(3, 6)],
    ['role' => 'admin', 'total' => rand(1, 3)]
];
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
    <style>
        .card-stat {
            border-left: 4px solid #007bff;
        }
        .card-stat.patient {
            border-left-color: #28a745;
        }
        .card-stat.doctor {
            border-left-color: #17a2b8;
        }
        .card-stat.admin {
            border-left-color: #dc3545;
        }
        .table td {
            vertical-align: middle;
        }
        .badge-role {
            padding: 0.4em 0.8em;
            font-size: 0.85em;
        }
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-people me-2"></i>Gestión de Usuarios
                    </h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoUsuarioModal">
                        <i class="bi bi-person-plus me-1"></i> Nuevo Usuario
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <i class="bi <?= $messageType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' ?> me-2"></i>
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <?php 
                    $totalUsuarios = count($usuarios);
                    $colors = ['patient' => 'success', 'doctor' => 'info', 'admin' => 'danger'];
                    ?>
                    
                    <?php foreach ($statsRoles as $stat): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card card-stat h-100 border-0 shadow-sm <?= $stat['role'] ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h2 class="mb-0"><?= $stat['total'] ?></h2>
                                            <p class="text-muted mb-0"><?= ucfirst($stat['role']) ?>s</p>
                                        </div>
                                        <div class="display-4 text-<?= $colors[$stat['role']] ?> opacity-25">
                                            <i class="bi bi-people-fill"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-0 shadow-sm bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="mb-0"><?= $totalUsuarios ?></h2>
                                        <p class="mb-0 opacity-75">Total Usuarios</p>
                                    </div>
                                    <div class="display-4 opacity-25">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bi bi-funnel me-2"></i>Filtrar por Rol
                        </h6>
                        <div class="btn-group" role="group">
                            <a href="?rol=todos" 
                               class="btn btn-outline-primary <?= $filtroRol === 'todos' ? 'active' : '' ?>">
                                <i class="bi bi-people me-1"></i> Todos
                            </a>
                            <a href="?rol=patient" 
                               class="btn btn-outline-success <?= $filtroRol === 'patient' ? 'active' : '' ?>">
                                <i class="bi bi-person me-1"></i> Pacientes
                            </a>
                            <a href="?rol=doctor" 
                               class="btn btn-outline-info <?= $filtroRol === 'doctor' ? 'active' : '' ?>">
                                <i class="bi bi-heart-pulse me-1"></i> Doctores
                            </a>
                            <a href="?rol=admin" 
                               class="btn btn-outline-danger <?= $filtroRol === 'admin' ? 'active' : '' ?>">
                                <i class="bi bi-shield-check me-1"></i> Administradores
                            </a>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Mostrando <?= count($usuarios) ?> usuarios
                                <?= $filtroRol !== 'todos' ? 'del rol ' . $filtroRol : '' ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Usuarios -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul me-2"></i>Lista de Usuarios
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaUsuarios">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Registro</th>
                                        <th>Estadísticas</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): 
                                        $esUsuarioActual = ($usuario['id'] == ($_SESSION['user_id'] ?? 1));
                                        $colorRol = [
                                            'admin' => 'danger',
                                            'doctor' => 'primary', 
                                            'patient' => 'success'
                                        ][$usuario['role']] ?? 'secondary';
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">#<?= $usuario['id'] ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-medium"><?= htmlspecialchars($usuario['nombre']) ?></div>
                                                <?php if ($esUsuarioActual): ?>
                                                    <span class="badge bg-info">Tú</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?= htmlspecialchars($usuario['email']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $colorRol ?> badge-role">
                                                    <i class="bi bi-<?= $usuario['role'] === 'admin' ? 'shield-check' : ($usuario['role'] === 'doctor' ? 'heart-pulse' : 'person') ?> me-1"></i>
                                                    <?= htmlspecialchars(ucfirst($usuario['role'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($usuario['activo']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i> Activo
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-x-circle me-1"></i> Inactivo
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($usuario['created_at'])) ?>
                                                    <br>
                                                    <span class="fst-italic"><?= date('H:i', strtotime($usuario['created_at'])) ?></span>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <span class="badge bg-light text-dark me-1">
                                                        <i class="bi bi-calendar-check me-1"></i><?= $usuario['total_citas'] ?>
                                                    </span>
                                                    <span class="badge bg-light text-dark">
                                                        <i class="bi bi-file-text me-1"></i><?= $usuario['total_reportes'] ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" 
                                                            class="btn btn-outline-info"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#verUsuarioModal<?= $usuario['id'] ?>"
                                                            title="Ver detalles">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-warning"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editarUsuarioModal<?= $usuario['id'] ?>"
                                                            title="Editar usuario">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-secondary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#resetPasswordModal<?= $usuario['id'] ?>"
                                                            title="Restablecer contraseña">
                                                        <i class="bi bi-key"></i>
                                                    </button>
                                                    <?php if (!$esUsuarioActual): ?>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger"
                                                                onclick="confirmarEliminacion(<?= $usuario['id'] ?>, '<?= addslashes($usuario['nombre']) ?>')"
                                                                title="Eliminar usuario">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Ver Usuario -->
                                        <div class="modal fade" id="verUsuarioModal<?= $usuario['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-person-badge me-2"></i>Usuario #<?= $usuario['id'] ?>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-4">
                                                            <div class="col-md-8">
                                                                <h5 class="text-primary"><?= htmlspecialchars($usuario['nombre']) ?></h5>
                                                                <p class="text-muted mb-0"><?= htmlspecialchars($usuario['email']) ?></p>
                                                            </div>
                                                            <div class="col-md-4 text-end">
                                                                <span class="badge bg-<?= $colorRol ?> px-3 py-2 fs-6">
                                                                    <i class="bi bi-<?= $usuario['role'] === 'admin' ? 'shield-check' : ($usuario['role'] === 'doctor' ? 'heart-pulse' : 'person') ?> me-1"></i>
                                                                    <?= htmlspecialchars(ucfirst($usuario['role'])) ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-4">
                                                                <div class="card h-100 border-0 shadow-sm">
                                                                    <div class="card-body">
                                                                        <h6 class="card-title text-primary mb-3">
                                                                            <i class="bi bi-info-circle me-2"></i>Información del Sistema
                                                                        </h6>
                                                                        <div class="row">
                                                                            <div class="col-12 mb-2">
                                                                                <small class="text-muted">ID de Usuario</small>
                                                                                <p class="mb-0"><strong>#<?= $usuario['id'] ?></strong></p>
                                                                            </div>
                                                                            <div class="col-12 mb-2">
                                                                                <small class="text-muted">Estado</small>
                                                                                <p class="mb-0">
                                                                                    <?php if ($usuario['activo']): ?>
                                                                                        <span class="badge bg-success">Activo</span>
                                                                                    <?php else: ?>
                                                                                        <span class="badge bg-secondary">Inactivo</span>
                                                                                    <?php endif; ?>
                                                                                </p>
                                                                            </div>
                                                                            <div class="col-12 mb-2">
                                                                                <small class="text-muted">Fecha de Registro</small>
                                                                                <p class="mb-0"><?= date('d/m/Y H:i', strtotime($usuario['created_at'])) ?></p>
                                                                            </div>
                                                                            <?php if ($usuario['updated_at']): ?>
                                                                            <div class="col-12 mb-2">
                                                                                <small class="text-muted">Última Actualización</small>
                                                                                <p class="mb-0"><?= date('d/m/Y H:i', strtotime($usuario['updated_at'])) ?></p>
                                                                            </div>
                                                                            <?php endif; ?>
                                                                            <?php if ($usuario['last_login']): ?>
                                                                            <div class="col-12">
                                                                                <small class="text-muted">Último Acceso</small>
                                                                                <p class="mb-0"><?= date('d/m/Y H:i', strtotime($usuario['last_login'])) ?></p>
                                                                            </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-4">
                                                                <div class="card h-100 border-0 shadow-sm">
                                                                    <div class="card-body">
                                                                        <h6 class="card-title text-primary mb-3">
                                                                            <i class="bi bi-bar-chart me-2"></i>Estadísticas
                                                                        </h6>
                                                                        <div class="row text-center">
                                                                            <div class="col-md-6 mb-3">
                                                                                <div class="card border-0 bg-light">
                                                                                    <div class="card-body py-3">
                                                                                        <h3 class="text-primary"><?= $usuario['total_citas'] ?></h3>
                                                                                        <p class="text-muted mb-0 small">Citas Totales</p>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6 mb-3">
                                                                                <div class="card border-0 bg-light">
                                                                                    <div class="card-body py-3">
                                                                                        <h3 class="text-primary"><?= $usuario['total_reportes'] ?></h3>
                                                                                        <p class="text-muted mb-0 small">Reportes</p>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="mt-3">
                                                                            <div class="progress mb-2" style="height: 8px;">
                                                                                <div class="progress-bar bg-primary" style="width: <?= min(100, $usuario['total_citas'] * 2) ?>%"></div>
                                                                            </div>
                                                                            <small class="text-muted">Nivel de actividad basado en citas</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            <i class="bi bi-x-lg me-1"></i> Cerrar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Editar Usuario -->
                                        <div class="modal fade" id="editarUsuarioModal<?= $usuario['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-warning text-dark">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-pencil-square me-2"></i>Editar Usuario #<?= $usuario['id'] ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" id="formEditar<?= $usuario['id'] ?>">
                                                        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                                        <div class="modal-body">
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
                                                                <label class="form-label">Rol</label>
                                                                <select name="role" class="form-select" required>
                                                                    <option value="patient" <?= $usuario['role'] === 'patient' ? 'selected' : '' ?>>Paciente</option>
                                                                    <option value="doctor" <?= $usuario['role'] === 'doctor' ? 'selected' : '' ?>>Doctor</option>
                                                                    <option value="admin" <?= $usuario['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3 form-check form-switch">
                                                                <input type="checkbox" name="activo" class="form-check-input" role="switch"
                                                                       id="activo<?= $usuario['id'] ?>" <?= $usuario['activo'] ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="activo<?= $usuario['id'] ?>">
                                                                    Usuario Activo
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                <i class="bi bi-x-lg me-1"></i> Cancelar
                                                            </button>
                                                            <button type="submit" name="actualizar_usuario" class="btn btn-warning">
                                                                <i class="bi bi-check-lg me-1"></i> Actualizar
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Reset Password -->
                                        <div class="modal fade" id="resetPasswordModal<?= $usuario['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-secondary text-white">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-key me-2"></i>Restablecer Contraseña
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" id="formReset<?= $usuario['id'] ?>">
                                                        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                                        <div class="modal-body">
                                                            <div class="alert alert-warning border-0">
                                                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                                <strong>Advertencia:</strong> Se restablecerá la contraseña del usuario 
                                                                <strong>"<?= htmlspecialchars($usuario['nombre']) ?>"</strong> 
                                                                a una contraseña temporal.
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Contraseña Temporal</label>
                                                                <input type="text" class="form-control" value="Temp1234" readonly>
                                                                <small class="text-muted">El usuario deberá cambiar esta contraseña en su próximo inicio de sesión.</small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                                <i class="bi bi-x-lg me-1"></i> Cancelar
                                                            </button>
                                                            <button type="submit" name="resetear_password" class="btn btn-warning">
                                                                <i class="bi bi-arrow-repeat me-1"></i> Restablecer
                                                            </button>
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
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formNuevoUsuario">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" 
                                   placeholder="Ej: Juan Pérez García" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   placeholder="usuario@ejemplo.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña Temporal</label>
                            <div class="input-group">
                                <input type="text" name="password" class="form-control" 
                                       value="Temp1234" required id="passwordInput">
                                <button type="button" class="btn btn-outline-secondary" onclick="generarPassword()">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                            </div>
                            <small class="text-muted">El usuario deberá cambiar la contraseña en su primer acceso</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select name="role" class="form-select" required>
                                <option value="">-- Seleccionar Rol --</option>
                                <option value="patient">Paciente</option>
                                <option value="doctor">Doctor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="alert alert-info border-0 small">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Nota:</strong> Este formulario es una demostración. En la versión real, los datos se guardarían en la base de datos.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i> Cancelar
                        </button>
                        <button type="submit" name="crear_usuario" class="btn btn-primary" id="btnCrearUsuario">
                            <i class="bi bi-check-lg me-1"></i> Crear Usuario
                        </button>
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
            // Inicializar DataTable
            $('#tablaUsuarios').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                order: [[0, 'desc']],
                responsive: true
            });
            
            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        // Generar contraseña aleatoria
        function generarPassword() {
            const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';
            let password = '';
            for (let i = 0; i < 10; i++) {
                password += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
            }
            document.getElementById('passwordInput').value = password;
        }
        
        // Confirmar eliminación
        function confirmarEliminacion(id, nombre) {
            if (confirm(`¿Está seguro de eliminar al usuario "${nombre}" (ID: ${id})?\n\nEsta acción no se puede deshacer.`)) {
                // En modo demostración, mostrar mensaje
                alert(`Usuario #${id} eliminado (modo demostración)\n\nEn la versión real, se redirigiría a: users.php?eliminar=${id}`);
            }
        }
        
        // Manejar envío de formularios
        document.addEventListener('submit', function(e) {
            if (e.target.id === 'formNuevoUsuario') {
                e.preventDefault();
                const btn = document.getElementById('btnCrearUsuario');
                const originalText = btn.innerHTML;
                
                // Simular procesamiento
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creando...';
                btn.disabled = true;
                
                setTimeout(() => {
                    alert('Usuario creado exitosamente (modo demostración)\n\nEn la versión real, se enviaría al servidor.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('nuevoUsuarioModal'));
                    modal.hide();
                }, 1500);
            }
            
            // Para formularios de edición y reset
            if (e.target.id && (e.target.id.startsWith('formEditar') || e.target.id.startsWith('formReset'))) {
                e.preventDefault();
                
                // Obtener ID del usuario
                const id = e.target.querySelector('input[name="id"]').value;
                
                if (e.target.id.startsWith('formEditar')) {
                    alert(`Usuario #${id} actualizado (modo demostración)`);
                } else {
                    alert(`Contraseña restablecida para usuario #${id} (modo demostración)`);
                }
                
                // Cerrar modal
                const modalId = e.target.id.replace('form', '#') + 'Modal' + id;
                const modal = bootstrap.Modal.getInstance(document.querySelector(modalId));
                modal.hide();
            }
        });
    </script>
</body>
</html>