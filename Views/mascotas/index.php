<?php
$tituloPagina = 'Mis Mascotas';
require __DIR__ . '/../partials/header.php';
?>

<h1>Mis Mascotas</h1>

<?php if (!empty($mensaje)): ?>
    <p class="mensaje mensaje-exito"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<div class="tarjeta" data-etiqueta="Mascotas registradas">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
        <a href="/mascota/mostrarCrear" class="boton">Nueva Mascota</a>

        <form action="/mascota/buscar" method="GET" style="display:flex; gap:8px; margin:0;">
            <input type="text" name="q" placeholder="Buscar por nombre, especie o N° de registro" value="<?= htmlspecialchars($termino ?? '') ?>" style="margin:0;">
            <button type="submit">Buscar</button>
        </form>
    </div>

    <?php if (empty($mascotas)): ?>
        <p>No tienes mascotas registradas. Agrega una desde el botón "Nueva Mascota".</p>
    <?php else: ?>
        <table>
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
                        <td data-etiqueta="N° Registro"><?= $mascota['id'] ?></td>
                        <td data-etiqueta="Nombre"><?= htmlspecialchars($mascota['nombre']) ?></td>
                        <td data-etiqueta="Especie"><?= htmlspecialchars($mascota['especie']) ?></td>
                        <td data-etiqueta="Raza"><?= htmlspecialchars($mascota['raza'] ?? '') ?></td>
                        <td data-etiqueta="Edad"><?= Mascota::calcularEdad($mascota['fecha_nacimiento']) ?></td>
                        <td data-etiqueta="Sexo"><?= htmlspecialchars($mascota['sexo']) ?></td>
                        <td data-etiqueta="Estado de salud"><?= Mascota::etiquetaEstadoSalud($mascota['estado_salud']) ?></td>
                        <td class="acciones" data-etiqueta="Acciones">
                            <a href="/mascota/mostrarEditar/<?= $mascota['id'] ?>">Editar</a>
                            <a href="/mascota/eliminar/<?= $mascota['id'] ?>"
                               onclick="return confirm('¿Seguro que deseas dar de baja esta mascota?')">
                               Dar de baja
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>