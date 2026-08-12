<?php
$error = $error ?? null;
?>

<h1>Debes cambiar tu contraseña</h1>

<p>Tu cuenta tiene una contraseña temporal. Por seguridad, define una nueva antes de continuar.</p>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="/auth/cambiarPassword" method="POST">
    <label>Nueva contraseña:<br>
        <input type="password" name="contrasena" required>
    </label><br><br>

    <label>Confirmar nueva contraseña:<br>
        <input type="password" name="confirmar_contrasena" required>
    </label><br><br>

    <button type="submit">Guardar</button>
</form>