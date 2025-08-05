<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="img/logo-inicio.jpg">
    <title>Login</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style-login.css">
</head>

<body class="bg-light">

    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card shadow p-4" style="width: 100%; max-width: 400px;">

            <!-- Logo centrado y redondo -->
            <div class="text-center mb-3">
                <img src="/img/logo-inicio.jpg" alt="logo-inicio" class="rounded-circle img-fluid" style="width: 100px; height: 100px; object-fit: cover;">
            </div>

            <h3 class="text-center mb-4">Iniciar Sesión</h3>

            <?php if (isset($_SESSION['login_error'])) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php
                    echo htmlspecialchars($_SESSION['login_error']);
                    unset($_SESSION['login_error']); // Limpiar el error después de mostrarlo
                    ?>
                </div>
            <?php endif; ?>

            <form action="Conexiones/login.php" method="POST">
                <div class="mb-3">
                    <label for="usuario" class="form-label fw-semibold">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-iniciar-sesion fw-bold d-flex align-items-center justify-content-center" id="loginButton">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                        <span class="button-text">Entrar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Seleccionar el formulario por su etiqueta <form>
        const loginForm = document.querySelector('form');
        loginForm.addEventListener('submit', function() {
            const loginButton = document.getElementById('loginButton');
            loginButton.disabled = true; // Deshabilitar el botón para evitar doble clic
            loginButton.querySelector('.spinner-border').classList.remove('d-none'); // Mostrar el spinner
            loginButton.querySelector('.button-text').textContent = 'Ingresando...'; // Cambiar el texto
        });
    </script>
</body>

</html>