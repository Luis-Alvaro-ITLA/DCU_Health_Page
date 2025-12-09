<?php
// includes/sidebar.php - Sidebar común para todas las páginas
$role = $_SESSION['role'] ?? '';
?>
<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" 
                   href="../../dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <?php if ($role === 'patient'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'appointments.php' ? 'active' : '' ?>" 
                       href="appointments.php">
                        <i class="bi bi-calendar-check"></i> Mis Citas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>" 
                       href="reports.php">
                        <i class="bi bi-file-medical"></i> Mis Reportes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'metrics.php' ? 'active' : '' ?>" 
                       href="metrics.php">
                        <i class="bi bi-graph-up"></i> Mis Métricas
                    </a>
                </li>
                
            <?php elseif ($role === 'doctor'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'appointments.php' ? 'active' : '' ?>" 
                       href="../doctor/appointments.php">
                        <i class="bi bi-calendar-day"></i> Mis Citas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'patients.php' ? 'active' : '' ?>" 
                       href="../doctor/patients.php">
                        <i class="bi bi-people"></i> Mis Pacientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>" 
                       href="../doctor/reports.php">
                        <i class="bi bi-file-earmark-medical"></i> Reportes
                    </a>
                </li>
                
            <?php elseif ($role === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>" 
                       href="../admin/users.php">
                        <i class="bi bi-people-fill"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'appointments.php' ? 'active' : '' ?>" 
                       href="../admin/appointments.php">
                        <i class="bi bi-calendar-event"></i> Citas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>" 
                       href="../admin/reports.php">
                        <i class="bi bi-database"></i> Reportes
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="nav-item mt-3">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>" 
                   href="../autentication/profile.php">
                    <i class="bi bi-person"></i> Mi Perfil
                </a>
            </li>
        </ul>
    </div>
</div>