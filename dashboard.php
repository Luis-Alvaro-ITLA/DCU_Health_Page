<?php
session_start();
require_once "db/db.php";

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: pages/autentication/login.php');
    exit;
}

// Obtener datos del usuario (corregido según tu DB)
$stmt = $pdo->prepare("SELECT id, nombre, email, role FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch();

// Si no se encuentra el usuario, redirigir al login
if (!$usuario) {
    header('Location: pages/autentication/login.php');
    exit;
}

// Obtener estadísticas según el rol del usuario
if ($usuario['role'] === 'patient') {
    // Para pacientes: obtener citas próximas y reportes
    $stmtCitas = $pdo->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ? AND estado = 'confirmada' AND fecha_cita >= CURDATE()");
    $stmtCitas->execute([$_SESSION['user_id']]);
    $citasProximas = $stmtCitas->fetch()['count'];
    
    $stmtReportes = $pdo->prepare("SELECT COUNT(*) as count FROM medical_reports WHERE patient_id = ?");
    $stmtReportes->execute([$_SESSION['user_id']]);
    $reportesRecibidos = $stmtReportes->fetch()['count'];
    
} elseif ($usuario['role'] === 'doctor') {
    // Para doctores: obtener citas del día y reportes generados
    $stmtCitas = $pdo->prepare("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = ? AND fecha_cita = CURDATE() AND estado IN ('confirmada', 'pendiente')");
    $stmtCitas->execute([$_SESSION['user_id']]);
    $citasProximas = $stmtCitas->fetch()['count'];
    
    $stmtReportes = $pdo->prepare("SELECT COUNT(*) as count FROM medical_reports WHERE doctor_id = ?");
    $stmtReportes->execute([$_SESSION['user_id']]);
    $reportesRecibidos = $stmtReportes->fetch()['count'];
    
} else {
    // Para admin (opcional)
    $citasProximas = 0;
    $reportesRecibidos = 0;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Panel de Control - <?= htmlspecialchars(ucfirst($usuario['role'])) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="index.php">DCU Medical</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle"><i class="fas fa-bars"></i></button>

        <ul class="navbar-nav ms-auto me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i> <?= htmlspecialchars($usuario['nombre']) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="pages/autentication/profile.php">Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="#">Configuración</a></li>
                    <li><hr class="dropdown-divider" /></li>
                    <li><a class="dropdown-item" href="process/logout.php">Cerrar Sesión</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">MENÚ</div>

                        <a class="nav-link active" href="dashboard.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Panel Principal
                        </a>

                        <div class="sb-sidenav-menu-heading">Funciones</div>
                        
                        <?php if ($usuario['role'] === 'patient'): ?>
                            <!-- Menú para pacientes -->
                            <a class="nav-link" href="pages/appointments.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-calendar-check"></i></div>
                                Citas Médicas
                            </a>
                            <a class="nav-link" href="pages/reports.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-file-medical"></i></div>
                                Mis Reportes
                            </a>
                            <a class="nav-link" href="pages/metrics.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                                Mis Estadísticas
                            </a>
                            
                        <?php elseif ($usuario['role'] === 'doctor'): ?>
                            <!-- Menú para doctores -->
                            <a class="nav-link" href="pages/doctor/appointments.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-calendar-check"></i></div>
                                Mis Citas
                            </a>
                            <a class="nav-link" href="pages/doctor/patients.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                                Mis Pacientes
                            </a>
                            <a class="nav-link" href="pages/doctor/reports.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-file-medical"></i></div>
                                Reportes Médicos
                            </a>
                            
                        <?php elseif ($usuario['role'] === 'admin'): ?>
                            <!-- Menú para administradores -->
                            <a class="nav-link" href="pages/admin/users.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-users-cog"></i></div>
                                Usuarios
                            </a>
                            <a class="nav-link" href="pages/admin/appointments.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-calendar-alt"></i></div>
                                Todas las Citas
                            </a>
                            <a class="nav-link" href="pages/admin/reports.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-database"></i></div>
                                Reportes del Sistema
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Conectado como:</div>
                    <?= htmlspecialchars(ucfirst($usuario['role'])) ?>: <?= htmlspecialchars($usuario['nombre']) ?>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?></h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Panel de <?= htmlspecialchars(ucfirst($usuario['role'])) ?></li>
                    </ol>

                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body">
                                    <h5><?= $citasProximas ?></h5>
                                    <?= ($usuario['role'] === 'patient') ? 'Citas próximas' : 'Citas hoy' ?>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="<?= ($usuario['role'] === 'patient') ? 'pages/appointments.php' : 'pages/doctor/appointments.php' ?>">Ver</a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-success text-white mb-4">
                                <div class="card-body">
                                    <h5><?= $reportesRecibidos ?></h5>
                                    <?= ($usuario['role'] === 'patient') ? 'Reportes recibidos' : 'Reportes generados' ?>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="<?= ($usuario['role'] === 'patient') ? 'pages/reports.php' : 'pages/doctor/reports.php' ?>">Ver</a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-warning text-white mb-4">
                                <div class="card-body">
                                    <h5>0</h5>
                                    Notificaciones
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="#">Ver</a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-info text-white mb-4">
                                <div class="card-body">
                                    <h5>Activo</h5>
                                    Estado del Sistema
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="#">Ver</a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($usuario['role'] === 'patient'): ?>
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header"><i class="fas fa-chart-area me-1"></i> Estado de Salud Reciente</div>
                                <div class="card-body"><canvas id="graficoEstado"></canvas></div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header"><i class="fas fa-chart-bar me-1"></i> Citas por Mes</div>
                                <div class="card-body"><canvas id="graficoCitas"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright © DCU Medical 2025</div>
                        <div>
                            <a href="#">Política de Privacidad</a> ·
                            <a href="#">Términos y Condiciones</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
    <script src="js/scripts.js"></script>

    <?php if ($usuario['role'] === 'patient'): ?>
    <!-- GRÁFICOS CON DATOS DEL PACIENTE -->
    <script>
    // Datos de ejemplo - en producción estos vendrían de PHP
    let estadoData = [65, 70, 68, 72, 75, 73, 78];
    let citasData = [1, 2, 1, 3, 2, 1, 2, 3, 2, 1, 2, 3];
    
    // Gráfico de estado de salud
    new Chart(document.getElementById('graficoEstado'), {
        type: 'line',
        data: { 
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul'],
            datasets: [{ 
                label: 'Índice de Salud (%)',
                data: estadoData,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false,
                    min: 50,
                    max: 100
                }
            }
        }
    });

    // Gráfico de citas por mes
    new Chart(document.getElementById('graficoCitas'), {
        type: 'bar',
        data: { 
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{ 
                label: 'Citas Médicas',
                data: citasData,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    </script>
    <?php endif; ?>
</body>
</html>