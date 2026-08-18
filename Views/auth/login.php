<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - MyPetts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="auth-page">

    <div class="auth-card">
        <div class="auth-header">
            <span class="logo">🐾 MyPetts</span>
            <h2>¡Hola de nuevo!</h2>
            <p>Ingresa tus credenciales para acceder</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="auth-alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensaje)): ?>
            <div class="auth-alert auth-success">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form action="/auth/login" method="POST" class="auth-form">
            <div class="form-group">
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required>
                <div class="field-actions">
                    <a href="/auth/olvidePassword" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div>
            </div>

            <button type="submit" class="btn-submit">Ingresar</button>
        </form>

        <div class="auth-footer">
            <p>¿No tienes una cuenta? <a href="/auth/registro">Regístrate aquí</a></p>
        </div>
    </div>

</body>

</html>