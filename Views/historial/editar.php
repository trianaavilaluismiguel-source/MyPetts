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

$tituloPagina = 'Editar Entrada Clínica';
require __DIR__ . '/../partials/header.php';
?>

<h1>Editar Entrada Clínica</h1>

<div class="tarjeta" data-etiqueta="Entrada clínica">
    <p><em>Solo puedes editar dentro de las 24 horas siguientes al registro.</em></p>

    <?php if (!empty($error)): ?>
        <p class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="/historial/editar/<?= (int) ($entrada['id'] ?? 0) ?>" method="POST">
        <label>Motivo de consulta:
            <input type="text" name="motivo_consulta" value="<?= htmlspecialchars($entrada['motivo_consulta'] ?? '') ?>" required>
        </label>

        <label>Diagnóstico:
            <textarea name="diagnostico" rows="4" required><?= htmlspecialchars($entrada['diagnostico'] ?? '') ?></textarea>
        </label>

        <label>Tratamiento:
            <textarea name="tratamiento" rows="4" required><?= htmlspecialchars($entrada['tratamiento'] ?? '') ?></textarea>
        </label>

        <label>Observaciones (opcional):
            <textarea name="observaciones" rows="3"><?= htmlspecialchars($entrada['observaciones'] ?? '') ?></textarea>
        </label>

        <button type="submit">Guardar cambios</button>
        <a href="/historial/verHistorial/<?= (int) ($entrada['mascota_id'] ?? 0) ?>" class="boton" style="background:var(--color-borde); color:var(--color-tinta);">Cancelar</a>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>