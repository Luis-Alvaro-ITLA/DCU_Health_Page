<?php
// pages/metrics.php - Gestión de métricas médicas del paciente
session_start();
require_once "../db/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../pages/autentication/login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$message = '';
$messageType = '';

// Operaciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear nueva métrica
    if (isset($_POST['agregar_metrica'])) {
        $tipo = trim($_POST['tipo']);
        $valor = (float)$_POST['valor'];
        $unidad = trim($_POST['unidad']);
        $fechaMedicion = $_POST['fecha_medicion'];
        $notas = trim($_POST['notas']);
        
        $stmt = $pdo->prepare("INSERT INTO medical_metrics (patient_id, tipo, valor, unidad, fecha_medicion, notas, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        
        if ($stmt->execute([$userId, $tipo, $valor, $unidad, $fechaMedicion, $notas])) {
            $message = 'Métrica registrada exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al registrar la métrica';
            $messageType = 'danger';
        }
    }
    
    // Actualizar métrica
    if (isset($_POST['actualizar_metrica'])) {
        $id = (int)$_POST['id'];
        $valor = (float)$_POST['valor'];
        $notas = trim($_POST['notas']);
        
        $stmt = $pdo->prepare("UPDATE medical_metrics SET valor = ?, notas = ?, updated_at = NOW() WHERE id = ? AND patient_id = ?");
        
        if ($stmt->execute([$valor, $notas, $id, $userId])) {
            $message = 'Métrica actualizada exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al actualizar la métrica';
            $messageType = 'danger';
        }
    }
}

// Eliminar métrica
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM medical_metrics WHERE id = ? AND patient_id = ?");
    
    if ($stmt->execute([$id, $userId])) {
        $message = 'Métrica eliminada';
        $messageType = 'success';
    } else {
        $message = 'Error al eliminar la métrica';
        $messageType = 'danger';
    }
}

// Obtener todas las métricas del paciente
$stmt = $pdo->prepare("
    SELECT * FROM medical_metrics 
    WHERE patient_id = ? 
    ORDER BY fecha_medicion DESC, created_at DESC
");
$stmt->execute([$userId]);
$metricas = $stmt->fetchAll();

// Tipos de métricas disponibles (se pueden agregar más)
$tiposMetricas = [
    'Presión Arterial Sistólica',
    'Presión Arterial Diastólica',
    'Frecuencia Cardíaca',
    'Temperatura',
    'Glucosa',
    'Peso',
    'Altura',
    'IMC',
    'Oxígeno en Sangre',
    'Colesterol Total',
    'Colesterol LDL',
    'Colesterol HDL',
    'Triglicéridos'
];

// Preparar datos para gráficos
$datosGrafico = [];
if (!empty($metricas)) {
    // Agrupar por tipo para gráficos
    $datosPorTipo = [];
    foreach ($metricas as $metrica) {
        $datosPorTipo[$metrica['tipo']][] = [
            'fecha' => $metrica['fecha_medicion'],
            'valor' => $metrica['valor'],
            'unidad' => $metrica['unidad']
        ];
    }
    
    // Preparar últimos 10 registros por tipo para gráfico
    foreach ($datosPorTipo as $tipo => $registros) {
        $ultimos = array_slice($registros, 0, 10);
        $datosGrafico[$tipo] = array_reverse($ultimos); // Orden ascendente
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mis Estadísticas - DCU Medical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Mis Estadísticas de Salud</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaMetricaModal">
                        <i class="bi bi-plus-circle"></i> Registrar Métrica
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Gráficos -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Evolución de Métricas</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($datosGrafico)): ?>
                                    <div class="alert alert-info">
                                        No hay datos suficientes para mostrar gráficos. Registra algunas métricas primero.
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($datosGrafico as $tipo => $datos): ?>
                                            <div class="col-md-6 mb-4">
                                                <h6><?= htmlspecialchars($tipo) ?></h6>
                                                <canvas id="chart_<?= preg_replace('/[^a-z0-9]/i', '_', $tipo) ?>" height="200"></canvas>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Métricas -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Historial de Métricas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($metricas)): ?>
                            <div class="alert alert-info">No tienes métricas registradas.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Valor</th>
                                            <th>Unidad</th>
                                            <th>Notas</th>
                                            <th>Registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($metricas as $metrica): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($metrica['fecha_medicion'])) ?></td>
                                                <td><?= htmlspecialchars($metrica['tipo']) ?></td>
                                                <td><strong><?= htmlspecialchars($metrica['valor']) ?></strong></td>
                                                <td><?= htmlspecialchars($metrica['unidad']) ?></td>
                                                <td>
                                                    <?php if ($metrica['notas']): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                data-bs-toggle="popover" 
                                                                title="Notas" 
                                                                data-bs-content="<?= htmlspecialchars($metrica['notas']) ?>">
                                                            <i class="bi bi-chat-text"></i> Ver
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y H:i', strtotime($metrica['created_at'])) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-warning"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editarMetricaModal<?= $metrica['id'] ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <a href="?eliminar=<?= $metrica['id'] ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('¿Eliminar esta métrica?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>

                                            <!-- Modal Editar -->
                                            <div class="modal fade" id="editarMetricaModal<?= $metrica['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Editar Métrica</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id" value="<?= $metrica['id'] ?>">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Tipo</label>
                                                                    <input type="text" class="form-control" 
                                                                           value="<?= htmlspecialchars($metrica['tipo']) ?>" disabled>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Valor</label>
                                                                    <input type="number" step="0.01" name="valor" 
                                                                           class="form-control" value="<?= $metrica['valor'] ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Unidad</label>
                                                                    <input type="text" class="form-control" 
                                                                           value="<?= htmlspecialchars($metrica['unidad']) ?>" disabled>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Notas</label>
                                                                    <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($metrica['notas']) ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="actualizar_metrica" class="btn btn-primary">Actualizar</button>
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

    <!-- Modal Nueva Métrica -->
    <div class="modal fade" id="nuevaMetricaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Nueva Métrica</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tipo de Métrica</label>
                            <select name="tipo" class="form-select" required>
                                <option value="">Seleccionar tipo...</option>
                                <?php foreach ($tiposMetricas as $tipo): ?>
                                    <option value="<?= $tipo ?>"><?= $tipo ?></option>
                                <?php endforeach; ?>
                                <option value="Otro">Otro (especificar en notas)</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valor</label>
                                <input type="number" step="0.01" name="valor" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unidad</label>
                                <input type="text" name="unidad" class="form-control" 
                                       placeholder="Ej: mg/dL, mmHg, bpm" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de Medición</label>
                            <input type="date" name="fecha_medicion" class="form-control" 
                                   value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notas (opcional)</label>
                            <textarea name="notas" class="form-control" rows="3" 
                                      placeholder="Condiciones especiales, hora de medición, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="agregar_metrica" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicializar popovers
        document.addEventListener('DOMContentLoaded', function() {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });

        // Generar gráficos
        <?php if (!empty($datosGrafico)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                <?php foreach ($datosGrafico as $tipo => $datos): ?>
                    var ctx = document.getElementById('chart_<?= preg_replace('/[^a-z0-9]/i', '_', $tipo) ?>');
                    if (ctx) {
                        var labels = <?= json_encode(array_column($datos, 'fecha')) ?>;
                        var data = <?= json_encode(array_column($datos, 'valor')) ?>;
                        
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: '<?= addslashes($tipo) ?>',
                                    data: data,
                                    borderColor: 'rgb(75, 192, 192)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                                    tension: 0.1,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { display: true }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: false
                                    }
                                }
                            }
                        });
                    }
                <?php endforeach; ?>
            });
        <?php endif; ?>
    </script>
</body>
</html>