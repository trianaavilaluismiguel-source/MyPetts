<?php
$registros = $registros ?? [];
$mensaje = $mensaje ?? null;
$mascota = $mascota ?? [];
?>

<h1>Vacunas y Desparasitaciones — <?= htmlspecialchars($mascota['nombre'] ?? '') ?></h1>

<?php if (!empty($mensaje)): ?>
    <p style="color:green;"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<?php if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2): ?>
    <a href="/vacuna/mostrarCrear/<?= (int) ($mascota['id'] ?? 0) ?>">Registrar vacuna/desparasitación</a>
<?php endif; ?>

<?php if (empty($registros)): ?>
    <p>Esta mascota aún no tiene vacunas ni desparasitaciones registradas.</p>
<?php else: ?>
    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Producto</th>
                <th>Lote</th>
                <th>Fecha aplicación</th>
                <th>Próxima dosis</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registros as $registro): ?>
                <?php
                $color = VacunaDesparasitacion::colorAlerta($registro['estado_alerta']);
                $etiquetaEstado = match ($registro['estado_alerta']) {
                    'vencida' => 'Vencida',
                    'proxima' => 'Próxima a vencer',
                    default   => 'Vigente',
                };
                ?>
                <tr>
                    <td><?= htmlspecialchars(VacunaDesparasitacion::etiquetaTipo($registro['tipo'])) ?></td>
                    <td><?= htmlspecialchars($registro['nombre_producto']) ?></td>
                    <td><?= htmlspecialchars($registro['lote']) ?></td>
                    <td><?= htmlspecialchars($registro['fecha_aplicacion']) ?></td>
                    <td><?= htmlspecialchars($registro['fecha_proxima_dosis']) ?></td>
                    <td style="color:<?= $color ?>; font-weight:bold;"><?= $etiquetaEstado ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="/historial/verHistorial/<?= (int) ($mascota['id'] ?? 0) ?>">Ver historial clínico</a>
&nbsp;|&nbsp;
<a href="/mascota">Volver a mascotas</a>