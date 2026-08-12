<?php
$mascota = $mascota ?? ['id' => 0, 'nombre' => ''];
$error = $error ?? null;
?>

<h1>Registrar Entrada Clínica — <?= htmlspecialchars($mascota['nombre']) ?></h1>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="/historial/crear/<?= (int) $mascota['id'] ?>" method="POST">
    <label>Motivo de consulta:<br>
        <input type="text" name="motivo_consulta" required style="width:100%;">
    </label><br><br>

    <label>Diagnóstico:<br>
        <textarea name="diagnostico" rows="4" required style="width:100%;"></textarea>
    </label><br><br>

    <label>Tratamiento:<br>
        <textarea name="tratamiento" rows="4" required style="width:100%;"></textarea>
    </label><br><br>

    <label>Observaciones (opcional):<br>
        <textarea name="observaciones" rows="3" style="width:100%;"></textarea>
    </label><br><br>

    <button type="submit">Guardar</button>
</form>

<a href="/historial/verHistorial/<?= (int) $mascota['id'] ?>">Cancelar y volver</a>