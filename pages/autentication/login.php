<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Iniciar Sesión - DCU Medical</title>
        <link href="/css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <style>
            .admin-login-toggle {
                cursor: pointer;
                color: #0d6efd;
                font-weight: 500;
            }
            .admin-login-toggle:hover {
                text-decoration: underline;
            }
            .admin-login-section {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 10px;
                margin-top: 20px;
                padding: 20px;
                display: none;
            }
            .role-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 0.8rem;
                margin: 5px 0;
            }
            .badge-patient {
                background-color: #28a745;
                color: white;
            }
            .badge-doctor {
                background-color: #007bff;
                color: white;
            }
            .badge-admin {
                background-color: #dc3545;
                color: white;
            }
            .credentials-box {
                background-color: rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                padding: 15px;
                margin-top: 15px;
            }
            .access-info {
                font-size: 0.9rem;
                color: #6c757d;
                margin-top: 10px;
            }
        </style>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <!-- Panel de Login Principal -->
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header">
                                        <h3 class="text-center font-weight-light my-4">
                                            <i class="fas fa-hospital me-2"></i>DCU Medical
                                        </h3>
                                        <p class="text-center text-muted mb-0">Sistema de Gestión Médica</p>
                                    </div>
                                    <div class="card-body">
                                        <!-- Mostrar errores si existen -->
                                        <?php if (isset($_SESSION['error']) && !isset($_GET['admin'])): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <?= htmlspecialchars($_SESSION['error']); ?>
                                                <?php unset($_SESSION['error']); ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form action="/process/login_process.php" method="POST" id="loginForm">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" name="email" type="email" required 
                                                       placeholder="nombre@ejemplo.com" />
                                                <label><i class="fas fa-envelope me-2"></i>Correo electrónico</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input class="form-control" name="password" type="password" required 
                                                       placeholder="Contraseña" />
                                                <label><i class="fas fa-lock me-2"></i>Contraseña</label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" name="remember" type="checkbox" id="rememberCheck" />
                                                <label class="form-check-label" for="rememberCheck">Recordar sesión</label>
                                            </div>
                                            <input type="hidden" name="login_type" value="regular" id="loginType">
                                            <div class="d-grid gap-2">
                                                <button class="btn btn-primary btn-lg" type="submit">
                                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                                </button>
                                            </div>
                                        </form>
                                        
                                        <!-- Separador -->
                                        <div class="text-center my-4">
                                            <span class="text-muted">¿Es administrador?</span>
                                        </div>
                                        
                                        <!-- Botón para mostrar login de administrador -->
                                        <div class="text-center">
                                            <button type="button" class="btn btn-outline-danger" id="toggleAdminLogin">
                                                <i class="fas fa-user-shield me-2"></i>Acceso Administrativo
                                            </button>
                                        </div>
                                        
                                        <div class="access-info text-center mt-3">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Los administradores serán redirigidos directamente al panel de control
                                        </div>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <div class="small">
                                            <a href="register.php">¿Necesita una cuenta? ¡Regístrese!</a>
                                            <span class="mx-2">|</span>
                                            <a href="recover.php">¿Olvidó su contraseña?</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Sección de Login para Administradores -->
                                <div class="admin-login-section" id="adminLoginSection">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">
                                            <i class="fas fa-user-shield me-2"></i>Acceso Administrativo
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" id="closeAdminLogin"></button>
                                    </div>
                                    
                                    <!-- Mostrar errores específicos de admin -->
                                    <?php if (isset($_SESSION['login_error']) && isset($_GET['admin'])): ?>
                                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                            <?= htmlspecialchars($_SESSION['login_error']); ?>
                                            <?php unset($_SESSION['login_error']); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <p class="mb-3">Acceso exclusivo para administradores del sistema. Será redirigido al panel de control.</p>
                                    
                                    
                                    <!-- Formulario de Administrador -->
                                    <form action="/process/login_process.php" method="POST" id="adminLoginForm" class="mt-4">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="email" type="email" required 
                                                   placeholder="admin@ejemplo.com" id="adminEmailInput" />
                                            <label><i class="fas fa-envelope me-2"></i>Email Administrativo</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="password" type="password" required 
                                                   placeholder="Contraseña" id="adminPasswordInput" />
                                            <label><i class="fas fa-key me-2"></i>Contraseña de Administrador</label>
                                        </div>
                                        <input type="hidden" name="login_type" value="admin">
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-light btn-lg" type="submit">
                                                <i class="fas fa-user-shield me-2"></i>Acceder al Panel Admin
                                            </button>
                                        </div>
                                    </form>
                                    
                                    <!-- Información sobre acceso -->
                                    <div class="mt-4 pt-3 border-top border-light border-opacity-25">
                                        <h6><i class="fas fa-info-circle me-2"></i>Información de Acceso</h6>
                                        <p class="small mb-2">
                                            <i class="fas fa-external-link-alt me-1"></i>
                                            <strong>Redirección automática:</strong> Al ingresar como administrador, será dirigido directamente a la página de gestión de usuarios.
                                        </p>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <span class="role-badge badge-patient">
                                                <i class="fas fa-user-injured me-1"></i>Paciente → Dashboard
                                            </span>
                                            <span class="role-badge badge-doctor">
                                                <i class="fas fa-user-md me-1"></i>Doctor → Dashboard
                                            </span>
                                            <span class="role-badge badge-admin">
                                                <i class="fas fa-user-cog me-1"></i>Admin → Gestión Usuarios
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Información del Sistema -->
                                <div class="card mt-4">
                                    <div class="card-body text-center">
                                        <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Información del Sistema</h6>
                                        <p class="card-text small">
                                            Versión 1.0.0 | DCU Medical System
                                        </p>
                                        <div class="d-flex justify-content-center gap-3">
                                            <a href="#" class="text-decoration-none small">
                                                <i class="fas fa-question-circle me-1"></i>Ayuda
                                            </a>
                                            <a href="#" class="text-decoration-none small">
                                                <i class="fas fa-phone me-1"></i>Soporte
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">
                                <i class="fas fa-copyright me-1"></i>Copyright &copy; DCU Medical 2025
                            </div>
                            <div>
                                <a href="#" class="text-decoration-none">Política de Privacidad</a>
                                &middot;
                                <a href="#" class="text-decoration-none">Términos y Condiciones</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="/js/scripts.js"></script>
        <script>
            // Mostrar/ocultar sección de administrador
            document.getElementById('toggleAdminLogin').addEventListener('click', function() {
                const adminSection = document.getElementById('adminLoginSection');
                adminSection.style.display = 'block';
                this.style.display = 'none';
                document.querySelector('.card-footer').style.display = 'none';
            });
            
            document.getElementById('closeAdminLogin').addEventListener('click', function() {
                const adminSection = document.getElementById('adminLoginSection');
                adminSection.style.display = 'none';
                document.getElementById('toggleAdminLogin').style.display = 'block';
                document.querySelector('.card-footer').style.display = 'block';
            });
            
            // Autocompletar credenciales de administrador
            function fillAdminCredentials() {
                document.getElementById('adminEmailInput').value = 'admin@dcumedical.com';
                document.getElementById('adminPasswordInput').value = 'Admin1234';
            }
            
            // Copiar al portapapeles
            function copyToClipboard(elementId) {
                const element = document.getElementById(elementId);
                element.type = 'text';
                element.select();
                element.setSelectionRange(0, 99999); // Para dispositivos móviles
                navigator.clipboard.writeText(element.value).then(() => {
                    // Cambiar temporalmente el icono
                    const button = element.nextElementSibling;
                    const originalIcon = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check"></i>';
                    
                    setTimeout(() => {
                        button.innerHTML = originalIcon;
                    }, 2000);
                });
                element.type = elementId.includes('Password') ? 'password' : 'text';
            }
            
            // Si hay parámetro admin en la URL, mostrar automáticamente la sección de admin
            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('admin') === 'true') {
                    document.getElementById('toggleAdminLogin').click();
                }
                
                // Marcar automáticamente el checkbox de recordar si existe la cookie
                if (document.cookie.includes('remember_login=true')) {
                    document.getElementById('rememberCheck').checked = true;
                }
            });
        </script>
    </body>
</html>