<?php
$mascota = $mascota ?? [
    'id' => 0,
    'nombre' => '',
    'especie' => '',
    'raza' => '',
    'fecha_nacimiento' => '',
    'sexo' => '',
    'peso' => '',
    'estado_salud' => 'al_dia',
];
$mascota['nombre'] = $mascota['nombre'] ?? '';
$mascota['especie'] = $mascota['especie'] ?? '';
$mascota['raza'] = $mascota['raza'] ?? '';
$mascota['fecha_nacimiento'] = $mascota['fecha_nacimiento'] ?? '';
$mascota['sexo'] = $mascota['sexo'] ?? '';
$mascota['peso'] = $mascota['peso'] ?? '';
$mascota['estado_salud'] = $mascota['estado_salud'] ?? 'al_dia';
$error = $error ?? null;
?>

<h1>Editar Mascota</h1>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="/mascota/editar/<?= (int) ($mascota['id'] ?? 0) ?>" method="POST">
    <label>Nombre:
        <input type="text" name="nombre" value="<?= htmlspecialchars($mascota['nombre']) ?>" required>
    </label><br>

    <label>Especie:
        <input type="text" name="especie" value="<?= htmlspecialchars($mascota['especie']) ?>" required>
    </label><br>

    <label>Raza:
        <input type="text" name="raza" value="<?= htmlspecialchars($mascota['raza']) ?>">
    </label><br>

    <label>Fecha de nacimiento:
        <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($mascota['fecha_nacimiento']) ?>" required>
    </label><br>

    <label>Sexo:
        <select name="sexo" required>
            <option value="Macho" <?= ($mascota['sexo'] ?? '') === 'Macho' ? 'selected' : '' ?>>Macho</option>
            <option value="Hembra" <?= ($mascota['sexo'] ?? '') === 'Hembra' ? 'selected' : '' ?>>Hembra</option>
        </select>
    </label><br>

    <label>Peso (kg):
        <input type="number" name="peso" step="0.01" min="0" value="<?= htmlspecialchars($mascota['peso']) ?>">
    </label><br>

    <label>Estado de salud:
        <select name="estado_salud">
            <option value="al_dia" <?= ($mascota['estado_salud'] ?? 'al_dia') === 'al_dia' ? 'selected' : '' ?>>Al día</option>
            <option value="pendiente_atencion" <?= ($mascota['estado_salud'] ?? 'al_dia') === 'pendiente_atencion' ? 'selected' : '' ?>>Pendiente de atención</option>
        </select>
    </label><br>

    <button type="submit">Guardar cambios</button>
</form>

<a href="/mascota">Volver a la lista</a>