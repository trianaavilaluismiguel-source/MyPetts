<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - MyPetts</title>
</head>
<body>
    <h1>Crear cuenta</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="/auth/registrar" method="POST">
        <label for="nombre">Nombre:</label><br>
        <input type="text" name="nombre" id="nombre" required><br><br>

        <label for="correo">Correo:</label><br>
        <input type="email" name="correo" id="correo" required><br><br>

        <label for="telefono">Teléfono:</label><br>
        <input type="text" name="telefono" id="telefono" required><br><br>

        <label for="contrasena">Contraseña:</label><br>
        <input type="password" name="contrasena" id="contrasena" required><br><br>

        <label for="confirmar_contrasena">Confirmar contraseña:</label><br>
        <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" required><br><br>

        <button type="submit">Registrarme</button>
    </form>

    <p>¿Ya tienes cuenta? <a href="/auth">Inicia sesión aquí</a></p>
</body>
</html>