<?php
$tituloPagina = 'Editar Mascota';
require __DIR__ . '/../partials/header.php';
?>

<?php
// Evitar notices si $mascota no está definido
$mascota = $mascota ?? [];
?>

<h1>Editar Mascota</h1>

<div class="tarjeta" data-etiqueta="Datos de la mascota">
    <?php if (!empty($error)): ?>
        <p class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="/mascota/editar/<?= (int) ($mascota['id'] ?? 0) ?>" method="POST">
        <label>Nombre:
            <input type="text" name="nombre" value="<?= htmlspecialchars($mascota['nombre'] ?? '') ?>" required>
        </label>

        <label>Especie:
            <input type="text" name="especie" value="<?= htmlspecialchars($mascota['especie'] ?? '') ?>" required>
        </label>

        <label>Raza:
            <input type="text" name="raza" value="<?= htmlspecialchars($mascota['raza'] ?? '') ?>" required>
        </label>

        <label>Fecha de nacimiento:
            <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($mascota['fecha_nacimiento'] ?? '') ?>" required>
        </label>

        <label>Sexo:
            <select name="sexo" required>
                <option value="Macho" <?= ($mascota['sexo'] ?? '') === 'Macho' ? 'selected' : '' ?>>Macho</option>
                <option value="Hembra" <?= ($mascota['sexo'] ?? '') === 'Hembra' ? 'selected' : '' ?>>Hembra</option>
            </select>
        </label>

        <label>Peso (kg):
            <input type="number" name="peso" step="0.01" min="0" value="<?= htmlspecialchars($mascota['peso'] ?? '') ?>" required>
        </label>

        <label>Estado de salud:
            <select name="estado_salud">
                <option value="al_dia" <?= ($mascota['estado_salud'] ?? '') === 'al_dia' ? 'selected' : '' ?>>Al día</option>
                <option value="pendiente_atencion" <?= ($mascota['estado_salud'] ?? '') === 'pendiente_atencion' ? 'selected' : '' ?>>Pendiente de atención</option>
            </select>
        </label>

        <button type="submit">Guardar cambios</button>
        <a href="/mascota" class="boton inline-cancel">Cancelar</a>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>