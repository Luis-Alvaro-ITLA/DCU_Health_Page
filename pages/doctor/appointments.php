<?php
// pages/doctor/appointments.php - Gestión de citas para doctores
session_start();
require_once "../../db/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header('Location: ../../pages/autentication/login.php');
    exit;
}

$doctorId = (int)$_SESSION['user_id'];
$message = '';
$messageType = '';

// Cambiar estado de cita
if (isset($_POST['cambiar_estado'])) {
    $citaId = (int)$_POST['cita_id'];
    $nuevoEstado = $_POST['nuevo_estado'];
    $comentario = trim($_POST['comentario'] ?? '');
    
    $stmt = $pdo->prepare("UPDATE appointments SET estado = ?, comentario_doctor = ?, updated_at = NOW() WHERE id = ? AND doctor_id = ?");
    
    if ($stmt->execute([$nuevoEstado, $comentario, $citaId, $doctorId])) {
        $message = 'Estado de cita actualizado';
        $messageType = 'success';
    } else {
        $message = 'Error al actualizar';
        $messageType = 'danger';
    }
}

// Obtener citas del doctor
$filtro = $_GET['filtro'] ?? 'todos';
$where = "WHERE a.doctor_id = ?";
$params = [$doctorId];

if ($filtro === 'hoy') {
    $where .= " AND a.fecha_cita = CURDATE()";
} elseif ($filtro === 'proximas') {
    $where .= " AND a.fecha_cita >= CURDATE() AND a.estado IN ('pendiente','confirmada')";
} elseif ($filtro === 'pasadas') {
    $where .= " AND a.fecha_cita < CURDATE()";
} elseif ($filtro === 'pendientes') {
    $where .= " AND a.estado = 'pendiente'";
} elseif ($filtro === 'confirmadas') {
    $where .= " AND a.estado = 'confirmada'";
}

$stmt = $pdo->prepare("
    SELECT a.*, u.nombre as paciente_nombre, u.email as paciente_email,
           p.fecha_nacimiento, p.sexo, p.tipo_sangre
    FROM appointments a 
    JOIN users u ON a.patient_id = u.id 
    LEFT JOIN patient_profile p ON u.id = p.user_id 
    $where 
    ORDER BY a.fecha_cita DESC, a.hora_cita DESC
");
$stmt->execute($params);
$citas = $stmt->fetchAll();

// Estadísticas rápidas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND fecha_cita = CURDATE()");
$stmt->execute([$doctorId]);
$citasHoy = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND estado = 'pendiente'");
$stmt->execute([$doctorId]);
$citasPendientes = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mis Citas - DCU Medical</title>
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
                    <h1 class="h2">Gestión de Citas</h1>
                    <div class="btn-group">
                        <span class="badge bg-primary me-2">Hoy: <?= $citasHoy ?></span>
                        <span class="badge bg-warning">Pendientes: <?= $citasPendientes ?></span>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="?filtro=todos" class="btn btn-outline-primary <?= $filtro === 'todos' ? 'active' : '' ?>">Todas</a>
                            <a href="?filtro=hoy" class="btn btn-outline-primary <?= $filtro === 'hoy' ? 'active' : '' ?>">Hoy</a>
                            <a href="?filtro=proximas" class="btn btn-outline-primary <?= $filtro === 'proximas' ? 'active' : '' ?>">Próximas</a>
                            <a href="?filtro=pendientes" class="btn btn-outline-warning <?= $filtro === 'pendientes' ? 'active' : '' ?>">Pendientes</a>
                            <a href="?filtro=confirmadas" class="btn btn-outline-success <?= $filtro === 'confirmadas' ? 'active' : '' ?>">Confirmadas</a>
                            <a href="?filtro=pasadas" class="btn btn-outline-secondary <?= $filtro === 'pasadas' ? 'active' : '' ?>">Pasadas</a>
                        </div>
                    </div>
                </div>

                <!-- Lista de Citas -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Citas Médicas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($citas)): ?>
                            <div class="alert alert-info">No hay citas para mostrar con el filtro actual.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Paciente</th>
                                            <th>Motivo</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($citas as $cita): ?>
                                            <tr>
                                                <td>
                                                    <?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?>
                                                    <?php if ($cita['fecha_cita'] == date('Y-m-d')): ?>
                                                        <span class="badge bg-info">Hoy</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($cita['hora_cita']) ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($cita['paciente_nombre']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($cita['paciente_email']) ?></small>
                                                    <?php if ($cita['fecha_nacimiento']): ?>
                                                        <br><small><?= floor((time() - strtotime($cita['fecha_nacimiento'])) / 31556926) ?> años</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($cita['motivo']) ?></td>
                                                <td>
                                                    <?php
                                                    $estadoColores = [
                                                        'pendiente' => 'warning',
                                                        'confirmada' => 'success',
                                                        'completada' => 'info',
                                                        'cancelada' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?= $estadoColores[$cita['estado']] ?? 'secondary' ?>">
                                                        <?= htmlspecialchars(ucfirst($cita['estado'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" 
                                                                class="btn btn-info"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#detalleCitaModal<?= $cita['id'] ?>">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        
                                                        <?php if (in_array($cita['estado'], ['pendiente', 'confirmada'])): ?>
                                                            <button type="button" 
                                                                    class="btn btn-warning"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#cambiarEstadoModal<?= $cita['id'] ?>">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Modal Detalles -->
                                            <div class="modal fade" id="detalleCitaModal<?= $cita['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detalles de Cita #<?= $cita['id'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <h6>Información del Paciente</h6>
                                                                    <p><strong>Nombre:</strong> <?= htmlspecialchars($cita['paciente_nombre']) ?></p>
                                                                    <p><strong>Email:</strong> <?= htmlspecialchars($cita['paciente_email']) ?></p>
                                                                    <?php if ($cita['fecha_nacimiento']): ?>
                                                                        <p><strong>Fecha Nac.:</strong> <?= date('d/m/Y', strtotime($cita['fecha_nacimiento'])) ?></p>
                                                                        <p><strong>Edad:</strong> <?= floor((time() - strtotime($cita['fecha_nacimiento'])) / 31556926) ?> años</p>
                                                                    <?php endif; ?>
                                                                    <?php if ($cita['sexo']): ?>
                                                                        <p><strong>Sexo:</strong> <?= htmlspecialchars($cita['sexo']) ?></p>
                                                                    <?php endif; ?>
                                                                    <?php if ($cita['tipo_sangre']): ?>
                                                                        <p><strong>Tipo Sangre:</strong> <?= htmlspecialchars($cita['tipo_sangre']) ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6>Información de la Cita</h6>
                                                                    <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?></p>
                                                                    <p><strong>Hora:</strong> <?= htmlspecialchars($cita['hora_cita']) ?></p>
                                                                    <p><strong>Estado:</strong> 
                                                                        <span class="badge bg-<?= $estadoColores[$cita['estado']] ?? 'secondary' ?>">
                                                                            <?= htmlspecialchars(ucfirst($cita['estado'])) ?>
                                                                        </span>
                                                                    </p>
                                                                    <p><strong>Motivo:</strong><br><?= nl2br(htmlspecialchars($cita['motivo'])) ?></p>
                                                                    <?php if ($cita['comentario_doctor']): ?>
                                                                        <p><strong>Comentario:</strong><br><?= nl2br(htmlspecialchars($cita['comentario_doctor'])) ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <p><strong>Historial:</strong></p>
                                                                    <small class="text-muted">
                                                                        Creación: <?= date('d/m/Y H:i', strtotime($cita['created_at'])) ?>
                                                                        <?php if ($cita['updated_at']): ?>
                                                                            | Última actualización: <?= date('d/m/Y H:i', strtotime($cita['updated_at'])) ?>
                                                                        <?php endif; ?>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                            <a href="../doctor/reports.php?paciente=<?= $cita['patient_id'] ?>" 
                                                               class="btn btn-primary">
                                                                <i class="bi bi-file-medical"></i> Ver Reportes
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Cambiar Estado -->
                                            <div class="modal fade" id="cambiarEstadoModal<?= $cita['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Cambiar Estado de Cita #<?= $cita['id'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <input type="hidden" name="cita_id" value="<?= $cita['id'] ?>">
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Nuevo Estado</label>
                                                                    <select name="nuevo_estado" class="form-select" required>
                                                                        <option value="confirmada" <?= $cita['estado'] === 'pendiente' ? 'selected' : '' ?>>Confirmar</option>
                                                                        <option value="completada" <?= $cita['estado'] === 'confirmada' ? 'selected' : '' ?>>Marcar como Completada</option>
                                                                        <option value="cancelada">Cancelar</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Comentario (opcional)</label>
                                                                    <textarea name="comentario" class="form-control" rows="3" 
                                                                              placeholder="Notas para el paciente o para el registro..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="cambiar_estado" class="btn btn-primary">Actualizar Estado</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>