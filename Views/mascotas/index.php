<?php
$esDueno = ($_SESSION['rol_id'] ?? null) == 4;
$tituloPagina = $esDueno ? 'Mis Mascotas' : 'Mascotas Registradas';
require __DIR__ . '/../partials/header.php';
?>

<a href="/dashboard" class="btn-volver">
    <span>←</span> Volver al Dashboard
</a>

<header class="section-header">
    <div>
        <h1><?= $esDueno ? 'Mis Mascotas' : 'Mascotas Registradas' ?></h1>
        <p><?= $esDueno ? 'Administra la información y el historial de tus peluditos.' : 'Administra la información y el historial clínico de las mascotas de la clínica.' ?></p>
    </div>
    <a href="/mascota/mostrarCrear" class="btn">+ Nueva Mascota</a>
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

<div class="tarjeta" data-etiqueta="MASCOTAS REGISTRADAS">

    <!-- Buscador -->
    <div class="toolbar-row">
        <form action="/mascota" method="GET" class="search-box">
            <input type="text" name="busqueda" class="input-search" placeholder="Buscar por nombre o especie..." value="<?= htmlspecialchars($termino ?? '') ?>">
            <button type="submit" class="btn">Buscar</button>
        </form>
    </div>

    <!-- Tabla de Mascotas -->
    <div class="tabla-contenedor">
        <?php if (!empty($mascotas)): ?>
            <table class="tabla-custom">
                <thead>
                    <tr>
                        <th>N° Registro</th>
                        <th>Nombre</th>
                        <th>Especie</th>
                        <th>Raza</th>
                        <th>Edad</th>
                        <th>Sexo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mascotas as $m): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($m['id']) ?></td>
                            <td>
                                <strong class="pet-name">
                                    <?= (strtolower($m['especie'] ?? '') == 'gato') ? '🐱' : ((strtolower($m['especie'] ?? '') == 'pajaro') ? '🦜' : '🐶') ?>
                                    <?= htmlspecialchars($m['nombre']) ?>
                                </strong>
                            </td>
                            <td><?= htmlspecialchars($m['especie']) ?></td>
                            <td><?= htmlspecialchars($m['raza'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars(Mascota::calcularEdad($m['fecha_nacimiento'])) ?></td>
                            <td><?= htmlspecialchars($m['sexo'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge-estado">
                                    <?= htmlspecialchars(Mascota::etiquetaEstadoSalud($m['estado_salud'] ?? 'al_dia')) ?>
                                </span>
                            </td>
                            <td>
                                <div class="acciones-cell">
                                    <a href="/mascota/editar/<?= (int) $m['id'] ?>" class="btn-editar">Editar</a>
                                    <a href="/mascota/darDeBaja/<?= (int) $m['id'] ?>" class="btn-baja" onclick="return confirm('¿Seguro que deseas dar de baja a esta mascota?');">Dar de baja</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-state">No se encontraron mascotas registradas.</p>
        <?php endif; ?>
    </div>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>