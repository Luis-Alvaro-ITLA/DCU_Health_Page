<?php
// pages/appointments.php - Gestión de citas para pacientes
session_start();

// Verificación de sesión (mantener real)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../pages/autentication/login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Operaciones CRUD (simuladas)
$message = '';
$messageType = '';

// Crear nueva cita (simulación)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_cita'])) {
    // Simulación: validar datos del formulario
    $doctorId = (int)$_POST['doctor_id'];
    $fechaCita = $_POST['fecha_cita'];
    $horaCita = $_POST['hora_cita'];
    $motivo = trim($_POST['motivo']);
    
    // Simulación: validaciones básicas
    if (empty($doctorId) || empty($fechaCita) || empty($horaCita) || empty($motivo)) {
        $message = 'Por favor complete todos los campos requeridos';
        $messageType = 'danger';
    } else {
        $message = 'Cita creada exitosamente (modo demostración)';
        $messageType = 'success';
    }
}

// Cancelar cita (simulación)
if (isset($_GET['cancelar']) && is_numeric($_GET['cancelar'])) {
    $citaId = (int)$_GET['cancelar'];
    $message = "Cita #{$citaId} cancelada (modo demostración)";
    $messageType = 'success';
}

// Datos de ejemplo para demostración (sin conexión a BD)
$doctores = [
    ['id' => 1, 'nombre' => 'Dr. Juan Pérez', 'especialidad' => 'Cardiología'],
    ['id' => 2, 'nombre' => 'Dra. María García', 'especialidad' => 'Pediatría'],
    ['id' => 3, 'nombre' => 'Dr. Carlos López', 'especialidad' => 'Dermatología'],
    ['id' => 4, 'nombre' => 'Dra. Ana Rodríguez', 'especialidad' => 'Ginecología'],
    ['id' => 5, 'nombre' => 'Dr. Pedro Martínez', 'especialidad' => 'Neurología'],
];

$citas = [
    [
        'id' => 1,
        'fecha_cita' => date('Y-m-d', strtotime('+2 days')),
        'hora_cita' => '09:00',
        'doctor_nombre' => 'Dr. Juan Pérez',
        'especialidad' => 'Cardiología',
        'motivo' => 'Consulta de rutina y revisión de presión arterial',
        'estado' => 'confirmada'
    ],
    [
        'id' => 2,
        'fecha_cita' => date('Y-m-d', strtotime('+5 days')),
        'hora_cita' => '14:30',
        'doctor_nombre' => 'Dra. María García',
        'especialidad' => 'Pediatría',
        'motivo' => 'Control de crecimiento y vacunación',
        'estado' => 'pendiente'
    ],
    [
        'id' => 3,
        'fecha_cita' => date('Y-m-d', strtotime('-3 days')),
        'hora_cita' => '11:00',
        'doctor_nombre' => 'Dr. Carlos López',
        'especialidad' => 'Dermatología',
        'motivo' => 'Revisión de lunar sospechoso',
        'estado' => 'completada'
    ],
    [
        'id' => 4,
        'fecha_cita' => date('Y-m-d', strtotime('+7 days')),
        'hora_cita' => '16:00',
        'doctor_nombre' => 'Dra. Ana Rodríguez',
        'especialidad' => 'Ginecología',
        'motivo' => 'Consulta anual y chequeo',
        'estado' => 'confirmada'
    ],
    [
        'id' => 5,
        'fecha_cita' => date('Y-m-d', strtotime('-10 days')),
        'hora_cita' => '10:30',
        'doctor_nombre' => 'Dr. Pedro Martínez',
        'especialidad' => 'Neurología',
        'motivo' => 'Seguimiento por migrañas frecuentes',
        'estado' => 'cancelada'
    ]
];

// Estado de colores
$estadoColores = [
    'pendiente' => 'warning',
    'confirmada' => 'success',
    'completada' => 'info',
    'cancelada' => 'secondary'
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
    <style>
        .cita-pasada {
            opacity: 0.7;
            background-color: #f8f9fa;
        }
        .badge {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }
        .table th {
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
        }
        .table td {
            vertical-align: middle;
        }
        .motivo-truncado {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-calendar-check me-2"></i>Mis Citas Médicas</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaCitaModal">
                        <i class="bi bi-plus-circle me-1"></i> Nueva Cita
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <i class="bi <?= $messageType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' ?> me-2"></i>
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Lista de Citas -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom-0">
                        <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Citas Programadas</h5>
                        <p class="text-muted mb-0 small">Mostrando <?= count($citas) ?> citas</p>
                    </div>
                    <div class="card-body">
                        <?php if (empty($citas)): ?>
                            <div class="alert alert-info border-0">
                                <i class="bi bi-info-circle me-2"></i> 
                                No tienes citas programadas. <a href="#" data-bs-toggle="modal" data-bs-target="#nuevaCitaModal" class="alert-link">Agenda tu primera cita</a>.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="12%">Fecha</th>
                                            <th width="10%">Hora</th>
                                            <th width="20%">Doctor</th>
                                            <th width="15%">Especialidad</th>
                                            <th width="25%">Motivo</th>
                                            <th width="10%">Estado</th>
                                            <th width="8%" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($citas as $cita): 
                                            $fechaHoraCita = strtotime($cita['fecha_cita'] . ' ' . $cita['hora_cita']);
                                            $esPasada = $fechaHoraCita < time();
                                            $esHoy = date('Y-m-d') === $cita['fecha_cita'];
                                        ?>
                                            <tr class="<?= $esPasada ? 'cita-pasada' : '' ?>">
                                                <td>
                                                    <span class="d-block"><?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?></span>
                                                    <?php if ($esHoy): ?>
                                                        <span class="badge bg-info text-white small">Hoy</span>
                                                    <?php elseif ($esPasada): ?>
                                                        <span class="badge bg-secondary text-white small">Pasada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="fw-medium"><?= htmlspecialchars($cita['hora_cita']) ?></span>
                                                </td>
                                                <td>
                                                    <div class="fw-medium"><?= htmlspecialchars($cita['doctor_nombre']) ?></div>
                                                </td>
                                                <td>
                                                    <span class="text-muted"><?= htmlspecialchars($cita['especialidad']) ?></span>
                                                </td>
                                                <td>
                                                    <div class="motivo-truncado" title="<?= htmlspecialchars($cita['motivo']) ?>">
                                                        <?= htmlspecialchars($cita['motivo']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $estadoColores[$cita['estado']] ?? 'secondary' ?>">
                                                        <i class="bi bi-circle-fill small me-1"></i>
                                                        <?= htmlspecialchars(ucfirst($cita['estado'])) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (in_array($cita['estado'], ['pendiente', 'confirmada']) && !$esPasada): ?>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="?cancelar=<?= $cita['id'] ?>" 
                                                               class="btn btn-outline-danger" 
                                                               onclick="return confirmCancelacion()"
                                                               title="Cancelar cita"
                                                               data-bs-toggle="tooltip">
                                                                <i class="bi bi-x-lg"></i>
                                                            </a>
                                                        </div>
                                                    <?php elseif ($cita['estado'] === 'completada'): ?>
                                                        <button class="btn btn-sm btn-outline-success" 
                                                                title="Cita completada"
                                                                disabled>
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted small">N/A</span>
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
                
                <!-- Información adicional -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3">
                                    <i class="bi bi-info-circle me-2"></i> Instrucciones para Pacientes
                                </h6>
                                <div class="small">
                                    <div class="mb-2">
                                        <span class="badge bg-light text-dark me-2">1</span>
                                        Las citas solo pueden cancelarse en estados "Pendiente" o "Confirmada".
                                    </div>
                                    <div class="mb-2">
                                        <span class="badge bg-light text-dark me-2">2</span>
                                        Horario de atención: <strong>Lunes a Viernes de 8:00 AM a 6:00 PM</strong>.
                                    </div>
                                    <div class="mb-2">
                                        <span class="badge bg-light text-dark me-2">3</span>
                                        Las citas se agendan en intervalos de 30 minutos.
                                    </div>
                                    <div class="mb-2">
                                        <span class="badge bg-light text-dark me-2">4</span>
                                        Se recomienda llegar <strong>15 minutos antes</strong> de la cita.
                                    </div>
                                    <div class="mb-2">
                                        <span class="badge bg-light text-dark me-2">5</span>
                                        Para emergencias, contacte al <strong>809-555-0000</strong>.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3">
                                    <i class="bi bi-clock-history me-2"></i> Leyenda de Estados
                                </h6>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge bg-warning">
                                        <i class="bi bi-clock me-1"></i> Pendiente
                                    </span>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i> Confirmada
                                    </span>
                                    <span class="badge bg-info">
                                        <i class="bi bi-check-all me-1"></i> Completada
                                    </span>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle me-1"></i> Cancelada
                                    </span>
                                </div>
                                <div class="alert alert-light border small">
                                    <i class="bi bi-lightbulb text-warning me-2"></i>
                                    <strong>Tip:</strong> Puedes ver el historial completo de tus citas en la sección "Historial Médico".
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Nueva Cita -->
    <div class="modal fade" id="nuevaCitaModal" tabindex="-1" aria-labelledby="nuevaCitaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="nuevaCitaModalLabel">
                        <i class="bi bi-calendar-plus me-2"></i>Nueva Cita Médica
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="formCita">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Seleccionar Doctor <span class="text-danger">*</span></label>
                                <select name="doctor_id" class="form-select" required id="doctorSelect">
                                    <option value="">-- Seleccione un doctor --</option>
                                    <?php foreach ($doctores as $doctor): ?>
                                        <option value="<?= $doctor['id'] ?>">
                                            <?= htmlspecialchars($doctor['nombre']) ?> - <?= htmlspecialchars($doctor['especialidad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Busque por especialidad o nombre</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Fecha de la Cita <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_cita" class="form-control" 
                                       id="fechaCita" min="<?= date('Y-m-d') ?>" required>
                                <small class="text-muted">No se permiten fines de semana</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Hora de la Cita <span class="text-danger">*</span></label>
                                <input type="time" name="hora_cita" class="form-control" 
                                       id="horaCita" min="08:00" max="18:00" step="1800" required>
                                <small class="text-muted">Horario: 8:00 AM - 6:00 PM (intervalos de 30 min)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Duración Estimada</label>
                                <select class="form-select" disabled>
                                    <option>30 minutos (consulta estándar)</option>
                                    <option>45 minutos (consulta extendida)</option>
                                    <option>60 minutos (evaluación completa)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-medium">Motivo de la Consulta <span class="text-danger">*</span></label>
                            <textarea name="motivo" class="form-control" rows="4" 
                                      placeholder="Describa brevemente el motivo de su consulta, síntomas, dudas o tratamiento a seguir..." 
                                      required maxlength="500" id="motivoTextarea"></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Máximo 500 caracteres</small>
                                <small><span id="contadorCaracteres">0</span>/500</small>
                            </div>
                        </div>
                        
                        <div class="alert alert-info border-0">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Nota:</strong> Esta es una demostración. En la versión real, el sistema verificará la disponibilidad del doctor.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i> Cancelar
                        </button>
                        <button type="submit" name="crear_cita" class="btn btn-primary" id="btnAgendar">
                            <i class="bi bi-calendar-check me-1"></i> Agendar Cita
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configurar fecha mínima como hoy
        const fechaInput = document.getElementById('fechaCita');
        const horaInput = document.getElementById('horaCita');
        const form = document.getElementById('formCita');
        const motivoTextarea = document.getElementById('motivoTextarea');
        const contadorCaracteres = document.getElementById('contadorCaracteres');
        
        // Inicializar tooltips de Bootstrap
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Configurar fecha mínima
        fechaInput.min = new Date().toISOString().split('T')[0];
        
        // Contador de caracteres para el motivo
        motivoTextarea.addEventListener('input', function() {
            contadorCaracteres.textContent = this.value.length;
        });
        
        // Inicializar contador
        contadorCaracteres.textContent = motivoTextarea.value.length;
        
        // Validar que no sea fin de semana
        fechaInput.addEventListener('change', function() {
            const fecha = new Date(this.value);
            const diaSemana = fecha.getDay(); // 0=Domingo, 6=Sábado
            
            if (diaSemana === 0 || diaSemana === 6) {
                alert('❌ No se pueden agendar citas los fines de semana.\n\nPor favor seleccione un día entre lunes y viernes.');
                this.value = '';
                return;
            }
            
            // Si es hoy, ajustar la hora mínima
            const hoy = new Date().toISOString().split('T')[0];
            if (this.value === hoy) {
                const ahora = new Date();
                let horaActual = ahora.getHours();
                let minutoActual = ahora.getMinutes();
                
                // Redondear a los próximos 30 minutos
                if (minutoActual > 0 && minutoActual < 30) {
                    minutoActual = 30;
                } else if (minutoActual > 30) {
                    horaActual += 1;
                    minutoActual = 0;
                }
                
                const horaStr = horaActual.toString().padStart(2, '0');
                const minutoStr = minutoActual.toString().padStart(2, '0');
                horaInput.min = horaStr + ':' + minutoStr;
                
                if (horaActual >= 18) {
                    alert('⚠️ Ya es muy tarde para agendar una cita para hoy.\n\nPor favor seleccione una fecha futura.');
                    this.value = '';
                }
            } else {
                horaInput.min = '08:00';
            }
        });
        
        // Validar hora seleccionada
        horaInput.addEventListener('change', function() {
            const fecha = fechaInput.value;
            const hora = this.value;
            
            if (fecha && hora) {
                const horaNum = parseInt(hora.split(':')[0]);
                const minutoNum = parseInt(hora.split(':')[1]);
                
                // Validar horario laboral
                if (horaNum < 8 || horaNum >= 18 || (horaNum === 17 && minutoNum > 30)) {
                    alert('⏰ Horario fuera del rango permitido.\n\nEl horario de atención es de 8:00 AM a 6:00 PM.');
                    this.value = '';
                }
                
                // Validar intervalo de 30 minutos
                if (minutoNum !== 0 && minutoNum !== 30) {
                    alert('⏱️ Las citas deben ser en intervalos de 30 minutos.\n\nPor favor seleccione una hora como 9:00, 9:30, 10:00, etc.');
                    this.value = '';
                }
            }
        });
        
        // Confirmación personalizada para cancelar citas
        function confirmCancelacion() {
            return confirm('¿Está seguro de cancelar esta cita?\n\nEsta acción no se puede deshacer.');
        }
        
        // Validar antes de enviar el formulario
        form.addEventListener('submit', function(e) {
            const fecha = fechaInput.value;
            const hora = horaInput.value;
            const doctor = document.getElementById('doctorSelect').value;
            const motivo = motivoTextarea.value.trim();
            
            // Validaciones básicas
            if (!doctor) {
                e.preventDefault();
                alert('⚠️ Por favor seleccione un doctor.');
                document.getElementById('doctorSelect').focus();
                return false;
            }
            
            if (!fecha) {
                e.preventDefault();
                alert('⚠️ Por favor seleccione una fecha.');
                fechaInput.focus();
                return false;
            }
            
            if (!hora) {
                e.preventDefault();
                alert('⚠️ Por favor seleccione una hora.');
                horaInput.focus();
                return false;
            }
            
            if (!motivo || motivo.length < 10) {
                e.preventDefault();
                alert('⚠️ Por favor describa el motivo de la consulta (mínimo 10 caracteres).');
                motivoTextarea.focus();
                return false;
            }
            
            // Validar que no sea en el pasado
            if (fecha && hora) {
                const fechaHoraSeleccionada = new Date(fecha + 'T' + hora);
                const ahora = new Date();
                
                if (fechaHoraSeleccionada <= ahora) {
                    e.preventDefault();
                    alert('⏰ No puedes agendar citas en el pasado.\n\nPor favor selecciona una fecha y hora futuras.');
                    return false;
                }
            }
            
            // Mostrar loading en el botón
            const btn = document.getElementById('btnAgendar');
            const btnOriginal = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Procesando...';
            btn.disabled = true;
            
            // Simular envío (en producción real no tendrías este timeout)
            setTimeout(() => {
                // En producción real, el formulario se enviaría normalmente
                btn.innerHTML = btnOriginal;
                btn.disabled = false;
                
                // Mostrar mensaje de éxito (en producción real esto vendría del servidor)
                alert('✅ Cita agendada exitosamente (modo demostración)\n\nEn la versión real, se enviaría al servidor y verificaría disponibilidad.');
            }, 2000);
            
            // Prevenir envío real en modo demostración
            e.preventDefault();
            return false;
        });
    </script>
</body>
</html>