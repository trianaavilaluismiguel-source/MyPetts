<?php
$error = $error ?? null;
$roles = $roles ?? [];
$usuario = $usuario ?? [];

$tituloPagina = 'Editar Usuario';
require __DIR__ . '/../partials/header.php';
?>

<a href="/usuario" class="btn-volver">
    <span>←</span> Volver a Usuarios
</a>

<h1><?= htmlspecialchars($tituloPagina) ?></h1>

<div class="tarjeta" data-etiqueta="Editar usuario">
    <?php if (!empty($error)): ?>
        <p class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="/usuario/editar/<?= (int) ($usuario['id'] ?? 0) ?>" method="POST">
        <label>Nombre:
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>
        </label>

        <label>Correo:
            <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>" required>
        </label>

        <label>Teléfono:
            <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" required>
        </label>

        <label>Rol:
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
        </label>

        <button type="submit">Guardar cambios</button>
        <a href="/usuario" class="boton inline-cancel">Cancelar</a>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>