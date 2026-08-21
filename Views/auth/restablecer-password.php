restablecer-password.php<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña - MyPetts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="auth-page">

    <div class="auth-card">
        <div class="auth-header">
            <span class="logo">🐾 MyPetts</span>
            <h2>Restablecer contraseña</h2>
            <p>Define tu nueva contraseña.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="auth-alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($token)): ?>
            <form action="/auth/procesarRestablecer/<?= htmlspecialchars($token) ?>" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="contrasena">Nueva contraseña</label>
                    <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required autofocus>
                    <small class="help-text">Mínimo 8 caracteres (debe incluir letras y números)</small>
                </div>

                <div class="form-group">
                    <label for="confirmar_contrasena">Confirmar nueva contraseña</label>
                    <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-submit">Guardar nueva contraseña</button>
            </form>
        <?php else: ?>
            <div class="auth-footer">
                <a href="/auth/olvidePassword" class="forgot-link">Solicitar un nuevo enlace</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>