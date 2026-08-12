<?php
// rol_id: 1 = Administrador, 2 = Veterinario, 3 = Recepcionista, 4 = DueñoMascota
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Preferir valores pasados por la vista, si no existen usar la sesión
$rolId = $rolId ?? ($_SESSION['rol_id'] ?? null);
$nombre = $nombre ?? ($_SESSION['nombre'] ?? 'Usuario');
$esAdministrador = ($rolId !== null) && ((int) $rolId === 1);

$tituloPagina = 'Inicio';
require __DIR__ . '/../partials/header.php';
?>

<h1>Bienvenido, <?= htmlspecialchars($nombre) ?></h1>

<?php
// Variables por defecto para evitar notices
$mascotas = $mascotas ?? [];
$citasProximas = $citasProximas ?? [];
$totalMascotas = $totalMascotas ?? 0;
$totalUsuarios = $totalUsuarios ?? 0;
?>

<?php if ($rolId == 4): // DueñoMascota ?>

    <div class="tarjeta" data-etiqueta="Tus mascotas">
        <?php if (empty($mascotas)): ?>
        <p>Aún no tienes mascotas registradas. <a href="/mascota/mostrarCrear">Agrega una aquí</a>.</p>
        <?php else: ?>
        <ul>
            <?php foreach ($mascotas as $mascota): ?>
            <li><?= htmlspecialchars($mascota['nombre']) ?> (<?= htmlspecialchars($mascota['especie']) ?>)
                — <a href="/historial/verHistorial/<?= (int) $mascota['id'] ?>">Ver historial</a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <a href="/mascota">Ver todas mis mascotas</a>
    </div>

    <div class="tarjeta" data-etiqueta="Próximas citas">
        <?php if (empty($citasProximas)): ?>
        <p>No tienes citas próximas. <a href="/cita/mostrarCrear">Agenda una aquí</a>.</p>
        <?php else: ?>
        <ul>
            <?php foreach ($citasProximas as $cita): ?>
            <li><?= htmlspecialchars($cita['fecha']) ?> a las <?= htmlspecialchars(substr($cita['hora'], 0, 5)) ?>
                — <?= htmlspecialchars($cita['nombre_mascota']) ?> con Dr(a). <?= htmlspecialchars($cita['nombre_veterinario']) ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <a href="/cita">Ver todas mis citas</a>
    </div>

<?php elseif ($rolId == 2): // Veterinario ?>

    <div class="tarjeta" data-etiqueta="Tu agenda">
        <?php if (empty($citasProximas)): ?>
        <p>No tienes citas próximas asignadas.</p>
        <?php else: ?>
        <ul>
            <?php foreach ($citasProximas as $cita): ?>
            <li><?= htmlspecialchars($cita['fecha']) ?> a las <?= htmlspecialchars(substr($cita['hora'], 0, 5)) ?>
                — <?= htmlspecialchars($cita['nombre_mascota']) ?> (dueño: <?= htmlspecialchars($cita['nombre_dueno']) ?>)
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <a href="/cita">Ver toda mi agenda</a>
    </div>

<?php elseif ($rolId == 3 || $rolId == 1): // Recepcionista o Administrador ?>

    <div class="tarjeta" data-etiqueta="Resumen">
        <p><strong><?= $totalMascotas ?></strong> mascotas activas reg
istradas</p>
        <?php if ($esAdministrador): ?>
        <p><strong><?= $totalUsuarios ?></strong> usuarios en el sistema — <a href="/usuario">gestionar</a></p>
        <?php endif; ?>
    </div>

    <div class="tarjeta" data-etiqueta="Agenda del día">
        <?php if (empty($citasProximas)): ?>
        <p>No hay citas próximas agendadas.</p>
        <?php else: ?>
        <ul>
            <?php foreach ($citasProximas as $cita): ?>
            <li><?= htmlspecialchars($cita['fecha']) ?> a las <?= htmlspecialchars(substr($cita['hora'], 0, 5)) ?>
                — <?= htmlspecialchars($cita['nombre_mascota']) ?> con Dr(a). <?= htmlspecialchars($cita['nombre_veterinario']) ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <a href="/cita">Ver toda la agenda</a>
    </div>

<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>