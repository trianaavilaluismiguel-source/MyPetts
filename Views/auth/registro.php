<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - MyPetts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-page">

    <div class="auth-card" style="max-width: 480px;">
        <div class="auth-header">
            <span class="logo">🐾 MyPetts</span>
            <h2>Crear cuenta de Dueño</h2>
            <p>Registra tus datos para gestionar tus mascotas y citas</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="auth-alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/auth/registro" method="POST" class="auth-form">
            <div class="form-group">
                <label for="nombre">Nombre completo *</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej. Carlos Pérez" required autofocus>
            </div>

            <!-- Fila doble para Correo y Teléfono -->
            <div style="display: flex; gap: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label for="correo">Correo electrónico *</label>
                    <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="telefono">Teléfono de contacto *</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="Ej. 3001234567" required>
                </div>
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña *</label>
                <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required>
                <small class="help-text">Mínimo 8 caracteres (debe incluir letras y números)</small>
            </div>

            <div class="form-group">
                <label for="confirmar_contrasena">Confirmar contraseña *</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">Registrarse</button>
        </form>

        <div class="auth-footer">
            <p>¿Ya tienes una cuenta? <a href="/auth">Inicia sesión</a></p>
        </div>
    </div>

</body>
</html>