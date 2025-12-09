<?php
// pages/appointments.php - Gestión de citas para pacientes
session_start();
require_once "../db/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../pages/autentication/login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Operaciones CRUD
$message = '';
$messageType = '';

// Crear nueva cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_cita'])) {
    $doctorId = (int)$_POST['doctor_id'];
    $fechaCita = $_POST['fecha_cita'];
    $horaCita = $_POST['hora_cita'];
    $motivo = trim($_POST['motivo']);
    $estado = 'pendiente'; // Estado inicial
    
    // Verificar disponibilidad
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND fecha_cita = ? AND hora_cita = ?");
    $stmt->execute([$doctorId, $fechaCita, $horaCita]);
    
    if ($stmt->fetchColumn() > 0) {
        $message = 'El doctor no está disponible en esa fecha/hora';
        $messageType = 'danger';
    } else {
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, fecha_cita, hora_cita, motivo, estado, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$userId, $doctorId, $fechaCita, $horaCita, $motivo, $estado])) {
            $message = 'Cita creada exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al crear la cita';
            $messageType = 'danger';
        }
    }
}

// Cancelar cita
if (isset($_GET['cancelar']) && is_numeric($_GET['cancelar'])) {
    $citaId = (int)$_GET['cancelar'];
    $stmt = $pdo->prepare("UPDATE appointments SET estado = 'cancelada' WHERE id = ? AND patient_id = ?");
    if ($stmt->execute([$citaId, $userId])) {
        $message = 'Cita cancelada';
        $messageType = 'success';
    } else {
        $message = 'Error al cancelar la cita';
        $messageType = 'danger';
    }
}

// Obtener lista de doctores para formulario
$stmt = $pdo->query("SELECT u.id, u.nombre, d.especialidad FROM users u JOIN doctor_profile d ON u.id = d.user_id WHERE u.role = 'doctor' ORDER BY u.nombre");
$doctores = $stmt->fetchAll();

// Obtener citas del paciente
$stmt = $pdo->prepare("
    SELECT a.*, u.nombre as doctor_nombre, d.especialidad 
    FROM appointments a 
    JOIN users u ON a.doctor_id = u.id 
    LEFT JOIN doctor_profile d ON u.id = d.user_id 
    WHERE a.patient_id = ? 
    ORDER BY a.fecha_cita DESC, a.hora_cita DESC
");
$stmt->execute([$userId]);
$citas = $stmt->fetchAll();

// Estado de colores
$estadoColores = [
    'pendiente' => 'warning',
    'confirmada' => 'success',
    'completada' => 'info',
    'cancelada' => 'danger'
];
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
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Mis Citas Médicas</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaCitaModal">
                        <i class="bi bi-plus-circle"></i> Nueva Cita
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Lista de Citas -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Citas Programadas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($citas)): ?>
                            <div class="alert alert-info">No tienes citas programadas.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Doctor</th>
                                            <th>Especialidad</th>
                                            <th>Motivo</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($citas as $cita): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?></td>
                                                <td><?= htmlspecialchars($cita['hora_cita']) ?></td>
                                                <td><?= htmlspecialchars($cita['doctor_nombre']) ?></td>
                                                <td><?= htmlspecialchars($cita['especialidad'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($cita['motivo']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $estadoColores[$cita['estado']] ?? 'secondary' ?>">
                                                        <?= htmlspecialchars(ucfirst($cita['estado'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (in_array($cita['estado'], ['pendiente', 'confirmada'])): ?>
                                                        <a href="?cancelar=<?= $cita['id'] ?>" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('¿Estás seguro de cancelar esta cita?')">
                                                            <i class="bi bi-x-circle"></i> Cancelar
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
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

    <!-- Modal Nueva Cita -->
    <div class="modal fade" id="nuevaCitaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Cita Médica</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Doctor</label>
                            <select name="doctor_id" class="form-select" required>
                                <option value="">Seleccionar doctor...</option>
                                <?php foreach ($doctores as $doctor): ?>
                                    <option value="<?= $doctor['id'] ?>">
                                        Dr. <?= htmlspecialchars($doctor['nombre']) ?> - <?= htmlspecialchars($doctor['especialidad']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha_cita" class="form-control" 
                                   min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hora</label>
                            <input type="time" name="hora_cita" class="form-control" 
                                   min="08:00" max="18:00" step="1800" required>
                            <small class="text-muted">Horario: 8:00 AM - 6:00 PM (cada 30 min)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo de la consulta</label>
                            <textarea name="motivo" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="crear_cita" class="btn btn-primary">Agendar Cita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configurar fecha mínima como hoy
        document.querySelector('input[name="fecha_cita"]').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>