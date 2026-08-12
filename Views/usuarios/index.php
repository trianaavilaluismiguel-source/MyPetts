<?php $titulo = 'Gestión de Usuarios'; ?>

<h1><?= $titulo ?></h1>

<?php if (!empty($mensaje)): ?>
    <p style="color:green;"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<a href="/usuario/mostrarCrear">Nuevo Usuario</a>

<?php if (empty($usuarios)): ?>
    <p>No hay usuarios registrados.</p>
<?php else: ?>
    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['correo']) ?></td>
                    <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                    <td><?= htmlspecialchars($usuario['nombre_rol']) ?></td>
                    <td><?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?></td>
                    <td>
                        <a href="/usuario/mostrarEditar/<?= (int) $usuario['id'] ?>">Editar</a>
                        |
                        <a href="/usuario/resetearContrasena/<?= (int) $usuario['id'] ?>"
                            onclick="return confirm('¿Restablecer la contraseña de este usuario?')">
                            Resetear contraseña
                        </a>
                        |
                        <a href="/usuario/cambiarEstado/<?= (int) $usuario['id'] ?>"
                            onclick="return confirm('¿Cambiar el estado de este usuario?')">
                            <?= $usuario['activo'] ? 'Desactivar' : 'Activar' ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>