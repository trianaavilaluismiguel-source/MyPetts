<?php
$mensaje = $mensaje ?? null;
$termino = $termino ?? '';
?>

<h1>Mis Mascotas</h1>

<?php if (!empty($mensaje)): ?>
    <p style="color:green;"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<a href="/mascota/mostrarCrear">Nueva Mascota</a>

<form action="/mascota/buscar" method="GET">
    <input type="text" name="q" placeholder="Buscar por nombre, especie o N° de registro" value="<?= htmlspecialchars($termino) ?>">
    <button type="submit">Buscar</button>
</form>

<?php if (empty($mascotas)): ?>
    <p>No tienes mascotas registradas. Agrega una desde el botón "Nueva Mascota".</p>
<?php else: ?>
    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>N° Registro</th>
                <th>Nombre</th>
                <th>Especie</th>
                <th>Raza</th>
                <th>Edad</th>
                <th>Sexo</th>
                <th>Estado de salud</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mascotas as $mascota): ?>
                <tr>
                    <td><?= (int) ($mascota['id'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($mascota['nombre'] ?? '') ?></td>
                    <td><?= htmlspecialchars($mascota['especie'] ?? '') ?></td>
                    <td><?= htmlspecialchars($mascota['raza'] ?? '') ?></td>
                    <td><?= isset($mascota['fecha_nacimiento']) ? Mascota::calcularEdad($mascota['fecha_nacimiento']) : 'N/A' ?></td>
                    <td><?= htmlspecialchars($mascota['sexo'] ?? '') ?></td>
                    <td><?= Mascota::etiquetaEstadoSalud($mascota['estado_salud'] ?? 'al_dia') ?></td>
                    <td>
                        <a href="/historial/verHistorial/<?= (int) ($mascota['id'] ?? 0) ?>">Ver historial</a>
                        |
                        <a href="/mascota/mostrarEditar/<?= (int) ($mascota['id'] ?? 0) ?>">Editar</a>
                        |
                        <a href="/mascota/eliminar/<?= (int) ($mascota['id'] ?? 0) ?>"
                           onclick="return confirm('¿Seguro que deseas dar de baja esta mascota?')">
                           Dar de baja
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>