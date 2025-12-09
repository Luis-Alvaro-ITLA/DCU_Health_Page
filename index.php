<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>DCU Medical - Tu Salud Digital</title>
  <meta name="description" content="Gestiona tus reportes médicos y reserva citas en hospitales de forma segura y eficiente">
  <meta name="keywords" content="historial médico, reserva de citas, salud digital, reportes médicos, hospitales, citas médicas">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="css/main.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Medicio
  * Template URL: https://bootstrapmade.com/medicio-free-bootstrap-theme/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

  <header id="header" class="header sticky-top">

    <div class="branding d-flex align-items-center">

      <div class="container position-relative d-flex align-items-center justify-content-end">
        <a href="index.php" class="logo d-flex align-items-center me-auto">
          <img src="assets/img/logo.png" alt="DCU Medical">
          <!-- Uncomment the line below if you also wish to use a text logo -->
        <h1 class="sitename">DCU Medical</h1>
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="#hero" class="active">Inicio</a></li>
            <li><a href="#about">Acerca de</a></li>
            <li class="dropdown"><a href="#"><span>Servicios</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="#featured-services">Reportes Médicos</a></li>
                <li><a href="#services">Reserva de Citas</a></li>
                <li><a href="#doctors">Hospitales Asociados</a></li>
                <li><a href="#contact">Soporte 24/7</a></li>
              </ul>
            </li>
            <li><a href="#contact">Contacto</a></li>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
        <a class="cta-btn" href="pages/autentication/login.php">Iniciar Sesión</a>
        <a class="cta-btn" href="pages/autentication/register.php">Registrarse</a>

      </div>

    </div>

  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">

      <div id="hero-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">

        <div class="carousel-item active">
          <img src="assets/img/hero-carousel/hero-carousel-1.jpg" alt="Gestión de salud digital">
          <div class="container">
            <h2>Tu Historia Médica, Siempre Contigo</h2>
            <p>Accede a tus reportes médicos desde cualquier lugar, en cualquier momento. DCU Medical te permite almacenar de forma segura tu historial clínico y compartirlo con profesionales de la salud autorizados.</p>
            <a href="pages/autentication/register.php" class="btn-get-started">Crear Cuenta Gratis</a>
          </div>
        </div><!-- End Carousel Item -->

        <div class="carousel-item">
          <img src="assets/img/hero-carousel/hero-carousel-2.jpg" alt="Reserva de citas médicas">
          <div class="container">
            <h2>Reserva Citas Fácilmente</h2>
            <p>Encuentra y reserva citas con especialistas en hospitales asociados. Programación flexible, recordatorios automáticos y cancelación sencilla. Tu salud merece la mejor atención.</p>
            <a href="pages/autentication/register.php" class="btn-get-started">Reservar Mi Primera Cita</a>
          </div>
        </div><!-- End Carousel Item -->

        <div class="carousel-item">
          <img src="assets/img/hero-carousel/hero-carousel-3.jpg" alt="Seguridad y privacidad">
          <div class="container">
            <h2>Seguridad y Privacidad Garantizada</h2>
            <p>Tu información médica está protegida con encriptación de grado médico. Cumplimos con los más altos estándares de seguridad y privacidad para garantizar la confidencialidad de tus datos.</p>
            <a href="#about" class="btn-get-started">Conocer Más</a>
          </div>
        </div><!-- End Carousel Item -->

        <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-slide="prev">
          <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
        </a>

        <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-slide="next">
          <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
        </a>

        <ol class="carousel-indicators"></ol>

      </div>

    </section><!-- /Hero Section -->

    <!-- Featured Services Section -->
    <section id="featured-services" class="featured-services section">

      <div class="container">

        <div class="row gy-4">

          <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item position-relative">
              <div class="icon"><i class="fas fa-file-medical icon"></i></div>
              <h4><a href="" class="stretched-link">Reportes Digitales</a></h4>
              <p>Almacena y organiza todos tus exámenes, diagnósticos y tratamientos en un solo lugar seguro</p>
            </div>
          </div><!-- End Service Item -->

          <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item position-relative">
              <div class="icon"><i class="fas fa-calendar-check icon"></i></div>
              <h4><a href="" class="stretched-link">Reserva de Citas</a></h4>
              <p>Programa citas médicas en hospitales asociados con solo unos clics, disponible 24/7</p>
            </div>
          </div><!-- End Service Item -->

          <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="300">
            <div class="service-item position-relative">
              <div class="icon"><i class="fas fa-shield-alt icon"></i></div>
              <h4><a href="" class="stretched-link">Seguridad Total</a></h4>
              <p>Encriptación de grado médico para proteger tu información de salud más sensible</p>
            </div>
          </div><!-- End Service Item -->

          <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="400">
            <div class="service-item position-relative">
              <div class="icon"><i class="fas fa-hospital icon"></i></div>
              <h4><a href="" class="stretched-link">Red Hospitalaria</a></h4>
              <p>Acceso a una red de hospitales y clínicas asociadas con especialistas calificados</p>
            </div>
          </div><!-- End Service Item -->

        </div>

      </div>

    </section><!-- /Featured Services Section -->

  </main>

  <footer id="footer" class="footer light-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.php" class="logo d-flex align-items-center">
            <span class="sitename">DCU Medical</span>
          </a>
          <div class="footer-contact pt-3">
            <p>Tu plataforma de salud digital confiable</p>
            <p>Gestión segura de historiales médicos y reservas de citas</p>
            <p class="mt-3"><strong>Teléfono:</strong> <span>+1 800 555 1234</span></p>
            <p><strong>Email:</strong> <span>soporte@dcumedical.com</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Enlaces Rápidos</h4>
          <ul>
            <li><a href="#hero">Inicio</a></li>
            <li><a href="#about">Acerca de</a></li>
            <li><a href="#featured-services">Servicios</a></li>
            <li><a href="#">Términos de servicio</a></li>
            <li><a href="#">Política de privacidad</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Nuestros Servicios</h4>
          <ul>
            <li><a href="#">Historial Médico Digital</a></li>
            <li><a href="#">Reserva de Citas</a></li>
            <li><a href="#">Recordatorios Automáticos</a></li>
            <li><a href="#">Compartir con Especialistas</a></li>
            <li><a href="#">Soporte Médico 24/7</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Hospitales Asociados</h4>
          <ul>
            <li><a href="#">Hospital Central</a></li>
            <li><a href="#">Clínica Especializada</a></li>
            <li><a href="#">Centro Médico Avanzado</a></li>
            <li><a href="#">Hospital Pediátrico</a></li>
            <li><a href="#">Clínica de Especialidades</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Especialidades</h4>
          <ul>
            <li><a href="#">Cardiología</a></li>
            <li><a href="#">Pediatría</a></li>
            <li><a href="#">Dermatología</a></li>
            <li><a href="#">Ginecología</a></li>
            <li><a href="#">Ortopedia</a></li>
          </ul>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">DCU Medical 2025</strong> <span>Todos los derechos reservados</span></p>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you've purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
        Diseñado por <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="js/main.js"></script>

</body>

</html>