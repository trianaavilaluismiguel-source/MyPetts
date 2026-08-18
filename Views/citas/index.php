<?php
$tituloPagina = 'Mis Citas';
require __DIR__ . '/../partials/header.php';
?>

<h1>Mis Citas</h1>

<?php if (!empty($mensaje)): ?>
    <p class="mensaje mensaje-exito"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<div class="tarjeta" data-etiqueta="Citas">
    <a href="/cita/mostrarCrear" class="boton" style="margin-bottom:18px; display:inline-block;">Nueva Cita</a>

    <?php if (empty($citas)): ?>
        <p>No tienes citas programadas. Agenda una desde el botón "Nueva Cita".</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Mascota</th>
                    <th>Tipo de consulta</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citas as $cita): ?>
                    <tr>
                        <td data-etiqueta="Fecha"><?= htmlspecialchars($cita['fecha']) ?></td>
                        <td data-etiqueta="Hora"><?= htmlspecialchars(substr($cita['hora'], 0, 5)) ?></td>
                        <td data-etiqueta="Mascota"><?= htmlspecialchars($cita['nombre_mascota']) ?></td>
                        <td data-etiqueta="Tipo"><?= htmlspecialchars($cita['tipo_consulta']) ?></td>
                        <td data-etiqueta="Estado"><?= htmlspecialchars(ucfirst($cita['estado'])) ?></td>
                        <td class="acciones" data-etiqueta="Acciones">
                            <?php if ($cita['estado'] === 'agendada'): ?>
                                <a href="/cita/mostrarReagendar/<?= $cita['id'] ?>">Reagendar</a>
                                <a href="/cita/cancelar/<?= $cita['id'] ?>"
                                   onclick="return confirm('¿Seguro que deseas cancelar esta cita?')">
                                   Cancelar
                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>