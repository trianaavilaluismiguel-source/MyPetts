<?php
$error = $error ?? null;
$roles = $roles ?? [];
$usuario = $usuario ?? [];
?>

<h1>Editar Usuario</h1>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="/usuario/editar/<?= (int) ($usuario['id'] ?? 0) ?>" method="POST">
    <label>Nombre:<br>
        <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required style="width:100%;">
    </label><br><br>

    <label>Correo:<br>
        <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>" required style="width:100%;">
    </label><br><br>

    <label>Teléfono:<br>
        <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" required style="width:100%;">
    </label><br><br>

    <label>Rol:<br>
        <select name="rol_id" required>
            <option value="">-- Selecciona --</option>
            <?php if (!empty($roles) && is_array($roles)): ?>
                <?php foreach ($roles as $rol): ?>
                    <?php $selected = isset($usuario['rol_id']) && $rol['id'] == $usuario['rol_id'] ? 'selected' : ''; ?>
                    <option value="<?= (int) $rol['id'] ?>" <?= $selected ?>><?= htmlspecialchars($rol['nombre_rol']) ?></option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="" disabled>No hay roles disponibles</option>
            <?php endif; ?>
        </select>
    </label><br><br>

    <button type="submit">Guardar cambios</button>
</form>

<a href="/usuario">Cancelar y volver</a>