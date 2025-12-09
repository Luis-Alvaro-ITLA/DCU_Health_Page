<?php
// pages/reports.php - Reportes médicos del paciente
session_start();
require_once "../db/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../pages/autentication/login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$message = '';
$messageType = '';

// Obtener reportes con archivos
$stmt = $pdo->prepare("
    SELECT r.*, u.nombre as doctor_nombre 
    FROM medical_reports r 
    JOIN users u ON r.doctor_id = u.id 
    WHERE r.patient_id = ? 
    ORDER BY r.fecha_reporte DESC
");
$stmt->execute([$userId]);
$reportes = $stmt->fetchAll();

// Obtener archivos para cada reporte
$archivosPorReporte = [];
if (!empty($reportes)) {
    $ids = array_column($reportes, 'id');
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM report_files WHERE report_id IN ($in)");
    $stmt->execute($ids);
    $archivos = $stmt->fetchAll();
    
    foreach ($archivos as $archivo) {
        $archivosPorReporte[$archivo['report_id']][] = $archivo;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mis Reportes - DCU Medical</title>
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
                    <h1 class="h2">Mis Reportes Médicos</h1>
                    <span class="badge bg-primary"><?= count($reportes) ?> reportes</span>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Lista de Reportes -->
                <?php if (empty($reportes)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No tienes reportes médicos registrados.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($reportes as $reporte): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Reporte #<?= $reporte['id'] ?></h5>
                                            <small class="text-muted">
                                                <?= date('d/m/Y', strtotime($reporte['fecha_reporte'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">
                                            <i class="bi bi-person-badge"></i> 
                                            Dr. <?= htmlspecialchars($reporte['doctor_nombre']) ?>
                                        </h6>
                                        
                                        <p class="card-text">
                                            <strong>Resumen:</strong><br>
                                            <?= nl2br(htmlspecialchars($reporte['resumen'])) ?>
                                        </p>
                                        
                                        <?php if (!empty($reporte['diagnostico'])): ?>
                                            <p class="card-text">
                                                <strong>Diagnóstico:</strong><br>
                                                <?= nl2br(htmlspecialchars($reporte['diagnostico'])) ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($reporte['tratamiento'])): ?>
                                            <p class="card-text">
                                                <strong>Tratamiento:</strong><br>
                                                <?= nl2br(htmlspecialchars($reporte['tratamiento'])) ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <!-- Archivos adjuntos -->
                                        <?php if (!empty($archivosPorReporte[$reporte['id']])): ?>
                                            <div class="mt-3">
                                                <strong><i class="bi bi-paperclip"></i> Archivos adjuntos:</strong>
                                                <ul class="list-unstyled mt-2">
                                                    <?php foreach ($archivosPorReporte[$reporte['id']] as $archivo): ?>
                                                        <li class="mb-1">
                                                            <a href="<?= htmlspecialchars($archivo['ruta_archivo']) ?>" 
                                                               target="_blank" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-download"></i>
                                                                <?= htmlspecialchars($archivo['nombre_archivo']) ?>
                                                                <span class="badge bg-secondary ms-1">
                                                                    <?= strtoupper(pathinfo($archivo['ruta_archivo'], PATHINFO_EXTENSION)) ?>
                                                                </span>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                Creación: <?= date('d/m/Y H:i', strtotime($reporte['created_at'])) ?>
                                            </small>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detalleReporteModal<?= $reporte['id'] ?>">
                                                <i class="bi bi-eye"></i> Ver Detalles
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Detalles -->
                            <div class="modal fade" id="detalleReporteModal<?= $reporte['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detalles del Reporte #<?= $reporte['id'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Doctor:</strong><br>
                                                    Dr. <?= htmlspecialchars($reporte['doctor_nombre']) ?></p>
                                                    
                                                    <p><strong>Fecha del Reporte:</strong><br>
                                                    <?= date('d/m/Y', strtotime($reporte['fecha_reporte'])) ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Fecha de Creación:</strong><br>
                                                    <?= date('d/m/Y H:i', strtotime($reporte['created_at'])) ?></p>
                                                    
                                                    <?php if ($reporte['updated_at']): ?>
                                                        <p><strong>Última Actualización:</strong><br>
                                                        <?= date('d/m/Y H:i', strtotime($reporte['updated_at'])) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <hr>
                                            
                                            <h6>Resumen Completo</h6>
                                            <div class="border rounded p-3 mb-3 bg-light">
                                                <?= nl2br(htmlspecialchars($reporte['resumen'])) ?>
                                            </div>
                                            
                                            <?php if (!empty($reporte['diagnostico'])): ?>
                                                <h6>Diagnóstico</h6>
                                                <div class="border rounded p-3 mb-3 bg-light">
                                                    <?= nl2br(htmlspecialchars($reporte['diagnostico'])) ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($reporte['tratamiento'])): ?>
                                                <h6>Tratamiento Prescrito</h6>
                                                <div class="border rounded p-3 mb-3 bg-light">
                                                    <?= nl2br(htmlspecialchars($reporte['tratamiento'])) ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($reporte['observaciones'])): ?>
                                                <h6>Observaciones Adicionales</h6>
                                                <div class="border rounded p-3 bg-light">
                                                    <?= nl2br(htmlspecialchars($reporte['observaciones'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            <a href="imprimir_reporte.php?id=<?= $reporte['id'] ?>" 
                                               target="_blank" 
                                               class="btn btn-primary">
                                                <i class="bi bi-printer"></i> Imprimir
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>