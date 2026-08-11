<?php $titulo = 'Mis Citas'; ?>

<h1><?= $titulo ?></h1>

<?php if (!empty($mensaje)): ?>
    <p style="color:green;"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<a href="/cita/mostrarCrear">Nueva Cita</a>

<?php if (empty($citas)): ?>
    <p>No tienes citas programadas. Agenda una desde el menú "Nueva Cita".</p>
<?php else: ?>
    <table border="1" cellpadding="8">
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
                    <td><?= htmlspecialchars($cita['fecha']) ?></td>
                    <td><?= htmlspecialchars(substr($cita['hora'], 0, 5)) ?></td>
                    <td><?= htmlspecialchars($cita['mascota_nombre']) ?></td>
                    <td><?= htmlspecialchars($cita['tipo_consulta']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($cita['estado'])) ?></td>
                    <td>
                        <?php if ($cita['estado'] === 'agendada'): ?>
                            <a href="/cita/mostrarReagendar/<?= $cita['id'] ?>">Reagendar</a>
                            |
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