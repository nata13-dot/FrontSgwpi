<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .login-container {
            background: linear-gradient(135deg, #1B396A 0%, #2D5A96 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            border-radius: 10px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div style="background: linear-gradient(135deg, #1B396A 0%, #2D5A96 100%); padding: 30px; text-align: center; color: white;">
                <h4 class="mb-0">Restablecer Contraseña</h4>
            </div>
            
            <div class="card-body p-4">
                <div id="alertBox"></div>
                
                <form id="resetForm">
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva Contraseña</label>
                        <input 
                            type="password" 
                            class="form-control form-control-lg"
                            id="password"
                            name="password"
                            placeholder="Nueva contraseña" 
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                        <input 
                            type="password" 
                            class="form-control form-control-lg"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Confirmar contraseña" 
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-2">
                        <i class="bi bi-check-circle"></i> Restablecer
                    </button>
                </form>

                <hr class="my-3">

                <div class="text-center">
                    <a href="/index.php" class="text-decoration-none small" style="color: #1B396A;">
                        <i class="bi bi-arrow-left"></i> Volver al login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const form = document.getElementById('resetForm');
        const alertBox = document.getElementById('alertBox');

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;

            if (password !== confirmation) {
                alertBox.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> Las contraseñas no coinciden
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                return;
            }

            alertBox.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> Contraseña restablecida correctamente
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            setTimeout(() => {
                window.location.href = '/index.php';
            }, 1500);
        });
    </script>
</body>
</html>