<?php
$error = $error ?? null;
$mascota = $mascota ?? null;
$mascotaNombre = is_array($mascota) ? ($mascota['nombre'] ?? '') : '';
$mascotaId = is_array($mascota) ? ((int) ($mascota['id'] ?? 0)) : 0;

$tituloPagina = 'Registrar Vacuna';
require __DIR__ . '/../partials/header.php';
?>

<a href="/vacuna/verVacunas/<?= $mascotaId ?>" class="btn-volver"><span>←</span> Volver</a>

<header class="section-header">
    <div>
        <h1>Registrar Vacuna/Desparasitación — <?= htmlspecialchars($mascotaNombre) ?></h1>
    </div>
</header>

<?php if (!empty($error)): ?>
    <div class="auth-alert"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="tarjeta form-container" data-etiqueta="NUEVO REGISTRO">
    <form action="/vacuna/crear/<?= $mascotaId ?>" method="POST">
        <div class="form-grid">
            <div>
                <label class="campo-label" for="tipo">Tipo *</label>
                <select id="tipo" name="tipo" class="select-custom" required>
                    <option value="">-- Selecciona --</option>
                    <option value="vacuna">Vacuna</option>
                    <option value="desparasitacion">Desparasitación</option>
                </select>
            </div>

            <div>
                <label class="campo-label" for="nombre_producto">Nombre del producto *</label>
                <input type="text" id="nombre_producto" name="nombre_producto" class="input-custom" required>
            </div>

            <div>
                <label class="campo-label" for="lote">Lote *</label>
                <input type="text" id="lote" name="lote" class="input-custom" required>
            </div>

            <div>
                <label class="campo-label" for="fecha_aplicacion">Fecha de aplicación *</label>
                <input type="date" id="fecha_aplicacion" name="fecha_aplicacion" class="input-custom" required>
            </div>

            <div class="form-group-full">
                <label class="campo-label" for="fecha_proxima_dosis">Fecha de la próxima dosis *</label>
                <input type="date" id="fecha_proxima_dosis" name="fecha_proxima_dosis" class="input-custom" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Guardar</button>
            <a href="/vacuna/verVacunas/<?= $mascotaId ?>" class="btn-cancelar">Cancelar</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>