<?php
$entrada = $entrada ?? [
    'id' => 0,
    'motivo_consulta' => '',
    'diagnostico' => '',
    'tratamiento' => '',
    'observaciones' => '',
    'mascota_id' => 0,
];
$entrada['motivo_consulta'] = $entrada['motivo_consulta'] ?? '';
$entrada['diagnostico'] = $entrada['diagnostico'] ?? '';
$entrada['tratamiento'] = $entrada['tratamiento'] ?? '';
$entrada['observaciones'] = $entrada['observaciones'] ?? '';
$entrada['mascota_id'] = $entrada['mascota_id'] ?? 0;
$error = $error ?? null;
?>

<h1>Editar Entrada Clínica</h1>

<p><em>Solo puedes editar dentro de las 24 horas siguientes al registro.</em></p>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="/historial/editar/<?= (int) ($entrada['id'] ?? 0) ?>" method="POST">
    <label>Motivo de consulta:<br>
        <input type="text" name="motivo_consulta" value="<?= htmlspecialchars($entrada['motivo_consulta'] ?? '') ?>" required style="width:100%;">
    </label><br><br>

    <label>Diagnóstico:<br>
        <textarea name="diagnostico" rows="4" required style="width:100%;"><?= htmlspecialchars($entrada['diagnostico'] ?? '') ?></textarea>
    </label><br><br>

    <label>Tratamiento:<br>
        <textarea name="tratamiento" rows="4" required style="width:100%;"><?= htmlspecialchars($entrada['tratamiento'] ?? '') ?></textarea>
    </label><br><br>

    <label>Observaciones (opcional):<br>
        <textarea name="observaciones" rows="3" style="width:100%;"><?= htmlspecialchars($entrada['observaciones'] ?? '') ?></textarea>
    </label><br><br>

    <button type="submit">Guardar cambios</button>
</form>

<a href="/historial/verHistorial/<?= (int) ($entrada['mascota_id'] ?? 0) ?>">Cancelar y volver</a>