<?php
$error = $error ?? null;
$roles = $roles ?? [];

$tituloPagina = 'Nuevo Usuario';
require __DIR__ . '/../partials/header.php';
?>

<a href="/usuario" class="btn-volver">
    <span>←</span> Volver a Usuarios
</a>

<h1><?= htmlspecialchars($tituloPagina) ?></h1>

<div class="tarjeta" data-etiqueta="Nuevo usuario">
    <?php if (!empty($error)): ?>
        <p class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="/usuario/crear" method="POST">
        <label>Nombre:
            <input type="text" name="nombre" required>
        </label>

        <label>Correo:
            <input type="email" name="correo" required>
        </label>

        <label>Teléfono:
            <input type="text" name="telefono" required>
        </label>

        <label>Rol:
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
        </label>

        <p><em>Se generará una contraseña temporal automáticamente, que se mostrará al guardar.</em></p>

        <button type="submit">Crear usuario</button>
        <a href="/usuario" class="boton inline-cancel">Cancelar</a>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>