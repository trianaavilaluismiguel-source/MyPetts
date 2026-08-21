<?php
$esDueno = ($_SESSION['rol_id'] ?? null) == 4;
$tituloPagina = $esDueno ? 'Mis Citas' : 'Citas de la Clínica';
require __DIR__ . '/../partials/header.php';
?>

<a href="/dashboard" class="btn-volver">
    <span>←</span> Volver al Dashboard
</a>

<header class="section-header">
    <div>
        <h1><?= $esDueno ? 'Mis Citas' : 'Citas de la Clínica' ?></h1>
        <p><?= $esDueno ? 'Revisa el historial y la programación de tus consultas médicas.' : 'Revisa el historial y la programación de las consultas médicas.' ?></p>
    </div>
    <a href="/cita/mostrarCrear" class="btn">+ Nueva Cita</a>
</header>

<?php if (!empty($mensaje)): ?>
    <div class="auth-alert auth-success">
        <?= htmlspecialchars($mensaje) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="auth-alert">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="tarjeta" data-etiqueta="HISTORIAL DE CITAS">

    <div class="tabla-contenedor">
        <?php if (!empty($citas)): ?>
            <table class="tabla-custom">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Mascota</th>
                        <th>Tipo de Consulta</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $c): ?>
                        <?php
                            $estado = strtolower($c['estado'] ?? 'agendada');
                            $badgeClass = 'badge-agendada';
                            if (str_contains($estado, 'cancel')) $badgeClass = 'badge-cancelada';
                            if (str_contains($estado, 'complet') || str_contains($estado, 'atendida')) $badgeClass = 'badge-completada';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['fecha']))) ?></td>
                            <td><strong><?= htmlspecialchars(date('h:i A', strtotime($c['hora']))) ?></strong></td>
                            <td><strong><?= htmlspecialchars($c['nombre_mascota'] ?? 'Mascota') ?></strong></td>
                            <td><?= htmlspecialchars($c['tipo_consulta'] ?? 'Consulta general') ?></td>
                            <td>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars(ucfirst($c['estado'] ?? 'Agendada')) ?>
                                </span>
                            </td>
                            <td>
                                <div class="acciones-cell">
                                    <?php if (!str_contains($estado, 'cancel')): ?>
                                        <a href="/cita/mostrarReagendar/<?= (int) $c['id'] ?>" class="btn-reagendar">Reagendar</a>
                                        <a href="/cita/cancelar/<?= (int) $c['id'] ?>" class="btn-cancelar" onclick="return confirm('¿Seguro que deseas cancelar esta cita?');">Cancelar</a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-state">No tienes citas registradas en el sistema.</p>
        <?php endif; ?>
    </div>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>