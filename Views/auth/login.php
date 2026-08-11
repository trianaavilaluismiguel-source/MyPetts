<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - MyPetts</title>
</head>
<body>
    <h1>Iniciar sesión</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="/auth/login" method="POST">
        <label for="correo">Correo:</label><br>
        <input type="email" name="correo" id="correo" required><br><br>

        <label for="contrasena">Contraseña:</label><br>
        <input type="password" name="contrasena" id="contrasena" required><br><br>

        <button type="submit">Ingresar</button>
    </form>

    <p>¿No tienes cuenta? <a href="/auth/mostrarRegistro">Regístrate aquí</a></p>
</body>
</html>