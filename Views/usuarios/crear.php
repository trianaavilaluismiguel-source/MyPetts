<?php
$error = $error ?? null;
$roles = $roles ?? [];
?>

<h1>Nuevo Usuario</h1>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="/usuario/crear" method="POST">
    <label>Nombre:<br>
        <input type="text" name="nombre" required style="width:100%;">
    </label><br><br>

    <label>Correo:<br>
        <input type="email" name="correo" required style="width:100%;">
    </label><br><br>

    <label>Teléfono:<br>
        <input type="text" name="telefono" required style="width:100%;">
    </label><br><br>

    <label>Rol:<br>
        <select name="rol_id" required>
            <option value="">-- Selecciona --</option>
            <?php if (!empty($roles) && is_array($roles)): ?>
                <?php foreach ($roles as $rol): ?>
                    <option value="<?= (int) $rol['id'] ?>"><?= htmlspecialchars($rol['nombre_rol']) ?></option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="" disabled>No hay roles disponibles</option>
            <?php endif; ?>
        </select>
    </label><br><br>

    <p><em>Se generará una contraseña temporal automáticamente, que se mostrará al guardar.</em></p>

    <button type="submit">Crear usuario</button>
</form>

<a href="/usuario">Cancelar y volver</a>