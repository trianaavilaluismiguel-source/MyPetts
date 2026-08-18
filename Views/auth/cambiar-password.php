<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña - MyPetts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-page">

    <div class="auth-card">
        <div class="auth-header">
            <span class="logo">🐾 MyPetts</span>
            <h2>Cambio de contraseña</h2>
            <p>Tu cuenta tiene una contraseña temporal. Por seguridad, define una nueva antes de continuar.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="auth-alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/auth/cambiarPassword" method="POST" class="auth-form">
            <div class="form-group">
                <label for="contrasena">Nueva contraseña</label>
                <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required autofocus>
                <small class="help-text">Mínimo 8 caracteres (debe incluir letras y números)</small>
            </div>

            <div class="form-group">
                <label for="confirmar_contrasena">Confirmar nueva contraseña</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">Guardar y Continuar</button>
        </form>
    </div>

</body>
</html>