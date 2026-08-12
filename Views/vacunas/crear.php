<?php
$error = $error ?? null;

// Valores seguros para evitar warnings si la vista se carga fuera del controlador
$mascota = $mascota ?? null;
$mascotaNombre = is_array($mascota) ? ($mascota['nombre'] ?? '') : '';
$mascotaId = is_array($mascota) ? ((int) ($mascota['id'] ?? 0)) : 0;
?>

<h1>Registrar Vacuna/Desparasitación — <?= htmlspecialchars($mascotaNombre) ?></h1>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="/vacuna/crear/<?= $mascotaId ?>" method="POST">
    <label>Tipo:<br>
        <select name="tipo" required>
            <option value="">-- Selecciona --</option>
            <option value="vacuna">Vacuna</option>
            <option value="desparasitacion">Desparasitación</option>
        </select>
    </label><br><br>

    <label>Nombre del producto:<br>
        <input type="text" name="nombre_producto" required style="width:100%;">
    </label><br><br>

    <label>Lote:<br>
        <input type="text" name="lote" required style="width:100%;">
    </label><br><br>

    <label>Fecha de aplicación:<br>
        <input type="date" name="fecha_aplicacion" required>
    </label><br><br>

    <label>Fecha de la próxima dosis:<br>
        <input type="date" name="fecha_proxima_dosis" required>
    </label><br><br>

    <button type="submit">Guardar</button>
</form>

<a href="/vacuna/verVacunas/<?= $mascotaId ?>">Cancelar y volver</a>