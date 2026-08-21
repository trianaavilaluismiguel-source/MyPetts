<?php
$registros = $registros ?? [];
$mensaje = $mensaje ?? null;
$mascota = $mascota ?? [];

$tituloPagina = 'Vacunas y Desparasitaciones';
require __DIR__ . '/../partials/header.php';
?>

<a href="/mascota" class="btn-volver"><span>←</span> Volver a Mascotas</a>

<header class="section-header">
    <div>
        <h1>Vacunas y Desparasitaciones — <?= htmlspecialchars($mascota['nombre'] ?? '') ?></h1>
    </div>
    <?php if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2): ?>
        <a href="/vacuna/mostrarCrear/<?= (int) ($mascota['id'] ?? 0) ?>" class="btn">+ Registrar vacuna</a>
    <?php endif; ?>
</header>

<?php if (!empty($mensaje)): ?>
    <div class="auth-alert auth-success"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="tarjeta" data-etiqueta="VACUNAS Y DESPARASITACIONES">
    <div class="tabla-contenedor">
        <?php if (empty($registros)): ?>
            <p class="empty-state">Esta mascota aún no tiene vacunas ni desparasitaciones registradas.</p>
        <?php else: ?>
            <table class="tabla-custom">
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
                        $etiquetaEstado = match ($registro['estado_alerta']) {
                            'vencida' => 'Vencida',
                            'proxima' => 'Próxima a vencer',
                            default   => 'Vigente',
                        };
                        $estadoClase = match ($registro['estado_alerta'] ?? 'vigente') {
                            'vencida' => 'estado-alerta--vencida',
                            'proxima' => 'estado-alerta--proxima',
                            default => 'estado-alerta--vigente',
                        };
                        ?>
                        <tr>
                            <td><?= htmlspecialchars(VacunaDesparasitacion::etiquetaTipo($registro['tipo'])) ?></td>
                            <td><?= htmlspecialchars($registro['nombre_producto']) ?></td>
                            <td><?= htmlspecialchars($registro['lote']) ?></td>
                            <td><?= htmlspecialchars($registro['fecha_aplicacion']) ?></td>
                            <td><?= htmlspecialchars($registro['fecha_proxima_dosis']) ?></td>
                            <td class="vaccine-status <?= $estadoClase ?>"><?= $etiquetaEstado ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<a href="/historial/verHistorial/<?= (int) ($mascota['id'] ?? 0) ?>">Ver historial clínico</a>

<?php require __DIR__ . '/../partials/footer.php'; ?>