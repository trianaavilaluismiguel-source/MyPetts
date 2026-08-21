<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - MyPetts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="auth-page">

    <div class="auth-card">
        <div class="auth-header">
            <span class="logo">🐾 MyPetts</span>
            <h2>Recuperar contraseña</h2>
            <p>Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="auth-alert auth-success">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($mensaje)): ?>
            <form action="/auth/enviarRecuperacion" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" required autofocus>
                </div>

                <button type="submit" class="btn-submit">Enviar enlace</button>
            </form>
        <?php endif; ?>

        <div class="auth-footer">
            <a href="/auth" class="forgot-link">← Volver a iniciar sesión</a>
        </div>
    </div>

</body>
</html>