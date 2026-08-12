<?php
$mascota = $mascota ?? ['id' => 0, 'nombre' => ''];
$entradas = $entradas ?? [];
$mensaje = $mensaje ?? null;
$rolActual = $_SESSION['rol_id'] ?? null;

$tituloPagina = 'Historial Clínico';
require __DIR__ . '/../partials/header.php';
?>

<h1>Historial Clínico de <?= htmlspecialchars($mascota['nombre']) ?></h1>

<?php if (!empty($mensaje)): ?>
    <p class="mensaje mensaje-exito"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<div class="tarjeta" data-etiqueta="Entradas registradas">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
        <a href="/mascota">← Volver a mascotas</a>
        <?php if ((int) ($rolActual ?? 0) === 2): ?>
            <a href="/historial/mostrarCrear/<?= (int) $mascota['id'] ?>" class="boton">Registrar nueva entrada</a>
        <?php endif; ?>
    </div>

    <?php if (empty($entradas)): ?>
        <!-- HU-05 Esc.3: sin historial previo -->
        <p>Esta mascota aún no tiene historial clínico registrado.</p>
    <?php else: ?>
        <?php foreach ($entradas as $entrada): ?>
            <div style="border:1px solid var(--color-borde); border-radius:var(--radio); padding:16px; margin-bottom:14px;">
                <p><strong>Fecha:</strong> <?= htmlspecialchars($entrada['fecha_registro']) ?></p>
                <p><strong>Veterinario:</strong> <?= htmlspecialchars($entrada['nombre_veterinario']) ?></p>
                <p><strong>Motivo de consulta:</strong> <?= htmlspecialchars($entrada['motivo_consulta']) ?></p>
                <p><strong>Diagnóstico:</strong> <?= htmlspecialchars($entrada['diagnostico']) ?></p>
                <p><strong>Tratamiento:</strong> <?= htmlspecialchars($entrada['tratamiento']) ?></p>
                <?php if (!empty($entrada['observaciones'])): ?>
                    <p><strong>Observaciones:</strong> <?= htmlspecialchars($entrada['observaciones']) ?></p>
                <?php endif; ?>
                <?php if (!empty($entrada['fecha_ultima_edicion'])): ?>
                    <p><em>Editado el <?= htmlspecialchars($entrada['fecha_ultima_edicion']) ?></em></p>
                <?php endif; ?>

                <?php if ((int) ($rolActual ?? 0) === 2): ?>
                    <a href="/historial/mostrarEditar/<?= (int) $entrada['id'] ?>">Editar</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>